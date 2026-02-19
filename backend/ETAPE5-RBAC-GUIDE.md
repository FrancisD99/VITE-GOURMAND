# 🔐 Étape 5: RBAC Complet (Role-Based Access Control)

## Vue d'ensemble

L'Étape 5 implémente un système de **contrôle d'accès basé sur les rôles (RBAC)** granulaire avec permissions flexibles et assignables.

## Architecture RBAC

### Hiérarchie des Rôles

```
┌─────────────┐
│    ADMIN    │  - Accès total à toutes les ressources
│  (role_id:1)│  - Gestion des utilisateurs et permissions
└─────────────┘

┌─────────────┐
│    CHEF     │  - Gestion des plats et menus
│ (role_id:3) │  - Consultation des commandes
└─────────────┘

┌─────────────┐
│   SERVEUR   │  - Gestion des statuts de commandes
│(Nouveau)    │  - Consultation des plats et menus
└─────────────┘

┌─────────────┐
│ MODÉRATEUR  │  - Modération des avis et messages
│(Nouveau)    │  - Gestion des contacts
└─────────────┘

┌─────────────┐
│   CLIENT    │  - Accès aux plats et menus
│ (role_id:2) │  - Gestion de ses commandes personnelles
└─────────────┘
```

### Structure des Permissions

Les permissions sont organisées par **catégories**:

#### 👥 Utilisateurs
- `users.create` - Créer un nouvel utilisateur
- `users.read` - Lire les données des utilisateurs
- `users.read.own` - Lire son propre profil
- `users.update` - Modifier les utilisateurs
- `users.update.own` - Modifier son profil
- `users.delete` - Supprimer des utilisateurs
- `users.list` - Lister les utilisateurs

#### 🍽️ Plats
- `plats.create` - Créer un plat
- `plats.read` - Consulter les plats
- `plats.update` - Modifier les plats
- `plats.delete` - Supprimer les plats
- `plats.list` - Lister les plats

#### 📋 Menus
- `menus.create` - Créer un menu
- `menus.read` - Consulter les menus
- `menus.update` - Modifier les menus
- `menus.delete` - Supprimer les menus
- `menus.list` - Lister les menus

#### 🛒 Commandes
- `commandes.create` - Créer une commande
- `commandes.read` - Consulter les commandes
- `commandes.read.own` - Consulter ses commandes
- `commandes.update` - Modifier les commandes
- `commandes.update.own` - Modifier sa commande
- `commandes.delete` - Supprimer une commande
- `commandes.delete.own` - Supprimer sa commande
- `commandes.list` - Lister les commandes
- `commandes.list.own` - Lister ses commandes
- `commandes.status` - Modifier le statut

#### ⭐ Avis
- `avis.create` - Créer un avis
- `avis.read` - Consulter les avis
- `avis.delete` - Supprimer un avis
- `avis.delete.own` - Supprimer son avis
- `avis.moderate` - Modérer les avis
- `avis.list` - Lister les avis

#### 📧 Contact
- `contact.create` - Envoyer un message
- `contact.read` - Consulter les messages
- `contact.delete` - Supprimer un message
- `contact.list` - Lister les messages

## Tables Base de Données

### permission
```sql
CREATE TABLE permission (
    permission_id INT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,      -- Ex: 'plats.create'
    libelle VARCHAR(100),          -- Label lisible
    description TEXT,
    categorie VARCHAR(50)          -- Groupe: utilisateurs, plats, etc.
)
```

### role_permission
```sql
CREATE TABLE role_permission (
    role_permission_id INT PRIMARY KEY,
    role_id INT,                   -- FK vers poste
    permission_id INT,             -- FK vers permission
    UNIQUE (role_id, permission_id)
)
```

## Utilisation en Code

### 1. Vérifier une Permission Simple

```php
use AuthHelper;
use PermissionManager;

try {
    $user = AuthHelper::requireAuth();
    
    // Vérifier la permission directement
    AuthHelper::requirePermission($user, 'plats.create', $db);
    
    // Créer le plat...
    
} catch (Exception $e) {
    AuthHelper::sendError($e->getMessage(), 403);
}
```

### 2. Vérifier Plusieurs Permissions (OR)

```php
try {
    $user = AuthHelper::requireAuth();
    
    // L'utilisateur a au moins une de ces permissions
    AuthHelper::requireAnyPermission($user, [
        'plats.update',
        'plats.delete'
    ], $db);
    
} catch (Exception $e) {
    AuthHelper::sendError($e->getMessage(), 403);
}
```

### 3. Accès au PermissionManager Avancé

```php
$user = AuthHelper::requireAuth();
$pm = AuthHelper::getPermissionManager($db);

// Récupérer toutes les permissions de l'utilisateur
$permissions = $pm->getUserPermissions($user);

// Vérifier avec cache
if ($pm->hasPermission($user, 'plats.create')) {
    // Faire quelque chose
}
```

## Exemples de Configuration par Rôle

### Admin
- ✅ Toutes les permissions

### Chef
```
Plats:    create, read, update, list
Menus:    read, list
Commandes: read, list, status (peut changer le statut)
Avis:     read, list
```

### Serveur
```
Commandes: read, list, status (update status)
Plats:    read, list
Menus:    read, list
```

### Modérateur
```
Avis:     read, moderate, delete, list
Contact:  read, list, delete
Utilisateurs: read (view users list)
```

### Client
```
Plats:    read, list
Menus:    read, list
Commandes: create, read.own, update.own, delete.own, list.own
Avis:     create, read, delete.own, list
Contact:  create
```

## Migration Depuis Étape 4

### Étapes pour mettre à jour:

1. **Exécuter le script SQL**
```bash
mysql -u root vite_gourmand < etape5-rbac.sql
```

2. **Mettre à jour les endpoints**
Replace `AuthHelper::requireRole()` par `AuthHelper::requirePermission()`

Avant (Étape 4):
```php
AuthHelper::requireRole($user, 1); // Admin seulement
```

Après (Étape 5):
```php
AuthHelper::requirePermission($user, 'plats.create', $db);
```

3. **Exemple complet: Endpoint Plats**

```php
case 'plats':
    if ($method == 'POST') {
        try {
            $user = AuthHelper::requireAuth();
            AuthHelper::requirePermission($user, 'plats.create', $db);
            
            if ($plat->create($input)) {
                http_response_code(201);
                echo json_encode(['message' => 'Plat créé']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Erreur création']);
            }
        } catch (Exception $e) {
            AuthHelper::sendError($e->getMessage(), 403);
        }
    }
    break;
```

## API de Gestion des Permissions (Admin)

### Listern toutes les permissions

```
GET /api/admin/permissions
Headers: Authorization: Bearer {token}
Response: 200 {
  "categories": {
    "plats": [ { code, libelle, description }, ... ],
    "commandes": [ ... ]
  }
}
```

### Récupérer permissions d'un rôle

```
GET /api/admin/roles/{role_id}/permissions
Headers: Authorization: Bearer {token}
Response: 200 [ { code, libelle, categorie }, ... ]
```

### Assigner permission à un rôle

```
POST /api/admin/roles/{role_id}/permissions
Headers: Authorization: Bearer {token}
Body: { permission_code: "plats.create" }
Response: 201 { message: "Permission assignée" }
```

### Révoquer permission

```
DELETE /api/admin/roles/{role_id}/permissions/{permission_code}
Headers: Authorization: Bearer {token}
Response: 200 { message: "Permission révoquée" }
```

## Tester le RBAC

Ouvrir le fichier de test :
```
http://localhost:8000/test-etape5.html
```

**Tests inclus:**
1. ✅ Vérifier les permissions du rôle connexion
2. ✅ Rôle client peut créer commande 
3. ✅ Rôle client ne peut pas créer plat (403)
4. ✅ Rôle chef peut créer plat
5. ✅ Admin peut tout faire
6. ✅ Modérateur peut modérer avis

## Avantages du RBAC Granulaire

| Aspect | Étape 4 (Role) | Étape 5 (Permission) |
|--------|---|---|
| Flexibilité | Rôles figés | Permissions assignables |
| Nouveaux rôles | Modifier code | Ajouter en DB |
| Permissions | Codées en dur | Gérées en DB |
| Audit | Minimal | Traçable |
| Scalabilité | Limitée | Extensible |
| Maintenance | Compliquée | Simple |

## Prochaines Étapes

### Étape 5.1: Audit Logging
Tracker toutes les opérations sensibles:
- Qui a changé quoi et quand
- Modifications de permissions
- Accès à données sensibles

### Étape 5.2: Admin Dashboard
Interface web pour:
- Gérer les rôles
- Assigner/révoquer les permissions
- Voir les logs d'audit

### Étape 5.3: Rôles Personnalisés
Permettre aux admins de créer des rôles personnalisés

## Fichiers Modifiés/Créés - Étape 5

```
Étape 5 - RBAC Complet:
├── backend/
│   ├── config/
│   │   ├── PermissionManager.php     (✨ NOUVEAU)
│   │   └── AuthHelper.php            (✏️ Amélioré)
│   ├── api/
│   │   └── index.php                 (✏️ Mises à jour includes)
│   ├── etape5-rbac.sql              (✨ NOUVEAU)
│   ├── test-etape5.html             (✨ NOUVEAU)
│   ├── ETAPE5-RBAC-GUIDE.md         (✨ NOUVEAU)
│   └── README.md                     (À mettre à jour)
└── public/
    └── api-client.js                 (À améliorer)
```

## Checklist Complete RBAC

- ✅ Tables permission et role_permission créées
- ✅ Permissions définies pour toutes les ressources
- ✅ Rôles avec permissions pré-configurés
- ✅ PermissionManager pour vérifier permissions
- ✅ AuthHelper amélioré avec requirePermission()
- 🟡 Endpoints mis à jour avec RBAC (À faire)
- 🟡 API de gestion permissions (À faire)
- 🟡 Dashboard admin (À faire - Étape 5.2)
- 🟡 Audit logging (À faire - Étape 5.1)

## Résumé

Vous avez un système RBAC **professionnel et scalable**:
- ✅ Permissions granulaires et flexibles
- ✅ Rôles facilement modifiables depuis la BD
- ✅ Système d'assignation dynamique
- ✅ Cache de permissions pour performance
- ✅ Extensible pour futurs rôles/permissions

Prochaine étape: **Étape 6 - Shopping Cart** ou **Étape 5.1 - Audit Logging**?
