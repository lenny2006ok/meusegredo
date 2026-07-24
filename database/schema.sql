CREATE DATABASE IF NOT EXISTS banco_site
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE banco_site;

-- Tabela de segredos (posts principais)
CREATE TABLE IF NOT EXISTS secrets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('relacionamentos', 'trabalho', 'familia', 'escola', 'dinheiro', 'sobrenatural', 'desabafos', 'segredos', 'engracados', 'outros') NOT NULL,
    pseudonym VARCHAR(50) NOT NULL,
    city VARCHAR(100) NULL,
    age INT NULL,
    ip VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0,
    reported BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'reported', 'removed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_likes (likes),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB;

-- Tabela de comentários
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    secret_id INT NOT NULL,
    parent_id INT NULL,
    content TEXT NOT NULL,
    pseudonym VARCHAR(50) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0,
    reported BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'reported', 'removed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_secret (secret_id),
    INDEX idx_parent (parent_id),
    INDEX idx_status (status),
    FOREIGN KEY (secret_id) REFERENCES secrets(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela para curtidas (evita duplicidade)
CREATE TABLE IF NOT EXISTS likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    secret_id INT NULL,
    comment_id INT NULL,
    ip VARCHAR(45) NOT NULL,
    type ENUM('like', 'dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_secret (secret_id),
    INDEX idx_comment (comment_id),
    INDEX idx_ip_secret (secret_id, ip),
    INDEX idx_ip_comment (comment_id, ip),
    CHECK (secret_id IS NOT NULL OR comment_id IS NOT NULL)
) ENGINE=InnoDB;

-- Tabela de denúncias
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    secret_id INT NULL,
    comment_id INT NULL,
    reason VARCHAR(255) NOT NULL,
    details TEXT NULL,
    ip VARCHAR(45) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    resolved_by VARCHAR(50) NULL,
    INDEX idx_secret (secret_id),
    INDEX idx_comment (comment_id),
    INDEX idx_status (status),
    FOREIGN KEY (secret_id) REFERENCES secrets(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de visualizações (para contagem única por IP)
CREATE TABLE IF NOT EXISTS views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    secret_id INT NOT NULL,
    ip VARCHAR(45) NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_view (secret_id, ip),
    FOREIGN KEY (secret_id) REFERENCES secrets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de rate limiting
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_action (ip, action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    icon VARCHAR(10) NOT NULL,
    description VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB;

-- Inserir categorias padrão
INSERT IGNORE INTO categories (name, icon, description, sort_order) VALUES
('relacionamentos', '❤️', 'Segredos sobre relacionamentos', 1),
('trabalho', '💼', 'Segredos do ambiente profissional', 2),
('familia', '👨‍👩‍👧', 'Segredos familiares', 3),
('escola', '🎓', 'Segredos da vida escolar', 4),
('dinheiro', '💰', 'Segredos sobre finanças', 5),
('sobrenatural', '👻', 'Histórias sobrenaturais', 6),
('desabafos', '😢', 'Desabafos e confissões', 7),
('segredos', '🤫', 'Segredos em geral', 8),
('engracados', '🤣', 'Segredos engraçados', 9),
('outros', '📌', 'Outros segredos', 10);

-- Tabela de configurações do sistema
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Inserir configurações padrão
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'MeuSegredo.Shop'),
('site_slogan', 'Todo mundo guarda um segredo. Alguns merecem ser contados.'),
('site_description', 'Plataforma anônima para compartilhar segredos e desabafos'),
('maintenance_mode', 'false'),
('max_secret_length', '5000'),
('max_title_length', '150'),
('auto_approve_comments', 'true'),
('trending_hours', '24');

-- Tabela de usuários administradores
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'moderator') DEFAULT 'moderator',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Inserir usuário admin padrão (senha: Admin@123)
INSERT IGNORE INTO admin_users (username, password_hash, email, role) VALUES
('admin', '$2y$10$f61X890m5Y2bE361R7v22.UaK7bE361R7v22.UaK7bE361R7v22a', 'admin@meusegredo.shop', 'admin');

-- Tabela de logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_type ENUM('admin', 'system') DEFAULT 'system',
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
