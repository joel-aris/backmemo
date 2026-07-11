-- VALIDIKA PostgreSQL Database Schema
-- Generated for Laravel 12 + PHP 8.4
-- PostgreSQL compatible script

-- Create extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    two_factor_confirmed_at TIMESTAMP NULL,
    remember_token TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NULL,
    CONSTRAINT sessions_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions(user_id);

-- Cache, jobs and tokens table
CREATE TABLE IF NOT EXISTS cache (
    key TEXT PRIMARY KEY,
    value BYTEA NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

-- Permission tables
CREATE TABLE IF NOT EXISTS permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name)
);

CREATE TABLE IF NOT EXISTS roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name)
);

CREATE TABLE IF NOT EXISTS model_has_permissions (
    permission_id BIGINT NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type),
    CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS model_has_permissions_model_id_model_type_index ON model_has_permissions(model_id, model_type);

CREATE TABLE IF NOT EXISTS model_has_roles (
    role_id BIGINT NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS model_has_roles_model_id_model_type_index ON model_has_roles(model_id, model_type);

CREATE TABLE IF NOT EXISTS role_has_permissions (
    permission_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Territory tables
CREATE TABLE IF NOT EXISTS provinces (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(120) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS cities (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    province_id UUID NOT NULL,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT cities_province_id_foreign FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS cities_province_id_name_unique ON cities(province_id, name);
CREATE INDEX IF NOT EXISTS cities_province_id_name_index ON cities(province_id, name);

CREATE TABLE IF NOT EXISTS communes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    province_id UUID NOT NULL,
    city_id UUID NOT NULL,
    name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT communes_province_id_foreign FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE,
    CONSTRAINT communes_city_id_foreign FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS communes_city_id_name_unique ON communes(city_id, name);
CREATE INDEX IF NOT EXISTS communes_province_id_city_id_index ON communes(province_id, city_id);

-- Pharmacists table
CREATE TABLE IF NOT EXISTS pharmacists (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    public_id VARCHAR(32) NOT NULL UNIQUE,
    photo_path VARCHAR(255) NOT NULL,
    first_name VARCHAR(120) NOT NULL,
    middle_name VARCHAR(120) NULL,
    last_name VARCHAR(120) NOT NULL,
    ordinal_number VARCHAR(80) NOT NULL UNIQUE,
    sex VARCHAR(20) NOT NULL,
    province_id UUID NOT NULL,
    city_id UUID NOT NULL,
    commune_id UUID NOT NULL,
    professional_address TEXT NOT NULL,
    professional_phone VARCHAR(40) NOT NULL,
    professional_email VARCHAR(255) NOT NULL,
    professional_status VARCHAR(40) NOT NULL,
    registered_at DATE NOT NULL,
    practice_started_at DATE NOT NULL,
    license_number VARCHAR(100) NOT NULL UNIQUE,
    license_status VARCHAR(40) NOT NULL,
    license_expires_at DATE NULL,
    pharmacy_establishment VARCHAR(180) NOT NULL,
    specialization VARCHAR(180) NULL,
    verification_hash VARCHAR(64) NOT NULL UNIQUE,
    qr_code_token VARCHAR(80) NOT NULL UNIQUE,
    qr_code_signature VARCHAR(2048) NOT NULL,
    public_key LONGTEXT NULL,
    public_key_fingerprint VARCHAR(64) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT pharmacists_province_id_foreign FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE,
    CONSTRAINT pharmacists_city_id_foreign FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
    CONSTRAINT pharmacists_commune_id_foreign FOREIGN KEY (commune_id) REFERENCES communes(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS pharmacists_province_id_index ON pharmacists(province_id);
CREATE INDEX IF NOT EXISTS pharmacists_city_id_index ON pharmacists(city_id);
CREATE INDEX IF NOT EXISTS pharmacists_commune_id_index ON pharmacists(commune_id);
CREATE INDEX IF NOT EXISTS pharmacists_license_status_index ON pharmacists(license_status);
CREATE INDEX IF NOT EXISTS pharmacists_license_expires_at_index ON pharmacists(license_expires_at);
CREATE INDEX IF NOT EXISTS pharmacists_public_key_fingerprint_index ON pharmacists(public_key_fingerprint);

-- Documents table
CREATE TABLE IF NOT EXISTS documents (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    pharmacist_id UUID NULL,
    owner_id BIGINT NOT NULL,
    title VARCHAR(180) NOT NULL,
    type VARCHAR(60) NOT NULL,
    path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    size BIGINT NOT NULL,
    sha256_hash VARCHAR(64) NOT NULL UNIQUE,
    current_sha256_hash VARCHAR(64) NULL,
    hash_algorithm VARCHAR(32) DEFAULT 'SHA-256',
    issued_at DATE NOT NULL,
    signature_payload LONGTEXT NULL,
    signature VARCHAR(2048) NOT NULL,
    signature_algorithm VARCHAR(80) NULL,
    public_key LONGTEXT NULL,
    public_key_fingerprint VARCHAR(64) NULL,
    trusted_timestamp TIMESTAMP NULL,
    integrity_verified_at TIMESTAMP NULL,
    integrity_status VARCHAR(32) DEFAULT 'pending',
    proof_metadata JSON NULL,
    qr_code_token VARCHAR(80) NOT NULL UNIQUE,
    status VARCHAR(40) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT documents_pharmacist_id_foreign FOREIGN KEY (pharmacist_id) REFERENCES pharmacists(id) ON DELETE SET NULL,
    CONSTRAINT documents_owner_id_foreign FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS documents_type_index ON documents(type);
CREATE INDEX IF NOT EXISTS documents_status_index ON documents(status);
CREATE INDEX IF NOT EXISTS documents_public_key_fingerprint_index ON documents(public_key_fingerprint);
CREATE INDEX IF NOT EXISTS documents_integrity_status_index ON documents(integrity_status);
CREATE INDEX IF NOT EXISTS documents_trusted_timestamp_index ON documents(trusted_timestamp);

-- Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id BIGINT NULL,
    action VARCHAR(120) NOT NULL,
    resource_type VARCHAR(255) NULL,
    resource_id VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    metadata JSONB NULL,
    previous_hash VARCHAR(64) NULL,
    entry_hash VARCHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS audit_logs_action_index ON audit_logs(action);
CREATE INDEX IF NOT EXISTS audit_logs_resource_type_index ON audit_logs(resource_type);
CREATE INDEX IF NOT EXISTS audit_logs_resource_id_index ON audit_logs(resource_id);
CREATE INDEX IF NOT EXISTS audit_logs_created_at_index ON audit_logs(created_at);

-- Seed default roles and permissions
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
    ('view pharmacists', 'web', NOW(), NOW()),
    ('manage pharmacists', 'web', NOW(), NOW()),
    ('view documents', 'web', NOW(), NOW()),
    ('manage documents', 'web', NOW(), NOW()),
    ('sign documents', 'web', NOW(), NOW()),
    ('view audit logs', 'web', NOW(), NOW()),
    ('manage users', 'web', NOW(), NOW()),
    ('reset user 2fa', 'web', NOW(), NOW())
ON CONFLICT (name, guard_name) DO UPDATE SET name = EXCLUDED.name;

INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
    ('Super Admin', 'web', NOW(), NOW()),
    ('Administrateur', 'web', NOW(), NOW()),
    ('Président', 'web', NOW(), NOW()),
    ('Secrétaire', 'web', NOW(), NOW()),
    ('Pharmacien', 'web', NOW(), NOW()),
    ('Auditeur', 'web', NOW(), NOW()),
    ('Visiteur', 'web', NOW(), NOW())
ON CONFLICT (name, guard_name) DO UPDATE SET name = EXCLUDED.name;

  -- Seed DRC provinces
INSERT INTO provinces (id, name, code, created_at, updated_at) VALUES
    (uuid_generate_v4(), 'Kinshasa', 'KIN', NOW(), NOW()),
    (uuid_generate_v4(), 'Kongo-Central', 'KOC', NOW(), NOW()),
    (uuid_generate_v4(), 'Kwango', 'KWG', NOW(), NOW()),
    (uuid_generate_v4(), 'Kwilu', 'KWU', NOW(), NOW()),
    (uuid_generate_v4(), 'Mai-Ndombe', 'MND', NOW(), NOW()),
    (uuid_generate_v4(), 'Kasaï', 'KAS', NOW(), NOW()),
    (uuid_generate_v4(), 'Kasaï-Central', 'KAC', NOW(), NOW()),
    (uuid_generate_v4(), 'Kasaï-Oriental', 'KASO', NOW(), NOW()),
    (uuid_generate_v4(), 'Lomami', 'LOM', NOW(), NOW()),
    (uuid_generate_v4(), 'Sankuru', 'SAN', NOW(), NOW()),
    (uuid_generate_v4(), 'Maniema', 'MAN', NOW(), NOW()),
    (uuid_generate_v4(), 'Tshopo', 'TSH', NOW(), NOW()),
    (uuid_generate_v4(), 'Ituri', 'ITU', NOW(), NOW()),
    (uuid_generate_v4(), 'Haut-Uele', 'HUE', NOW(), NOW()),
    (uuid_generate_v4(), 'Bas-Uele', 'BUE', NOW(), NOW()),
    (uuid_generate_v4(), 'Nord-Kivu', 'NK', NOW(), NOW()),
    (uuid_generate_v4(), 'Sud-Kivu', 'SK', NOW(), NOW()),
    (uuid_generate_v4(), 'Tanganyika', 'TAN', NOW(), NOW()),
    (uuid_generate_v4(), 'Haut-Lomami', 'HLO', NOW(), NOW()),
    (uuid_generate_v4(), 'Lualaba', 'LUA', NOW(), NOW()),
    (uuid_generate_v4(), 'Haut-Katanga', 'HK', NOW(), NOW()),
    (uuid_generate_v4(), 'Équateur', 'EQU', NOW(), NOW()),
    (uuid_generate_v4(), 'Tshuapa', 'TPA', NOW(), NOW()),
    (uuid_generate_v4(), 'Mongala', 'MON', NOW(), NOW()),
    (uuid_generate_v4(), 'Nord-Ubangi', 'NUB', NOW(), NOW()),
    (uuid_generate_v4(), 'Sud-Ubangi', 'SUB', NOW(), NOW())
ON CONFLICT (code) DO UPDATE SET name = EXCLUDED.name;

-- Seed admin user (password: UVQUG777)
-- INSERT INTO users (name, email, password, email_verified_at, two_factor_confirmed_at, created_at, updated_at) VALUES
--     ('Jairo Admin', 'jairo404@validika.cd', '$argon2id$v=19$m=65536,t=4,p=3$...', NOW(), NOW(), NOW(), NOW());

-- Database credentials for DBeaver/PostgreSQL:
-- Host: 127.0.0.1
-- Port: 5432
-- Database: validika
-- Username: jairo404
-- Password: UVQUG777