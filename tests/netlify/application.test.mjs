import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile, readdir } from 'node:fs/promises';
import { randomBytes } from 'node:crypto';
import { once } from 'node:events';
import { PGlite } from '@electric-sql/pglite';
import { drizzle } from 'drizzle-orm/pglite';
import { eq } from 'drizzle-orm';
import * as schema from '../../db/schema.ts';
import { createApp } from '../../netlify/server/app.mjs';

// Ephemeral PostgreSQL is used only for tests. Production uses Netlify Database.
test('Netlify application: authentication, permissions, inventory and approval transactions', async t => {
  const pg = new PGlite();
  for(const folder of (await readdir(new URL('../../netlify/database/migrations/',import.meta.url))).sort()){
    if(folder.endsWith('.json')) continue;
    await pg.exec(await readFile(new URL(`../../netlify/database/migrations/${folder}/migration.sql`,import.meta.url),'utf8'));
  }
  const db=drizzle({client:pg,schema});
  const password=randomBytes(24).toString('hex');
  let configuredPassword=password;
  const app=createApp(db,{secure:false,bootstrapPassword:()=>configuredPassword});
  const server=app.listen(0,'127.0.0.1');
  await once(server,'listening');
  const origin=`http://127.0.0.1:${server.address().port}`;
  t.after(async()=>{await new Promise(resolve=>server.close(resolve));await pg.close();});
  function browser(){
    const cookies=new Map();
    return {async request(path,values,options={}){
      const headers={cookie:[...cookies].map(([k,v])=>`${k}=${v}`).join('; '),...options.headers};
      let body;
      if(values){headers.origin=options.origin||origin;headers['content-type']='application/x-www-form-urlencoded';body=new URLSearchParams({_csrf:cookies.get('ct_csrf')||'',...values});}
      const response=await fetch(origin+path,{method:values?'POST':'GET',headers,body,redirect:'manual'});
      for(const raw of response.headers.getSetCookie()){
        const pair=raw.split(';')[0];const i=pair.indexOf('=');cookies.set(pair.slice(0,i),pair.slice(i+1));
      }
      return {status:response.status,location:response.headers.get('location'),html:await response.text()};
    }};
  }
  const admin=browser(),member=browser(),reviewer=browser(),otherReviewer=browser(),otherMember=browser(),guest=browser();
  const part={drawingNoSupplier:'DRAW-17',name:'Insulator <special>',sizeType:'M12',qty:'5',subAssyDrawingUnit:'Unit A',location:'Rack B'};
  async function addUser(username,role,npk){
    const result=await admin.request('/admin/users',{name:username,username,npk,email:'',role,password,password_confirmation:password});
    assert.equal(result.status,303,`create ${role} account`);
    return (await db.select().from(schema.users).where(eq(schema.users.username,username)))[0];
  }
  async function login(client,username){await client.request('/login');assert.equal((await client.request('/login',{username,password})).status,303);}
  await t.test('missing or invalid bootstrap configuration explains setup failure without leaking secrets',async()=>{
    configuredPassword=undefined;
    const unavailable=await guest.request('/setup');
    assert.equal(unavailable.status,503);
    assert.ok(unavailable.html.includes('belum tersedia di server'));
    const missingPost=await guest.request('/setup',{name:'Administrator',username:'owner',npk:'A01',password});
    assert.equal(missingPost.status,503);
    assert.ok(missingPost.html.includes('belum tersedia di server'));
    configuredPassword=password.slice(0,8);
    const invalid=await guest.request('/setup',{name:'Administrator',username:'owner',npk:'A01',password});
    assert.equal(invalid.status,503);
    assert.ok(invalid.html.includes('12–128 karakter'));
    assert.equal(invalid.html.includes(configuredPassword),false);
    assert.equal((await db.select().from(schema.users)).length,0);
    configuredPassword=password;
  });
  await t.test('root and protected deep links route to login; setup is protected',async()=>{
    assert.equal((await guest.request('/')).location,'/login');
    assert.equal((await guest.request('/member/tickets')).location,'/login');
    assert.equal((await admin.request('/login')).location,'/setup');
    assert.equal((await admin.request('/setup')).status,200);
    assert.equal((await admin.request('/setup',{name:'Administrator',username:'owner',npk:'A01',password},{origin:'https://untrusted.invalid'})).status,403);
    assert.equal((await admin.request('/setup',{name:'Administrator',username:'owner',npk:'A01',password})).location,'/admin/dashboard');
    assert.equal((await admin.request('/setup',{name:'Other',username:'other',npk:'A02',password})).status,409);
    assert.equal((await admin.request('/admin/dashboard')).status,200);
  });
  let m,a,a2,m2,p;
  await t.test('admin can manage accounts; role and CSRF checks are enforced',async()=>{
    m=await addUser('member','member','M01');a=await addUser('approver','approver','P01');
    a2=await addUser('approver-two','approver','P02');m2=await addUser('member-two','member','M02');
    await login(member,m.username);await login(reviewer,a.username);await login(otherReviewer,a2.username);await login(otherMember,m2.username);
    assert.equal((await member.request('/admin/users')).status,403);
    assert.equal((await reviewer.request('/admin/partlists')).status,403);
    assert.equal((await member.request('/admin/users',{name:'unauthorized'})).status,403);
    assert.equal((await admin.request('/admin/partlists',{...part,_csrf:''})).status,403);
    assert.equal((await admin.request('/admin/users/1',{name:'Administrator',username:'owner',npk:'A01',email:'',role:'member',password:'',password_confirmation:''})).status,409);
    assert.equal((await admin.request('/admin/users/1/delete',{})).status,409);
  });
  await t.test('inventory CRUD validates quantities, uniqueness and approver limitations',async()=>{
    assert.equal((await admin.request('/admin/partlists',{...part,qty:'-1'})).status,422);
    assert.equal((await admin.request('/admin/partlists',part)).status,303);
    assert.equal((await admin.request('/admin/partlists',part)).status,409);
    p=(await db.select().from(schema.partlists))[0];
    const html=(await admin.request('/admin/partlists')).html;
    assert.ok(html.includes('Insulator &lt;special&gt;'));
    assert.equal((await reviewer.request('/approver/add-partlist',{...part,drawingNoSupplier:'DRAW-18',qty:'2'})).status,422);
    assert.equal((await reviewer.request('/approver/add-partlist',{...part,drawingNoSupplier:'DRAW-18',qty:'1'})).status,303);
    assert.equal((await admin.request(`/admin/partlists/${p.id}`,{...part,qty:'5'})).status,303);
    assert.equal((await member.request('/member/tickets/create')).status,200);
  });
  const ticketInput=()=>({partlistId:String(p.id),approverId:String(a.id),lineRequester:'Line 1',qty:'3',remark:'Needed for maintenance'});
  let ticketId;
  await t.test('members create tickets; approval belongs to assigned reviewer',async()=>{
    assert.equal((await member.request('/member/tickets',{...ticketInput(),qty:'6'})).status,409);
    assert.equal((await member.request('/member/tickets',{...ticketInput(),approverId:String(m2.id)})).status,422);
    assert.equal((await member.request('/member/tickets',ticketInput())).status,303);
    ticketId=(await db.select().from(schema.tickets))[0].id;
    assert.equal((await otherReviewer.request(`/approver/tickets/${ticketId}/approve`,{})).status,403);
    assert.equal((await member.request(`/approver/tickets/${ticketId}/approve`,{})).status,403);
    assert.equal((await admin.request(`/admin/partlists/${p.id}/delete`,{})).status,409);
    assert.equal((await admin.request(`/admin/users/${a.id}/delete`,{})).status,409);
    assert.equal((await otherMember.request('/member/tickets')).html.includes('Needed for maintenance'),false);
  });
  await t.test('approval deducts stock once and records exactly one stockout',async()=>{
    assert.equal((await reviewer.request(`/approver/tickets/${ticketId}/approve`,{})).status,303);
    assert.equal((await reviewer.request(`/approver/tickets/${ticketId}/approve`,{})).status,409);
    assert.equal((await reviewer.request(`/approver/tickets/${ticketId}/reject`,{rejectionRemark:'Too late'})).status,409);
    assert.equal((await db.select().from(schema.partlists).where(eq(schema.partlists.id,p.id)))[0].qty,2);
    assert.equal((await db.select().from(schema.stockouts)).length,1);
    assert.ok((await member.request('/member/stockouts/history')).html.includes('DRAW-17'));
    assert.equal((await otherMember.request('/member/stockouts/history')).html.includes('DRAW-17'),false);
  });
  await t.test('competing tickets cannot overdraw stock; rejection preserves stock',async()=>{
    const data={...ticketInput(),qty:'2'};
    assert.equal((await member.request('/member/tickets',data)).status,303);
    assert.equal((await member.request('/member/tickets',data)).status,303);
    const pending=(await db.select().from(schema.tickets).where(eq(schema.tickets.status,'pending')));
    assert.equal((await reviewer.request(`/approver/tickets/${pending[0].id}/approve`,{})).status,303);
    assert.equal((await reviewer.request(`/approver/tickets/${pending[1].id}/approve`,{})).status,409);
    assert.equal((await db.select().from(schema.tickets).where(eq(schema.tickets.id,pending[1].id)))[0].status,'pending');
    assert.equal((await db.select().from(schema.partlists).where(eq(schema.partlists.id,p.id)))[0].qty,0);
    assert.equal((await reviewer.request(`/approver/tickets/${pending[1].id}/reject`,{rejectionRemark:''})).status,422);
    assert.equal((await reviewer.request(`/approver/tickets/${pending[1].id}/reject`,{rejectionRemark:'Stok habis'})).status,303);
    assert.equal((await db.select().from(schema.stockouts)).length,2);
    assert.ok((await reviewer.request('/approver/tickets/history')).html.includes('Stok habis'));
  });
  await t.test('concurrent approvals cannot spend the same stock twice',async()=>{
    assert.equal((await admin.request(`/admin/partlists/${p.id}`,{...part,qty:'1'})).status,303);
    const data={...ticketInput(),qty:'1'};
    await member.request('/member/tickets',data);await member.request('/member/tickets',data);
    const pending=await db.select().from(schema.tickets).where(eq(schema.tickets.status,'pending'));
    const results=await Promise.all(pending.map(ticket=>reviewer.request(`/approver/tickets/${ticket.id}/approve`,{})));
    assert.deepEqual(results.map(r=>r.status).sort(),[303,409]);
    assert.equal((await db.select().from(schema.partlists).where(eq(schema.partlists.id,p.id)))[0].qty,0);
    assert.equal((await db.select().from(schema.stockouts)).length,3);
  });
  await t.test('account edits revoke sessions; unused records can be deleted',async()=>{
    const newPassword=randomBytes(24).toString('hex');
    assert.equal((await admin.request(`/admin/users/${m2.id}`,{name:m2.name,username:m2.username,npk:m2.npk,email:'',role:'member',password:newPassword,password_confirmation:newPassword})).status,303);
    assert.equal((await otherMember.request('/member/dashboard')).location,'/login');
    assert.equal((await admin.request(`/admin/users/${m2.id}/delete`,{})).status,303);
    const [unused]=await db.select().from(schema.partlists).where(eq(schema.partlists.drawingNoSupplier,'DRAW-18'));
    assert.equal((await admin.request(`/admin/partlists/${unused.id}/delete`,{})).status,303);
  });
  await t.test('logout invalidates sessions and unknown routes return a useful page',async()=>{
    assert.equal((await member.request('/does-not-exist')).status,404);
    assert.equal((await member.request('/logout',{})).status,303);
    assert.equal((await member.request('/member/dashboard')).location,'/login');
    for(let i=0;i<10;i++) await guest.request('/login',{username:'missing',password});
    assert.equal((await guest.request('/login',{username:'missing',password})).status,429);
  });
});
