import { randomBytes, scrypt as scryptCallback, timingSafeEqual, createHash } from 'node:crypto';
import { promisify } from 'node:util';
const scrypt = promisify(scryptCallback);
export const digest = value => createHash('sha256').update(value).digest('hex');
export const token = () => randomBytes(32).toString('hex');
export function equal(a, b) { return typeof a === 'string' && typeof b === 'string' && timingSafeEqual(Buffer.from(digest(a)), Buffer.from(digest(b))); }
export async function hashPassword(value) {
  const salt = randomBytes(16).toString('hex');
  const hash = await scrypt(value, salt, 64);
  return `scrypt:${salt}:${hash.toString('hex')}`;
}
export async function verifyPassword(value, stored) {
  const [method, salt, hash] = stored.split(':');
  if (method !== 'scrypt' || !salt || !hash) return false;
  return equal((await scrypt(value, salt, 64)).toString('hex'), hash);
}
export const publicUser = ({ password, ...user }) => user;
export class HttpError extends Error { constructor(status, message, code) { super(message); this.status = status; this.code = code; } }
