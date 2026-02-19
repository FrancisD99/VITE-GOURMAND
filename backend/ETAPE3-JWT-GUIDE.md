# 🔐 Étape 3: JWT Authentication Guide

## Vue d'ensemble

L'Étape 3 implémente un système d'authentification basé sur JWT (JSON Web Tokens) permettant aux utilisateurs de se connecter de manière sécurisée et accéder aux endpoints protégés.

## Architecture

### 1. Backend (PHP)

#### JWTHandler.php
- **Localisation**: `backend/config/JWTHandler.php`
- **Responsabilités**:
  - Encoder les JWT (création de tokens)
  - Décoder les JWT (extraction du payload)
  - Valider les tokens (signature + expiration)
  - Extraire le token de l'en-tête Authorization

#### Endpoints d'Authentification
- **POST `/api/auth/login`** - Connexion et génération de token
  - Body: `{ "email": "user@example.com", "password": "SecurePass123!" }`
  - Response: `{ "token": "eyJ...", "user": { "utilisateur_id", "nom", "prenom", "email" } }`
  - Status: 201 (success) | 401 (auth failed) | 400 (validation error)

- **GET `/api/auth/me`** - Récupérer l'utilisateur courant (protégé)
  - Headers: `Authorization: Bearer {token}`
  - Response: `{ "utilisateur_id", "nom", "prenom", "email", "telephone", "adresse" }`
  - Status: 200 (success) | 401 (invalid token)

- **POST `/api/auth/logout`** - Déconnexion
  - En pratique: Le client supprime le token du localStorage
  - Le backend JWT est stateless (pas de session côté serveur)
  - Status: 200

### 2. Frontend (JavaScript/HTML)

#### api-client.js
Fonctions d'authentification centralisées:

```javascript
// Gestion du token
setAuthToken(token)              // Sauvegarde dans localStorage
getAuthToken()                   // Récupère le token
clearAuthToken()                 // Supprime le token (logout)
getAuthHeaders()                 // Retourne headers avec Authorization

// Authentification
login(email, password)           // POST /api/auth/login
logout()                         // POST /api/auth/logout + clearAuthToken()
getCurrentUser()                 // GET /api/auth/me (protégé)
isAuthenticated()                // Boolean: token existe?
```

#### login.html & main.js
- Formulaire de connexion avec validation
- Affichage du message de bienvenue
- Redirection vers index.html après succès
- Affichage d'erreurs détaillées

## Configuration de Sécurité

### ⚠️ IMPORTANT: Changer la clé secrète JWT

Par défaut, le secret est: `'your-secret-key-change-this'`

**Générer une clé sécurisée:**

```bash
# Windows (PowerShell)
$bytes = [System.Text.Encoding]::UTF8.GetBytes("")
$rng = [System.Security.Cryptography.RNGCryptoServiceProvider]::new()
$key = [System.Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($rng))

# ou utiliser openssl (si disponible)
openssl rand -base64 32

# ou sur Windows, générer avec PHP
php -r "echo bin2hex(random_bytes(32));"
```

**Mettre à jour JWTHandler.php:**

```php
private $secret = 'YOUR_GENERATED_SECRET_HERE';
```

### Paramètres JWT

- **Algorithme**: HS256 (HMAC-SHA256)
- **Expiration**: 86400 secondes (24 heures)
- **Claims standards**:
  - `iat` (issuedAt): Timestamp de création
  - `exp` (expiration): Timestamp d'expiration
  - `utilisateur_id`: ID de l'utilisateur authentifié

## Test de l'Authentification

### Option 1: Interface Web (Recommandé)

Ouvrir le fichier de test inclus:
```
http://localhost:8000/test-etape3.html
```

**Étapes de test:**
1. Enregistrer un nouvel utilisateur (ou utiliser un existant)
2. Se connecter avec email/mot de passe
3. Vérifier le token dans localStorage
4. Appeler l'endpoint protégé `/api/auth/me`
5. Afficher les informations du token JWT

### Option 2: cURL (Commande)

**Étape 1: Login**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"SecurePass123!"}'
```

**Réponse:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1dGlsaXNhdGV1cl9pZCI6MSwiZW1haWwiOiJ1c2VyQGV4YW1wbGUuY29tIiwiaWF0IjoxNjk3MDAwMDAwLCJleHAiOjE2OTcwODY0MDB9.signature",
  "user": {
    "utilisateur_id": 1,
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "user@example.com"
  }
}
```

**Étape 2: Utiliser le token**
```bash
TOKEN="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."

curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse:**
```json
{
  "utilisateur_id": 1,
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "user@example.com",
  "telephone": "0123456789",
  "adresse": "123 Rue Test"
}
```

## Flux d'Authentification

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │ 1. POST /api/auth/login
       │    { email, password }
       │
       ▼
┌─────────────────────────────────┐
│      Backend API Server         │
│                                 │
│ 1. Valider email/password       │
│ 2. Créer JWT Token              │
│ 3. Retourner { token, user }    │
└──────┬──────────────────────────┘
       │ 2. Response + Token
       │
       ▼
┌─────────────────────────────────┐
│        Client Browser           │
│                                 │
│ 1. Sauvegarde token en          │
│    localStorage.auth_token      │
│ 2. Redirige vers index.html     │
└─────────────────────────────────┘
       │
       │ 3. GET /api/auth/me
       │    Headers: Authorization: Bearer {token}
       │
       ▼
┌─────────────────────────────────┐
│      Backend API Server         │
│                                 │
│ 1. Extraire token de header     │
│ 2. Valider signature            │
│ 3. Vérifier expiration          │
│ 4. Extraire utilisateur_id      │
│ 5. Retourner données user       │
└──────┬──────────────────────────┘
       │ 4. Réponse user + 200 OK
       │
       ▼
┌─────────────────────────────────┐
│     Client Browser              │
│                                 │
│ Met à jour l'UI avec données    │
│ utilisateur                     │
└─────────────────────────────────┘
```

## Sécurité

### ✅ Implémenté
- Mot de passe hashé avec bcrypt (password_hash)
- Token signé avec HMAC-SHA256
- Validation de signature côté serveur
- Vérification d'expiration
- Token stocké sécurisé dans localStorage (accessible via JavaScript uniquement sur HTTPS en production)
- Prepared statements (PDO) contre SQL injection
- CORS headers configurés

### ⚠️ À Implémenter pour Production
- HTTPS obligatoire (actuellement HTTP en dev)
- HttpOnly cookies au lieu de localStorage (résiste aux XSS)
- Refresh tokens (pour renouveler l'accès sans relog)
- Rate limiting sur /api/auth/login
- CSRF protection si formulaires HTML
- Revoking tokens (liste noire)
- Roles et permissions (RBAC)

## Prochaines Étapes

### Étape 3.1: Protéger les Endpoints
```php
// Au début de chaque endpoint qui doit être protégé:
$token = JWTHandler::getTokenFromRequest();
if (!$token || !$jwt->isValid($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
$user = $jwt->decode($token);
```

### Étape 3.2: Implémenter RBAC
```php
// Vérifier le rôle de l'utilisateur
$user = $jwt->decode($token);
if ($user['role_id'] != 1) { // 1 = Admin
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit();
}
```

### Étape 3.3: Frontend - Gestion de l'Auth State
```javascript
// main.js
import { isAuthenticated, getCurrentUser } from './api-client.js';

if (isAuthenticated()) {
    const user = await getCurrentUser();
    console.log('Connecté en tant que:', user.prenom);
    // Afficher menu utilisateur
} else {
    // Rediriger vers login.html
}
```

## Dépannage

### Token invalide / 401 Unauthorized
- Vérifier que le token est correctement extrait de l'en-tête Authorization
- Vérifier que la clé secrète dans JWTHandler.php correspond à celle utilisée pour signer
- Vérifier l'expiration du token (24h par défaut)

### CORS errors
- Assurez-vous que les headers CORS sont présents dans api/index.php
- Vérifier que le domaine frontend est autorisé

### Token ne persiste pas après rechargement
- Vérifier que localStorage fonctionne (DevTools > Application > localStorage)
- JavaScript peut être désactivé
- L'origine du site peut ne pas permettre localStorage

## Fichiers Impliqués

```
Étape 3 - JWT Authentication:
├── backend/
│   ├── config/
│   │   └── JWTHandler.php          (✨ NOUVEAU)
│   ├── api/
│   │   └── index.php               (Modifié)
│   ├── models/
│   │   └── Utilisateur.php         (Modifié)
│   ├── test-etape3.html            (✨ NOUVEAU)
│   ├── ETAPE3-JWT-GUIDE.md         (✨ NOUVEAU)
│   └── README.md                   (Modifié)
└── public/
    ├── api-client.js               (Modifié)
    ├── main.js                     (Modifié)
    ├── login.html                  (Modifié)
    └── index.html
```

## Résumé

Vous avez maintenant un système d'authentification JWT complet:
- ✅ Backend: Génération et validation de tokens
- ✅ API: Endpoints de login et utilisateur courant
- ✅ Frontend: Gestion des tokens et session utilisateur
- ✅ Test: Interface web pour valider tout le flux

**Prochaine action**: Tester le flux complet via `http://localhost:8000/test-etape3.html`
