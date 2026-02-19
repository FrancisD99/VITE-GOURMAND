# Backend Vite & Gourmand

Backend PHP/PDO pour l'application Vite & Gourmand.

## Structure du projet

```
backend/
├── api/
│   └── index.php              # Point d'entrée principal des API
├── config/
│   └── Database.php           # Configuration de la Base de Données
├── models/
│   ├── Utilisateur.php        # Modèle Utilisateur
│   ├── Menu.php               # Modèle Menu
│   ├── Commandante.php        # Modèle Commande
│   ├── Avis.php               # Modèle Avis
│   └── Contact.php            # Modèle Contact
└── database.sql               # Schéma de la base de données
```

## Installation

### 1. Configuration de la Base de Données

Modifiez les paramètres de `backend/config/Database.php`:

```php
private $host = 'localhost';      // Serveur MySQL
private $db_name = 'vite_gourmand'; // Nom de la base
private $user = 'root';           // Utilisateur MySQL
private $password = '';           // Mot de passe
```

### 2. Créer la Base de Données

```bash
mysql -u root -p < backend/database.sql
```

Ou copiez le contenu de `database.sql` dans phpMyAdmin.

### 3. Configuration du serveur

Le backend peut fonctionner avec un serveur PHP intégré:

```bash
cd backend
php -S localhost:8000
```

## API Endpoints

### Utilisateurs

```
POST   /api/utilisateurs              # Créer un utilisateur
GET    /api/utilisateurs              # Lister tous les utilisateurs
GET    /api/utilisateurs/:id          # Récupérer un utilisateur
PUT    /api/utilisateurs/:id          # Mettre à jour un utilisateur
DELETE /api/utilisateurs/:id          # Supprimer un utilisateur
```

**Exemple (POST):**
```json
{
  "email": "user@example.com",
  "password": "secure_password",
  "prenom": "Jean",
  "telephone": "0123456789",
  "ville": "Paris",
  "pays": "France",
  "adresse_postale": "123 Rue de Paris",
  "role_id": 2
}
```

### Menus

```
POST   /api/menus                    # Créer un menu
GET    /api/menus                    # Lister tous les menus
GET    /api/menus?regime=Vegan        # Filtrer par régime
GET    /api/menus/:id                # Récupérer un menu
PUT    /api/menus/:id                # Mettre à jour un menu
DELETE /api/menus/:id                # Supprimer un menu
```

**Exemple (POST):**
```json
{
  "titre": "Menu Spécial",
  "nombre_personne_minimum": 4,
  "prix_par_personne": 35.50,
  "regime": "Classique",
  "description": "Menu délicieux avec...",
  "quantite_restante": 10
}
```

### Commandes

```
POST   /api/commandes                # Créer une commande
GET    /api/commandes                # Lister toutes les commandes
GET    /api/commandes/:id            # Récupérer une commande
PUT    /api/commandes/:id            # Mettre à jour une commande
DELETE /api/commandes/:id            # Supprimer une commande
```

**Exemple (POST):**
```json
{
  "utilisateur_id": 1,
  "date_commandante": "2026-02-20",
  "date_livraison": "2026-02-25",
  "heure_livraison": "18:00",
  "prix_menu": 142.00,
  "nombre_personnes": 4,
  "prix_livraison": 15.00,
  "pret_materiel": true,
  "etat_materiel": "neuf",
  "statut": "confirmée"
}
```

### Avis

```
POST   /api/avis                     # Créer un avis
GET    /api/avis                     # Lister les avis approuvés
GET    /api/avis/:id                 # Récupérer un avis
DELETE /api/avis/:id                 # Supprimer un avis
```

**Exemple (POST):**
```json
{
  "utilisateur_id": 1,
  "titre": "Excellent service!",
  "description": "Service magnifique, nourriture délicieuse",
  "note": 5,
  "statut": "en attente"
}
```

### Contact

```
POST   /api/contact                  # Envoyer un message de contact
GET    /api/contact                  # Lister tous les messages
GET    /api/contact/:id              # Récupérer un message
DELETE /api/contact/:id              # Supprimer un message
```

**Exemple (POST):**
```json
{
  "email": "client@example.com",
  "titre": "Question sur les services",
  "message": "Proposez-vous un service de..."
}
```

## Modèle de Données

### Entités principales

- **utilisateur**: Comptes clients et administrateurs
- **poste**: Rôles (admin, cliente, chef)
- **menu**: Menus proposés
- **commandante**: Commandes de clients
- **avis**: Avis clients
- **contact**: Messages de contact
- **plat**: Plats individuels
- **theme**: Thèmes d'événements
- **regime**: Régimes alimentaires
- **allergen**: Allergènes/informations diététiques
- **horaire**: Horaires d'ouverture/fermeture

## Authentification

Les mots de passe sont hachés avec `password_hash()` (algorithme par défaut bcrypt).

Pour authentifier un utilisateur:

```php
$utilisateur = new Utilisateur($db);
$user = $utilisateur->authenticate($email, $password);
if ($user) {
    // Utilisateur authentifié
} else {
    // Erreur d'authentification
}
```

## Gestion des erreurs

Les réponses d'erreur sont au format JSON:

```json
{
  "error": "Description de l'erreur"
}
```

Les codes HTTP utilisés:
- `200 OK`: Succès
- `201 Created`: Ressource créée
- `400 Bad Request`: Erreur de requête
- `404 Not Found`: Ressource non trouvée
- `500 Internal Server Error`: Erreur serveur

## Notes

- Tous les paramètres sensibles (mots de passe) sont protégés
- Les requêtes utilisent des prepared statements pour éviter les injections SQL
- CORS est activé pour les requêtes du frontend
- Les timestamps (created_at, updated_at) sont gérés automatiquement
