CREATE TABLE "login_attempts" (
	"key" text PRIMARY KEY,
	"attempts" integer DEFAULT 1 NOT NULL,
	"expires_at" timestamp with time zone NOT NULL
);
--> statement-breakpoint
CREATE TABLE "partlists" (
	"id" serial PRIMARY KEY,
	"drawing_no_supplier" text NOT NULL UNIQUE,
	"name" text NOT NULL,
	"size_type" text NOT NULL,
	"qty" integer NOT NULL,
	"sub_assy_drawing_unit" text NOT NULL,
	"location" text NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "nonnegative_stock" CHECK ("qty" >= 0)
);
--> statement-breakpoint
CREATE TABLE "sessions" (
	"token_hash" text PRIMARY KEY,
	"user_id" integer NOT NULL,
	"expires_at" timestamp with time zone NOT NULL
);
--> statement-breakpoint
CREATE TABLE "stockouts" (
	"id" serial PRIMARY KEY,
	"ticket_id" integer NOT NULL UNIQUE,
	"qty" integer NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "positive_stockout" CHECK ("qty" > 0)
);
--> statement-breakpoint
CREATE TABLE "tickets" (
	"id" serial PRIMARY KEY,
	"created_by" integer NOT NULL,
	"approver_id" integer NOT NULL,
	"partlist_id" integer NOT NULL,
	"line_requester" text NOT NULL,
	"qty" integer NOT NULL,
	"remark" text DEFAULT '' NOT NULL,
	"status" text DEFAULT 'pending' NOT NULL,
	"approval_remark" text,
	"rejection_remark" text,
	"approved_at" timestamp with time zone,
	"rejected_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "positive_quantity" CHECK ("qty" > 0),
	CONSTRAINT "valid_status" CHECK ("status" IN ('pending','approved','rejected'))
);
--> statement-breakpoint
CREATE TABLE "users" (
	"id" serial PRIMARY KEY,
	"name" text NOT NULL,
	"username" text NOT NULL UNIQUE,
	"npk" text NOT NULL UNIQUE,
	"email" text UNIQUE,
	"password" text NOT NULL,
	"role" text NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "valid_role" CHECK ("role" IN ('admin','approver','member'))
);
--> statement-breakpoint
CREATE INDEX "tickets_creator_idx" ON "tickets" ("created_by");--> statement-breakpoint
CREATE INDEX "tickets_approver_idx" ON "tickets" ("approver_id");--> statement-breakpoint
ALTER TABLE "sessions" ADD CONSTRAINT "sessions_user_id_users_id_fkey" FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE;--> statement-breakpoint
ALTER TABLE "stockouts" ADD CONSTRAINT "stockouts_ticket_id_tickets_id_fkey" FOREIGN KEY ("ticket_id") REFERENCES "tickets"("id");--> statement-breakpoint
ALTER TABLE "tickets" ADD CONSTRAINT "tickets_created_by_users_id_fkey" FOREIGN KEY ("created_by") REFERENCES "users"("id");--> statement-breakpoint
ALTER TABLE "tickets" ADD CONSTRAINT "tickets_approver_id_users_id_fkey" FOREIGN KEY ("approver_id") REFERENCES "users"("id");--> statement-breakpoint
ALTER TABLE "tickets" ADD CONSTRAINT "tickets_partlist_id_partlists_id_fkey" FOREIGN KEY ("partlist_id") REFERENCES "partlists"("id");