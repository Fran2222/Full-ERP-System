--
-- PostgreSQL database dump
--

\restrict kr8wwwmOgr89Nj6OGQ77xuuApx76enjTz8hiPyk1aSfYV22Thp2kcud4LqH8gRJ

-- Dumped from database version 15.17
-- Dumped by pg_dump version 15.17

-- Started on 2026-05-14 10:53:08

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 241 (class 1259 OID 19698)
-- Name: announcements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.announcements (
    id bigint NOT NULL,
    title character varying(255) NOT NULL,
    content text NOT NULL,
    user_id bigint NOT NULL,
    is_published boolean DEFAULT false NOT NULL,
    published_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.announcements OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 19697)
-- Name: announcements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.announcements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.announcements_id_seq OWNER TO postgres;

--
-- TOC entry 3942 (class 0 OID 0)
-- Dependencies: 240
-- Name: announcements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.announcements_id_seq OWNED BY public.announcements.id;


--
-- TOC entry 235 (class 1259 OID 19646)
-- Name: branches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.branches (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    address text,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT branches_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[])))
);


ALTER TABLE public.branches OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 19645)
-- Name: branches_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.branches_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.branches_id_seq OWNER TO postgres;

--
-- TOC entry 3943 (class 0 OID 0)
-- Dependencies: 234
-- Name: branches_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.branches_id_seq OWNED BY public.branches.id;


--
-- TOC entry 283 (class 1259 OID 20215)
-- Name: customers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.customers (
    id bigint NOT NULL,
    customer_code character varying(255) NOT NULL,
    customer_name character varying(255) NOT NULL,
    contact_person character varying(255),
    phone character varying(255),
    email character varying(255),
    billing_address text,
    shipping_address text,
    tin character varying(255),
    payment_terms character varying(255),
    status boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.customers OWNER TO postgres;

--
-- TOC entry 282 (class 1259 OID 20214)
-- Name: customers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.customers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.customers_id_seq OWNER TO postgres;

--
-- TOC entry 3944 (class 0 OID 0)
-- Dependencies: 282
-- Name: customers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.customers_id_seq OWNED BY public.customers.id;


--
-- TOC entry 233 (class 1259 OID 19633)
-- Name: departments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.departments (
    id bigint NOT NULL,
    name character varying(150) NOT NULL,
    code character varying(50) NOT NULL,
    description text,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT departments_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[])))
);


ALTER TABLE public.departments OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 19632)
-- Name: departments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.departments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.departments_id_seq OWNER TO postgres;

--
-- TOC entry 3945 (class 0 OID 0)
-- Dependencies: 232
-- Name: departments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.departments_id_seq OWNED BY public.departments.id;


--
-- TOC entry 220 (class 1259 OID 19540)
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 19539)
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.failed_jobs_id_seq OWNER TO postgres;

--
-- TOC entry 3946 (class 0 OID 0)
-- Dependencies: 219
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- TOC entry 287 (class 1259 OID 20255)
-- Name: invoice_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invoice_items (
    id bigint NOT NULL,
    invoice_id bigint NOT NULL,
    item_id bigint,
    item_code character varying(255),
    item_name character varying(255) NOT NULL,
    description text,
    quantity numeric(15,2) DEFAULT '1'::numeric NOT NULL,
    unit_price numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    discount_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    tax_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    line_total numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.invoice_items OWNER TO postgres;

--
-- TOC entry 286 (class 1259 OID 20254)
-- Name: invoice_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.invoice_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.invoice_items_id_seq OWNER TO postgres;

--
-- TOC entry 3947 (class 0 OID 0)
-- Dependencies: 286
-- Name: invoice_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.invoice_items_id_seq OWNED BY public.invoice_items.id;


--
-- TOC entry 285 (class 1259 OID 20227)
-- Name: invoices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invoices (
    id bigint NOT NULL,
    invoice_no character varying(255) NOT NULL,
    customer_id bigint NOT NULL,
    invoice_date date NOT NULL,
    due_date date,
    reference_no character varying(255),
    payment_terms character varying(255),
    subtotal numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    discount_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    tax_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    total_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    paid_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    balance_due numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'unpaid'::character varying NOT NULL,
    notes text,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.invoices OWNER TO postgres;

--
-- TOC entry 284 (class 1259 OID 20226)
-- Name: invoices_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.invoices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.invoices_id_seq OWNER TO postgres;

--
-- TOC entry 3948 (class 0 OID 0)
-- Dependencies: 284
-- Name: invoices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.invoices_id_seq OWNED BY public.invoices.id;


--
-- TOC entry 231 (class 1259 OID 19621)
-- Name: media; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.media (
    id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL,
    uuid uuid,
    collection_name character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    file_name character varying(255) NOT NULL,
    mime_type character varying(255),
    disk character varying(255) NOT NULL,
    conversions_disk character varying(255),
    size bigint NOT NULL,
    manipulations json NOT NULL,
    custom_properties json NOT NULL,
    generated_conversions json NOT NULL,
    responsive_images json NOT NULL,
    order_column integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.media OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 19620)
-- Name: media_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.media_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.media_id_seq OWNER TO postgres;

--
-- TOC entry 3949 (class 0 OID 0)
-- Dependencies: 230
-- Name: media_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.media_id_seq OWNED BY public.media.id;


--
-- TOC entry 215 (class 1259 OID 19512)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 214 (class 1259 OID 19511)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO postgres;

--
-- TOC entry 3950 (class 0 OID 0)
-- Dependencies: 214
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 227 (class 1259 OID 19583)
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 19594)
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 19533)
-- Name: password_resets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_resets (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_resets OWNER TO postgres;

--
-- TOC entry 289 (class 1259 OID 20279)
-- Name: payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payments (
    id bigint NOT NULL,
    payment_no character varying(255) NOT NULL,
    customer_id bigint NOT NULL,
    invoice_id bigint,
    payment_date date NOT NULL,
    payment_method character varying(255) DEFAULT 'Cash'::character varying NOT NULL,
    reference_no character varying(255),
    amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.payments OWNER TO postgres;

--
-- TOC entry 288 (class 1259 OID 20278)
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.payments_id_seq OWNER TO postgres;

--
-- TOC entry 3951 (class 0 OID 0)
-- Dependencies: 288
-- Name: payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payments_id_seq OWNED BY public.payments.id;


--
-- TOC entry 224 (class 1259 OID 19561)
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    parent_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 19560)
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.permissions_id_seq OWNER TO postgres;

--
-- TOC entry 3952 (class 0 OID 0)
-- Dependencies: 223
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- TOC entry 297 (class 1259 OID 20401)
-- Name: purchase_order_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchase_order_items (
    id bigint NOT NULL,
    purchase_order_id bigint NOT NULL,
    item_id bigint NOT NULL,
    item_code character varying(255),
    item_name character varying(255) NOT NULL,
    description text,
    quantity numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    received_quantity numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    unit_name character varying(255),
    unit_price numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    tax_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    line_total numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.purchase_order_items OWNER TO postgres;

--
-- TOC entry 296 (class 1259 OID 20400)
-- Name: purchase_order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchase_order_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.purchase_order_items_id_seq OWNER TO postgres;

--
-- TOC entry 3953 (class 0 OID 0)
-- Dependencies: 296
-- Name: purchase_order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchase_order_items_id_seq OWNED BY public.purchase_order_items.id;


--
-- TOC entry 295 (class 1259 OID 20369)
-- Name: purchase_orders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchase_orders (
    id bigint NOT NULL,
    po_no character varying(255) NOT NULL,
    supplier_id bigint NOT NULL,
    po_date date NOT NULL,
    expected_date date,
    location_id bigint,
    reference_no character varying(255),
    ship_via character varying(255),
    payment_terms character varying(255),
    subtotal numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    tax_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    total_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    notes text,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.purchase_orders OWNER TO postgres;

--
-- TOC entry 294 (class 1259 OID 20368)
-- Name: purchase_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchase_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.purchase_orders_id_seq OWNER TO postgres;

--
-- TOC entry 3954 (class 0 OID 0)
-- Dependencies: 294
-- Name: purchase_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchase_orders_id_seq OWNED BY public.purchase_orders.id;


--
-- TOC entry 229 (class 1259 OID 19605)
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 19572)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    status smallint DEFAULT '1'::smallint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 19571)
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.roles_id_seq OWNER TO postgres;

--
-- TOC entry 3955 (class 0 OID 0)
-- Dependencies: 225
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- TOC entry 293 (class 1259 OID 20335)
-- Name: sales_receipt_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sales_receipt_items (
    id bigint NOT NULL,
    sales_receipt_id bigint NOT NULL,
    item_id bigint,
    item_code character varying(255),
    item_name character varying(255) NOT NULL,
    description text,
    quantity numeric(15,2) DEFAULT '1'::numeric NOT NULL,
    unit_price numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    discount_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    tax_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    line_total numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sales_receipt_items OWNER TO postgres;

--
-- TOC entry 292 (class 1259 OID 20334)
-- Name: sales_receipt_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sales_receipt_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sales_receipt_items_id_seq OWNER TO postgres;

--
-- TOC entry 3956 (class 0 OID 0)
-- Dependencies: 292
-- Name: sales_receipt_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sales_receipt_items_id_seq OWNED BY public.sales_receipt_items.id;


--
-- TOC entry 291 (class 1259 OID 20307)
-- Name: sales_receipts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sales_receipts (
    id bigint NOT NULL,
    receipt_no character varying(255) NOT NULL,
    customer_id bigint NOT NULL,
    receipt_date date NOT NULL,
    payment_method character varying(255) DEFAULT 'Cash'::character varying NOT NULL,
    reference_no character varying(255),
    subtotal numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    discount_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    tax_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    total_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    paid_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'paid'::character varying NOT NULL,
    notes text,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    branch_id bigint,
    location_id bigint
);


ALTER TABLE public.sales_receipts OWNER TO postgres;

--
-- TOC entry 290 (class 1259 OID 20306)
-- Name: sales_receipts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sales_receipts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sales_receipts_id_seq OWNER TO postgres;

--
-- TOC entry 3957 (class 0 OID 0)
-- Dependencies: 290
-- Name: sales_receipts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sales_receipts_id_seq OWNED BY public.sales_receipts.id;


--
-- TOC entry 239 (class 1259 OID 19686)
-- Name: user_module_accesses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_module_accesses (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    module character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.user_module_accesses OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 19685)
-- Name: user_module_accesses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_module_accesses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_module_accesses_id_seq OWNER TO postgres;

--
-- TOC entry 3958 (class 0 OID 0)
-- Dependencies: 238
-- Name: user_module_accesses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_module_accesses_id_seq OWNED BY public.user_module_accesses.id;


--
-- TOC entry 237 (class 1259 OID 19669)
-- Name: user_module_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_module_assignments (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    module character varying(255) NOT NULL,
    access_level character varying(255) NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.user_module_assignments OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 19668)
-- Name: user_module_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_module_assignments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_module_assignments_id_seq OWNER TO postgres;

--
-- TOC entry 3959 (class 0 OID 0)
-- Dependencies: 236
-- Name: user_module_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_module_assignments_id_seq OWNED BY public.user_module_assignments.id;


--
-- TOC entry 222 (class 1259 OID 19552)
-- Name: user_profiles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_profiles (
    id bigint NOT NULL,
    company_name character varying(255),
    street_addr_1 character varying(255),
    street_addr_2 character varying(255),
    phone_number character varying(255),
    alt_phone_number character varying(255),
    country character varying(255),
    state character varying(255),
    city character varying(255),
    pin_code bigint,
    facebook_url character varying(255),
    instagram_url character varying(255),
    twitter_url character varying(255),
    linkdin_url character varying(255),
    user_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.user_profiles OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 19551)
-- Name: user_profiles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_profiles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_profiles_id_seq OWNER TO postgres;

--
-- TOC entry 3960 (class 0 OID 0)
-- Dependencies: 221
-- Name: user_profiles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_profiles_id_seq OWNED BY public.user_profiles.id;


--
-- TOC entry 217 (class 1259 OID 19519)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    username character varying(255) NOT NULL,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone_number character varying(255),
    email_verified_at timestamp(0) without time zone,
    user_type character varying(255) DEFAULT 'user'::character varying NOT NULL,
    password character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    branch_id bigint,
    department_id bigint,
    primary_module character varying(255)
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 216 (class 1259 OID 19518)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 3961 (class 0 OID 0)
-- Dependencies: 216
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 243 (class 1259 OID 19713)
-- Name: warehouse_categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_categories (
    id bigint NOT NULL,
    name character varying(150) NOT NULL,
    description text,
    status boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_categories OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 19712)
-- Name: warehouse_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_categories_id_seq OWNER TO postgres;

--
-- TOC entry 3962 (class 0 OID 0)
-- Dependencies: 242
-- Name: warehouse_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_categories_id_seq OWNED BY public.warehouse_categories.id;


--
-- TOC entry 281 (class 1259 OID 20187)
-- Name: warehouse_inventories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_inventories (
    id bigint NOT NULL,
    item_id bigint NOT NULL,
    branch_id bigint NOT NULL,
    location_id bigint NOT NULL,
    quantity numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_inventories OWNER TO postgres;

--
-- TOC entry 280 (class 1259 OID 20186)
-- Name: warehouse_inventories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_inventories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_inventories_id_seq OWNER TO postgres;

--
-- TOC entry 3963 (class 0 OID 0)
-- Dependencies: 280
-- Name: warehouse_inventories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_inventories_id_seq OWNED BY public.warehouse_inventories.id;


--
-- TOC entry 265 (class 1259 OID 19943)
-- Name: warehouse_item_serials; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_item_serials (
    id bigint NOT NULL,
    item_id bigint NOT NULL,
    location_id bigint NOT NULL,
    serial_number character varying(150) NOT NULL,
    status character varying(30) DEFAULT 'available'::character varying NOT NULL,
    receiving_id bigint,
    transfer_id bigint,
    received_date date,
    remarks text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_item_serials OWNER TO postgres;

--
-- TOC entry 264 (class 1259 OID 19942)
-- Name: warehouse_item_serials_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_item_serials_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_item_serials_id_seq OWNER TO postgres;

--
-- TOC entry 3964 (class 0 OID 0)
-- Dependencies: 264
-- Name: warehouse_item_serials_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_item_serials_id_seq OWNED BY public.warehouse_item_serials.id;


--
-- TOC entry 253 (class 1259 OID 19790)
-- Name: warehouse_item_stocks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_item_stocks (
    id bigint NOT NULL,
    item_id bigint NOT NULL,
    location_id bigint NOT NULL,
    quantity_on_hand integer DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_item_stocks OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 19789)
-- Name: warehouse_item_stocks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_item_stocks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_item_stocks_id_seq OWNER TO postgres;

--
-- TOC entry 3965 (class 0 OID 0)
-- Dependencies: 252
-- Name: warehouse_item_stocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_item_stocks_id_seq OWNED BY public.warehouse_item_stocks.id;


--
-- TOC entry 251 (class 1259 OID 19761)
-- Name: warehouse_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_items (
    id bigint NOT NULL,
    item_code character varying(100) NOT NULL,
    item_name character varying(200) NOT NULL,
    description text,
    category_id bigint NOT NULL,
    unit_id bigint NOT NULL,
    default_supplier_id bigint,
    minimum_stock integer DEFAULT 0 NOT NULL,
    is_serialized boolean DEFAULT false NOT NULL,
    status boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    code character varying(255),
    reorder_level integer DEFAULT 0 NOT NULL,
    name character varying(255),
    supplier_id bigint,
    cost_price numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    selling_price numeric(12,2) DEFAULT '0'::numeric NOT NULL
);


ALTER TABLE public.warehouse_items OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 19760)
-- Name: warehouse_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_items_id_seq OWNER TO postgres;

--
-- TOC entry 3966 (class 0 OID 0)
-- Dependencies: 250
-- Name: warehouse_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_items_id_seq OWNED BY public.warehouse_items.id;


--
-- TOC entry 249 (class 1259 OID 19744)
-- Name: warehouse_locations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_locations (
    id bigint NOT NULL,
    location_code character varying(50) NOT NULL,
    location_name character varying(200) NOT NULL,
    location_type character varying(30) NOT NULL,
    branch_id bigint,
    address text,
    status boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    name character varying(255)
);


ALTER TABLE public.warehouse_locations OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 19743)
-- Name: warehouse_locations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_locations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_locations_id_seq OWNER TO postgres;

--
-- TOC entry 3967 (class 0 OID 0)
-- Dependencies: 248
-- Name: warehouse_locations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_locations_id_seq OWNED BY public.warehouse_locations.id;


--
-- TOC entry 279 (class 1259 OID 20096)
-- Name: warehouse_opening_balance_item_serials; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_opening_balance_item_serials (
    id bigint NOT NULL,
    opening_balance_item_id bigint NOT NULL,
    serial_number character varying(150) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_opening_balance_item_serials OWNER TO postgres;

--
-- TOC entry 278 (class 1259 OID 20095)
-- Name: warehouse_opening_balance_item_serials_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_opening_balance_item_serials_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_opening_balance_item_serials_id_seq OWNER TO postgres;

--
-- TOC entry 3968 (class 0 OID 0)
-- Dependencies: 278
-- Name: warehouse_opening_balance_item_serials_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_opening_balance_item_serials_id_seq OWNED BY public.warehouse_opening_balance_item_serials.id;


--
-- TOC entry 277 (class 1259 OID 20072)
-- Name: warehouse_opening_balance_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_opening_balance_items (
    id bigint NOT NULL,
    opening_balance_id bigint NOT NULL,
    item_id bigint NOT NULL,
    location_id bigint NOT NULL,
    quantity integer NOT NULL,
    remarks text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_opening_balance_items OWNER TO postgres;

--
-- TOC entry 276 (class 1259 OID 20071)
-- Name: warehouse_opening_balance_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_opening_balance_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_opening_balance_items_id_seq OWNER TO postgres;

--
-- TOC entry 3969 (class 0 OID 0)
-- Dependencies: 276
-- Name: warehouse_opening_balance_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_opening_balance_items_id_seq OWNED BY public.warehouse_opening_balance_items.id;


--
-- TOC entry 275 (class 1259 OID 20055)
-- Name: warehouse_opening_balances; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_opening_balances (
    id bigint NOT NULL,
    opening_no character varying(100) NOT NULL,
    reference_no character varying(100),
    opening_date date NOT NULL,
    remarks text,
    created_by bigint,
    status character varying(30) DEFAULT 'posted'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_opening_balances OWNER TO postgres;

--
-- TOC entry 274 (class 1259 OID 20054)
-- Name: warehouse_opening_balances_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_opening_balances_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_opening_balances_id_seq OWNER TO postgres;

--
-- TOC entry 3970 (class 0 OID 0)
-- Dependencies: 274
-- Name: warehouse_opening_balances_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_opening_balances_id_seq OWNED BY public.warehouse_opening_balances.id;


--
-- TOC entry 257 (class 1259 OID 19837)
-- Name: warehouse_receiving_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_receiving_items (
    id bigint NOT NULL,
    receiving_id bigint NOT NULL,
    item_id bigint NOT NULL,
    quantity integer NOT NULL,
    unit_cost numeric(15,2),
    total_cost numeric(15,2),
    remarks text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_receiving_items OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 19836)
-- Name: warehouse_receiving_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_receiving_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_receiving_items_id_seq OWNER TO postgres;

--
-- TOC entry 3971 (class 0 OID 0)
-- Dependencies: 256
-- Name: warehouse_receiving_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_receiving_items_id_seq OWNED BY public.warehouse_receiving_items.id;


--
-- TOC entry 255 (class 1259 OID 19810)
-- Name: warehouse_receivings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_receivings (
    id bigint NOT NULL,
    receiving_no character varying(100) NOT NULL,
    supplier_id bigint NOT NULL,
    location_id bigint NOT NULL,
    reference_no character varying(100),
    received_date date NOT NULL,
    remarks text,
    received_by bigint,
    status character varying(30) DEFAULT 'posted'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_receivings OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 19809)
-- Name: warehouse_receivings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_receivings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_receivings_id_seq OWNER TO postgres;

--
-- TOC entry 3972 (class 0 OID 0)
-- Dependencies: 254
-- Name: warehouse_receivings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_receivings_id_seq OWNED BY public.warehouse_receivings.id;


--
-- TOC entry 273 (class 1259 OID 20036)
-- Name: warehouse_stock_adjustment_item_serials; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_stock_adjustment_item_serials (
    id bigint NOT NULL,
    adjustment_item_id bigint NOT NULL,
    item_serial_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_stock_adjustment_item_serials OWNER TO postgres;

--
-- TOC entry 272 (class 1259 OID 20035)
-- Name: warehouse_stock_adjustment_item_serials_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_stock_adjustment_item_serials_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_stock_adjustment_item_serials_id_seq OWNER TO postgres;

--
-- TOC entry 3973 (class 0 OID 0)
-- Dependencies: 272
-- Name: warehouse_stock_adjustment_item_serials_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_stock_adjustment_item_serials_id_seq OWNED BY public.warehouse_stock_adjustment_item_serials.id;


--
-- TOC entry 271 (class 1259 OID 20017)
-- Name: warehouse_stock_adjustment_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_stock_adjustment_items (
    id bigint NOT NULL,
    adjustment_id bigint NOT NULL,
    item_id bigint NOT NULL,
    quantity integer NOT NULL,
    remarks text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_stock_adjustment_items OWNER TO postgres;

--
-- TOC entry 270 (class 1259 OID 20016)
-- Name: warehouse_stock_adjustment_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_stock_adjustment_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_stock_adjustment_items_id_seq OWNER TO postgres;

--
-- TOC entry 3974 (class 0 OID 0)
-- Dependencies: 270
-- Name: warehouse_stock_adjustment_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_stock_adjustment_items_id_seq OWNED BY public.warehouse_stock_adjustment_items.id;


--
-- TOC entry 269 (class 1259 OID 19995)
-- Name: warehouse_stock_adjustments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_stock_adjustments (
    id bigint NOT NULL,
    adjustment_no character varying(100) NOT NULL,
    location_id bigint NOT NULL,
    adjustment_date date NOT NULL,
    adjustment_type character varying(30) NOT NULL,
    remarks text,
    created_by bigint,
    status character varying(30) DEFAULT 'posted'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_stock_adjustments OWNER TO postgres;

--
-- TOC entry 268 (class 1259 OID 19994)
-- Name: warehouse_stock_adjustments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_stock_adjustments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_stock_adjustments_id_seq OWNER TO postgres;

--
-- TOC entry 3975 (class 0 OID 0)
-- Dependencies: 268
-- Name: warehouse_stock_adjustments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_stock_adjustments_id_seq OWNED BY public.warehouse_stock_adjustments.id;


--
-- TOC entry 263 (class 1259 OID 19917)
-- Name: warehouse_stock_movements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_stock_movements (
    id bigint NOT NULL,
    item_id bigint NOT NULL,
    location_id bigint NOT NULL,
    movement_type character varying(50) NOT NULL,
    quantity integer NOT NULL,
    balance_after integer DEFAULT '0'::numeric NOT NULL,
    reference_type character varying(50),
    reference_id bigint,
    remarks text,
    transaction_date date NOT NULL,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_stock_movements OWNER TO postgres;

--
-- TOC entry 262 (class 1259 OID 19916)
-- Name: warehouse_stock_movements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_stock_movements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_stock_movements_id_seq OWNER TO postgres;

--
-- TOC entry 3976 (class 0 OID 0)
-- Dependencies: 262
-- Name: warehouse_stock_movements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_stock_movements_id_seq OWNED BY public.warehouse_stock_movements.id;


--
-- TOC entry 247 (class 1259 OID 19734)
-- Name: warehouse_suppliers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_suppliers (
    id bigint NOT NULL,
    supplier_name character varying(200) NOT NULL,
    contact_person character varying(150),
    phone character varying(50),
    email character varying(150),
    address text,
    status boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_suppliers OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 19733)
-- Name: warehouse_suppliers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_suppliers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_suppliers_id_seq OWNER TO postgres;

--
-- TOC entry 3977 (class 0 OID 0)
-- Dependencies: 246
-- Name: warehouse_suppliers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_suppliers_id_seq OWNED BY public.warehouse_suppliers.id;


--
-- TOC entry 267 (class 1259 OID 19976)
-- Name: warehouse_transfer_item_serials; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_transfer_item_serials (
    id bigint NOT NULL,
    transfer_item_id bigint NOT NULL,
    item_serial_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_transfer_item_serials OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 19975)
-- Name: warehouse_transfer_item_serials_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_transfer_item_serials_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_transfer_item_serials_id_seq OWNER TO postgres;

--
-- TOC entry 3978 (class 0 OID 0)
-- Dependencies: 266
-- Name: warehouse_transfer_item_serials_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_transfer_item_serials_id_seq OWNED BY public.warehouse_transfer_item_serials.id;


--
-- TOC entry 261 (class 1259 OID 19898)
-- Name: warehouse_transfer_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_transfer_items (
    id bigint NOT NULL,
    transfer_id bigint NOT NULL,
    item_id bigint NOT NULL,
    quantity integer NOT NULL,
    remarks text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_transfer_items OWNER TO postgres;

--
-- TOC entry 260 (class 1259 OID 19897)
-- Name: warehouse_transfer_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_transfer_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_transfer_items_id_seq OWNER TO postgres;

--
-- TOC entry 3979 (class 0 OID 0)
-- Dependencies: 260
-- Name: warehouse_transfer_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_transfer_items_id_seq OWNED BY public.warehouse_transfer_items.id;


--
-- TOC entry 259 (class 1259 OID 19856)
-- Name: warehouse_transfers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_transfers (
    id bigint NOT NULL,
    transfer_no character varying(100) NOT NULL,
    from_location_id bigint NOT NULL,
    to_location_id bigint NOT NULL,
    transfer_date date NOT NULL,
    remarks text,
    requested_by bigint,
    approved_by bigint,
    transferred_by bigint,
    received_by bigint,
    status character varying(30) DEFAULT 'completed'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_transfers OWNER TO postgres;

--
-- TOC entry 258 (class 1259 OID 19855)
-- Name: warehouse_transfers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_transfers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_transfers_id_seq OWNER TO postgres;

--
-- TOC entry 3980 (class 0 OID 0)
-- Dependencies: 258
-- Name: warehouse_transfers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_transfers_id_seq OWNED BY public.warehouse_transfers.id;


--
-- TOC entry 245 (class 1259 OID 19725)
-- Name: warehouse_units; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.warehouse_units (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    abbreviation character varying(30) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.warehouse_units OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 19724)
-- Name: warehouse_units_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.warehouse_units_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.warehouse_units_id_seq OWNER TO postgres;

--
-- TOC entry 3981 (class 0 OID 0)
-- Dependencies: 244
-- Name: warehouse_units_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.warehouse_units_id_seq OWNED BY public.warehouse_units.id;


--
-- TOC entry 3402 (class 2604 OID 19701)
-- Name: announcements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements ALTER COLUMN id SET DEFAULT nextval('public.announcements_id_seq'::regclass);


--
-- TOC entry 3397 (class 2604 OID 19649)
-- Name: branches id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.branches ALTER COLUMN id SET DEFAULT nextval('public.branches_id_seq'::regclass);


--
-- TOC entry 3441 (class 2604 OID 20218)
-- Name: customers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers ALTER COLUMN id SET DEFAULT nextval('public.customers_id_seq'::regclass);


--
-- TOC entry 3395 (class 2604 OID 19636)
-- Name: departments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments ALTER COLUMN id SET DEFAULT nextval('public.departments_id_seq'::regclass);


--
-- TOC entry 3388 (class 2604 OID 19543)
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- TOC entry 3451 (class 2604 OID 20258)
-- Name: invoice_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoice_items ALTER COLUMN id SET DEFAULT nextval('public.invoice_items_id_seq'::regclass);


--
-- TOC entry 3443 (class 2604 OID 20230)
-- Name: invoices id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoices ALTER COLUMN id SET DEFAULT nextval('public.invoices_id_seq'::regclass);


--
-- TOC entry 3394 (class 2604 OID 19624)
-- Name: media id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media ALTER COLUMN id SET DEFAULT nextval('public.media_id_seq'::regclass);


--
-- TOC entry 3384 (class 2604 OID 19515)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 3457 (class 2604 OID 20282)
-- Name: payments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments ALTER COLUMN id SET DEFAULT nextval('public.payments_id_seq'::regclass);


--
-- TOC entry 3391 (class 2604 OID 19564)
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- TOC entry 3479 (class 2604 OID 20404)
-- Name: purchase_order_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_items ALTER COLUMN id SET DEFAULT nextval('public.purchase_order_items_id_seq'::regclass);


--
-- TOC entry 3474 (class 2604 OID 20372)
-- Name: purchase_orders id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders ALTER COLUMN id SET DEFAULT nextval('public.purchase_orders_id_seq'::regclass);


--
-- TOC entry 3392 (class 2604 OID 19575)
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- TOC entry 3468 (class 2604 OID 20338)
-- Name: sales_receipt_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipt_items ALTER COLUMN id SET DEFAULT nextval('public.sales_receipt_items_id_seq'::regclass);


--
-- TOC entry 3460 (class 2604 OID 20310)
-- Name: sales_receipts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipts ALTER COLUMN id SET DEFAULT nextval('public.sales_receipts_id_seq'::regclass);


--
-- TOC entry 3401 (class 2604 OID 19689)
-- Name: user_module_accesses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_module_accesses ALTER COLUMN id SET DEFAULT nextval('public.user_module_accesses_id_seq'::regclass);


--
-- TOC entry 3399 (class 2604 OID 19672)
-- Name: user_module_assignments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_module_assignments ALTER COLUMN id SET DEFAULT nextval('public.user_module_assignments_id_seq'::regclass);


--
-- TOC entry 3390 (class 2604 OID 19555)
-- Name: user_profiles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_profiles ALTER COLUMN id SET DEFAULT nextval('public.user_profiles_id_seq'::regclass);


--
-- TOC entry 3385 (class 2604 OID 19522)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 3404 (class 2604 OID 19716)
-- Name: warehouse_categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_categories ALTER COLUMN id SET DEFAULT nextval('public.warehouse_categories_id_seq'::regclass);


--
-- TOC entry 3439 (class 2604 OID 20190)
-- Name: warehouse_inventories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_inventories ALTER COLUMN id SET DEFAULT nextval('public.warehouse_inventories_id_seq'::regclass);


--
-- TOC entry 3428 (class 2604 OID 19946)
-- Name: warehouse_item_serials id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_serials ALTER COLUMN id SET DEFAULT nextval('public.warehouse_item_serials_id_seq'::regclass);


--
-- TOC entry 3418 (class 2604 OID 19793)
-- Name: warehouse_item_stocks id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_stocks ALTER COLUMN id SET DEFAULT nextval('public.warehouse_item_stocks_id_seq'::regclass);


--
-- TOC entry 3411 (class 2604 OID 19764)
-- Name: warehouse_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_items ALTER COLUMN id SET DEFAULT nextval('public.warehouse_items_id_seq'::regclass);


--
-- TOC entry 3409 (class 2604 OID 19747)
-- Name: warehouse_locations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_locations ALTER COLUMN id SET DEFAULT nextval('public.warehouse_locations_id_seq'::regclass);


--
-- TOC entry 3438 (class 2604 OID 20099)
-- Name: warehouse_opening_balance_item_serials id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_item_serials ALTER COLUMN id SET DEFAULT nextval('public.warehouse_opening_balance_item_serials_id_seq'::regclass);


--
-- TOC entry 3437 (class 2604 OID 20075)
-- Name: warehouse_opening_balance_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_items ALTER COLUMN id SET DEFAULT nextval('public.warehouse_opening_balance_items_id_seq'::regclass);


--
-- TOC entry 3435 (class 2604 OID 20058)
-- Name: warehouse_opening_balances id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balances ALTER COLUMN id SET DEFAULT nextval('public.warehouse_opening_balances_id_seq'::regclass);


--
-- TOC entry 3422 (class 2604 OID 19840)
-- Name: warehouse_receiving_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receiving_items ALTER COLUMN id SET DEFAULT nextval('public.warehouse_receiving_items_id_seq'::regclass);


--
-- TOC entry 3420 (class 2604 OID 19813)
-- Name: warehouse_receivings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receivings ALTER COLUMN id SET DEFAULT nextval('public.warehouse_receivings_id_seq'::regclass);


--
-- TOC entry 3434 (class 2604 OID 20039)
-- Name: warehouse_stock_adjustment_item_serials id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_item_serials ALTER COLUMN id SET DEFAULT nextval('public.warehouse_stock_adjustment_item_serials_id_seq'::regclass);


--
-- TOC entry 3433 (class 2604 OID 20020)
-- Name: warehouse_stock_adjustment_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_items ALTER COLUMN id SET DEFAULT nextval('public.warehouse_stock_adjustment_items_id_seq'::regclass);


--
-- TOC entry 3431 (class 2604 OID 19998)
-- Name: warehouse_stock_adjustments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustments ALTER COLUMN id SET DEFAULT nextval('public.warehouse_stock_adjustments_id_seq'::regclass);


--
-- TOC entry 3426 (class 2604 OID 19920)
-- Name: warehouse_stock_movements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_movements ALTER COLUMN id SET DEFAULT nextval('public.warehouse_stock_movements_id_seq'::regclass);


--
-- TOC entry 3407 (class 2604 OID 19737)
-- Name: warehouse_suppliers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_suppliers ALTER COLUMN id SET DEFAULT nextval('public.warehouse_suppliers_id_seq'::regclass);


--
-- TOC entry 3430 (class 2604 OID 19979)
-- Name: warehouse_transfer_item_serials id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_item_serials ALTER COLUMN id SET DEFAULT nextval('public.warehouse_transfer_item_serials_id_seq'::regclass);


--
-- TOC entry 3425 (class 2604 OID 19901)
-- Name: warehouse_transfer_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_items ALTER COLUMN id SET DEFAULT nextval('public.warehouse_transfer_items_id_seq'::regclass);


--
-- TOC entry 3423 (class 2604 OID 19859)
-- Name: warehouse_transfers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers ALTER COLUMN id SET DEFAULT nextval('public.warehouse_transfers_id_seq'::regclass);


--
-- TOC entry 3406 (class 2604 OID 19728)
-- Name: warehouse_units id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_units ALTER COLUMN id SET DEFAULT nextval('public.warehouse_units_id_seq'::regclass);


--
-- TOC entry 3880 (class 0 OID 19698)
-- Dependencies: 241
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcements (id, title, content, user_id, is_published, published_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3874 (class 0 OID 19646)
-- Dependencies: 235
-- Data for Name: branches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.branches (id, name, code, address, status, created_at, updated_at) FROM stdin;
1	Wizmaster Solana	SOLANA BRANCH MAIN	test	active	2026-04-13 02:34:50	2026-04-13 02:34:50
2	Wizmaster Cagayan de Oro	WIZMASTER CDO	Cagayan de Oro	active	2026-04-29 15:32:02	2026-04-29 15:32:02
\.


--
-- TOC entry 3922 (class 0 OID 20215)
-- Dependencies: 283
-- Data for Name: customers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.customers (id, customer_code, customer_name, contact_person, phone, email, billing_address, shipping_address, tin, payment_terms, status, created_at, updated_at) FROM stdin;
1	KEPTE	KEPTE STORE	KEPTE	123312123	kepte@gmail.com	KEPTE STORE	Test	123123123	Net 30	t	2026-04-30 02:40:36	2026-04-30 02:45:44
\.


--
-- TOC entry 3872 (class 0 OID 19633)
-- Dependencies: 233
-- Data for Name: departments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departments (id, name, code, description, status, created_at, updated_at) FROM stdin;
1	Warehouse	Warehouse	Purchasing	active	2026-04-17 08:48:05	2026-04-17 08:48:05
2	Purchasing	Purchasing	Test	active	2026-05-02 03:09:47	2026-05-02 03:09:47
\.


--
-- TOC entry 3859 (class 0 OID 19540)
-- Dependencies: 220
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- TOC entry 3926 (class 0 OID 20255)
-- Dependencies: 287
-- Data for Name: invoice_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.invoice_items (id, invoice_id, item_id, item_code, item_name, description, quantity, unit_price, discount_amount, tax_amount, line_total, created_at, updated_at) FROM stdin;
1	1	3	ITEM-00002	Test Item	test	1.00	2000.00	0.00	0.00	2000.00	2026-04-30 02:54:26	2026-04-30 02:54:26
2	2	3	ITEM-00002	Test Item	test	1.00	2000.00	0.00	0.00	2000.00	2026-05-01 02:42:31	2026-05-01 02:42:31
3	3	3	ITEM-00002	Test Item	test	1.00	2000.00	0.00	0.00	2000.00	2026-05-01 02:45:49	2026-05-01 02:45:49
\.


--
-- TOC entry 3924 (class 0 OID 20227)
-- Dependencies: 285
-- Data for Name: invoices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.invoices (id, invoice_no, customer_id, invoice_date, due_date, reference_no, payment_terms, subtotal, discount_amount, tax_amount, total_amount, paid_amount, balance_due, status, notes, created_by, created_at, updated_at) FROM stdin;
1	INV-20260430-0001	1	2026-04-30	2026-05-09	\N	Net 30	2000.00	0.00	0.00	2000.00	2000.00	0.00	paid	Test	1	2026-04-30 02:54:26	2026-04-30 03:04:36
2	INV-20260501-0001	1	2026-05-01	2026-05-08	\N	Net 7	2000.00	0.00	0.00	2000.00	0.00	2000.00	unpaid	Test due soon invoice	1	2026-05-01 02:42:31	2026-05-01 02:42:31
3	INV-20260501-0002	1	2026-04-20	2026-04-26	\N	Due on receipt	2000.00	0.00	0.00	2000.00	500.00	1500.00	partially_paid	Test overdue	1	2026-05-01 02:45:49	2026-05-01 03:13:51
\.


--
-- TOC entry 3870 (class 0 OID 19621)
-- Dependencies: 231
-- Data for Name: media; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.media (id, model_type, model_id, uuid, collection_name, name, file_name, mime_type, disk, conversions_disk, size, manipulations, custom_properties, generated_conversions, responsive_images, order_column, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3854 (class 0 OID 19512)
-- Dependencies: 215
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2014_10_12_000000_create_users_table	1
2	2014_10_12_100000_create_password_resets_table	1
3	2019_08_19_000000_create_failed_jobs_table	1
4	2021_11_09_064224_create_user_profiles_table	1
5	2021_11_11_110731_create_permission_tables	1
6	2021_11_16_114009_create_media_table	1
7	2026_04_03_000001_create_departments_table	1
8	2026_04_03_015945_create_branches_table	1
9	2026_04_03_020156_add_branch_id_to_users_table	1
10	2026_04_03_134629_add_department_id_to_users_table	1
11	2026_04_03_150000_force_add_department_id_to_users_table	1
12	2026_04_04_000001_create_user_module_assignments_table	1
13	2026_04_04_054006_add_primary_module_to_users_table	1
14	2026_04_04_054048_create_user_module_accesses_table	1
15	2026_04_08_021117_create_announcements_table	1
16	2026_04_10_011008_create_warehouse_categories_table	1
17	2026_04_10_011012_create_warehouse_units_table	1
18	2026_04_10_011014_create_warehouse_suppliers_table	1
19	2026_04_10_011015_create_warehouse_locations_table	1
20	2026_04_10_011016_create_warehouse_items_table	1
21	2026_04_10_011017_create_warehouse_item_stocks_table	1
22	2026_04_10_011017_create_warehouse_receivings_table	1
23	2026_04_10_011018_create_warehouse_receiving_items_table	1
24	2026_04_10_011020_create_warehouse_transfers_table	1
25	2026_04_10_011021_create_warehouse_transfer_items_table	1
26	2026_04_10_011024_create_warehouse_stock_movements_table	1
27	2026_04_13_000001_create_warehouse_item_serials_table	1
28	2026_04_13_000002_create_warehouse_transfer_item_serials_table	1
29	2026_04_13_000003_create_warehouse_stock_adjustments_table	1
30	2026_04_13_000004_create_warehouse_stock_adjustment_items_table	1
31	2026_04_13_000005_create_warehouse_stock_adjustment_item_serials_table	1
32	2026_04_13_000006_create_warehouse_opening_balances_table	1
33	2026_04_13_000007_create_warehouse_opening_balance_items_table	1
34	2026_04_13_000008_create_warehouse_opening_balance_item_serials_table	1
35	2026_04_16_000100_convert_warehouse_quantities_to_integer_and_add_indexes	2
36	2026_04_25_090001_create_warehouse_phase1_tables	3
37	2026_04_25_100001_create_warehouse_phase2_tables	4
38	2026_04_25_074028_create_warehouse_inventory_tables	5
39	2026_04_28_000001_add_code_to_warehouse_items	6
40	2026_04_28_000002_fix_warehouse_items_name_column	7
41	2026_04_28_000003_safely_fix_warehouse_items_columns	8
42	2026_04_28_000003_fix_warehouse_locations_name_status	9
43	2026_04_30_022853_create_customers_table	10
44	2026_04_30_024714_create_invoices_table	11
45	2026_04_30_024718_create_invoice_items_table	11
46	2026_04_30_025547_create_payments_table	12
47	2026_04_30_030759_create_sales_receipts_table	13
48	2026_04_30_030801_create_sales_receipt_items_table	13
49	2026_04_30_102915_add_branch_location_to_sales_receipts_table	14
50	2026_05_01_033717_create_purchase_orders_table	15
51	2026_05_01_033720_create_purchase_order_items_table	15
\.


--
-- TOC entry 3866 (class 0 OID 19583)
-- Dependencies: 227
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
77	App\\Models\\User	9
86	App\\Models\\User	9
87	App\\Models\\User	9
88	App\\Models\\User	9
92	App\\Models\\User	9
110	App\\Models\\User	9
111	App\\Models\\User	9
112	App\\Models\\User	9
123	App\\Models\\User	9
124	App\\Models\\User	9
125	App\\Models\\User	9
126	App\\Models\\User	9
129	App\\Models\\User	9
135	App\\Models\\User	9
136	App\\Models\\User	9
137	App\\Models\\User	9
138	App\\Models\\User	9
139	App\\Models\\User	9
140	App\\Models\\User	9
141	App\\Models\\User	9
142	App\\Models\\User	9
77	App\\Models\\User	8
78	App\\Models\\User	8
80	App\\Models\\User	8
82	App\\Models\\User	8
84	App\\Models\\User	8
86	App\\Models\\User	8
87	App\\Models\\User	8
88	App\\Models\\User	8
92	App\\Models\\User	8
110	App\\Models\\User	8
111	App\\Models\\User	8
112	App\\Models\\User	8
113	App\\Models\\User	8
114	App\\Models\\User	8
115	App\\Models\\User	8
116	App\\Models\\User	8
117	App\\Models\\User	8
118	App\\Models\\User	8
119	App\\Models\\User	8
120	App\\Models\\User	8
121	App\\Models\\User	8
122	App\\Models\\User	8
123	App\\Models\\User	8
124	App\\Models\\User	8
125	App\\Models\\User	8
126	App\\Models\\User	8
127	App\\Models\\User	8
128	App\\Models\\User	8
129	App\\Models\\User	8
130	App\\Models\\User	8
131	App\\Models\\User	8
132	App\\Models\\User	8
133	App\\Models\\User	8
134	App\\Models\\User	8
135	App\\Models\\User	8
136	App\\Models\\User	8
137	App\\Models\\User	8
138	App\\Models\\User	8
139	App\\Models\\User	8
140	App\\Models\\User	8
141	App\\Models\\User	8
142	App\\Models\\User	8
143	App\\Models\\User	8
144	App\\Models\\User	8
143	App\\Models\\User	9
144	App\\Models\\User	9
77	App\\Models\\User	2
78	App\\Models\\User	2
80	App\\Models\\User	2
82	App\\Models\\User	2
84	App\\Models\\User	2
86	App\\Models\\User	2
87	App\\Models\\User	2
88	App\\Models\\User	2
92	App\\Models\\User	2
110	App\\Models\\User	2
111	App\\Models\\User	2
112	App\\Models\\User	2
113	App\\Models\\User	2
114	App\\Models\\User	2
115	App\\Models\\User	2
116	App\\Models\\User	2
117	App\\Models\\User	2
118	App\\Models\\User	2
119	App\\Models\\User	2
120	App\\Models\\User	2
121	App\\Models\\User	2
122	App\\Models\\User	2
123	App\\Models\\User	2
124	App\\Models\\User	2
129	App\\Models\\User	2
63	App\\Models\\User	1
68	App\\Models\\User	1
69	App\\Models\\User	1
70	App\\Models\\User	1
71	App\\Models\\User	1
77	App\\Models\\User	1
78	App\\Models\\User	1
80	App\\Models\\User	1
82	App\\Models\\User	1
84	App\\Models\\User	1
86	App\\Models\\User	1
87	App\\Models\\User	1
88	App\\Models\\User	1
89	App\\Models\\User	1
92	App\\Models\\User	1
110	App\\Models\\User	1
111	App\\Models\\User	1
112	App\\Models\\User	1
113	App\\Models\\User	1
114	App\\Models\\User	1
115	App\\Models\\User	1
116	App\\Models\\User	1
117	App\\Models\\User	1
118	App\\Models\\User	1
119	App\\Models\\User	1
120	App\\Models\\User	1
121	App\\Models\\User	1
122	App\\Models\\User	1
123	App\\Models\\User	1
124	App\\Models\\User	1
125	App\\Models\\User	1
126	App\\Models\\User	1
127	App\\Models\\User	1
128	App\\Models\\User	1
129	App\\Models\\User	1
130	App\\Models\\User	1
131	App\\Models\\User	1
132	App\\Models\\User	1
133	App\\Models\\User	1
134	App\\Models\\User	1
135	App\\Models\\User	1
136	App\\Models\\User	1
137	App\\Models\\User	1
138	App\\Models\\User	1
139	App\\Models\\User	1
140	App\\Models\\User	1
141	App\\Models\\User	1
142	App\\Models\\User	1
143	App\\Models\\User	1
144	App\\Models\\User	1
145	App\\Models\\User	1
146	App\\Models\\User	1
147	App\\Models\\User	1
148	App\\Models\\User	1
149	App\\Models\\User	1
150	App\\Models\\User	1
151	App\\Models\\User	1
152	App\\Models\\User	1
153	App\\Models\\User	1
154	App\\Models\\User	1
155	App\\Models\\User	1
156	App\\Models\\User	1
157	App\\Models\\User	1
158	App\\Models\\User	1
159	App\\Models\\User	1
160	App\\Models\\User	1
161	App\\Models\\User	1
162	App\\Models\\User	1
163	App\\Models\\User	1
164	App\\Models\\User	1
165	App\\Models\\User	1
166	App\\Models\\User	1
167	App\\Models\\User	1
168	App\\Models\\User	1
169	App\\Models\\User	1
170	App\\Models\\User	1
171	App\\Models\\User	1
172	App\\Models\\User	1
173	App\\Models\\User	1
174	App\\Models\\User	1
175	App\\Models\\User	1
176	App\\Models\\User	1
177	App\\Models\\User	1
178	App\\Models\\User	1
179	App\\Models\\User	1
180	App\\Models\\User	1
181	App\\Models\\User	1
182	App\\Models\\User	1
183	App\\Models\\User	1
\.


--
-- TOC entry 3867 (class 0 OID 19594)
-- Dependencies: 228
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
5	App\\Models\\User	3
8	App\\Models\\User	8
8	App\\Models\\User	2
14	App\\Models\\User	1
8	App\\Models\\User	9
\.


--
-- TOC entry 3857 (class 0 OID 19533)
-- Dependencies: 218
-- Data for Name: password_resets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_resets (email, token, created_at) FROM stdin;
\.


--
-- TOC entry 3928 (class 0 OID 20279)
-- Dependencies: 289
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payments (id, payment_no, customer_id, invoice_id, payment_date, payment_method, reference_no, amount, notes, created_by, created_at, updated_at) FROM stdin;
1	PAY-20260430-0001	1	1	2026-04-30	Cash	\N	500.00	1st payment 500	1	2026-04-30 03:01:05	2026-04-30 03:01:05
2	PAY-20260430-0002	1	1	2026-04-30	Cash	\N	1500.00	Full payment	1	2026-04-30 03:04:36	2026-04-30 03:04:36
3	PAY-20260501-0001	1	3	2026-05-01	Cash	\N	500.00	\N	1	2026-05-01 03:13:51	2026-05-01 03:13:51
\.


--
-- TOC entry 3863 (class 0 OID 19561)
-- Dependencies: 224
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, name, title, guard_name, parent_id, created_at, updated_at) FROM stdin;
40	dashboard.view	Dashboard View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
41	role.permission.view	Role Permission View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
42	role.permission.edit	Role Permission Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
43	users.view	Users View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
44	users.create	Users Create	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
45	users.edit	Users Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
46	users.delete	Users Delete	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
47	roles.view	Roles View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
48	roles.create	Roles Create	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
49	roles.edit	Roles Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
50	roles.delete	Roles Delete	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
51	permissions.view	Permissions View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
52	permissions.create	Permissions Create	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
53	permissions.edit	Permissions Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
54	permissions.delete	Permissions Delete	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
55	branches.view	Branches View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
56	branches.create	Branches Create	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
57	branches.edit	Branches Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
58	branches.delete	Branches Delete	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
59	departments.view	Departments View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
60	departments.create	Departments Create	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
61	departments.edit	Departments Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
62	departments.delete	Departments Delete	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
63	hr.view	Hr View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
64	inventory.view	Inventory View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
65	warehouse.view	Warehouse View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
66	procurement.view	Procurement View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
67	sales.view	Sales View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
68	accounting.view	Accounting View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
69	payroll.view	Payroll View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
70	reports.view	Reports View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
71	project_management.view	Project management View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
72	announcements.view	Announcements View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
73	announcements.create	Announcements Create	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
74	announcements.edit	Announcements Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
75	announcements.delete	Announcements Delete	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
76	warehouse.module.access	Warehouse Module Access	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
77	warehouse.dashboard.view	Warehouse Dashboard View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
78	warehouse.categories.view	Warehouse Categories View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
79	warehouse.categories.manage	Warehouse Categories Manage	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
80	warehouse.units.view	Warehouse Units View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
81	warehouse.units.manage	Warehouse Units Manage	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
82	warehouse.suppliers.view	Warehouse Suppliers View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
83	warehouse.suppliers.manage	Warehouse Suppliers Manage	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
84	warehouse.locations.view	Warehouse Locations View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
85	warehouse.locations.manage	Warehouse Locations Manage	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
86	warehouse.items.view	Warehouse Items View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
87	warehouse.items.create	Warehouse Items Create	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
88	warehouse.items.edit	Warehouse Items Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
89	warehouse.items.delete	Warehouse Items Delete	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
90	warehouse.inventory.view_all	Warehouse Inventory View all	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
91	warehouse.inventory.view_branch	Warehouse Inventory View branch	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
92	warehouse.ledger.view	Warehouse Ledger View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
93	warehouse.serials.view	Warehouse Serials View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
94	warehouse.pricing.view	Warehouse Pricing View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
95	warehouse.pricing.edit	Warehouse Pricing Edit	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
96	warehouse.receivings.view	Warehouse Receivings View	web	\N	2026-04-20 00:51:26	2026-04-20 00:51:26
97	warehouse.receivings.create	Warehouse Receivings Create	web	\N	2026-04-20 00:51:27	2026-04-20 00:51:27
98	warehouse.transfers.view	Warehouse Transfers View	web	\N	2026-04-20 00:51:27	2026-04-20 00:51:27
99	warehouse.transfers.create	Warehouse Transfers Create	web	\N	2026-04-20 00:51:27	2026-04-20 00:51:27
100	warehouse.adjustments.view	Warehouse Adjustments View	web	\N	2026-04-20 00:51:27	2026-04-20 00:51:27
101	warehouse.adjustments.create	Warehouse Adjustments Create	web	\N	2026-04-20 00:51:27	2026-04-20 00:51:27
102	warehouse.opening_balances.view	Warehouse Opening balances View	web	\N	2026-04-20 00:51:27	2026-04-20 00:51:27
103	warehouse.opening_balances.create	Warehouse Opening balances Create	web	\N	2026-04-20 00:51:27	2026-04-20 00:51:27
104	warehouse.create	Warehouse Create	web	\N	2026-04-28 02:26:27	2026-04-28 02:26:27
105	warehouse.edit	Warehouse Edit	web	\N	2026-04-28 02:26:27	2026-04-28 02:26:27
106	warehouse.delete	Warehouse Delete	web	\N	2026-04-28 02:26:27	2026-04-28 02:26:27
107	warehouse.approve	Warehouse Approve	web	\N	2026-04-28 02:26:27	2026-04-28 02:26:27
108	warehouse.export	Warehouse Export	web	\N	2026-04-28 02:26:27	2026-04-28 02:26:27
109	warehouse.import	Warehouse Import	web	\N	2026-04-28 02:26:27	2026-04-28 02:26:27
110	warehouse.inventory.view	Warehouse Inventory View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
111	warehouse.stock_in.create	Warehouse Stock in Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
112	warehouse.stock_out.create	Warehouse Stock out Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
113	warehouse.categories.create	Warehouse Categories Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
114	warehouse.categories.edit	Warehouse Categories Edit	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
115	warehouse.units.create	Warehouse Units Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
116	warehouse.units.edit	Warehouse Units Edit	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
117	warehouse.suppliers.create	Warehouse Suppliers Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
118	warehouse.suppliers.edit	Warehouse Suppliers Edit	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
119	warehouse.locations.create	Warehouse Locations Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
120	warehouse.locations.edit	Warehouse Locations Edit	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
121	warehouse.transfer.create	Warehouse Transfer Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
122	warehouse.adjustment.create	Warehouse Adjustment Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
123	purchasing.dashboard.view	Purchasing Dashboard View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
124	purchasing.po.view	Purchasing Po View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
125	purchasing.po.create	Purchasing Po Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
126	purchasing.po.edit	Purchasing Po Edit	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
127	purchasing.po.delete	Purchasing Po Delete	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
128	purchasing.po.mark_ordered	Purchasing Po Mark ordered	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
129	purchasing.receiving.view	Purchasing Receiving View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
130	purchasing.receiving.post	Purchasing Receiving Post	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
131	purchasing.bills.view	Purchasing Bills View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
132	purchasing.bills.create	Purchasing Bills Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
133	purchasing.payments.view	Purchasing Payments View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
134	purchasing.payments.create	Purchasing Payments Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
135	sales.dashboard.view	Sales Dashboard View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
136	sales.customers.view	Sales Customers View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
137	sales.customers.create	Sales Customers Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
138	sales.customers.edit	Sales Customers Edit	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
139	sales.invoices.view	Sales Invoices View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
140	sales.invoices.create	Sales Invoices Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
141	sales.receipts.view	Sales Receipts View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
142	sales.receipts.create	Sales Receipts Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
143	sales.payments.view	Sales Payments View	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
144	sales.payments.create	Sales Payments Create	web	\N	2026-05-06 01:15:29	2026-05-06 01:15:29
145	warehouse.categories.delete	Warehouse Categories Delete	web	\N	2026-05-12 04:49:34	2026-05-12 04:49:34
146	warehouse.units.delete	Warehouse Units Delete	web	\N	2026-05-12 04:49:34	2026-05-12 04:49:34
147	warehouse.suppliers.delete	Warehouse Suppliers Delete	web	\N	2026-05-12 04:49:34	2026-05-12 04:49:34
148	warehouse.locations.delete	Warehouse Locations Delete	web	\N	2026-05-12 04:49:34	2026-05-12 04:49:34
149	hr.create	Hr Create	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
150	hr.edit	Hr Edit	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
151	hr.delete	Hr Delete	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
152	hr.approve	Hr Approve	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
153	hr.export	Hr Export	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
154	hr.import	Hr Import	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
155	sales.customers.delete	Sales Customers Delete	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
156	sales.invoices.edit	Sales Invoices Edit	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
157	sales.invoices.delete	Sales Invoices Delete	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
158	sales.receipts.delete	Sales Receipts Delete	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
159	sales.reports.view	Sales Reports View	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
160	accounting.create	Accounting Create	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
161	accounting.edit	Accounting Edit	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
162	accounting.delete	Accounting Delete	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
163	accounting.approve	Accounting Approve	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
164	accounting.export	Accounting Export	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
165	accounting.import	Accounting Import	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
166	payroll.create	Payroll Create	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
167	payroll.edit	Payroll Edit	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
168	payroll.delete	Payroll Delete	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
169	payroll.approve	Payroll Approve	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
170	payroll.export	Payroll Export	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
171	payroll.import	Payroll Import	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
172	reports.create	Reports Create	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
173	reports.edit	Reports Edit	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
174	reports.delete	Reports Delete	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
175	reports.approve	Reports Approve	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
176	reports.export	Reports Export	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
177	reports.import	Reports Import	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
178	project_management.create	Project management Create	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
179	project_management.edit	Project management Edit	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
180	project_management.delete	Project management Delete	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
181	project_management.approve	Project management Approve	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
182	project_management.export	Project management Export	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
183	project_management.import	Project management Import	web	\N	2026-05-12 14:57:52	2026-05-12 14:57:52
\.


--
-- TOC entry 3936 (class 0 OID 20401)
-- Dependencies: 297
-- Data for Name: purchase_order_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchase_order_items (id, purchase_order_id, item_id, item_code, item_name, description, quantity, received_quantity, unit_name, unit_price, tax_amount, line_total, created_at, updated_at) FROM stdin;
1	1	3	ITEM-00002	Test Item	test	2.00	2.00	Pieces	1500.00	0.00	3000.00	2026-05-01 04:29:35	2026-05-01 05:12:05
\.


--
-- TOC entry 3934 (class 0 OID 20369)
-- Dependencies: 295
-- Data for Name: purchase_orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchase_orders (id, po_no, supplier_id, po_date, expected_date, location_id, reference_no, ship_via, payment_terms, subtotal, tax_amount, total_amount, status, notes, created_by, created_at, updated_at) FROM stdin;
1	PO-20260501-0001	2	2026-05-01	2026-05-10	3	\N	Delivery	Net 30	3000.00	0.00	3000.00	received	\N	1	2026-05-01 04:29:35	2026-05-01 05:12:05
\.


--
-- TOC entry 3868 (class 0 OID 19605)
-- Dependencies: 229
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
40	5
41	5
42	5
43	5
44	5
45	5
46	5
47	5
48	5
49	5
50	5
51	5
52	5
53	5
54	5
55	5
56	5
57	5
58	5
59	5
60	5
61	5
62	5
63	5
64	5
65	5
66	5
67	5
68	5
69	5
70	5
71	5
72	5
73	5
74	5
75	5
76	5
77	5
78	5
79	5
80	5
81	5
82	5
83	5
84	5
85	5
86	5
87	5
88	5
89	5
90	5
91	5
92	5
93	5
94	5
95	5
96	5
97	5
98	5
99	5
100	5
101	5
102	5
103	5
40	14
41	14
42	14
43	14
40	6
41	6
42	6
43	6
44	6
45	6
46	6
47	6
48	6
49	6
50	6
51	6
52	6
53	6
54	6
55	6
56	6
57	6
58	6
59	6
60	6
61	6
62	6
65	6
76	6
77	6
86	6
90	6
92	6
93	6
96	6
98	6
100	6
102	6
40	7
63	7
72	7
73	7
74	7
75	7
40	8
65	9
76	9
77	9
78	9
79	9
80	9
81	9
82	9
83	9
84	9
85	9
86	9
44	14
45	14
46	14
47	14
48	14
49	14
50	14
51	14
52	14
53	14
54	14
55	14
56	14
57	14
58	14
59	14
60	14
61	14
62	14
63	14
64	14
65	14
66	14
67	14
68	14
69	14
70	14
71	14
72	14
73	14
74	14
75	14
76	14
77	14
78	14
79	14
80	14
81	14
82	14
83	14
84	14
85	14
86	14
87	14
88	14
89	14
90	14
91	14
92	14
93	14
94	14
95	14
96	14
97	14
98	14
99	14
100	14
101	14
87	9
88	9
89	9
90	9
92	9
93	9
94	9
95	9
96	9
97	9
98	9
99	9
100	9
101	9
102	9
103	9
65	10
76	10
77	10
78	10
80	10
82	10
84	10
86	10
90	10
92	10
93	10
94	10
96	10
97	10
98	10
99	10
100	10
101	10
102	10
103	10
65	11
76	11
77	11
86	11
91	11
92	11
93	11
94	11
96	11
98	11
102	14
103	14
\.


--
-- TOC entry 3865 (class 0 OID 19572)
-- Dependencies: 226
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, title, guard_name, status, created_at, updated_at) FROM stdin;
5	super-admin	Super Admin	web	1	2026-04-20 00:51:27	2026-04-20 00:51:27
6	admin	Admin	web	1	2026-04-20 00:51:27	2026-04-20 00:51:27
7	hr	HR	web	1	2026-04-20 00:51:27	2026-04-20 00:51:27
8	user	User	web	1	2026-04-20 00:51:27	2026-04-20 00:51:27
9	warehouse_admin	Warehouse Admin	web	1	2026-04-20 00:51:27	2026-04-20 00:51:27
10	warehouse_manager	Warehouse Manager	web	1	2026-04-20 00:51:27	2026-04-20 00:51:27
11	branch_manager	Branch Manager	web	1	2026-04-20 00:51:27	2026-04-20 00:51:27
14	Super Admin	Super Administrator	web	1	2026-04-25 04:44:48	2026-04-25 04:44:48
\.


--
-- TOC entry 3932 (class 0 OID 20335)
-- Dependencies: 293
-- Data for Name: sales_receipt_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sales_receipt_items (id, sales_receipt_id, item_id, item_code, item_name, description, quantity, unit_price, discount_amount, tax_amount, line_total, created_at, updated_at) FROM stdin;
1	1	3	ITEM-00002	Test Item	test	1.00	2000.00	0.00	0.00	2000.00	2026-04-30 03:24:35	2026-04-30 03:24:35
\.


--
-- TOC entry 3930 (class 0 OID 20307)
-- Dependencies: 291
-- Data for Name: sales_receipts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sales_receipts (id, receipt_no, customer_id, receipt_date, payment_method, reference_no, subtotal, discount_amount, tax_amount, total_amount, paid_amount, status, notes, created_by, created_at, updated_at, branch_id, location_id) FROM stdin;
1	SR-20260430-0001	1	2026-04-30	Cash	\N	2000.00	0.00	0.00	2000.00	2000.00	paid	Test Sales R	1	2026-04-30 03:24:35	2026-04-30 03:24:35	\N	\N
\.


--
-- TOC entry 3878 (class 0 OID 19686)
-- Dependencies: 239
-- Data for Name: user_module_accesses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_module_accesses (id, user_id, module, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3876 (class 0 OID 19669)
-- Dependencies: 237
-- Data for Name: user_module_assignments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_module_assignments (id, user_id, module, access_level, is_primary, created_at, updated_at) FROM stdin;
22	8	inventory	staff	f	2026-05-06 01:15:29	2026-05-06 01:15:29
23	8	warehouse	manager	f	2026-05-06 01:15:29	2026-05-06 01:15:29
24	8	purchasing	admin	t	2026-05-06 01:15:29	2026-05-06 01:15:29
25	8	sales	staff	f	2026-05-06 01:15:29	2026-05-06 01:15:29
28	2	inventory	staff	f	2026-05-10 10:24:36	2026-05-10 10:24:36
29	2	warehouse	manager	t	2026-05-10 10:24:36	2026-05-10 10:24:36
30	2	purchasing	viewer	f	2026-05-10 10:24:36	2026-05-10 10:24:36
47	1	hr	admin	f	2026-05-12 14:57:52	2026-05-12 14:57:52
48	1	inventory	admin	f	2026-05-12 14:57:52	2026-05-12 14:57:52
49	1	warehouse	admin	t	2026-05-12 14:57:52	2026-05-12 14:57:52
50	1	purchasing	admin	f	2026-05-12 14:57:52	2026-05-12 14:57:52
51	1	sales	admin	f	2026-05-12 14:57:52	2026-05-12 14:57:52
52	1	accounting	admin	f	2026-05-12 14:57:52	2026-05-12 14:57:52
53	1	payroll	admin	f	2026-05-12 14:57:52	2026-05-12 14:57:52
54	1	reports	admin	f	2026-05-12 14:57:52	2026-05-12 14:57:52
55	1	project_management	admin	f	2026-05-12 14:57:52	2026-05-12 14:57:52
60	9	inventory	staff	f	2026-05-13 05:06:15	2026-05-13 05:06:15
61	9	warehouse	staff	f	2026-05-13 05:06:15	2026-05-13 05:06:15
62	9	purchasing	staff	f	2026-05-13 05:06:15	2026-05-13 05:06:15
63	9	sales	staff	t	2026-05-13 05:06:15	2026-05-13 05:06:15
\.


--
-- TOC entry 3861 (class 0 OID 19552)
-- Dependencies: 222
-- Data for Name: user_profiles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_profiles (id, company_name, street_addr_1, street_addr_2, phone_number, alt_phone_number, country, state, city, pin_code, facebook_url, instagram_url, twitter_url, linkdin_url, user_id, created_at, updated_at) FROM stdin;
1	Wizmaster Corporation	Purakan	\N	\N	\N	Philippines	\N	\N	\N	\N	\N	\N	\N	2	2026-04-17 08:49:15	2026-04-17 08:49:15
2	Wizmaster Corporation	test	\N	\N	\N	Philippines	\N	\N	\N	\N	\N	\N	\N	1	2026-04-28 02:26:27	2026-04-28 02:26:27
3	Wizmaster Corporation	Test	\N	\N	\N	Philippines	\N	\N	\N	\N	\N	\N	\N	8	2026-05-02 03:10:43	2026-05-02 03:10:43
4	Wizmaster Corporation	test	\N	\N	\N	Philippines	\N	\N	\N	\N	\N	\N	\N	9	2026-05-10 14:56:21	2026-05-10 14:56:21
\.


--
-- TOC entry 3856 (class 0 OID 19519)
-- Dependencies: 217
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, username, first_name, last_name, email, phone_number, email_verified_at, user_type, password, status, remember_token, created_at, updated_at, branch_id, department_id, primary_module) FROM stdin;
3	systemadmin	System	Admin	admin@example.com	+12398190255	2026-04-20 00:51:27	admin	$2y$10$TiuTFAb36tKcbj68Y18yu.ctvxKghpel5qG3i0HeW3yV8jb8pI5B.	active	\N	2026-04-20 00:51:27	2026-04-20 00:51:27	1	\N	\N
8	maricel	Maricel	Manao	maricel@gmail.com	\N	\N	user	$2y$10$zpGngwl197xP.yaMUQUtC.o8vaom67/sCaThCC6gUnHSXHwtWSfMG	active	\N	2026-05-02 03:10:42	2026-05-02 03:10:42	1	2	\N
1	superadmin	Super	Admin	admin@wizmaster.test	\N	2026-04-13 02:27:59	super-admin	$2y$10$tQ7bmp1JOIJU0uSuG610PuAVpvhH/K4x17vJ0fbQe.Kd7bQcy/IWK	active	HPvBNAU2GkMeU3wmmk760UZXU6k85iFmws7bAKfu2xkTlHPdmUE0UeFSN8G9	2026-04-13 02:27:59	2026-04-28 02:26:27	1	1	\N
2	larry	Larry	Celencio	larry@gmail.com	\N	\N	user	$2y$10$VzzDkt213MyDExHxek069.ou1V8Z/AADwlvZ560lTQo5TjV1JrY7e	active	uL5UVwmOzzSxDHJOwbiEUaRd6owd5Jr0WrlCo59GJAZw2Rz5wM0SdoHOThzl	2026-04-17 08:49:15	2026-04-29 05:36:17	1	1	\N
9	rj	RJ	RJ	rj@gmail.com	\N	\N	user	$2y$10$NCCpbNQX2zsVAC0/OPp3KuqL.8c/xXe5vMmH22ga3REiw3eGA58/W	active	\N	2026-05-10 14:56:21	2026-05-13 05:06:15	1	1	sales
\.


--
-- TOC entry 3882 (class 0 OID 19713)
-- Dependencies: 243
-- Data for Name: warehouse_categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_categories (id, name, description, status, created_at, updated_at) FROM stdin;
1	Network Switches	Test	t	2026-04-13 02:33:33	2026-04-13 02:33:33
2	Routers	test	t	2026-04-13 03:27:25	2026-04-13 03:27:25
3	Test Category	Test	t	2026-04-29 13:32:50	2026-04-29 13:32:50
\.


--
-- TOC entry 3920 (class 0 OID 20187)
-- Dependencies: 281
-- Data for Name: warehouse_inventories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_inventories (id, item_id, branch_id, location_id, quantity, created_at, updated_at) FROM stdin;
3	3	2	4	1.00	2026-04-29 15:33:36	2026-04-29 15:33:36
1	3	1	2	8.00	2026-04-29 13:51:30	2026-04-30 10:40:32
5	3	1	3	2.00	2026-05-01 05:12:05	2026-05-01 05:12:05
\.


--
-- TOC entry 3904 (class 0 OID 19943)
-- Dependencies: 265
-- Data for Name: warehouse_item_serials; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_item_serials (id, item_id, location_id, serial_number, status, receiving_id, transfer_id, received_date, remarks, created_at, updated_at) FROM stdin;
1	1	2	123123123	available	\N	\N	2026-04-13	Opening Balance via OB-20260413-0001	2026-04-13 03:31:56	2026-04-13 03:31:56
2	1	2	123321123	available	\N	\N	2026-04-13	Opening Balance via OB-20260413-0001	2026-04-13 03:31:56	2026-04-13 03:31:56
3	1	2	123123321	available	\N	\N	2026-04-13	Opening Balance via OB-20260413-0001	2026-04-13 03:31:56	2026-04-13 03:31:56
4	1	1	332332332	available	1	\N	2026-04-16	Received via RCV-20260416-0001	2026-04-16 02:49:37	2026-04-16 02:49:37
5	1	1	321321321	available	1	\N	2026-04-16	Received via RCV-20260416-0001	2026-04-16 02:49:37	2026-04-16 02:49:37
6	1	1	213213213	available	1	\N	2026-04-16	Received via RCV-20260416-0001	2026-04-16 02:49:37	2026-04-16 02:49:37
\.


--
-- TOC entry 3892 (class 0 OID 19790)
-- Dependencies: 253
-- Data for Name: warehouse_item_stocks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_item_stocks (id, item_id, location_id, quantity_on_hand, created_at, updated_at) FROM stdin;
1	1	2	3	2026-04-13 03:31:56	2026-04-13 03:31:56
2	1	1	3	2026-04-16 02:49:37	2026-04-16 02:49:37
\.


--
-- TOC entry 3890 (class 0 OID 19761)
-- Dependencies: 251
-- Data for Name: warehouse_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_items (id, item_code, item_name, description, category_id, unit_id, default_supplier_id, minimum_stock, is_serialized, status, created_at, updated_at, code, reorder_level, name, supplier_id, cost_price, selling_price) FROM stdin;
1	#000001	TP-Link 8 Ports Gigabit Switch	test	1	1	1	3	t	t	2026-04-13 02:35:53	2026-04-13 02:35:53	ITEM-00001	0	ITEM-00001	\N	0.00	0.00
3	ITEM-00002	Test Item	test	2	1	1	3	f	t	2026-04-29 06:34:20	2026-04-29 06:34:20	ITEM-00002	3	Test Item	1	1000.00	2000.00
\.


--
-- TOC entry 3888 (class 0 OID 19744)
-- Dependencies: 249
-- Data for Name: warehouse_locations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_locations (id, location_code, location_name, location_type, branch_id, address, status, created_at, updated_at, name) FROM stdin;
2	Wizmaster Solana	Wizmaster Solana	branch	1	test	t	2026-04-13 02:35:18	2026-04-13 02:35:18	Wizmaster Solana
4	WIZ-CDO	Wizmaster CDO	Stock Room	2	Cagayan de Oro	t	2026-04-29 15:33:12	2026-04-29 15:33:12	Wizmaster CDO
3	KIW_0001	Kiwalan Main Warehouse	Warehouse	1	Test	t	2026-04-29 13:23:23	2026-04-29 13:23:23	Kiwalan Main Warehouse
1	TIP-0002	Tipanoy Warehouse	Warehouse	1	test	t	2026-04-13 02:34:12	2026-04-29 13:24:00	Tipanoy Warehouse
\.


--
-- TOC entry 3918 (class 0 OID 20096)
-- Dependencies: 279
-- Data for Name: warehouse_opening_balance_item_serials; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_opening_balance_item_serials (id, opening_balance_item_id, serial_number, created_at, updated_at) FROM stdin;
1	1	123123123	2026-04-13 03:31:56	2026-04-13 03:31:56
2	1	123321123	2026-04-13 03:31:56	2026-04-13 03:31:56
3	1	123123321	2026-04-13 03:31:56	2026-04-13 03:31:56
\.


--
-- TOC entry 3916 (class 0 OID 20072)
-- Dependencies: 277
-- Data for Name: warehouse_opening_balance_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_opening_balance_items (id, opening_balance_id, item_id, location_id, quantity, remarks, created_at, updated_at) FROM stdin;
1	1	1	2	3	test	2026-04-13 03:31:56	2026-04-13 03:31:56
\.


--
-- TOC entry 3914 (class 0 OID 20055)
-- Dependencies: 275
-- Data for Name: warehouse_opening_balances; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_opening_balances (id, opening_no, reference_no, opening_date, remarks, created_by, status, created_at, updated_at) FROM stdin;
1	OB-20260413-0001	Test01	2026-04-13	test 01	1	posted	2026-04-13 03:31:56	2026-04-13 03:31:56
\.


--
-- TOC entry 3896 (class 0 OID 19837)
-- Dependencies: 257
-- Data for Name: warehouse_receiving_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_receiving_items (id, receiving_id, item_id, quantity, unit_cost, total_cost, remarks, created_at, updated_at) FROM stdin;
1	1	1	3	100.00	300.00	test	2026-04-16 02:49:37	2026-04-16 02:49:37
3	3	3	2	1500.00	3000.00	\N	2026-05-01 05:12:05	2026-05-01 05:12:05
\.


--
-- TOC entry 3894 (class 0 OID 19810)
-- Dependencies: 255
-- Data for Name: warehouse_receivings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_receivings (id, receiving_no, supplier_id, location_id, reference_no, received_date, remarks, received_by, status, created_at, updated_at) FROM stdin;
1	RCV-20260416-0001	1	1	00001	2026-04-16	test	1	posted	2026-04-16 02:49:37	2026-04-16 02:49:37
3	RCV-20260501-0001	2	3	PO-20260501-0001	2026-05-01	\N	1	received	2026-05-01 05:12:05	2026-05-01 05:12:05
\.


--
-- TOC entry 3912 (class 0 OID 20036)
-- Dependencies: 273
-- Data for Name: warehouse_stock_adjustment_item_serials; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_stock_adjustment_item_serials (id, adjustment_item_id, item_serial_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3910 (class 0 OID 20017)
-- Dependencies: 271
-- Data for Name: warehouse_stock_adjustment_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_stock_adjustment_items (id, adjustment_id, item_id, quantity, remarks, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3908 (class 0 OID 19995)
-- Dependencies: 269
-- Data for Name: warehouse_stock_adjustments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_stock_adjustments (id, adjustment_no, location_id, adjustment_date, adjustment_type, remarks, created_by, status, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3902 (class 0 OID 19917)
-- Dependencies: 263
-- Data for Name: warehouse_stock_movements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_stock_movements (id, item_id, location_id, movement_type, quantity, balance_after, reference_type, reference_id, remarks, transaction_date, created_by, created_at, updated_at) FROM stdin;
1	1	2	opening_balance	3	3	opening_balance	1	Opening Balance #OB-20260413-0001	2026-04-13	1	2026-04-13 03:31:56	2026-04-13 03:31:56
2	1	1	receiving	3	3	receiving	1	Receiving #RCV-20260416-0001	2026-04-16	1	2026-04-16 02:49:37	2026-04-16 02:49:37
3	3	2	stock_in	10	10	STOCK-IN-20260429135130	\N	Test	2026-04-29	1	2026-04-29 13:51:30	2026-04-29 13:51:30
4	3	2	stock_out	-2	8	STOCK-OUT-20260429151927	\N	Test	2026-04-29	1	2026-04-29 15:19:27	2026-04-29 15:19:27
5	3	2	transfer_out	-1	7	TRANSFER-20260429153336	\N	Test	2026-04-29	1	2026-04-29 15:33:36	2026-04-29 15:33:36
6	3	4	transfer_in	1	1	TRANSFER-20260429153336	\N	Test	2026-04-29	1	2026-04-29 15:33:36	2026-04-29 15:33:36
7	3	2	adjustment_add	1	8	ADJ-20260429154758	\N	Test	2026-04-29	1	2026-04-29 15:47:58	2026-04-29 15:47:58
8	3	2	stock_out	-1	7	sales_receipt	2	Sales Receipt #SR-20260430-0002	2026-04-30	1	2026-04-30 10:38:16	2026-04-30 10:38:16
9	3	2	sales_receipt_void	1	8	sales_receipt_deleted	2	Deleted Sales Receipt #SR-20260430-0002	2026-04-30	1	2026-04-30 10:40:32	2026-04-30 10:40:32
10	3	3	Receiving	2	2	purchase_order_receiving	3	Received from PO #PO-20260501-0001	2026-05-01	1	2026-05-01 05:12:05	2026-05-01 05:12:05
\.


--
-- TOC entry 3886 (class 0 OID 19734)
-- Dependencies: 247
-- Data for Name: warehouse_suppliers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_suppliers (id, supplier_name, contact_person, phone, email, address, status, created_at, updated_at) FROM stdin;
1	Philteq	Philteq person	123456789111	philteq@gmail.com	test	t	2026-04-13 02:33:59	2026-04-13 02:33:59
2	Test Supplier	Juan de la Cruz	123123123	test@gmail.com	test only	t	2026-04-29 13:15:54	2026-04-29 13:15:54
\.


--
-- TOC entry 3906 (class 0 OID 19976)
-- Dependencies: 267
-- Data for Name: warehouse_transfer_item_serials; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_transfer_item_serials (id, transfer_item_id, item_serial_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3900 (class 0 OID 19898)
-- Dependencies: 261
-- Data for Name: warehouse_transfer_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_transfer_items (id, transfer_id, item_id, quantity, remarks, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3898 (class 0 OID 19856)
-- Dependencies: 259
-- Data for Name: warehouse_transfers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_transfers (id, transfer_no, from_location_id, to_location_id, transfer_date, remarks, requested_by, approved_by, transferred_by, received_by, status, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3884 (class 0 OID 19725)
-- Dependencies: 245
-- Data for Name: warehouse_units; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.warehouse_units (id, name, abbreviation, created_at, updated_at) FROM stdin;
1	Pieces	pcs	2026-04-13 02:33:43	2026-04-13 02:33:43
2	Box	box	2026-04-13 03:29:02	2026-04-13 03:29:02
\.


--
-- TOC entry 3982 (class 0 OID 0)
-- Dependencies: 240
-- Name: announcements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.announcements_id_seq', 1, false);


--
-- TOC entry 3983 (class 0 OID 0)
-- Dependencies: 234
-- Name: branches_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.branches_id_seq', 2, true);


--
-- TOC entry 3984 (class 0 OID 0)
-- Dependencies: 282
-- Name: customers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.customers_id_seq', 1, true);


--
-- TOC entry 3985 (class 0 OID 0)
-- Dependencies: 232
-- Name: departments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departments_id_seq', 2, true);


--
-- TOC entry 3986 (class 0 OID 0)
-- Dependencies: 219
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- TOC entry 3987 (class 0 OID 0)
-- Dependencies: 286
-- Name: invoice_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.invoice_items_id_seq', 3, true);


--
-- TOC entry 3988 (class 0 OID 0)
-- Dependencies: 284
-- Name: invoices_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.invoices_id_seq', 3, true);


--
-- TOC entry 3989 (class 0 OID 0)
-- Dependencies: 230
-- Name: media_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.media_id_seq', 1, false);


--
-- TOC entry 3990 (class 0 OID 0)
-- Dependencies: 214
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 51, true);


--
-- TOC entry 3991 (class 0 OID 0)
-- Dependencies: 288
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payments_id_seq', 3, true);


--
-- TOC entry 3992 (class 0 OID 0)
-- Dependencies: 223
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 183, true);


--
-- TOC entry 3993 (class 0 OID 0)
-- Dependencies: 296
-- Name: purchase_order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchase_order_items_id_seq', 1, true);


--
-- TOC entry 3994 (class 0 OID 0)
-- Dependencies: 294
-- Name: purchase_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchase_orders_id_seq', 1, true);


--
-- TOC entry 3995 (class 0 OID 0)
-- Dependencies: 225
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 14, true);


--
-- TOC entry 3996 (class 0 OID 0)
-- Dependencies: 292
-- Name: sales_receipt_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sales_receipt_items_id_seq', 2, true);


--
-- TOC entry 3997 (class 0 OID 0)
-- Dependencies: 290
-- Name: sales_receipts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sales_receipts_id_seq', 2, true);


--
-- TOC entry 3998 (class 0 OID 0)
-- Dependencies: 238
-- Name: user_module_accesses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_module_accesses_id_seq', 1, false);


--
-- TOC entry 3999 (class 0 OID 0)
-- Dependencies: 236
-- Name: user_module_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_module_assignments_id_seq', 63, true);


--
-- TOC entry 4000 (class 0 OID 0)
-- Dependencies: 221
-- Name: user_profiles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_profiles_id_seq', 4, true);


--
-- TOC entry 4001 (class 0 OID 0)
-- Dependencies: 216
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 9, true);


--
-- TOC entry 4002 (class 0 OID 0)
-- Dependencies: 242
-- Name: warehouse_categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_categories_id_seq', 3, true);


--
-- TOC entry 4003 (class 0 OID 0)
-- Dependencies: 280
-- Name: warehouse_inventories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_inventories_id_seq', 5, true);


--
-- TOC entry 4004 (class 0 OID 0)
-- Dependencies: 264
-- Name: warehouse_item_serials_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_item_serials_id_seq', 6, true);


--
-- TOC entry 4005 (class 0 OID 0)
-- Dependencies: 252
-- Name: warehouse_item_stocks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_item_stocks_id_seq', 2, true);


--
-- TOC entry 4006 (class 0 OID 0)
-- Dependencies: 250
-- Name: warehouse_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_items_id_seq', 3, true);


--
-- TOC entry 4007 (class 0 OID 0)
-- Dependencies: 248
-- Name: warehouse_locations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_locations_id_seq', 4, true);


--
-- TOC entry 4008 (class 0 OID 0)
-- Dependencies: 278
-- Name: warehouse_opening_balance_item_serials_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_opening_balance_item_serials_id_seq', 3, true);


--
-- TOC entry 4009 (class 0 OID 0)
-- Dependencies: 276
-- Name: warehouse_opening_balance_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_opening_balance_items_id_seq', 1, true);


--
-- TOC entry 4010 (class 0 OID 0)
-- Dependencies: 274
-- Name: warehouse_opening_balances_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_opening_balances_id_seq', 1, true);


--
-- TOC entry 4011 (class 0 OID 0)
-- Dependencies: 256
-- Name: warehouse_receiving_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_receiving_items_id_seq', 3, true);


--
-- TOC entry 4012 (class 0 OID 0)
-- Dependencies: 254
-- Name: warehouse_receivings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_receivings_id_seq', 3, true);


--
-- TOC entry 4013 (class 0 OID 0)
-- Dependencies: 272
-- Name: warehouse_stock_adjustment_item_serials_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_stock_adjustment_item_serials_id_seq', 1, false);


--
-- TOC entry 4014 (class 0 OID 0)
-- Dependencies: 270
-- Name: warehouse_stock_adjustment_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_stock_adjustment_items_id_seq', 1, false);


--
-- TOC entry 4015 (class 0 OID 0)
-- Dependencies: 268
-- Name: warehouse_stock_adjustments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_stock_adjustments_id_seq', 1, false);


--
-- TOC entry 4016 (class 0 OID 0)
-- Dependencies: 262
-- Name: warehouse_stock_movements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_stock_movements_id_seq', 10, true);


--
-- TOC entry 4017 (class 0 OID 0)
-- Dependencies: 246
-- Name: warehouse_suppliers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_suppliers_id_seq', 2, true);


--
-- TOC entry 4018 (class 0 OID 0)
-- Dependencies: 266
-- Name: warehouse_transfer_item_serials_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_transfer_item_serials_id_seq', 1, false);


--
-- TOC entry 4019 (class 0 OID 0)
-- Dependencies: 260
-- Name: warehouse_transfer_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_transfer_items_id_seq', 1, false);


--
-- TOC entry 4020 (class 0 OID 0)
-- Dependencies: 258
-- Name: warehouse_transfers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_transfers_id_seq', 1, false);


--
-- TOC entry 4021 (class 0 OID 0)
-- Dependencies: 244
-- Name: warehouse_units_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.warehouse_units_id_seq', 2, true);


--
-- TOC entry 3538 (class 2606 OID 19706)
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (id);


--
-- TOC entry 3528 (class 2606 OID 19657)
-- Name: branches branches_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.branches
    ADD CONSTRAINT branches_code_unique UNIQUE (code);


--
-- TOC entry 3530 (class 2606 OID 19655)
-- Name: branches branches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.branches
    ADD CONSTRAINT branches_pkey PRIMARY KEY (id);


--
-- TOC entry 3614 (class 2606 OID 20225)
-- Name: customers customers_customer_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_customer_code_unique UNIQUE (customer_code);


--
-- TOC entry 3616 (class 2606 OID 20223)
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- TOC entry 3524 (class 2606 OID 19644)
-- Name: departments departments_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_code_unique UNIQUE (code);


--
-- TOC entry 3526 (class 2606 OID 19642)
-- Name: departments departments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_pkey PRIMARY KEY (id);


--
-- TOC entry 3497 (class 2606 OID 19548)
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 3499 (class 2606 OID 19550)
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- TOC entry 3622 (class 2606 OID 20267)
-- Name: invoice_items invoice_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoice_items
    ADD CONSTRAINT invoice_items_pkey PRIMARY KEY (id);


--
-- TOC entry 3618 (class 2606 OID 20253)
-- Name: invoices invoices_invoice_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT invoices_invoice_no_unique UNIQUE (invoice_no);


--
-- TOC entry 3620 (class 2606 OID 20241)
-- Name: invoices invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT invoices_pkey PRIMARY KEY (id);


--
-- TOC entry 3520 (class 2606 OID 19628)
-- Name: media media_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_pkey PRIMARY KEY (id);


--
-- TOC entry 3522 (class 2606 OID 19631)
-- Name: media media_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_uuid_unique UNIQUE (uuid);


--
-- TOC entry 3488 (class 2606 OID 19517)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 3512 (class 2606 OID 19593)
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- TOC entry 3515 (class 2606 OID 19604)
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- TOC entry 3624 (class 2606 OID 20305)
-- Name: payments payments_payment_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_payment_no_unique UNIQUE (payment_no);


--
-- TOC entry 3626 (class 2606 OID 20288)
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- TOC entry 3503 (class 2606 OID 19570)
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 3505 (class 2606 OID 19568)
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 3640 (class 2606 OID 20413)
-- Name: purchase_order_items purchase_order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_items
    ADD CONSTRAINT purchase_order_items_pkey PRIMARY KEY (id);


--
-- TOC entry 3634 (class 2606 OID 20380)
-- Name: purchase_orders purchase_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_pkey PRIMARY KEY (id);


--
-- TOC entry 3637 (class 2606 OID 20399)
-- Name: purchase_orders purchase_orders_po_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_po_no_unique UNIQUE (po_no);


--
-- TOC entry 3517 (class 2606 OID 19619)
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- TOC entry 3507 (class 2606 OID 19582)
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 3509 (class 2606 OID 19580)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 3632 (class 2606 OID 20347)
-- Name: sales_receipt_items sales_receipt_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipt_items
    ADD CONSTRAINT sales_receipt_items_pkey PRIMARY KEY (id);


--
-- TOC entry 3628 (class 2606 OID 20321)
-- Name: sales_receipts sales_receipts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipts
    ADD CONSTRAINT sales_receipts_pkey PRIMARY KEY (id);


--
-- TOC entry 3630 (class 2606 OID 20333)
-- Name: sales_receipts sales_receipts_receipt_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipts
    ADD CONSTRAINT sales_receipts_receipt_no_unique UNIQUE (receipt_no);


--
-- TOC entry 3536 (class 2606 OID 19691)
-- Name: user_module_accesses user_module_accesses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_module_accesses
    ADD CONSTRAINT user_module_accesses_pkey PRIMARY KEY (id);


--
-- TOC entry 3532 (class 2606 OID 19677)
-- Name: user_module_assignments user_module_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_module_assignments
    ADD CONSTRAINT user_module_assignments_pkey PRIMARY KEY (id);


--
-- TOC entry 3534 (class 2606 OID 19684)
-- Name: user_module_assignments user_module_assignments_user_id_module_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_module_assignments
    ADD CONSTRAINT user_module_assignments_user_id_module_unique UNIQUE (user_id, module);


--
-- TOC entry 3501 (class 2606 OID 19559)
-- Name: user_profiles user_profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_profiles
    ADD CONSTRAINT user_profiles_pkey PRIMARY KEY (id);


--
-- TOC entry 3490 (class 2606 OID 19532)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 3492 (class 2606 OID 19528)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 3494 (class 2606 OID 19530)
-- Name: users users_username_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_unique UNIQUE (username);


--
-- TOC entry 3540 (class 2606 OID 19723)
-- Name: warehouse_categories warehouse_categories_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_categories
    ADD CONSTRAINT warehouse_categories_name_unique UNIQUE (name);


--
-- TOC entry 3542 (class 2606 OID 19721)
-- Name: warehouse_categories warehouse_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_categories
    ADD CONSTRAINT warehouse_categories_pkey PRIMARY KEY (id);


--
-- TOC entry 3610 (class 2606 OID 20210)
-- Name: warehouse_inventories warehouse_inventories_item_id_branch_id_location_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_inventories
    ADD CONSTRAINT warehouse_inventories_item_id_branch_id_location_id_unique UNIQUE (item_id, branch_id, location_id);


--
-- TOC entry 3612 (class 2606 OID 20193)
-- Name: warehouse_inventories warehouse_inventories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_inventories
    ADD CONSTRAINT warehouse_inventories_pkey PRIMARY KEY (id);


--
-- TOC entry 3581 (class 2606 OID 19951)
-- Name: warehouse_item_serials warehouse_item_serials_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_serials
    ADD CONSTRAINT warehouse_item_serials_pkey PRIMARY KEY (id);


--
-- TOC entry 3584 (class 2606 OID 19974)
-- Name: warehouse_item_serials warehouse_item_serials_serial_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_serials
    ADD CONSTRAINT warehouse_item_serials_serial_number_unique UNIQUE (serial_number);


--
-- TOC entry 3560 (class 2606 OID 19808)
-- Name: warehouse_item_stocks warehouse_item_stocks_item_location_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_stocks
    ADD CONSTRAINT warehouse_item_stocks_item_location_unique UNIQUE (item_id, location_id);


--
-- TOC entry 3562 (class 2606 OID 19796)
-- Name: warehouse_item_stocks warehouse_item_stocks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_stocks
    ADD CONSTRAINT warehouse_item_stocks_pkey PRIMARY KEY (id);


--
-- TOC entry 3555 (class 2606 OID 19788)
-- Name: warehouse_items warehouse_items_item_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_items
    ADD CONSTRAINT warehouse_items_item_code_unique UNIQUE (item_code);


--
-- TOC entry 3558 (class 2606 OID 19771)
-- Name: warehouse_items warehouse_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_items
    ADD CONSTRAINT warehouse_items_pkey PRIMARY KEY (id);


--
-- TOC entry 3550 (class 2606 OID 19759)
-- Name: warehouse_locations warehouse_locations_location_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_locations
    ADD CONSTRAINT warehouse_locations_location_code_unique UNIQUE (location_code);


--
-- TOC entry 3552 (class 2606 OID 19752)
-- Name: warehouse_locations warehouse_locations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_locations
    ADD CONSTRAINT warehouse_locations_pkey PRIMARY KEY (id);


--
-- TOC entry 3606 (class 2606 OID 20101)
-- Name: warehouse_opening_balance_item_serials warehouse_opening_balance_item_serials_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_item_serials
    ADD CONSTRAINT warehouse_opening_balance_item_serials_pkey PRIMARY KEY (id);


--
-- TOC entry 3608 (class 2606 OID 20108)
-- Name: warehouse_opening_balance_item_serials warehouse_opening_balance_item_serials_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_item_serials
    ADD CONSTRAINT warehouse_opening_balance_item_serials_unique UNIQUE (opening_balance_item_id, serial_number);


--
-- TOC entry 3604 (class 2606 OID 20079)
-- Name: warehouse_opening_balance_items warehouse_opening_balance_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_items
    ADD CONSTRAINT warehouse_opening_balance_items_pkey PRIMARY KEY (id);


--
-- TOC entry 3600 (class 2606 OID 20070)
-- Name: warehouse_opening_balances warehouse_opening_balances_opening_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balances
    ADD CONSTRAINT warehouse_opening_balances_opening_no_unique UNIQUE (opening_no);


--
-- TOC entry 3602 (class 2606 OID 20063)
-- Name: warehouse_opening_balances warehouse_opening_balances_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balances
    ADD CONSTRAINT warehouse_opening_balances_pkey PRIMARY KEY (id);


--
-- TOC entry 3568 (class 2606 OID 19844)
-- Name: warehouse_receiving_items warehouse_receiving_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receiving_items
    ADD CONSTRAINT warehouse_receiving_items_pkey PRIMARY KEY (id);


--
-- TOC entry 3564 (class 2606 OID 19818)
-- Name: warehouse_receivings warehouse_receivings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receivings
    ADD CONSTRAINT warehouse_receivings_pkey PRIMARY KEY (id);


--
-- TOC entry 3566 (class 2606 OID 19835)
-- Name: warehouse_receivings warehouse_receivings_receiving_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receivings
    ADD CONSTRAINT warehouse_receivings_receiving_no_unique UNIQUE (receiving_no);


--
-- TOC entry 3596 (class 2606 OID 20041)
-- Name: warehouse_stock_adjustment_item_serials warehouse_stock_adjustment_item_serials_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_item_serials
    ADD CONSTRAINT warehouse_stock_adjustment_item_serials_pkey PRIMARY KEY (id);


--
-- TOC entry 3598 (class 2606 OID 20053)
-- Name: warehouse_stock_adjustment_item_serials warehouse_stock_adjustment_item_serials_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_item_serials
    ADD CONSTRAINT warehouse_stock_adjustment_item_serials_unique UNIQUE (adjustment_item_id, item_serial_id);


--
-- TOC entry 3594 (class 2606 OID 20024)
-- Name: warehouse_stock_adjustment_items warehouse_stock_adjustment_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_items
    ADD CONSTRAINT warehouse_stock_adjustment_items_pkey PRIMARY KEY (id);


--
-- TOC entry 3590 (class 2606 OID 20015)
-- Name: warehouse_stock_adjustments warehouse_stock_adjustments_adjustment_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustments
    ADD CONSTRAINT warehouse_stock_adjustments_adjustment_no_unique UNIQUE (adjustment_no);


--
-- TOC entry 3592 (class 2606 OID 20003)
-- Name: warehouse_stock_adjustments warehouse_stock_adjustments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustments
    ADD CONSTRAINT warehouse_stock_adjustments_pkey PRIMARY KEY (id);


--
-- TOC entry 3576 (class 2606 OID 19925)
-- Name: warehouse_stock_movements warehouse_stock_movements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_movements
    ADD CONSTRAINT warehouse_stock_movements_pkey PRIMARY KEY (id);


--
-- TOC entry 3548 (class 2606 OID 19742)
-- Name: warehouse_suppliers warehouse_suppliers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_suppliers
    ADD CONSTRAINT warehouse_suppliers_pkey PRIMARY KEY (id);


--
-- TOC entry 3586 (class 2606 OID 19981)
-- Name: warehouse_transfer_item_serials warehouse_transfer_item_serials_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_item_serials
    ADD CONSTRAINT warehouse_transfer_item_serials_pkey PRIMARY KEY (id);


--
-- TOC entry 3588 (class 2606 OID 19993)
-- Name: warehouse_transfer_item_serials warehouse_transfer_item_serials_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_item_serials
    ADD CONSTRAINT warehouse_transfer_item_serials_unique UNIQUE (transfer_item_id, item_serial_id);


--
-- TOC entry 3574 (class 2606 OID 19905)
-- Name: warehouse_transfer_items warehouse_transfer_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_items
    ADD CONSTRAINT warehouse_transfer_items_pkey PRIMARY KEY (id);


--
-- TOC entry 3570 (class 2606 OID 19864)
-- Name: warehouse_transfers warehouse_transfers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers
    ADD CONSTRAINT warehouse_transfers_pkey PRIMARY KEY (id);


--
-- TOC entry 3572 (class 2606 OID 19896)
-- Name: warehouse_transfers warehouse_transfers_transfer_no_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers
    ADD CONSTRAINT warehouse_transfers_transfer_no_unique UNIQUE (transfer_no);


--
-- TOC entry 3544 (class 2606 OID 19732)
-- Name: warehouse_units warehouse_units_abbreviation_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_units
    ADD CONSTRAINT warehouse_units_abbreviation_unique UNIQUE (abbreviation);


--
-- TOC entry 3546 (class 2606 OID 19730)
-- Name: warehouse_units warehouse_units_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_units
    ADD CONSTRAINT warehouse_units_pkey PRIMARY KEY (id);


--
-- TOC entry 3518 (class 1259 OID 19629)
-- Name: media_model_type_model_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX media_model_type_model_id_index ON public.media USING btree (model_type, model_id);


--
-- TOC entry 3510 (class 1259 OID 19586)
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- TOC entry 3513 (class 1259 OID 19597)
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- TOC entry 3495 (class 1259 OID 19538)
-- Name: password_resets_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX password_resets_email_index ON public.password_resets USING btree (email);


--
-- TOC entry 3641 (class 1259 OID 20424)
-- Name: purchase_order_items_purchase_order_id_item_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX purchase_order_items_purchase_order_id_item_id_index ON public.purchase_order_items USING btree (purchase_order_id, item_id);


--
-- TOC entry 3635 (class 1259 OID 20397)
-- Name: purchase_orders_po_date_expected_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX purchase_orders_po_date_expected_date_index ON public.purchase_orders USING btree (po_date, expected_date);


--
-- TOC entry 3638 (class 1259 OID 20396)
-- Name: purchase_orders_supplier_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX purchase_orders_supplier_id_status_index ON public.purchase_orders USING btree (supplier_id, status);


--
-- TOC entry 3579 (class 1259 OID 19972)
-- Name: warehouse_item_serials_item_location_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX warehouse_item_serials_item_location_index ON public.warehouse_item_serials USING btree (item_id, location_id);


--
-- TOC entry 3582 (class 1259 OID 20156)
-- Name: warehouse_item_serials_serial_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX warehouse_item_serials_serial_number_index ON public.warehouse_item_serials USING btree (serial_number);


--
-- TOC entry 3553 (class 1259 OID 20157)
-- Name: warehouse_items_item_code_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX warehouse_items_item_code_index ON public.warehouse_items USING btree (item_code);


--
-- TOC entry 3556 (class 1259 OID 20158)
-- Name: warehouse_items_item_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX warehouse_items_item_name_index ON public.warehouse_items USING btree (item_name);


--
-- TOC entry 3577 (class 1259 OID 19941)
-- Name: warehouse_stock_movements_reference_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX warehouse_stock_movements_reference_index ON public.warehouse_stock_movements USING btree (reference_type, reference_id);


--
-- TOC entry 3578 (class 1259 OID 20155)
-- Name: warehouse_stock_movements_transaction_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX warehouse_stock_movements_transaction_date_index ON public.warehouse_stock_movements USING btree (transaction_date);


--
-- TOC entry 3650 (class 2606 OID 19707)
-- Name: announcements announcements_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3695 (class 2606 OID 20268)
-- Name: invoice_items invoice_items_invoice_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoice_items
    ADD CONSTRAINT invoice_items_invoice_id_foreign FOREIGN KEY (invoice_id) REFERENCES public.invoices(id) ON DELETE CASCADE;


--
-- TOC entry 3696 (class 2606 OID 20273)
-- Name: invoice_items invoice_items_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoice_items
    ADD CONSTRAINT invoice_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE SET NULL;


--
-- TOC entry 3693 (class 2606 OID 20247)
-- Name: invoices invoices_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT invoices_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3694 (class 2606 OID 20242)
-- Name: invoices invoices_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT invoices_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- TOC entry 3644 (class 2606 OID 19587)
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 3645 (class 2606 OID 19598)
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 3697 (class 2606 OID 20299)
-- Name: payments payments_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3698 (class 2606 OID 20289)
-- Name: payments payments_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- TOC entry 3699 (class 2606 OID 20294)
-- Name: payments payments_invoice_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_invoice_id_foreign FOREIGN KEY (invoice_id) REFERENCES public.invoices(id) ON DELETE SET NULL;


--
-- TOC entry 3709 (class 2606 OID 20419)
-- Name: purchase_order_items purchase_order_items_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_items
    ADD CONSTRAINT purchase_order_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3710 (class 2606 OID 20414)
-- Name: purchase_order_items purchase_order_items_purchase_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_items
    ADD CONSTRAINT purchase_order_items_purchase_order_id_foreign FOREIGN KEY (purchase_order_id) REFERENCES public.purchase_orders(id) ON DELETE CASCADE;


--
-- TOC entry 3706 (class 2606 OID 20391)
-- Name: purchase_orders purchase_orders_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3707 (class 2606 OID 20386)
-- Name: purchase_orders purchase_orders_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE SET NULL;


--
-- TOC entry 3708 (class 2606 OID 20381)
-- Name: purchase_orders purchase_orders_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.warehouse_suppliers(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3646 (class 2606 OID 19608)
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 3647 (class 2606 OID 19613)
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 3704 (class 2606 OID 20353)
-- Name: sales_receipt_items sales_receipt_items_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipt_items
    ADD CONSTRAINT sales_receipt_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE SET NULL;


--
-- TOC entry 3705 (class 2606 OID 20348)
-- Name: sales_receipt_items sales_receipt_items_sales_receipt_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipt_items
    ADD CONSTRAINT sales_receipt_items_sales_receipt_id_foreign FOREIGN KEY (sales_receipt_id) REFERENCES public.sales_receipts(id) ON DELETE CASCADE;


--
-- TOC entry 3700 (class 2606 OID 20358)
-- Name: sales_receipts sales_receipts_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipts
    ADD CONSTRAINT sales_receipts_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.branches(id) ON DELETE SET NULL;


--
-- TOC entry 3701 (class 2606 OID 20327)
-- Name: sales_receipts sales_receipts_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipts
    ADD CONSTRAINT sales_receipts_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3702 (class 2606 OID 20322)
-- Name: sales_receipts sales_receipts_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipts
    ADD CONSTRAINT sales_receipts_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- TOC entry 3703 (class 2606 OID 20363)
-- Name: sales_receipts sales_receipts_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_receipts
    ADD CONSTRAINT sales_receipts_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE SET NULL;


--
-- TOC entry 3649 (class 2606 OID 19692)
-- Name: user_module_accesses user_module_accesses_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_module_accesses
    ADD CONSTRAINT user_module_accesses_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3648 (class 2606 OID 19678)
-- Name: user_module_assignments user_module_assignments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_module_assignments
    ADD CONSTRAINT user_module_assignments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3642 (class 2606 OID 19658)
-- Name: users users_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.branches(id) ON DELETE SET NULL;


--
-- TOC entry 3643 (class 2606 OID 19663)
-- Name: users users_department_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_department_id_foreign FOREIGN KEY (department_id) REFERENCES public.departments(id) ON DELETE SET NULL;


--
-- TOC entry 3690 (class 2606 OID 20199)
-- Name: warehouse_inventories warehouse_inventories_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_inventories
    ADD CONSTRAINT warehouse_inventories_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.branches(id) ON DELETE CASCADE;


--
-- TOC entry 3691 (class 2606 OID 20194)
-- Name: warehouse_inventories warehouse_inventories_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_inventories
    ADD CONSTRAINT warehouse_inventories_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE CASCADE;


--
-- TOC entry 3692 (class 2606 OID 20204)
-- Name: warehouse_inventories warehouse_inventories_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_inventories
    ADD CONSTRAINT warehouse_inventories_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE CASCADE;


--
-- TOC entry 3673 (class 2606 OID 19952)
-- Name: warehouse_item_serials warehouse_item_serials_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_serials
    ADD CONSTRAINT warehouse_item_serials_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE CASCADE;


--
-- TOC entry 3674 (class 2606 OID 19957)
-- Name: warehouse_item_serials warehouse_item_serials_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_serials
    ADD CONSTRAINT warehouse_item_serials_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE RESTRICT;


--
-- TOC entry 3675 (class 2606 OID 19962)
-- Name: warehouse_item_serials warehouse_item_serials_receiving_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_serials
    ADD CONSTRAINT warehouse_item_serials_receiving_id_foreign FOREIGN KEY (receiving_id) REFERENCES public.warehouse_receivings(id) ON DELETE SET NULL;


--
-- TOC entry 3676 (class 2606 OID 19967)
-- Name: warehouse_item_serials warehouse_item_serials_transfer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_serials
    ADD CONSTRAINT warehouse_item_serials_transfer_id_foreign FOREIGN KEY (transfer_id) REFERENCES public.warehouse_transfers(id) ON DELETE SET NULL;


--
-- TOC entry 3655 (class 2606 OID 19797)
-- Name: warehouse_item_stocks warehouse_item_stocks_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_stocks
    ADD CONSTRAINT warehouse_item_stocks_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE CASCADE;


--
-- TOC entry 3656 (class 2606 OID 19802)
-- Name: warehouse_item_stocks warehouse_item_stocks_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_item_stocks
    ADD CONSTRAINT warehouse_item_stocks_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE CASCADE;


--
-- TOC entry 3652 (class 2606 OID 19772)
-- Name: warehouse_items warehouse_items_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_items
    ADD CONSTRAINT warehouse_items_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.warehouse_categories(id) ON DELETE RESTRICT;


--
-- TOC entry 3653 (class 2606 OID 19782)
-- Name: warehouse_items warehouse_items_default_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_items
    ADD CONSTRAINT warehouse_items_default_supplier_id_foreign FOREIGN KEY (default_supplier_id) REFERENCES public.warehouse_suppliers(id) ON DELETE SET NULL;


--
-- TOC entry 3654 (class 2606 OID 19777)
-- Name: warehouse_items warehouse_items_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_items
    ADD CONSTRAINT warehouse_items_unit_id_foreign FOREIGN KEY (unit_id) REFERENCES public.warehouse_units(id) ON DELETE RESTRICT;


--
-- TOC entry 3651 (class 2606 OID 19753)
-- Name: warehouse_locations warehouse_locations_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_locations
    ADD CONSTRAINT warehouse_locations_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.branches(id) ON DELETE SET NULL;


--
-- TOC entry 3689 (class 2606 OID 20102)
-- Name: warehouse_opening_balance_item_serials warehouse_opening_balance_item_serials_opening_balance_item_id_; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_item_serials
    ADD CONSTRAINT warehouse_opening_balance_item_serials_opening_balance_item_id_ FOREIGN KEY (opening_balance_item_id) REFERENCES public.warehouse_opening_balance_items(id) ON DELETE CASCADE;


--
-- TOC entry 3686 (class 2606 OID 20085)
-- Name: warehouse_opening_balance_items warehouse_opening_balance_items_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_items
    ADD CONSTRAINT warehouse_opening_balance_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE RESTRICT;


--
-- TOC entry 3687 (class 2606 OID 20090)
-- Name: warehouse_opening_balance_items warehouse_opening_balance_items_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_items
    ADD CONSTRAINT warehouse_opening_balance_items_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE RESTRICT;


--
-- TOC entry 3688 (class 2606 OID 20080)
-- Name: warehouse_opening_balance_items warehouse_opening_balance_items_opening_balance_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balance_items
    ADD CONSTRAINT warehouse_opening_balance_items_opening_balance_id_foreign FOREIGN KEY (opening_balance_id) REFERENCES public.warehouse_opening_balances(id) ON DELETE CASCADE;


--
-- TOC entry 3685 (class 2606 OID 20064)
-- Name: warehouse_opening_balances warehouse_opening_balances_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_opening_balances
    ADD CONSTRAINT warehouse_opening_balances_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3660 (class 2606 OID 19850)
-- Name: warehouse_receiving_items warehouse_receiving_items_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receiving_items
    ADD CONSTRAINT warehouse_receiving_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE RESTRICT;


--
-- TOC entry 3661 (class 2606 OID 19845)
-- Name: warehouse_receiving_items warehouse_receiving_items_receiving_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receiving_items
    ADD CONSTRAINT warehouse_receiving_items_receiving_id_foreign FOREIGN KEY (receiving_id) REFERENCES public.warehouse_receivings(id) ON DELETE CASCADE;


--
-- TOC entry 3657 (class 2606 OID 19824)
-- Name: warehouse_receivings warehouse_receivings_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receivings
    ADD CONSTRAINT warehouse_receivings_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE RESTRICT;


--
-- TOC entry 3658 (class 2606 OID 19829)
-- Name: warehouse_receivings warehouse_receivings_received_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receivings
    ADD CONSTRAINT warehouse_receivings_received_by_foreign FOREIGN KEY (received_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3659 (class 2606 OID 19819)
-- Name: warehouse_receivings warehouse_receivings_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_receivings
    ADD CONSTRAINT warehouse_receivings_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.warehouse_suppliers(id) ON DELETE RESTRICT;


--
-- TOC entry 3683 (class 2606 OID 20042)
-- Name: warehouse_stock_adjustment_item_serials warehouse_stock_adjustment_item_serials_adjustment_item_id_fore; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_item_serials
    ADD CONSTRAINT warehouse_stock_adjustment_item_serials_adjustment_item_id_fore FOREIGN KEY (adjustment_item_id) REFERENCES public.warehouse_stock_adjustment_items(id) ON DELETE CASCADE;


--
-- TOC entry 3684 (class 2606 OID 20047)
-- Name: warehouse_stock_adjustment_item_serials warehouse_stock_adjustment_item_serials_item_serial_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_item_serials
    ADD CONSTRAINT warehouse_stock_adjustment_item_serials_item_serial_id_foreign FOREIGN KEY (item_serial_id) REFERENCES public.warehouse_item_serials(id) ON DELETE RESTRICT;


--
-- TOC entry 3681 (class 2606 OID 20025)
-- Name: warehouse_stock_adjustment_items warehouse_stock_adjustment_items_adjustment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_items
    ADD CONSTRAINT warehouse_stock_adjustment_items_adjustment_id_foreign FOREIGN KEY (adjustment_id) REFERENCES public.warehouse_stock_adjustments(id) ON DELETE CASCADE;


--
-- TOC entry 3682 (class 2606 OID 20030)
-- Name: warehouse_stock_adjustment_items warehouse_stock_adjustment_items_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustment_items
    ADD CONSTRAINT warehouse_stock_adjustment_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE RESTRICT;


--
-- TOC entry 3679 (class 2606 OID 20009)
-- Name: warehouse_stock_adjustments warehouse_stock_adjustments_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustments
    ADD CONSTRAINT warehouse_stock_adjustments_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3680 (class 2606 OID 20004)
-- Name: warehouse_stock_adjustments warehouse_stock_adjustments_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_adjustments
    ADD CONSTRAINT warehouse_stock_adjustments_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE RESTRICT;


--
-- TOC entry 3670 (class 2606 OID 19936)
-- Name: warehouse_stock_movements warehouse_stock_movements_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_movements
    ADD CONSTRAINT warehouse_stock_movements_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3671 (class 2606 OID 19926)
-- Name: warehouse_stock_movements warehouse_stock_movements_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_movements
    ADD CONSTRAINT warehouse_stock_movements_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE RESTRICT;


--
-- TOC entry 3672 (class 2606 OID 19931)
-- Name: warehouse_stock_movements warehouse_stock_movements_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_stock_movements
    ADD CONSTRAINT warehouse_stock_movements_location_id_foreign FOREIGN KEY (location_id) REFERENCES public.warehouse_locations(id) ON DELETE RESTRICT;


--
-- TOC entry 3677 (class 2606 OID 19987)
-- Name: warehouse_transfer_item_serials warehouse_transfer_item_serials_item_serial_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_item_serials
    ADD CONSTRAINT warehouse_transfer_item_serials_item_serial_id_foreign FOREIGN KEY (item_serial_id) REFERENCES public.warehouse_item_serials(id) ON DELETE RESTRICT;


--
-- TOC entry 3678 (class 2606 OID 19982)
-- Name: warehouse_transfer_item_serials warehouse_transfer_item_serials_transfer_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_item_serials
    ADD CONSTRAINT warehouse_transfer_item_serials_transfer_item_id_foreign FOREIGN KEY (transfer_item_id) REFERENCES public.warehouse_transfer_items(id) ON DELETE CASCADE;


--
-- TOC entry 3668 (class 2606 OID 19911)
-- Name: warehouse_transfer_items warehouse_transfer_items_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_items
    ADD CONSTRAINT warehouse_transfer_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.warehouse_items(id) ON DELETE RESTRICT;


--
-- TOC entry 3669 (class 2606 OID 19906)
-- Name: warehouse_transfer_items warehouse_transfer_items_transfer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfer_items
    ADD CONSTRAINT warehouse_transfer_items_transfer_id_foreign FOREIGN KEY (transfer_id) REFERENCES public.warehouse_transfers(id) ON DELETE CASCADE;


--
-- TOC entry 3662 (class 2606 OID 19880)
-- Name: warehouse_transfers warehouse_transfers_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers
    ADD CONSTRAINT warehouse_transfers_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3663 (class 2606 OID 19865)
-- Name: warehouse_transfers warehouse_transfers_from_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers
    ADD CONSTRAINT warehouse_transfers_from_location_id_foreign FOREIGN KEY (from_location_id) REFERENCES public.warehouse_locations(id) ON DELETE RESTRICT;


--
-- TOC entry 3664 (class 2606 OID 19890)
-- Name: warehouse_transfers warehouse_transfers_received_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers
    ADD CONSTRAINT warehouse_transfers_received_by_foreign FOREIGN KEY (received_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3665 (class 2606 OID 19875)
-- Name: warehouse_transfers warehouse_transfers_requested_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers
    ADD CONSTRAINT warehouse_transfers_requested_by_foreign FOREIGN KEY (requested_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3666 (class 2606 OID 19870)
-- Name: warehouse_transfers warehouse_transfers_to_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers
    ADD CONSTRAINT warehouse_transfers_to_location_id_foreign FOREIGN KEY (to_location_id) REFERENCES public.warehouse_locations(id) ON DELETE RESTRICT;


--
-- TOC entry 3667 (class 2606 OID 19885)
-- Name: warehouse_transfers warehouse_transfers_transferred_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.warehouse_transfers
    ADD CONSTRAINT warehouse_transfers_transferred_by_foreign FOREIGN KEY (transferred_by) REFERENCES public.users(id) ON DELETE SET NULL;


-- Completed on 2026-05-14 10:53:08

--
-- PostgreSQL database dump complete
--

\unrestrict kr8wwwmOgr89Nj6OGQ77xuuApx76enjTz8hiPyk1aSfYV22Thp2kcud4LqH8gRJ

