-- Base de données pour Vite & Gourmand
CREATE DATABASE IF NOT EXISTS vite_gourmand;
USE vite_gourmand;

-- Table: poste (rôles utilisateur)
CREATE TABLE IF NOT EXISTS poste (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: utilisateur
CREATE TABLE IF NOT EXISTS utilisateur (
    utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(60) NOT NULL,
    prenom VARCHAR(60) NOT NULL,
    telephone VARCHAR(60),
    ville VARCHAR(60),
    pays VARCHAR(60),
    adresse_postale VARCHAR(60),
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES poste(role_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: regime
CREATE TABLE IF NOT EXISTS regime (
    regime_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: theme
CREATE TABLE IF NOT EXISTS theme (
    theme_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: plat (assiette/plat)
CREATE TABLE IF NOT EXISTS plat (
    plat_id INT AUTO_INCREMENT PRIMARY KEY,
    titre_plat VARCHAR(56) NOT NULL,
    photo BLOB,
    description TEXT,
    prix DOUBLE DEFAULT 0,
    regime_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (regime_id) REFERENCES regime(regime_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu
CREATE TABLE IF NOT EXISTS menu (
    menu_id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(50) NOT NULL,
    nombre_personne_minimum INT NOT NULL DEFAULT 1,
    prix_par_personne DOUBLE NOT NULL,
    regime VARCHAR(50),
    description TEXT,
    quantite_restante INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: propose (relation Menu - Plat - Theme)
CREATE TABLE IF NOT EXISTS propose (
    propose_id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    theme_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE,
    FOREIGN KEY (theme_id) REFERENCES theme(theme_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: addon (suppléments/options)
CREATE TABLE IF NOT EXISTS addon (
    addon_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL,
    prix DOUBLE DEFAULT 0,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: commandante (Commande)
CREATE TABLE IF NOT EXISTS commandante (
    commandante_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    date_commandante DATE NOT NULL,
    date_livraison DATE NOT NULL,
    heure_livraison VARCHAR(50) NOT NULL,
    prix_menu DOUBLE NOT NULL,
    nombre_personnes INT NOT NULL,
    prix_livraison DOUBLE DEFAULT 0,
    pret_materiel BOOL DEFAULT FALSE,
    etat_materiel VARCHAR(50),
    statut VARCHAR(50) DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: public (relation Utilisateur - Commande)
CREATE TABLE IF NOT EXISTS public (
    public_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    commandante_id INT NOT NULL,
    UNIQUE KEY unique_user_command (utilisateur_id, commandante_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE,
    FOREIGN KEY (commandante_id) REFERENCES commandante(commandante_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: avis (Avis clients)
CREATE TABLE IF NOT EXISTS avis (
    avis_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(100),
    description TEXT NOT NULL,
    note INT DEFAULT 5 CHECK (note >= 1 AND note <= 5),
    statut VARCHAR(50) DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: contact (Messages de contact)
CREATE TABLE IF NOT EXISTS contact (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    titre VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    statut VARCHAR(50) DEFAULT 'non lu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: horaire (Horaires ouverture/fermeture)
CREATE TABLE IF NOT EXISTS horaire (
    horaire_id INT AUTO_INCREMENT PRIMARY KEY,
    jour VARCHAR(20) NOT NULL UNIQUE,
    heure_ouverture TIME NOT NULL,
    heure_fermeture TIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: allergen (Allergènes)
CREATE TABLE IF NOT EXISTS allergen (
    allergen_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: plat_allergen (Relation Plat - Allergène)
CREATE TABLE IF NOT EXISTS plat_allergen (
    plat_id INT NOT NULL,
    allergen_id INT NOT NULL,
    PRIMARY KEY (plat_id, allergen_id),
    FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE,
    FOREIGN KEY (allergen_id) REFERENCES allergen(allergen_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion de données de base
INSERT INTO poste (libelle) VALUES ('admin'), ('cliente'), ('chef');

-- Créer l'utilisateur admin par défaut
INSERT INTO utilisateur (email, password, nom, prenom, role_id) VALUES 
('admin@vite-gourmand.test', '$2y$10$gKioYteiz/mGwM5SAx1Ncue2eYtsrpj.PTob6GZ8j18ZTzTh1KL2S', 'Admin', 'System', (SELECT role_id FROM poste WHERE libelle = 'admin'));

INSERT INTO regime (libelle) VALUES ('Classique'), ('Vegan'), ('Végétarien'), ('Sans gluten');
INSERT INTO theme (libelle) VALUES ('Mariage'), ('Anniversaire'), ('Réunion professionnelle'), ('Soirée privée');
INSERT INTO allergen (libelle) VALUES ('Arachides'), ('Gluten'), ('Produits laitiers'), ('Oeufs'), ('Noix');
INSERT INTO horaire (jour, heure_ouverture, heure_fermeture) VALUES
    ('Lundi', '09:00:00', '18:00:00'),
    ('Mardi', '09:00:00', '18:00:00'),
    ('Mercredi', '09:00:00', '18:00:00'),
    ('Jeudi', '09:00:00', '18:00:00'),
    ('Vendredi', '09:00:00', '18:00:00'),
    ('Samedi', '10:00:00', '16:00:00'),
    ('Dimanche', '00:00:00', '00:00:00');
