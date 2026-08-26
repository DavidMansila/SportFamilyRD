-- =====================================================================
-- SportFamilyRD - Esquema de base de datos (PostgreSQL)
-- =====================================================================
-- Generado por introspeccion del esquema real.
-- Contiene UNICAMENTE estructura (DDL). No incluye datos.
-- Para datos de ejemplo ver: database/seed.sql
--
-- Uso:
--   createdb sportfamilyrd
--   psql -d sportfamilyrd -f database/schema.sql
-- =====================================================================

BEGIN;

-- =====================================================================
-- Secuencias
-- Deben existir antes que las tablas: las columnas id las referencian
-- en su DEFAULT nextval().
-- =====================================================================

CREATE SEQUENCE IF NOT EXISTS "NewsScrapping_id_seq";
CREATE SEQUENCE IF NOT EXISTS "achievements_id_seq";
CREATE SEQUENCE IF NOT EXISTS "calendars_id_seq";
CREATE SEQUENCE IF NOT EXISTS "cart_items_id_seq";
CREATE SEQUENCE IF NOT EXISTS "carts_id_seq";
CREATE SEQUENCE IF NOT EXISTS "chats_id_seq";
CREATE SEQUENCE IF NOT EXISTS "comments_id_seq";
CREATE SEQUENCE IF NOT EXISTS "configuration_id_seq";
CREATE SEQUENCE IF NOT EXISTS "configuration_user_id_seq";
CREATE SEQUENCE IF NOT EXISTS "failed_jobs_id_seq";
CREATE SEQUENCE IF NOT EXISTS "jobs_id_seq";
CREATE SEQUENCE IF NOT EXISTS "likes_id_seq";
CREATE SEQUENCE IF NOT EXISTS "messages_id_seq";
CREATE SEQUENCE IF NOT EXISTS "migrations_id_seq";
CREATE SEQUENCE IF NOT EXISTS "news_id_seq";
CREATE SEQUENCE IF NOT EXISTS "personal_access_tokens_id_seq";
CREATE SEQUENCE IF NOT EXISTS "posts_id_seq";
CREATE SEQUENCE IF NOT EXISTS "products_id_seq";
CREATE SEQUENCE IF NOT EXISTS "replies_id_seq";
CREATE SEQUENCE IF NOT EXISTS "saved_news_id_seq";
CREATE SEQUENCE IF NOT EXISTS "specialties_id_seq";
CREATE SEQUENCE IF NOT EXISTS "sports_id_seq";
CREATE SEQUENCE IF NOT EXISTS "trainer_id_seq";
CREATE SEQUENCE IF NOT EXISTS "training_requests_id_seq";
CREATE SEQUENCE IF NOT EXISTS "users_id_seq";

-- ---------------------------------------------------------------------
-- Tabla: NewsScrapping
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "NewsScrapping" (
    "id" bigint DEFAULT nextval('"NewsScrapping_id_seq"'::regclass) NOT NULL,
    "title" character varying(255) NOT NULL,
    "description" text NOT NULL,
    "author" character varying(255) NOT NULL,
    "source" character varying(255),
    "url" character varying(255),
    "image" character varying(255),
    "category" character varying(255) NOT NULL,
    "published_at" timestamp(0) without time zone,
    "deleted_at" timestamp(0) without time zone,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "NewsScrapping_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: achievements
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "achievements" (
    "id" bigint DEFAULT nextval('achievements_id_seq'::regclass) NOT NULL,
    "trainer_id" bigint NOT NULL,
    "title" character varying(255) NOT NULL,
    "description" text,
    "achievement_date" date,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "achievements_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: cache
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "cache" (
    "key" character varying(255) NOT NULL,
    "value" text NOT NULL,
    "expiration" integer NOT NULL,
    CONSTRAINT "cache_pkey" PRIMARY KEY (key)
);

-- ---------------------------------------------------------------------
-- Tabla: cache_locks
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "cache_locks" (
    "key" character varying(255) NOT NULL,
    "owner" character varying(255) NOT NULL,
    "expiration" integer NOT NULL,
    CONSTRAINT "cache_locks_pkey" PRIMARY KEY (key)
);

-- ---------------------------------------------------------------------
-- Tabla: calendars
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "calendars" (
    "id" bigint DEFAULT nextval('calendars_id_seq'::regclass) NOT NULL,
    "date" date NOT NULL,
    "time" time(0) without time zone NOT NULL,
    "place" character varying(255) NOT NULL,
    "price" numeric(8,2) NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "quantity" integer DEFAULT 100 NOT NULL,
    "image" character varying(255),
    "Title" character varying(255) DEFAULT 'Evento sin título'::character varying NOT NULL,
    "Description" text,
    CONSTRAINT "calendars_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: cart_items
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "cart_items" (
    "id" bigint DEFAULT nextval('cart_items_id_seq'::regclass) NOT NULL,
    "cart_id" bigint NOT NULL,
    "item_type" character varying(20) DEFAULT 'product'::character varying NOT NULL,
    "item_id" bigint NOT NULL,
    "quantity" integer DEFAULT 1 NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "cart_items_item_type_check" CHECK (((item_type)::text = ANY ((ARRAY['product'::character varying, 'event'::character varying])::text[]))),
    CONSTRAINT "cart_items_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: carts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "carts" (
    "id" bigint DEFAULT nextval('carts_id_seq'::regclass) NOT NULL,
    "user_id" bigint NOT NULL,
    "status" character varying(255) DEFAULT 'active'::character varying NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "carts_status_check" CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'completed'::character varying])::text[]))),
    CONSTRAINT "carts_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: chats
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "chats" (
    "id" bigint DEFAULT nextval('chats_id_seq'::regclass) NOT NULL,
    "user_id" bigint NOT NULL,
    "trainer_id" bigint NOT NULL,
    "status" character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "chats_status_check" CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'accepted'::character varying, 'rejected'::character varying])::text[]))),
    CONSTRAINT "chats_pkey" PRIMARY KEY (id),
    CONSTRAINT "chats_user_id_trainer_id_unique" UNIQUE (user_id, trainer_id)
);

-- ---------------------------------------------------------------------
-- Tabla: comments
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "comments" (
    "id" bigint DEFAULT nextval('comments_id_seq'::regclass) NOT NULL,
    "post_id" bigint NOT NULL,
    "texto" text NOT NULL,
    "likes" integer DEFAULT 0 NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "user_id" bigint,
    CONSTRAINT "comments_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: configuration
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "configuration" (
    "id" bigint DEFAULT nextval('configuration_id_seq'::regclass) NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "configuration" character varying(255),
    CONSTRAINT "configuration_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: configuration_user
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "configuration_user" (
    "id" bigint DEFAULT nextval('configuration_user_id_seq'::regclass) NOT NULL,
    "user_id" bigint NOT NULL,
    "configuration_id" bigint NOT NULL,
    "status" character varying(255) DEFAULT 'enabled'::character varying NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "configuration_user_status_check" CHECK (((status)::text = ANY ((ARRAY['disabled'::character varying, 'enabled'::character varying])::text[]))),
    CONSTRAINT "configuration_user_pkey" PRIMARY KEY (id),
    CONSTRAINT "configuration_user_user_id_configuration_id_unique" UNIQUE (user_id, configuration_id)
);

-- ---------------------------------------------------------------------
-- Tabla: failed_jobs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "failed_jobs" (
    "id" bigint DEFAULT nextval('failed_jobs_id_seq'::regclass) NOT NULL,
    "uuid" character varying(255) NOT NULL,
    "connection" text NOT NULL,
    "queue" text NOT NULL,
    "payload" text NOT NULL,
    "exception" text NOT NULL,
    "failed_at" timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT "failed_jobs_pkey" PRIMARY KEY (id),
    CONSTRAINT "failed_jobs_uuid_unique" UNIQUE (uuid)
);

-- ---------------------------------------------------------------------
-- Tabla: job_batches
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "job_batches" (
    "id" character varying(255) NOT NULL,
    "name" character varying(255) NOT NULL,
    "total_jobs" integer NOT NULL,
    "pending_jobs" integer NOT NULL,
    "failed_jobs" integer NOT NULL,
    "failed_job_ids" text NOT NULL,
    "options" text,
    "cancelled_at" integer,
    "created_at" integer NOT NULL,
    "finished_at" integer,
    CONSTRAINT "job_batches_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: jobs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "jobs" (
    "id" bigint DEFAULT nextval('jobs_id_seq'::regclass) NOT NULL,
    "queue" character varying(255) NOT NULL,
    "payload" text NOT NULL,
    "attempts" smallint NOT NULL,
    "reserved_at" integer,
    "available_at" integer NOT NULL,
    "created_at" integer NOT NULL,
    CONSTRAINT "jobs_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: likes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "likes" (
    "id" bigint DEFAULT nextval('likes_id_seq'::regclass) NOT NULL,
    "likeable_type" character varying(255) NOT NULL,
    "likeable_id" bigint NOT NULL,
    "user_id" bigint NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "likes_pkey" PRIMARY KEY (id),
    CONSTRAINT "likes_likeable_id_likeable_type_user_id_unique" UNIQUE (likeable_id, likeable_type, user_id)
);

-- ---------------------------------------------------------------------
-- Tabla: messages
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "messages" (
    "id" bigint DEFAULT nextval('messages_id_seq'::regclass) NOT NULL,
    "chat_id" bigint NOT NULL,
    "sender_id" bigint NOT NULL,
    "message" text NOT NULL,
    "read" boolean DEFAULT false NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "sender_type" character varying(255) DEFAULT 'user'::character varying NOT NULL,
    CONSTRAINT "messages_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: migrations
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "migrations" (
    "id" integer DEFAULT nextval('migrations_id_seq'::regclass) NOT NULL,
    "migration" character varying(255) NOT NULL,
    "batch" integer NOT NULL,
    CONSTRAINT "migrations_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: news
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "news" (
    "id" bigint DEFAULT nextval('news_id_seq'::regclass) NOT NULL,
    "title" character varying(255) NOT NULL,
    "content" text NOT NULL,
    "author" character varying(255) NOT NULL,
    "source" character varying(255) NOT NULL,
    "url" character varying(255) NOT NULL,
    "categoria" character varying(255) NOT NULL,
    "published_at" timestamp(0) without time zone,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "news_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: password_reset_tokens
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "password_reset_tokens" (
    "email" character varying(255) NOT NULL,
    "token" character varying(255) NOT NULL,
    "created_at" timestamp(0) without time zone,
    CONSTRAINT "password_reset_tokens_pkey" PRIMARY KEY (email)
);

-- ---------------------------------------------------------------------
-- Tabla: personal_access_tokens
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "personal_access_tokens" (
    "id" bigint DEFAULT nextval('personal_access_tokens_id_seq'::regclass) NOT NULL,
    "tokenable_type" character varying(255) NOT NULL,
    "tokenable_id" bigint NOT NULL,
    "name" character varying(255) NOT NULL,
    "token" character varying(64) NOT NULL,
    "abilities" text,
    "last_used_at" timestamp(0) without time zone,
    "expires_at" timestamp(0) without time zone,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "personal_access_tokens_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: posts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "posts" (
    "id" bigint DEFAULT nextval('posts_id_seq'::regclass) NOT NULL,
    "titulo" character varying(255) NOT NULL,
    "contenido" text NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "likes_quantity" integer DEFAULT 0 NOT NULL,
    "imagen" character varying(255),
    "video" character varying(255),
    "categoria" character varying(255),
    "user_id" bigint,
    CONSTRAINT "posts_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: products
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "products" (
    "id" bigint DEFAULT nextval('products_id_seq'::regclass) NOT NULL,
    "name" character varying(255) NOT NULL,
    "description" text NOT NULL,
    "stock" integer NOT NULL,
    "price" numeric(10,2) NOT NULL,
    "category" character varying(255) NOT NULL,
    "image" character varying(255),
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "products_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: replies
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "replies" (
    "id" bigint DEFAULT nextval('replies_id_seq'::regclass) NOT NULL,
    "comment_id" bigint NOT NULL,
    "likes" integer DEFAULT 0 NOT NULL,
    "texto" text NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "user_id" bigint,
    CONSTRAINT "replies_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: saved_news
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "saved_news" (
    "id" bigint DEFAULT nextval('saved_news_id_seq'::regclass) NOT NULL,
    "user_id" bigint NOT NULL,
    "news_id" bigint NOT NULL,
    "saved_at" timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "saved_news_pkey" PRIMARY KEY (id),
    CONSTRAINT "saved_news_user_id_news_id_unique" UNIQUE (user_id, news_id)
);

-- ---------------------------------------------------------------------
-- Tabla: sessions
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "sessions" (
    "id" character varying(255) NOT NULL,
    "user_id" bigint,
    "ip_address" character varying(45),
    "user_agent" text,
    "payload" text NOT NULL,
    "last_activity" integer NOT NULL,
    CONSTRAINT "sessions_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: specialties
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "specialties" (
    "id" bigint DEFAULT nextval('specialties_id_seq'::regclass) NOT NULL,
    "trainer_id" bigint NOT NULL,
    "description" character varying(255) NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "specialties_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: sports
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "sports" (
    "id" bigint DEFAULT nextval('sports_id_seq'::regclass) NOT NULL,
    "name" character varying(255) NOT NULL,
    "region" character varying(255) NOT NULL,
    "type" character varying(255),
    "popularity" character varying(255),
    "image" character varying(255),
    "short_description" character varying(255),
    "description" text,
    "requirements" jsonb,
    "places" jsonb,
    "sort_order" integer DEFAULT 0 NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "sports_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: trainer
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "trainer" (
    "id" bigint DEFAULT nextval('trainer_id_seq'::regclass) NOT NULL,
    "user_id" bigint NOT NULL,
    "name" character varying(255) NOT NULL,
    "email" character varying(255) NOT NULL,
    "phone" character varying(255) NOT NULL,
    "city_country" character varying(255) NOT NULL,
    "sport_category" character varying(255) NOT NULL,
    "experience" character varying(255) NOT NULL,
    "level_of_certification" character varying(255) NOT NULL,
    "certificates_linked" character varying(255),
    "description" text,
    "schedule" text,
    "cost" numeric(10,2),
    "status" character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "trainer_level_of_certification_check" CHECK (((level_of_certification)::text = ANY ((ARRAY['ninguna'::character varying, 'basica'::character varying, 'intermedia'::character varying, 'avanzada'::character varying, 'nacional'::character varying, 'internacional'::character varying])::text[]))),
    CONSTRAINT "trainer_sport_category_check" CHECK (((sport_category)::text = ANY ((ARRAY['Fútbol'::character varying, 'Baloncesto'::character varying, 'Tenis'::character varying, 'Natación'::character varying, 'Ciclismo'::character varying, 'Atletismo'::character varying, 'Artes Marciales'::character varying])::text[]))),
    CONSTRAINT "trainer_status_check" CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[]))),
    CONSTRAINT "trainer_pkey" PRIMARY KEY (id),
    CONSTRAINT "trainer_email_unique" UNIQUE (email)
);

-- ---------------------------------------------------------------------
-- Tabla: training_requests
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "training_requests" (
    "id" bigint DEFAULT nextval('training_requests_id_seq'::regclass) NOT NULL,
    "user_id" bigint NOT NULL,
    "trainer_id" bigint NOT NULL,
    "sport_level" character varying(255) NOT NULL,
    "description" text,
    "status" character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    CONSTRAINT "training_requests_pkey" PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------
-- Tabla: users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "users" (
    "id" bigint DEFAULT nextval('users_id_seq'::regclass) NOT NULL,
    "name" character varying(255) NOT NULL,
    "email" character varying(255) NOT NULL,
    "password" character varying(255) NOT NULL,
    "user_type" character varying(255) DEFAULT 'user'::character varying NOT NULL,
    "remember_token" character varying(100),
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "image" character varying(255),
    "phone" character varying(255),
    "location" character varying(255),
    "birthdate" date,
    "bio" text,
    "category" character varying(255),
    "email_verified_at" timestamp(0) without time zone,
    CONSTRAINT "users_category_check" CHECK (((category)::text = ANY ((ARRAY['Fútbol'::character varying, 'Baloncesto'::character varying, 'Tenis'::character varying, 'Natación'::character varying, 'Ciclismo'::character varying, 'Atletismo'::character varying, 'Artes Marciales'::character varying])::text[]))),
    CONSTRAINT "users_pkey" PRIMARY KEY (id),
    CONSTRAINT "users_email_unique" UNIQUE (email)
);

-- =====================================================================
-- Integridad referencial
-- Se declaran al final para que el orden de creacion de tablas no importe.
-- =====================================================================

ALTER TABLE "achievements" ADD CONSTRAINT "achievements_trainer_id_foreign" FOREIGN KEY (trainer_id) REFERENCES trainer(id) ON DELETE CASCADE;
ALTER TABLE "cart_items" ADD CONSTRAINT "cart_items_cart_id_foreign" FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE;
ALTER TABLE "carts" ADD CONSTRAINT "carts_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE "chats" ADD CONSTRAINT "chats_trainer_id_foreign" FOREIGN KEY (trainer_id) REFERENCES trainer(id) ON DELETE CASCADE;
ALTER TABLE "chats" ADD CONSTRAINT "chats_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE "comments" ADD CONSTRAINT "comments_post_id_foreign" FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE;
ALTER TABLE "comments" ADD CONSTRAINT "comments_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE "configuration_user" ADD CONSTRAINT "configuration_user_configuration_id_foreign" FOREIGN KEY (configuration_id) REFERENCES configuration(id) ON DELETE CASCADE;
ALTER TABLE "configuration_user" ADD CONSTRAINT "configuration_user_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE "likes" ADD CONSTRAINT "likes_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE "messages" ADD CONSTRAINT "messages_chat_id_foreign" FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE;
ALTER TABLE "messages" ADD CONSTRAINT "messages_sender_id_foreign" FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE "posts" ADD CONSTRAINT "posts_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE "replies" ADD CONSTRAINT "replies_comment_id_foreign" FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE;
ALTER TABLE "replies" ADD CONSTRAINT "replies_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE "saved_news" ADD CONSTRAINT "saved_news_news_id_foreign" FOREIGN KEY (news_id) REFERENCES "NewsScrapping"(id) ON DELETE CASCADE;
ALTER TABLE "saved_news" ADD CONSTRAINT "saved_news_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE "specialties" ADD CONSTRAINT "specialties_trainer_id_foreign" FOREIGN KEY (trainer_id) REFERENCES trainer(id) ON DELETE CASCADE;
ALTER TABLE "trainer" ADD CONSTRAINT "trainer_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE "training_requests" ADD CONSTRAINT "training_requests_trainer_id_foreign" FOREIGN KEY (trainer_id) REFERENCES trainer(id) ON DELETE CASCADE;
ALTER TABLE "training_requests" ADD CONSTRAINT "training_requests_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- =====================================================================
-- Indices
-- =====================================================================

CREATE INDEX IF NOT EXISTS jobs_queue_index ON public.jobs USING btree (queue);
CREATE INDEX IF NOT EXISTS likes_likeable_type_likeable_id_index ON public.likes USING btree (likeable_type, likeable_id);
CREATE INDEX IF NOT EXISTS personal_access_tokens_token_index ON public.personal_access_tokens USING btree (token);
CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_id);
CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_type_index ON public.personal_access_tokens USING btree (tokenable_type);
CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON public.sessions USING btree (last_activity);
CREATE INDEX IF NOT EXISTS sessions_user_id_index ON public.sessions USING btree (user_id);
CREATE INDEX IF NOT EXISTS sports_sort_order_index ON public.sports USING btree (sort_order);

-- =====================================================================
-- Row Level Security (RLS)
-- Defensa a nivel de motor: aunque una consulta se cuele, Postgres
-- filtra las filas que el rol no tiene derecho a ver.
-- =====================================================================

ALTER TABLE "NewsScrapping" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "achievements" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "cache" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "cache_locks" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "calendars" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "cart_items" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "carts" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "chats" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "comments" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "configuration" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "configuration_user" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "failed_jobs" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "job_batches" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "jobs" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "likes" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "messages" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "migrations" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "news" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "password_reset_tokens" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "personal_access_tokens" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "posts" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "products" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "replies" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "saved_news" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "sessions" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "specialties" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "sports" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "trainer" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "training_requests" ENABLE ROW LEVEL SECURITY;
ALTER TABLE "users" ENABLE ROW LEVEL SECURITY;

CREATE POLICY "public_read_newsscrapping" ON "NewsScrapping"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (true);

CREATE POLICY "public_read_calendars" ON "calendars"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (true);

CREATE POLICY "public_read_comments" ON "comments"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (true);

CREATE POLICY "public_read_likes" ON "likes"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (true);

CREATE POLICY "public_read_posts" ON "posts"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (true);

CREATE POLICY "Public read access" ON "products"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (true);

CREATE POLICY "public_read_replies" ON "replies"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (true);

CREATE POLICY "Public read access" ON "sports"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (true);

CREATE POLICY "Public read of approved trainers" ON "trainer"
    AS PERMISSIVE
    FOR SELECT
    TO anon,authenticated
    USING (((status)::text = 'approved'::text));

COMMIT;
