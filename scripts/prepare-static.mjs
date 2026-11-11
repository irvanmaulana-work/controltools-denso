import { mkdir, copyFile } from 'node:fs/promises';
// Publish only browser assets; never publish Laravel's PHP entry point.
await mkdir('public/build', { recursive: true });
for (const file of ['denso.png', 'favicon.ico', 'robots.txt']) {
  await copyFile(`public/${file}`, `public/build/${file}`);
}
await copyFile('resources/css/netlify.css', 'public/build/site.css');
