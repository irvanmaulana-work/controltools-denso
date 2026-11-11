import { pgTable, serial, text, integer, timestamp, check, index } from 'drizzle-orm/pg-core';
import { sql } from 'drizzle-orm';

export const users = pgTable('users', {
  id: serial().primaryKey(), name: text().notNull(), username: text().notNull().unique(),
  npk: text().notNull().unique(), email: text().unique(), password: text().notNull(),
  role: text().notNull(), createdAt: timestamp('created_at', { withTimezone: true }).notNull().defaultNow(),
}, t => [check('valid_role', sql`${t.role} IN ('admin','approver','member')`)]);
export const partlists = pgTable('partlists', {
  id: serial().primaryKey(), drawingNoSupplier: text('drawing_no_supplier').notNull().unique(),
  name: text().notNull(), sizeType: text('size_type').notNull(), qty: integer().notNull(),
  subAssyDrawingUnit: text('sub_assy_drawing_unit').notNull(), location: text().notNull(),
  createdAt: timestamp('created_at', { withTimezone: true }).notNull().defaultNow(),
}, t => [check('nonnegative_stock', sql`${t.qty} >= 0`)]);
export const tickets = pgTable('tickets', {
  id: serial().primaryKey(), createdBy: integer('created_by').notNull().references(() => users.id),
  approverId: integer('approver_id').notNull().references(() => users.id),
  partlistId: integer('partlist_id').notNull().references(() => partlists.id),
  lineRequester: text('line_requester').notNull(), qty: integer().notNull(),
  remark: text().notNull().default(''), status: text().notNull().default('pending'),
  approvalRemark: text('approval_remark'), rejectionRemark: text('rejection_remark'),
  approvedAt: timestamp('approved_at', { withTimezone: true }), rejectedAt: timestamp('rejected_at', { withTimezone: true }),
  createdAt: timestamp('created_at', { withTimezone: true }).notNull().defaultNow(),
}, t => [check('positive_quantity', sql`${t.qty} > 0`), check('valid_status', sql`${t.status} IN ('pending','approved','rejected')`), index('tickets_creator_idx').on(t.createdBy), index('tickets_approver_idx').on(t.approverId)]);
export const stockouts = pgTable('stockouts', {
  id: serial().primaryKey(), ticketId: integer('ticket_id').notNull().unique().references(() => tickets.id),
  qty: integer().notNull(), createdAt: timestamp('created_at', { withTimezone: true }).notNull().defaultNow(),
}, t => [check('positive_stockout', sql`${t.qty} > 0`)]);
export const sessions = pgTable('sessions', {
  tokenHash: text('token_hash').primaryKey(), userId: integer('user_id').notNull().references(() => users.id, { onDelete: 'cascade' }),
  expiresAt: timestamp('expires_at', { withTimezone: true }).notNull(),
});
export const loginAttempts = pgTable('login_attempts', {
  key: text().primaryKey(), attempts: integer().notNull().default(1),
  expiresAt: timestamp('expires_at', { withTimezone: true }).notNull(),
});
