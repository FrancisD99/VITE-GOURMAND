# 🔐 Étape 4: Protection des Endpoints avec JWT

## Vue d'ensemble

L'Étape 4 améliore la sécurité en protégeant tous les endpoints qui modifient les données (POST, PUT, DELETE) et certains endpoints sensibles (GET) avec la vérification des tokens JWT.

## Architecture de Sécurité

### Niveaux d'Accès

| Endpoint | GET | POST | PUT | DELETE | Notes |
|----------|-----|------|-----|--------|-------|
| **utilisateurs** | PUBLIC | PUBLIC | AUTH | ADMIN | Inscription libre, profil sécurisé |
| **menus** | PUBLIC | ADMIN | ADMIN | ADMIN | Menu visible, modification admin |
| **plats** | PUBLIC | ADMIN | ADMIN | ADMIN | Carte visible, gestion admin |
| **commandes** | AUTH | AUTH | AUTH | AUTH | Chacun voit ses commandes |
| **avis** | PUBLIC | AUTH | - | AUTH | Lecture libre, écriture sécurisée |
| **contact** | ADMIN | PUBLIC | - | ADMIN | Formulaire public, gestion admin |

### Légende
- **PUBLIC**: N'importe qui peut accéder
- **AUTH**: Require JWT token valide
- **ADMIN**: Require JWT token avec role_id = 1

## Classes et Fonctions

### AuthHelper.php

Nouvelle classe utilitaire pour l'authentification:

```php
class AuthHelper {
    // Vérifier JWT et retourner les données utilisateur
    public static function requireAuth();
    
    // Vérifier le rôle de l'utilisateur
    public static function requireRole($user, $required_role);
    
    // Vérifier l'ownership (user own la ressource ou est admin)
    public static function requireOwnershipOrAdmin($user, $resource_user_id);
    
    // Envoyer erreur JSON
    public static function sendError($message, $status_code);
    
    // Envoyer réponse succès JSON
    public static function sendSuccess($data, $status_code);
}
```

### Utilisation dans les Endpoints

**Exemple 1: Endpoint protégé (JWT requis)**
```php
case 'commandes':
    if ($method == 'POST') {
        try {
            $user = AuthHelper::requireAuth();
            // L'utilisateur est authentifié
            $input['utilisateur_id'] = $user['utilisateur_id'];
            // ... créer la commande
        } catch (Exception $e) {
            AuthHelper::sendError($e->getMessage(), 401);
        }
    }
    break;
```

**Exemple 2: Endpoint avec vérification de rôle (Admin)**
```php
case 'plats':
    if ($method == 'POST') {
        try {
            $user = AuthHelper::requireAuth();
            AuthHelper::requireRole($user, 1); // 1 = Admin
            // ... créer le plat
        } catch (Exception $e) {
            AuthHelper::sendError($e->getMessage(), 401);
        }
    }
    break;
```

**Exemple 3: Endpoint avec vérification d'ownership**
```php
case 'commandes':
    if ($method == 'PUT' && !empty($resource)) {
        try {
            $user = AuthHelper::requireAuth();
            $commande = $commandante->getById($resource);
            
            // Vérifier que l'utilisateur modifie sa propre commande
            if ($commande['utilisateur_id'] != $user['utilisateur_id'] && $user['role_id'] != 1) {
                AuthHelper::sendError('Forbidden: Not your resource', 403);
            }
            // ... mettre à jour la commande
        } catch (Exception $e) {
            AuthHelper::sendError($e->getMessage(), 401);
        }
    }
    break;
```

## Codes de Réponse HTTP

| Code | Sens | Exemple |
|------|------|---------|
| **200** | OK | GET réussi, POST réussi |
| **201** | Created | Ressource créée (POST) |
| **400** | Bad Request | Données invalides |
| **401** | Unauthorized | Token manquant/invalide |
| **403** | Forbidden | Insufficient permissions |
| **404** | Not Found | Ressource inexistante |
| **500** | Server Error | Erreur serveur |

## Endpoints Détaillés

### Utilisateurs

```
POST   /api/utilisateurs          Créer compte (PUBLIC)
├─ Body: { nom, prenom, email, telephone, adresse, password }
└─ Response: 201 { message: "Utilisateur créé..." }

GET    /api/utilisateurs/{id}     Récupérer utilisateur (PUBLIC)
├─ Response: 200 { utilisateur_id, nom, prenom, email, ... }
└─ Response: 404 { error: "Utilisateur non trouvé" }

GET    /api/utilisateurs          Lister utilisateurs (PUBLIC)
└─ Response: 200 [ { utilisateur_id, nom, prenom, ... } ]

PUT    /api/utilisateurs/{id}     Mettre à jour (AUTH - self or admin)
├─ Headers: Authorization: Bearer {token}
├─ Body: { nom, prenom, email, ... }
└─ Response: 200 { message: "Utilisateur mis à jour" }

DELETE /api/utilisateurs/{id}     Supprimer (ADMIN)
├─ Headers: Authorization: Bearer {token}
└─ Response: 200 { message: "Utilisateur supprimé" }
```

### Menus

```
POST   /api/menus                 Créer menu (ADMIN)
├─ Headers: Authorization: Bearer {token}
├─ Body: { libelle, description, prix, ... }
└─ Response: 201 { message: "Menu créé..." }

GET    /api/menus/{id}            Récupérer menu (PUBLIC)
└─ Response: 200 { menu_id, libelle, description, ... }

GET    /api/menus                 Lister menus (PUBLIC)
├─ Query: ?regime={regime_id} (optionnel)
└─ Response: 200 [ { menu_id, libelle, ... } ]

PUT    /api/menus/{id}            Mettre à jour (ADMIN)
├─ Headers: Authorization: Bearer {token}
├─ Body: { libelle, description, prix, ... }
└─ Response: 200 { message: "Menu mis à jour" }

DELETE /api/menus/{id}            Supprimer (ADMIN)
├─ Headers: Authorization: Bearer {token}
└─ Response: 200 { message: "Menu supprimé" }
```

### Plats

```
POST   /api/plats                 Créer plat (ADMIN)
├─ Headers: Authorization: Bearer {token}
├─ Body: { libelle, description, prix, regime_id, ... }
└─ Response: 201 { message: "Plat créé..." }

GET    /api/plats/{id}            Récupérer plat (PUBLIC)
├─ Response: 200 { plat_id, libelle, allergenes: [ ... ] }
└─ Response: 404 { error: "Plat non trouvé" }

GET    /api/plats                 Lister plats (PUBLIC)
├─ Query: ?regime_id={id} ou ?search={term}
└─ Response: 200 [ { plat_id, libelle, ... } ]

PUT    /api/plats/{id}            Mettre à jour (ADMIN)
├─ Headers: Authorization: Bearer {token}
├─ Body: { libelle, description, prix, ... }
└─ Response: 200 { message: "Plat mis à jour" }

DELETE /api/plats/{id}            Supprimer (ADMIN)
├─ Headers: Authorization: Bearer {token}
└─ Response: 200 { message: "Plat supprimé" }
```

### Commandes

```
POST   /api/commandes             Créer commande (AUTH)
├─ Headers: Authorization: Bearer {token}
├─ Body: { menu_id, date_commande, ... }
├─ Note: utilisateur_id ajouté automatiquement
└─ Response: 201 { message: "Commande créée..." }

GET    /api/commandes/{id}        Récupérer commande (AUTH - self/admin)
├─ Headers: Authorization: Bearer {token}
├─ Response: 200 { commande_id, utilisateur_id, menu_id, ... }
└─ Response: 403 { error: "Vous n'avez pas accès..." }

GET    /api/commandes             Lister commandes (AUTH - self/admin)
├─ Headers: Authorization: Bearer {token}
├─ Admin voit toutes, client voit siennes
└─ Response: 200 [ { commande_id, ... } ]

PUT    /api/commandes/{id}        Mettre à jour (AUTH - self/admin)
├─ Headers: Authorization: Bearer {token}
├─ Body: { date_commande, ... }
└─ Response: 200 { message: "Commande mise à jour" }

DELETE /api/commandes/{id}        Supprimer (AUTH - self/admin)
├─ Headers: Authorization: Bearer {token}
└─ Response: 200 { message: "Commande supprimée" }
```

### Avis

```
POST   /api/avis                  Créer avis (AUTH)
├─ Headers: Authorization: Bearer {token}
├─ Body: { plat_id, note, commentaire, ... }
├─ Note: utilisateur_id ajouté automatiquement
└─ Response: 201 { message: "Avis créé..." }

GET    /api/avis/{id}             Récupérer avis (PUBLIC)
└─ Response: 200 { avis_id, utilisateur_id, note, commentaire, ... }

GET    /api/avis                  Lister avis (PUBLIC)
└─ Response: 200 [ { avis_id, note, ... } ]

DELETE /api/avis/{id}             Supprimer (AUTH - self/admin)
├─ Headers: Authorization: Bearer {token}
└─ Response: 200 { message: "Avis supprimé" }
```

### Contact

```
POST   /api/contact               Envoyer message (PUBLIC)
├─ Body: { nom, email, sujet, message, ... }
└─ Response: 201 { message: "Message envoyé..." }

GET    /api/contact/{id}          Récupérer message (ADMIN)
├─ Headers: Authorization: Bearer {token}
└─ Response: 200 { contact_id, nom, email, ... }

GET    /api/contact               Lister messages (ADMIN)
├─ Headers: Authorization: Bearer {token}
└─ Response: 200 [ { contact_id, nom, ... } ]

DELETE /api/contact/{id}          Supprimer (ADMIN)
├─ Headers: Authorization: Bearer {token}
└─ Response: 200 { message: "Message supprimé" }
```

## Tester les Endpoints Protégés

### Avec cURL

**1. Créer un compte**
```bash
curl -X POST http://localhost:8000/api/utilisateurs \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean@test.com",
    "password": "SecurePass123!",
    "telephone": "0123456789",
    "adresse": "123 Rue Test"
  }'
```

**2. Se connecter et obtenir token**
```bash
TOKEN=$(curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"jean@test.com","password":"SecurePass123!"}' | jq -r '.token')

echo "Token: $TOKEN"
```

**3. Créer une commande (protégée)**
```bash
curl -X POST http://localhost:8000/api/commandes \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"menu_id":1,"date_commande":"2026-02-20"}'
```

**4. Essayer sans token (doit échouer avec 401)**
```bash
curl -X POST http://localhost:8000/api/commandes \
  -H "Content-Type: application/json" \
  -d '{"menu_id":1}'
```

**5. Essayer créer plat sans role admin (doit échouer avec 403)**
```bash
curl -X POST http://localhost:8000/api/plats \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"libelle":"Pizza","prix":12.99}'
```

### Avec test-etape4.html

Une interface web complète est fournie pour tester:
- Création/modification d'utilisateurs
- Opérations admin (créer/modifier plats)
- Vérification des permissionsOuvrir: `http://localhost:8000/test-etape4.html`

## Sécurité

### ✅ Implémenté en Étape 4
- Vérification JWT sur POSt/PUT/DELETE
- Vérification de rôle (admin)
- Vérification d'ownership (l'utilisateur ne peut modifier que ses données)
- Réponses d'erreur appropriées (401, 403)

### ⚠️ À Améliorer
- Ajouter getByUserId() au modèle Commandante pour filtrer les commandes par utilisateur
- Implémenter le caching des données utilisateur/rôles
- Ajouter rate limiting pour prévenir les attaques

## Prochaines Étapes

### Étape 5: RBAC Avancé
- Rôles supplémentaires (chef, serveur, etc.)
- Permissions par ressource
- Audit logging

### Étape 6: Shopping Cart
- Endpoint POST /api/cart
- Endpoint GET /api/cart/{user_id}
- Endpoint PUT /api/cart/{item_id}
- Stockage en base de données

### Étape 7: Checkout & Payments
- Endpoint POST /api/checkout
- Intégration Stripe/PayPal
- Confirmation email

## Dépannage

### 401 Unauthorized
- Vérifier que le token est dans le header Authorization: Bearer {token}
- Vérifier que le token n'a pas expiré (24h)
- Vérifier que la clé secrète dans JWTHandler.php est correcte

### 403 Forbidden
- Vous n'avez pas le rôle requis (admin)
- Vous essayez d'accéder aux données de quelqu'un d'autre

### Token Invalide
- S'assurer que le format est: Authorization: Bearer eyJ...
- Vérifier que les tirets et underscores du token ne sont pas modifiés

## Fichiers Modifiés/Créés - Étape 4

```
Étape 4 - API Protection & RBAC:
├── backend/
│   ├── config/
│   │   ├── AuthHelper.php              (✨ NOUVEAU)
│   │   └── JWTHandler.php              (Inchangé)
│   ├── api/
│   │   └── index.php                   (✏️ Modifié - ajout protections)
│   ├── test-etape4.html                (✨ NOUVEAU)
│   ├── ETAPE4-PROTECTION-GUIDE.md      (✨ NOUVEAU)
│   └── README.md                       (À mettre à jour)
└── public/
    └── api-client.js                   (À améliorer - wrappers pour endpoints protégés)
```

## Résumé

Vous avez maintenant:
- ✅ Protection JWT sur tous les endpoints critiques
- ✅ Vérification de rôle pour les opérations admin
- ✅ Vérification d'ownership pour les données personnelles
- ✅ Codes d'erreur HTTP appropriés (401, 403)
- ✅ AuthHelper pour centraliser la logique d'authentification

**Prochain objectif**: Tester tous les endpoints via le fichier test fourni et valider les cas d'erreur.
