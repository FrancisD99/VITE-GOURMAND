-- Étape 5: RBAC Complet (Role-Based Access Control)
-- Script SQL pour ajouter les tables de permissions et rôles granulaires

USE vite_gourmand;

-- Table: permission
-- Définit toutes les permissions disponibles dans le système
CREATE TABLE IF NOT EXISTS permission (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    description TEXT,
    categorie VARCHAR(50) COMMENT 'utilisateurs, plats, menus, commandes, avis, contact'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: role_permission
-- Association entre un rôle et ses permissions
CREATE TABLE IF NOT EXISTS role_permission (
    role_permission_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES poste(role_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permission(permission_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter des rôles supplémentaires (s'ils n'existent pas)
INSERT IGNORE INTO poste (libelle) VALUES 
    ('serveur'),
    ('modérateur');

-- Insérer les permissions
INSERT INTO permission (code, libelle, description, categorie) VALUES
-- Permissions Utilisateurs
('users.create', 'Créer utilisateur', 'Créer un nouveau compte utilisateur', 'utilisateurs'),
('users.read', 'Lire utilisateurs', 'Consulter les données des utilisateurs', 'utilisateurs'),
('users.read.own', 'Lire son profil', 'Consulter son propre profil', 'utilisateurs'),
('users.update', 'Modifier utilisateur', 'Mettre à jour les données des utilisateurs', 'utilisateurs'),
('users.update.own', 'Modifier son profil', 'Mettre à jour son propre profil', 'utilisateurs'),
('users.delete', 'Supprimer utilisateur', 'Supprimer des comptes utilisateurs', 'utilisateurs'),
('users.list', 'Lister utilisateurs', 'Voir la liste de tous les utilisateurs', 'utilisateurs'),

-- Permissions Plats
('plats.create', 'Créer plat', 'Ajouter un nouveau plat au menu', 'plats'),
('plats.read', 'Lire plats', 'Consulter les plats', 'plats'),
('plats.update', 'Modifier plat', 'Mettre à jour les informations des plats', 'plats'),
('plats.delete', 'Supprimer plat', 'Supprimer un plat', 'plats'),
('plats.list', 'Lister plats', 'Voir la liste des plats', 'plats'),

-- Permissions Menus
('menus.create', 'Créer menu', 'Créer un nouveau menu', 'menus'),
('menus.read', 'Lire menus', 'Consulter les menus', 'menus'),
('menus.update', 'Modifier menu', 'Mettre à jour les menus', 'menus'),
('menus.delete', 'Supprimer menu', 'Supprimer un menu', 'menus'),
('menus.list', 'Lister menus', 'Voir la liste des menus', 'menus'),

-- Permissions Commandes
('commandes.create', 'Créer commande', 'Créer une nouvelle commande', 'commandes'),
('commandes.read', 'Lire commande', 'Consulter les commandes', 'commandes'),
('commandes.read.own', 'Voir ses commandes', 'Consulter ses propres commandes', 'commandes'),
('commandes.update', 'Modifier commande', 'Mettre à jour les commandes', 'commandes'),
('commandes.update.own', 'Modifier sa commande', 'Modifier sa propre commande', 'commandes'),
('commandes.delete', 'Supprimer commande', 'Supprimer une commande', 'commandes'),
('commandes.delete.own', 'Supprimer sa commande', 'Supprimer sa propre commande', 'commandes'),
('commandes.list', 'Lister commandes', 'Voir la liste des commandes', 'commandes'),
('commandes.list.own', 'Voir ses commandes', 'Voir ses propres commandes', 'commandes'),
('commandes.status', 'Modifier statut', 'Changer le statut d\'une commande', 'commandes'),

-- Permissions Avis
('avis.create', 'Créer avis', 'Créer un nouvel avis', 'avis'),
('avis.read', 'Lire avis', 'Consulter les avis', 'avis'),
('avis.delete', 'Supprimer avis', 'Supprimer un avis', 'avis'),
('avis.delete.own', 'Supprimer son avis', 'Supprimer son propre avis', 'avis'),
('avis.moderate', 'Modérer avis', 'Approuver ou rejeter les avis', 'avis'),
('avis.list', 'Lister avis', 'Voir la liste des avis', 'avis'),

-- Permissions Contact
('contact.create', 'Créer message', 'Envoyer un message de contact', 'contact'),
('contact.read', 'Lire messages', 'Consulter les messages de contact', 'contact'),
('contact.delete', 'Supprimer message', 'Supprimer un message de contact', 'contact'),
('contact.list', 'Lister messages', 'Voir tous les messages de contact', 'contact'),

-- Permissions Admin
('admin.view', 'Voir dashboard admin', 'Accéder au panneau d\'administration', 'admin'),
('admin.logs', 'Voir journaux', 'Consulter les logs du système', 'admin'),
('admin.settings', 'Modifier paramètres', 'Changer les paramètres du système', 'admin');

-- Attribuer les permissions par rôle

-- ADMIN: Toutes les permissions
INSERT INTO role_permission (role_id, permission_id)
SELECT (SELECT role_id FROM poste WHERE libelle = 'admin'), permission_id FROM permission
ON DUPLICATE KEY UPDATE role_permission_id = role_permission_id;

-- CLIENT: Permissions limitées (lire publiques + ses propres données)
INSERT INTO role_permission (role_id, permission_id) VALUES
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'users.read.own')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'users.update.own')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'plats.read')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'plats.list')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'menus.read')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'menus.list')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'commandes.create')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'commandes.read.own')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'commandes.update.own')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'commandes.delete.own')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'commandes.list.own')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'avis.create')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'avis.read')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'avis.delete.own')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'avis.list')),
((SELECT role_id FROM poste WHERE libelle = 'cliente'), (SELECT permission_id FROM permission WHERE code = 'contact.create'))
ON DUPLICATE KEY UPDATE role_permission_id = role_permission_id;

-- CHEF: Permissions cuisine (lire/gérer plats, voir commandes)
INSERT INTO role_permission (role_id, permission_id) VALUES
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'users.read.own')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'plats.read')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'plats.create')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'plats.update')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'plats.list')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'commandes.read')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'commandes.list')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'commandes.status')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'menus.read')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'menus.list')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'avis.read')),
((SELECT role_id FROM poste WHERE libelle = 'chef'), (SELECT permission_id FROM permission WHERE code = 'avis.list'))
ON DUPLICATE KEY UPDATE role_permission_id = role_permission_id;

-- SERVEUR: Permissions service (voir commandes, mettre à jour statuts)
INSERT INTO role_permission (role_id, permission_id) VALUES
((SELECT role_id FROM poste WHERE libelle = 'serveur'), (SELECT permission_id FROM permission WHERE code = 'users.read.own')),
((SELECT role_id FROM poste WHERE libelle = 'serveur'), (SELECT permission_id FROM permission WHERE code = 'commandes.read')),
((SELECT role_id FROM poste WHERE libelle = 'serveur'), (SELECT permission_id FROM permission WHERE code = 'commandes.list')),
((SELECT role_id FROM poste WHERE libelle = 'serveur'), (SELECT permission_id FROM permission WHERE code = 'commandes.status')),
((SELECT role_id FROM poste WHERE libelle = 'serveur'), (SELECT permission_id FROM permission WHERE code = 'plats.read')),
((SELECT role_id FROM poste WHERE libelle = 'serveur'), (SELECT permission_id FROM permission WHERE code = 'menus.read')),
((SELECT role_id FROM poste WHERE libelle = 'serveur'), (SELECT permission_id FROM permission WHERE code = 'avis.read'))
ON DUPLICATE KEY UPDATE role_permission_id = role_permission_id;

-- MODÉRATEUR: Permissions de modération
INSERT INTO role_permission (role_id, permission_id) VALUES
((SELECT role_id FROM poste WHERE libelle = 'modérateur'), (SELECT permission_id FROM permission WHERE code = 'users.read')),
((SELECT role_id FROM poste WHERE libelle = 'modérateur'), (SELECT permission_id FROM permission WHERE code = 'avis.read')),
((SELECT role_id FROM poste WHERE libelle = 'modérateur'), (SELECT permission_id FROM permission WHERE code = 'avis.moderate')),
((SELECT role_id FROM poste WHERE libelle = 'modérateur'), (SELECT permission_id FROM permission WHERE code = 'avis.delete')),
((SELECT role_id FROM poste WHERE libelle = 'modérateur'), (SELECT permission_id FROM permission WHERE code = 'contact.read')),
((SELECT role_id FROM poste WHERE libelle = 'modérateur'), (SELECT permission_id FROM permission WHERE code = 'contact.list')),
((SELECT role_id FROM poste WHERE libelle = 'modérateur'), (SELECT permission_id FROM permission WHERE code = 'avis.list')),
((SELECT role_id FROM poste WHERE libelle = 'modérateur'), (SELECT permission_id FROM permission WHERE code = 'contact.delete'))
ON DUPLICATE KEY UPDATE role_permission_id = role_permission_id;
