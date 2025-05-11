-- GitHub Crawler Database Schema

-- Users (GitHub users)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    github_id INTEGER NOT NULL UNIQUE,
    login VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    company VARCHAR(255),
    blog VARCHAR(255),
    location VARCHAR(255),
    email VARCHAR(255),
    bio TEXT,
    avatar_url VARCHAR(255),
    url VARCHAR(255),
    public_repos INTEGER DEFAULT 0,
    public_gists INTEGER DEFAULT 0,
    followers INTEGER DEFAULT 0,
    following INTEGER DEFAULT 0,
    created_at INTEGER,
    updated_at INTEGER,
    last_crawled_at INTEGER
);

-- Create index on login
CREATE INDEX IF NOT EXISTS idx_users_login ON users(login);
CREATE INDEX IF NOT EXISTS idx_users_location ON users(location);

-- Organizations (GitHub organizations)
CREATE TABLE IF NOT EXISTS organizations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    github_id INTEGER NOT NULL UNIQUE,
    login VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    description TEXT,
    url VARCHAR(255),
    avatar_url VARCHAR(255),
    location VARCHAR(255),
    email VARCHAR(255),
    public_repos INTEGER DEFAULT 0,
    followers INTEGER DEFAULT 0,
    following INTEGER DEFAULT 0,
    created_at INTEGER,
    updated_at INTEGER,
    last_crawled_at INTEGER
);

-- Create index on login
CREATE INDEX IF NOT EXISTS idx_organizations_login ON organizations(login);
CREATE INDEX IF NOT EXISTS idx_organizations_location ON organizations(location);

-- Languages
CREATE TABLE IF NOT EXISTS languages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    color VARCHAR(7),
    created_at INTEGER
);

-- Licenses
CREATE TABLE IF NOT EXISTS licenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    license_key VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    spdx_id VARCHAR(255),
    url VARCHAR(255),
    created_at INTEGER
);

-- Topics
CREATE TABLE IF NOT EXISTS topics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at INTEGER
);

-- Repositories
CREATE TABLE IF NOT EXISTS repositories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    github_id INTEGER NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    owner_id INTEGER NOT NULL,
    owner_login VARCHAR(255) NOT NULL,
    owner_type VARCHAR(255) NOT NULL,
    description TEXT,
    homepage VARCHAR(255),
    language_id INTEGER,
    license_id INTEGER,
    forks_count INTEGER DEFAULT 0,
    stargazers_count INTEGER DEFAULT 0,
    watchers_count INTEGER DEFAULT 0,
    open_issues_count INTEGER DEFAULT 0,
    is_fork BOOLEAN DEFAULT 0,
    is_archived BOOLEAN DEFAULT 0,
    is_template BOOLEAN DEFAULT 0,
    created_at INTEGER,
    updated_at INTEGER,
    pushed_at INTEGER,
    last_crawled_at INTEGER,
    FOREIGN KEY (language_id) REFERENCES languages(id),
    FOREIGN KEY (license_id) REFERENCES licenses(id)
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_repositories_owner_login ON repositories(owner_login);
CREATE INDEX IF NOT EXISTS idx_repositories_language_id ON repositories(language_id);
CREATE INDEX IF NOT EXISTS idx_repositories_license_id ON repositories(license_id);
CREATE INDEX IF NOT EXISTS idx_repositories_full_name ON repositories(full_name);
CREATE INDEX IF NOT EXISTS idx_repositories_is_fork ON repositories(is_fork);
CREATE INDEX IF NOT EXISTS idx_repositories_stargazers ON repositories(stargazers_count);
CREATE INDEX IF NOT EXISTS idx_repositories_last_crawled ON repositories(last_crawled_at);

-- Repository topics (many-to-many relationship)
CREATE TABLE IF NOT EXISTS repository_topics (
    repository_id INTEGER NOT NULL,
    topic_id INTEGER NOT NULL,
    created_at INTEGER,
    PRIMARY KEY (repository_id, topic_id),
    FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
);

-- Files (README.md and other Markdown files)
CREATE TABLE IF NOT EXISTS files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    entity_type VARCHAR(255) NOT NULL,  -- 'repository', 'organization', or 'user'
    entity_id INTEGER NOT NULL,
    filename VARCHAR(255) NOT NULL,
    content TEXT,
    html_content TEXT,
    created_at INTEGER,
    updated_at INTEGER
);

-- Create index on entity type and id
CREATE INDEX IF NOT EXISTS idx_files_entity ON files(entity_type, entity_id);

-- Crawler schedules
CREATE TABLE IF NOT EXISTS crawler_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type VARCHAR(255) NOT NULL, -- 'auto', 'manual', or 'cli'
    status VARCHAR(255) NOT NULL, -- 'pending', 'running', 'completed', 'failed', or 'stopped'
    scheduled_at INTEGER NOT NULL,
    started_at INTEGER,
    completed_at INTEGER,
    created_at INTEGER,
    updated_at INTEGER
);

-- Logs
CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type VARCHAR(255) NOT NULL, -- 'crawler', 'github_api', 'admin', etc.
    message TEXT NOT NULL,
    details TEXT,
    reference VARCHAR(255), -- Reference ID (e.g., 'crawler_123')
    created_at INTEGER NOT NULL
);

-- Create index on log type and reference
CREATE INDEX IF NOT EXISTS idx_logs_type ON logs(type);
CREATE INDEX IF NOT EXISTS idx_logs_reference ON logs(reference);
CREATE INDEX IF NOT EXISTS idx_logs_created_at ON logs(created_at);
