import express from 'express';
import { and, eq, ne, desc, gt, lt, sql, or, ilike } from 'drizzle-orm';
import { alias } from 'drizzle-orm/pg-core';
import { z } from 'zod';
import { users, partlists, tickets, stockouts, sessions, loginAttempts } from '../../db/schema.ts';
import { token, digest, equal, hashPassword, verifyPassword, publicUser, HttpError } from './security.mjs';
import { layout, authForm, userForm, partForm, ticketTable, table, esc, field, select, hidden, date } from './views.mjs';

const text = z.string().trim().min(1).max(255);
const id = z.coerce.number().int().positive().max(2147483647);
const qty = z.coerce.number().int().nonnegative().max(2147483647);
const partInput = z.object({ name: text, drawingNoSupplier: text, sizeType: text, qty, subAssyDrawingUnit: text, location: text });
const userInput = z.object({ name: text, username: text, npk: text, email: z.union([z.literal(''), z.string().trim().email().max(255)]).optional(), role: z.enum(['admin','approver','member']), password: z.string().max(128).optional(), password_confirmation: z.string().max(128).optional() });
const safeFields = { id: users.id, name: users.name, username: users.username, npk: users.npk, email: users.email, role: users.role, createdAt: users.createdAt };
const fail = (status, message) => { throw new HttpError(status, message); };
const creator = alias(users, 'creator');
const approver = alias(users, 'approver');
const roles = ['admin','approver','member'];

export function createApp(db, options = {}) {
  const app = express();
  const secure = options.secure ?? process.env.NODE_ENV !== 'test';
  const sessionName = secure ? '__Host-ct_session' : 'ct_session';
  const csrfName = secure ? '__Host-ct_csrf' : 'ct_csrf';
  const bootstrapPassword = options.bootstrapPassword ?? (() => process.env.INITIAL_ADMIN_PASSWORD);
  function requireBootstrapPassword() {
    const configured = bootstrapPassword();
    if (!configured) throw new HttpError(503, 'Password administrator awal belum tersedia di server. Konfigurasi INITIAL_ADMIN_PASSWORD harus aktif untuk runtime situs dan situs perlu di-deploy ulang.', 'bootstrap_missing');
    if (configured.length < 12 || configured.length > 128) throw new HttpError(503, 'Konfigurasi password administrator awal harus berisi 12–128 karakter. Perbarui INITIAL_ADMIN_PASSWORD lalu deploy ulang situs.', 'bootstrap_invalid_length');
    return configured;
  }
  app.disable('x-powered-by');
  app.use(express.urlencoded({ extended: false, limit: '24kb' }));
  app.use((req, res, next) => {
    res.set({ 'Cache-Control': 'no-store', 'X-Content-Type-Options': 'nosniff', 'Referrer-Policy': 'same-origin', 'X-Frame-Options': 'DENY', 'Content-Security-Policy': "default-src 'self'; style-src 'self'; img-src 'self'; script-src 'none'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'" });
    req.cookies = Object.fromEntries((req.headers.cookie || '').split(';').filter(v => v.includes('=')).map(v => { const i = v.indexOf('='); return [v.slice(0,i).trim(), v.slice(i+1)]; }));
    req.csrf = /^[a-f0-9]{64}$/.test(req.cookies[csrfName] || '') ? req.cookies[csrfName] : token();
    if (req.csrf !== req.cookies[csrfName]) res.cookie(csrfName, req.csrf, { secure, httpOnly: true, sameSite: 'strict', path: '/' });
    if (req.method === 'POST') {
      let sameOrigin = false;
      try { const origin = new URL(req.get('origin')); sameOrigin = origin.host === req.get('host') && (!secure || origin.protocol === 'https:'); } catch {}
      if (!sameOrigin || !equal(req.body?._csrf, req.cookies[csrfName])) return next(new HttpError(403, 'Form tidak valid atau sudah kedaluwarsa. Buka ulang halaman lalu coba lagi.'));
    }
    next();
  });
  const render = (req, res, title, body, extra = {}) => res.type('html').send(layout(title, body, { user: req.user, csrf: req.csrf, path: req.path, success: req.query.saved === '1' ? 'Perubahan berhasil disimpan.' : undefined, ...extra }));
  const countUsers = async () => (await db.select({ n: sql`count(*)::integer` }).from(users))[0].n;
  async function rateLimit(req, purpose) {
    // Persisted limits survive cold starts and are shared across server instances.
    const username = typeof req.body?.username === 'string' ? req.body.username.slice(0,255).trim().toLowerCase() : '';
    const key = digest(`${purpose}:${req.headers['x-nf-client-connection-ip'] || req.socket.remoteAddress || 'unknown'}:${username}`);
    await db.delete(loginAttempts).where(lt(loginAttempts.expiresAt, new Date()));
    const [attempt] = await db.insert(loginAttempts).values({ key, expiresAt: new Date(Date.now()+15*60*1000) })
      .onConflictDoUpdate({ target: loginAttempts.key, set: { attempts: sql`${loginAttempts.attempts}+1` } }).returning();
    if (attempt.attempts > 10) fail(429, 'Terlalu banyak percobaan. Silakan coba lagi dalam 15 menit.');
  }
  async function startSession(req, res, user) {
    const value = token();
    await db.delete(sessions).where(lt(sessions.expiresAt, new Date()));
    if (req.cookies[sessionName]) await db.delete(sessions).where(eq(sessions.tokenHash, digest(req.cookies[sessionName])));
    await db.insert(sessions).values({ tokenHash: digest(value), userId: user.id, expiresAt: new Date(Date.now()+8*3600*1000) });
    res.cookie(sessionName, value, { httpOnly: true, secure, sameSite: 'lax', path: '/', maxAge: 8*3600*1000 });
    res.redirect(303, `/${user.role}/dashboard`);
  }
  app.get('/', (req,res) => res.redirect(302, '/login'));
  app.get('/login', async (req,res) => {
    if (!await countUsers()) return res.redirect(302, '/setup');
    render(req,res,'Selamat datang',authForm(false,req.csrf));
  });
  app.get('/setup', async (req,res) => {
    if (await countUsers()) return res.redirect(302,'/login');
    requireBootstrapPassword();
    render(req,res,'Penyiapan workspace',authForm(true,req.csrf));
  });
  app.post('/setup', async (req,res) => {
    await rateLimit(req, 'setup');
    const data = z.object({name:text,username:text,npk:text,password:z.string().min(12).max(128)}).parse(req.body);
    const configured = requireBootstrapPassword();
    if (!equal(data.password, configured)) fail(403,'Password administrator awal tidak cocok.');
    const password = await hashPassword(data.password);
    const user = await db.transaction(async tx => {
      await tx.execute(sql`SELECT pg_advisory_xact_lock(817201)`);
      if ((await tx.select({id:users.id}).from(users).limit(1)).length) fail(409,'Workspace sudah disiapkan. Silakan masuk.');
      return (await tx.insert(users).values({...data,password,role:'admin'}).returning())[0];
    });
    await startSession(req,res,user);
  });
  app.post('/login', async (req,res) => {
    await rateLimit(req, 'login');
    const data = z.object({username:text,password:z.string().min(1).max(128)}).parse(req.body);
    const [user] = await db.select().from(users).where(eq(users.username,data.username));
    // Perform the same expensive operation for unknown usernames.
    const valid = user ? await verifyPassword(data.password,user.password) : (await hashPassword(data.password), false);
    if (!valid) return res.status(401).type('html').send(layout('Selamat datang',authForm(false,req.csrf),{csrf:req.csrf,error:'Username atau password salah.'}));
    await startSession(req,res,user);
  });
  app.use(async (req,res,next) => {
    const session = req.cookies[sessionName];
    if (session && /^[a-f0-9]{64}$/.test(session)) {
      const [record] = await db.select({user:safeFields}).from(sessions).innerJoin(users,eq(sessions.userId,users.id)).where(and(eq(sessions.tokenHash,digest(session)),gt(sessions.expiresAt,new Date())));
      req.user = record?.user;
    }
    if (!req.user) return res.redirect(303,'/login');
    const rolePrefix = req.path.split('/')[1];
    if (roles.includes(rolePrefix) && rolePrefix !== req.user.role) return next(new HttpError(403,'Anda tidak memiliki akses ke halaman ini.'));
    next();
  });
  app.post('/logout', async (req,res) => {
    await db.delete(sessions).where(eq(sessions.tokenHash,digest(req.cookies[sessionName] || '')));
    res.clearCookie(sessionName,{secure,httpOnly:true,sameSite:'lax',path:'/'});
    res.redirect(303,'/login');
  });
  app.get('/dashboard',(req,res) => res.redirect(302,`/${req.user.role}/dashboard`));
  const visibleTickets = user => user.role === 'admin' ? undefined : user.role === 'approver' ? eq(tickets.approverId,user.id) : eq(tickets.createdBy,user.id);
  const ticketQuery = where => db.select({ticket:tickets,part:partlists,creator:{id:creator.id,name:creator.name},approver:{id:approver.id,name:approver.name}}).from(tickets).innerJoin(partlists,eq(tickets.partlistId,partlists.id)).innerJoin(creator,eq(tickets.createdBy,creator.id)).innerJoin(approver,eq(tickets.approverId,approver.id)).where(where).orderBy(desc(tickets.id));
  for (const role of roles) app.get(`/${role}/dashboard`, async (req,res) => {
    const grouped = await db.select({status:tickets.status,n:sql`count(*)::integer`}).from(tickets).where(visibleTickets(req.user)).groupBy(tickets.status);
    const counts = Object.fromEntries(grouped.map(r=>[r.status,r.n]));
    const parts = await db.select().from(partlists).where(lt(partlists.qty,2)).orderBy(partlists.qty).limit(10);
    const total = Object.values(counts).reduce((a,b)=>a+b,0);
    const statValues = role === 'admin' ? [['Pengguna',await countUsers()],['Partlist',(await db.select({n:sql`count(*)::integer`}).from(partlists))[0].n],['Menunggu approval',counts.pending || 0],['Disetujui hari ini',(await db.select({n:sql`count(*)::integer`}).from(tickets).where(and(eq(tickets.status,'approved'),sql`(${tickets.approvedAt} AT TIME ZONE 'Asia/Jakarta')::date = (now() AT TIME ZONE 'Asia/Jakarta')::date`)))[0].n]] : [['Total tiket',total],['Menunggu',counts.pending||0],['Disetujui',counts.approved||0],['Ditolak',counts.rejected||0]];
    const stats = `<div class="stats">${statValues.map(([label,n])=>`<div class="stat"><span>${label}</span><strong>${n}</strong></div>`).join('')}</div>`;
    const rows = await ticketQuery(visibleTickets(req.user)).limit(10);
    render(req,res,'Dashboard',stats+`<div class="toolbar"><h2>Tiket terbaru</h2>${role==='member'?'<a class="button" href="/member/tickets/create">Buat tiket</a>':''}</div>`+ticketTable(rows)+ (role === 'admin' ? '<h2 class="section-head">Stok menipis · 1 atau kurang</h2>'+table(['Partlist','Drawing no.','Lokasi','Stok'],parts.map(p=>[esc(p.name),esc(p.drawingNoSupplier),esc(p.location),`<span class="stock-low">${p.qty}</span>`])) : ''));
  });
  // User management. The lock protects the final administrator against concurrent edits.
  app.get('/admin/users',async(req,res)=>{
    const rows=await db.select(safeFields).from(users).orderBy(desc(users.id));
    render(req,res,'Pengguna','<div class="toolbar"><p class="muted">Kelola akun dan hak akses workspace.</p><a class="button" href="/admin/users/create">Tambah pengguna</a></div>'+table(['Nama','Username','NPK','Email','Peran','Tindakan'],rows.map(u=>[esc(u.name),esc(u.username),esc(u.npk),esc(u.email||'—'),esc(u.role),`<a href="/admin/users/${u.id}/edit">Edit</a> ${u.id===req.user.id?'':`<form method="post" action="/admin/users/${u.id}/delete">${hidden(req.csrf)}<button class="danger compact">Hapus</button></form>`}`])));
  });
  app.get('/admin/users/create',(req,res)=>render(req,res,'Tambah pengguna',userForm({},req.csrf,'/admin/users')));
  app.get('/admin/users/:id/edit',async(req,res)=>{
    const [u]=await db.select(safeFields).from(users).where(eq(users.id,id.parse(req.params.id)));
    if(!u) fail(404,'Pengguna tidak ditemukan.');
    render(req,res,'Edit pengguna',userForm(u,req.csrf,`/admin/users/${u.id}`));
  });
  async function saveUser(req,res,existingId){
    const data=userInput.parse(req.body);
    if(!existingId || data.password){
      if((data.password||'').length<12) fail(422,'Password minimal 12 karakter.');
      if(data.password!==data.password_confirmation) fail(422,'Konfirmasi password tidak cocok.');
    }
    const values={name:data.name,username:data.username,npk:data.npk,email:data.email||null,role:data.role};
    if(data.password) values.password=await hashPassword(data.password);
    await db.transaction(async tx=>{
      await tx.execute(sql`SELECT pg_advisory_xact_lock(817201)`);
      if(existingId){
        const [current]=await tx.select().from(users).where(eq(users.id,existingId));
        if(!current) fail(404,'Pengguna tidak ditemukan.');
        if(current.role==='admin'&&data.role!=='admin'){
          if(!(await tx.select({id:users.id}).from(users).where(and(eq(users.role,'admin'),ne(users.id,existingId)))).length) fail(409,'Minimal satu administrator harus tetap aktif.');
        }
        if(current.role==='approver'&&data.role!=='approver'&&(await tx.select({id:tickets.id}).from(tickets).where(and(eq(tickets.approverId,existingId),eq(tickets.status,'pending'))).limit(1)).length) fail(409,'Pengguna masih memiliki tiket menunggu approval.');
        await tx.update(users).set(values).where(eq(users.id,existingId));
        if(data.password || current.role!==data.role) await tx.delete(sessions).where(eq(sessions.userId,existingId));
      }else await tx.insert(users).values(values);
    });
    res.redirect(303,'/admin/users?saved=1');
  }
  app.post('/admin/users',(req,res)=>saveUser(req,res));
  app.post('/admin/users/:id',(req,res)=>saveUser(req,res,id.parse(req.params.id)));
  app.post('/admin/users/:id/delete',async(req,res)=>{
    const userId=id.parse(req.params.id);
    if(userId===req.user.id) fail(409,'Akun sendiri tidak dapat dihapus.');
    await db.transaction(async tx=>{
      await tx.execute(sql`SELECT pg_advisory_xact_lock(817201)`);
      if((await tx.select({id:tickets.id}).from(tickets).where(or(eq(tickets.createdBy,userId),eq(tickets.approverId,userId))).limit(1)).length) fail(409,'Pengguna memiliki histori tiket dan tidak dapat dihapus.');
      await tx.delete(users).where(eq(users.id,userId));
    });
    res.redirect(303,'/admin/users?saved=1');
  });
  // Inventory is editable by admins; approvers may add a single item, as in Laravel.
  for(const prefix of ['/admin/partlists','/approver/add-partlist']){
    const isApprover=prefix.startsWith('/approver');
    app.get(prefix,async(req,res)=>{
      const search=typeof req.query.q==='string'?req.query.q.slice(0,100):'';
      const page=Math.max(1,Math.min(100000,Number.parseInt(req.query.page)||1));
      const filter=search?or(ilike(partlists.name,`%${search}%`),ilike(partlists.drawingNoSupplier,`%${search}%`),ilike(partlists.location,`%${search}%`)):undefined;
      const rows=await db.select().from(partlists).where(filter).orderBy(desc(partlists.id)).limit(26).offset((page-1)*25);
      const pageRows=rows.slice(0,25);
      render(req,res,'Partlist',`<div class="toolbar"><form class="search" method="get"><input name="q" aria-label="Cari partlist" placeholder="Nama, drawing, atau lokasi" value="${esc(search)}"><button class="quiet">Cari</button></form><a class="button" href="${prefix}/create">Tambah partlist</a></div>`+table(['Drawing no.','Nama','Size / type','Stok','Sub assy / unit','Lokasi',...(!isApprover?['Tindakan']:[])],pageRows.map(p=>[esc(p.drawingNoSupplier),esc(p.name),esc(p.sizeType),`<span class="${p.qty<=1?'stock-low':''}">${p.qty}</span>`,esc(p.subAssyDrawingUnit),esc(p.location),...(!isApprover?[`<a href="${prefix}/${p.id}/edit">Edit</a> <form method="post" action="${prefix}/${p.id}/delete">${hidden(req.csrf)}<button class="compact danger">Hapus</button></form>`]:[])]))+`<div class="toolbar">${page>1?`<a href="?page=${page-1}&q=${encodeURIComponent(search)}">Sebelumnya</a>`:'<span></span>'}<span class="muted">Halaman ${page}</span>${rows.length>25?`<a href="?page=${page+1}&q=${encodeURIComponent(search)}">Berikutnya</a>`:'<span></span>'}</div>`);
    });
    app.get(`${prefix}/create`,(req,res)=>render(req,res,'Tambah partlist',partForm({},req.csrf,prefix,isApprover)));
    app.post(prefix,async(req,res)=>{
      const data=partInput.parse(req.body);
      if(isApprover&&data.qty!==1) fail(422,'Approver hanya dapat menambahkan stok awal sebanyak 1.');
      await db.insert(partlists).values(data);
      res.redirect(303,`${prefix}?saved=1`);
    });
  }
  app.get('/admin/partlists/:id/edit',async(req,res)=>{
    const [part]=await db.select().from(partlists).where(eq(partlists.id,id.parse(req.params.id)));
    if(!part) fail(404,'Partlist tidak ditemukan.');
    render(req,res,'Edit partlist',partForm(part,req.csrf,`/admin/partlists/${part.id}`));
  });
  app.post('/admin/partlists/:id',async(req,res)=>{
    const data=partInput.parse(req.body);
    const rows=await db.update(partlists).set(data).where(eq(partlists.id,id.parse(req.params.id))).returning({id:partlists.id});
    if(!rows.length) fail(404,'Partlist tidak ditemukan.');
    res.redirect(303,'/admin/partlists?saved=1');
  });
  app.post('/admin/partlists/:id/delete',async(req,res)=>{
    const partId=id.parse(req.params.id);
    if((await db.select({id:tickets.id}).from(tickets).where(eq(tickets.partlistId,partId)).limit(1)).length) fail(409,'Partlist memiliki histori tiket dan tidak dapat dihapus.');
    await db.delete(partlists).where(eq(partlists.id,partId));
    res.redirect(303,'/admin/partlists?saved=1');
  });
  app.get('/member/tickets/create',async(req,res)=>{
    const parts=await db.select().from(partlists).orderBy(partlists.name);
    const reviewers=await db.select(safeFields).from(users).where(eq(users.role,'approver'));
    if(!parts.length||!reviewers.length) return render(req,res,'Buat tiket','<section class="panel"><h2>Workspace belum siap menerima tiket</h2><p class="muted">Minta administrator menambahkan partlist dan akun approver terlebih dahulu.</p><a href="/member/tickets">Kembali ke tiket saya</a></section>');
    render(req,res,'Buat tiket',`<section class="panel form-panel"><p class="muted">Stok berkurang setelah tiket disetujui oleh approver.</p><form method="post" action="/member/tickets">${hidden(req.csrf)}${select('Partlist / drawing / stok / lokasi','partlistId',parts.map(p=>[p.id,`${p.name} · ${p.drawingNoSupplier} · stok ${p.qty} · ${p.location}`]))}<div class="form-grid">${field('Line pemohon','lineRequester')}${field('Jumlah diminta','qty',1,'number',true,'min="1" max="2147483647"')}${select('Approver','approverId',reviewers.map(u=>[u.id,u.name]))}</div><label>Catatan (opsional)<textarea name="remark" maxlength="500"></textarea></label><div class="form-actions"><button>Kirim untuk approval</button><a href="/member/tickets">Batal</a></div></form></section>`);
  });
  app.post('/member/tickets',async(req,res)=>{
    const data=z.object({partlistId:id,approverId:id,lineRequester:text,qty:qty.refine(n=>n>0),remark:z.string().trim().max(500).default('')}).parse(req.body);
    await db.transaction(async tx=>{
      const [p]=await tx.select().from(partlists).where(eq(partlists.id,data.partlistId)).for('update');
      if(!p) fail(404,'Partlist tidak ditemukan.');
      if(p.qty<data.qty) fail(409,`Jumlah melebihi stok tersedia (${p.qty}).`);
      const [a]=await tx.select(safeFields).from(users).where(and(eq(users.id,data.approverId),eq(users.role,'approver'))).for('share');
      if(!a) fail(422,'Approver tidak valid.');
      await tx.insert(tickets).values({...data,createdBy:req.user.id});
    });
    res.redirect(303,'/member/tickets?saved=1');
  });
  for(const path of ['/member/tickets','/approver/tickets','/approver/tickets/history']) app.get(path,async(req,res)=>{
    const isApprover=req.user.role==='approver';
    const filter=and(visibleTickets(req.user),isApprover?(path.endsWith('/history')?ne(tickets.status,'pending'):eq(tickets.status,'pending')):undefined);
    const page=Math.max(1,Number.parseInt(req.query.page)||1);
    const rows=await ticketQuery(filter).limit(51).offset((page-1)*50);
    render(req,res,isApprover?(path.endsWith('/history')?'Riwayat approval':'Approval tiket'):'Tiket saya',`<div class="toolbar"><p class="muted">${isApprover?'Tiket yang ditugaskan kepada Anda.':'Pantau status permintaan partlist Anda.'}</p>${!isApprover?'<a class="button" href="/member/tickets/create">Buat tiket</a>':''}</div>`+ticketTable(rows.slice(0,50),{actions:isApprover&&!path.endsWith('/history'),csrf:req.csrf})+`<div class="toolbar">${page>1?`<a href="?page=${page-1}">Sebelumnya</a>`:'<span></span>'}<span>Halaman ${page}</span>${rows.length>50?`<a href="?page=${page+1}">Berikutnya</a>`:''}</div>`);
  });
  app.get('/approver/tickets/:id/reject',async(req,res)=>{
    const [t]=await db.select().from(tickets).where(and(eq(tickets.id,id.parse(req.params.id)),eq(tickets.approverId,req.user.id)));
    if(!t) fail(404,'Tiket tidak ditemukan.');
    if(t.status!=='pending') fail(409,'Tiket sudah diproses.');
    render(req,res,`Tolak tiket #${t.id}`,`<section class="panel form-panel"><form method="post" action="/approver/tickets/${t.id}/reject">${hidden(req.csrf)}<label>Alasan penolakan<textarea name="rejectionRemark" maxlength="500" required></textarea></label><div class="form-actions"><button>Tolak tiket</button><a href="/approver/tickets">Batal</a></div></form></section>`);
  });
  for(const decision of ['approve','reject']) app.post(`/approver/tickets/:id/${decision}`,async(req,res)=>{
    const ticketId=id.parse(req.params.id);
    const rejectionRemark=decision==='reject'?z.string().trim().min(1).max(500).parse(req.body.rejectionRemark):null;
    await db.transaction(async tx=>{
      const [t]=await tx.select().from(tickets).where(eq(tickets.id,ticketId)).for('update');
      if(!t||t.approverId!==req.user.id) fail(403,'Tiket ini tidak ditugaskan kepada Anda.');
      if(t.status!=='pending') fail(409,'Tiket sudah diproses.');
      if(decision==='reject'){
        await tx.update(tickets).set({status:'rejected',rejectionRemark,rejectedAt:new Date()}).where(eq(tickets.id,t.id));
      }else{
        const [p]=await tx.select().from(partlists).where(eq(partlists.id,t.partlistId)).for('update');
        if(!p||p.qty<t.qty) fail(409,`Stok tidak cukup. Stok tersedia: ${p?.qty||0}.`);
        await tx.update(partlists).set({qty:sql`${partlists.qty}-${t.qty}`}).where(eq(partlists.id,p.id));
        await tx.update(tickets).set({status:'approved',approvedAt:new Date(),approvalRemark:'Disetujui oleh approver'}).where(eq(tickets.id,t.id));
        await tx.insert(stockouts).values({ticketId:t.id,qty:t.qty});
      }
    });
    res.redirect(303,'/approver/tickets?saved=1');
  });
  app.get('/member/stockouts/history',async(req,res)=>{
    const page=Math.max(1,Number.parseInt(req.query.page)||1);
    const rows=await db.select({stockout:stockouts,part:partlists,ticket:tickets}).from(stockouts).innerJoin(tickets,eq(stockouts.ticketId,tickets.id)).innerJoin(partlists,eq(tickets.partlistId,partlists.id)).where(eq(tickets.createdBy,req.user.id)).orderBy(desc(stockouts.id)).limit(51).offset((page-1)*50);
    render(req,res,'Riwayat pengeluaran',table(['Tanggal','Tiket','Partlist','Drawing no.','Qty','Line'],rows.slice(0,50).map(({stockout:s,part:p,ticket:t})=>[date(s.createdAt),`#${t.id}`,esc(p.name),esc(p.drawingNoSupplier),esc(s.qty),esc(t.lineRequester)]))+`<div class="toolbar">${page>1?`<a href="?page=${page-1}">Sebelumnya</a>`:'<span></span>'}${rows.length>50?`<a href="?page=${page+1}">Berikutnya</a>`:''}</div>`);
  });
  app.use((req,res)=>res.status(404).type('html').send(layout('Halaman tidak ditemukan','<section class="panel"><p>Alamat halaman ini tidak tersedia.</p><a href="/dashboard">Kembali ke dashboard</a></section>',{user:req.user,csrf:req.csrf})));
  app.use((error,req,res,next)=>{
    if(res.headersSent) return next(error);
    let status=error.status||500;
    let message=error instanceof HttpError || status<500?error.message:'Layanan belum dapat memproses permintaan. Silakan coba lagi.';
    if(error instanceof z.ZodError){status=422;message='Periksa isian formulir: '+error.issues.map(i=>i.path.join('.')||'nilai').join(', ')+'. Pastikan semua kolom wajib terisi dengan nilai yang valid.';}
    const code=error.code||error.cause?.code;
    if(code==='23505'){status=409;message='Username, NPK, email, atau drawing number sudah digunakan.';}
    if(code==='23503'){status=409;message='Data masih digunakan oleh catatan lain dan tidak dapat dihapus.';}
    if(status>=500) console.error('Request failed',{path:req.path,code:code||'internal_error'});
    res.status(status).type('html').send(layout('Permintaan belum berhasil',`<section class="panel"><p>${esc(message)}</p><a href="${req.user?'/dashboard':'/login'}">${req.user?'Kembali ke dashboard':'Kembali ke halaman masuk'}</a></section>`,{user:req.user,csrf:req.csrf}));
  });
  return app;
}
