-- Étape 5.1: Audit Logging
-- Script SQL pour ajouter la table de logs d'audit

USE vite_gourmand;

-- Table: audit_log
-- Stocke toutes les opérations sensibles: créations, modifications, suppressions
CREATE TABLE IF NOT EXISTS audit_log (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    action VARCHAR(50) NOT NULL COMMENT 'create, read, update, delete, login, logout',
    ressource VARCHAR(50) NOT NULL COMMENT 'utilisateurs, plats, menus, commandes, avis, contact',
    ressource_id INT COMMENT 'ID de la ressource affectée',
    details JSON COMMENT 'Colonnes modifiées: {ancien: value, nouveau: value}',
    ip_address VARCHAR(45),
    user_agent TEXT,
    statut VARCHAR(20) DEFAULT 'success' COMMENT 'success, error, denied',
    raison_refus VARCHAR(255) COMMENT 'Raison du refus si statut=denied',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (utilisateur_id),
    INDEX idx_action (action),
    INDEX idx_ressource (ressource),
    INDEX idx_created (created_at),
    INDEX idx_statut (statut),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: audit_retention_policy
-- Politique de conservation des logs (Admin peut configurer)
CREATE TABLE IF NOT EXISTS audit_retention_policy (
    policy_id INT AUTO_INCREMENT PRIMARY KEY,
    ressource VARCHAR(50),
    action VARCHAR(50),
    retention_days INT DEFAULT 90 COMMENT 'Jours de conservation du log',
    enabled BOOLEAN DEFAULT TRUE,
    
    UNIQUE KEY unique_retention (ressource, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Politique de conservation par défaut
INSERT INTO audit_retention_policy (ressource, action, retention_days, enabled) VALUES
-- Utilisateurs: garder longtemps
('utilisateurs', 'create', 365, TRUE),
('utilisateurs', 'delete', 365, TRUE),
('utilisateurs', 'update', 180, TRUE),
('utilisateurs', 'login', 90, TRUE),

-- Plats: garder 1 an
('plats', 'create', 365, TRUE),
('plats', 'delete', 365, TRUE),
('plats', 'update', 180, TRUE),

-- Menus: garder 1 an
('menus', 'create', 365, TRUE),
('menus', 'delete', 365, TRUE),
('menus', 'update', 180, TRUE),

-- Commandes: garder 2 ans (compliance)
('commandes', 'create', 730, TRUE),
('commandes', 'delete', 730, TRUE),
('commandes', 'update', 365, TRUE),

-- Avis: garder 1 an
('avis', 'create', 365, TRUE),
('avis', 'delete', 365, TRUE),
('avis', 'moderate', 365, TRUE),

-- Contact: garder 6 mois
('contact', 'create', 180, TRUE),
('contact', 'delete', 180, TRUE),

-- Permission changes: garder 2 ans
('permissions', 'update', 730, TRUE);

-- Procédure de nettoyage des logs (à appeler via CRON)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS cleanup_expired_audit_logs()
BEGIN
    DELETE FROM audit_log
    WHERE created_at < NOW() - INTERVAL (
        SELECT MAX(COALESCE(retention_days, 90))
        FROM audit_retention_policy
    ) DAY
    AND EXISTS (
        SELECT 1 FROM audit_retention_policy
        WHERE audit_retention_policy.ressource = audit_log.ressource
        AND audit_retention_policy.action = audit_log.action
        AND audit_log.created_at < NOW() - INTERVAL audit_retention_policy.retention_days DAY
        AND audit_retention_policy.enabled = TRUE
    );
END //
DELIMITER ;

-- Créer un événement pour nettoyer automatiquement (optionnel)
-- SET GLOBAL event_scheduler = ON;
-- CREATE EVENT IF NOT EXISTS daily_audit_cleanup
-- ON SCHEDULE EVERY 1 DAY
-- STARTS CURRENT_TIMESTAMP
-- DO CALL cleanup_expired_audit_logs();
