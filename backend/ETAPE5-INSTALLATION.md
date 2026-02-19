# 🚀 Installation Étape 5 - Instructions

## Prérequis Vérifiés
- ✅ PHP 7.4+ en cours d'exécution
- ✅ MySQL 5.7+ accessible
- ✅ Base de données `vite_gourmand` créée
- ✅ Backend API fonctionnelle
- ✅ Étapes 1-4 complétées

## 📋 Étapes d'Installation

### Étape 1: Exécuter le Script SQL RBAC

Vous devez exécuter le fichier **`etape5-rbac.sql`** pour créer les tables de permissions.

#### Option A: Depuis PowerShell (Windows)

```powershell
# Naviguez au dossier backend
cd c:\Users\adianzinga\Documents\GitHub\VITE-GOURMAND\backend

# Exécutez le script
$env:MYSQL_PWD=""
mysql -u root vite_gourmand < etape5-rbac.sql

# Vérifier que c'est OK
mysql -u root vite_gourmand -e "SELECT COUNT(*) as permissions FROM permission;"
```

#### Option B: Depuis CMD (Windows)

```cmd
cd c:\Users\adianzinga\Documents\GitHub\VITE-GOURMAND\backend
mysql -u root vite_gourmand < etape5-rbac.sql
```

#### Option C: Depuis MySQL Workbench

1. Ouvrez MySQL Workbench
2. Connectez-vous à votre serveur MySQL
3. Ouvrez le fichier `etape5-rbac.sql`
4. Exécutez le script complet (Ctrl+Shift+Enter)

#### Option D: Depuis un Terminal Linux/Mac

```bash
cd ~/GitHub/VITE-GOURMAND/backend
mysql -u root vite_gourmand < etape5-rbac.sql
```

### Étape 2: Vérifier l'Installation

Une fois le script exécuté, vérifiez que tout s'est bien passé:

```bash
# 1. Vérifier les tables créées
mysql -u root vite_gourmand -e "
  SHOW TABLES LIKE 'permission%';
  SHOW TABLES LIKE 'role_permission%';
"

# 2. Vérifier les permissions
mysql -u root vite_gourmand -e "SELECT COUNT(*) as count FROM permission;"
# Résultat attendu: 50+ permissions

# 3. Vérifier les rôles
mysql -u root vite_gourmand -e "SELECT * FROM poste;"
# Résultat attendu: admin, cliente, chef, serveur, modérateur

# 4. Vérifier les assignations
mysql -u root vite_gourmand -e "SELECT COUNT(*) as count FROM role_permission;"
# Résultat attendu: 150+ assignations
```

### Étape 3: Créer les Comptes de Test

Vous pouvez créer les comptes de test via l'API:

```bash
# Admin
curl -X POST http://localhost:8000/api/utilisateurs \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Admin",
    "prenom": "Test",
    "email": "admin@test.com",
    "password": "AdminPass123!",
    "telephone": "0123456789",
    "adresse": "123 Rue Test"
  }'

# Chef
curl -X POST http://localhost:8000/api/utilisateurs \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Chef",
    "prenom": "Test",
    "email": "chef@test.com",
    "password": "ChefPass123!",
    "telephone": "0123456789",
    "adresse": "123 Rue Test"
  }'

# Client
curl -X POST http://localhost:8000/api/utilisateurs \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Client",
    "prenom": "Test",
    "email": "client@test.com",
    "password": "ClientPass123!",
    "telephone": "0123456789",
    "adresse": "123 Rue Test"
  }'
```

Ou utilisez le bouton "Créer Comptes de Test" dans `test-etape5.html`

### Étape 4: Tester le RBAC

Ouvrez le fichier de test dans votre navigateur:

```
http://localhost:8000/test-etape5.html
```

**Tests à effectuer:**
1. ✅ Se connecter avec différents rôles
2. ✅ Afficher les permissions du token
3. ✅ Tester créer plat (chef OK, client forbidden)
4. ✅ Tester créer commande (client OK)
5. ✅ Vérifier les restrictions

### Étape 5: Mettre à Jour les Endpoints (Optionnel pour cette étape)

Les endpoints continuent de fonctionner avec `AuthHelper::requireRole()` de l'étape 4.

Pour utiliser le nouveau système de permissions:

**Avant (Étape 4):**
```php
AuthHelper::requireRole($user, 1); // Admin uniquement
```

**Après (Étape 5):**
```php
AuthHelper::requirePermission($user, 'plats.create', $db);
```

Exemple d'endpoint mis à jour:
```php
case 'plats':
    if ($method == 'POST') {
        try {
            $user = AuthHelper::requireAuth();
            AuthHelper::requirePermission($user, 'plats.create', $db);
            
            // Créer le plat
            if ($plat->create($input)) {
                http_response_code(201);
                echo json_encode(['message' => 'Plat créé']);
            }
        } catch (Exception $e) {
            AuthHelper::sendError($e->getMessage(), 403);
        }
    }
    break;
```

## 🐛 Dépannage

### Erreur: "permission table doesn't exist"

**Cause**: Le script SQL n'a pas été exécuté

**Solution**:
```bash
mysql -u root vite_gourmand < etape5-rbac.sql
```

### Erreur: "Access denied for user 'root'@'localhost'"

**Solution**: Spécifiez le mot de passe si nécessaire:
```bash
mysql -u root -p vite_gourmand < etape5-rbac.sql
# Entrez le mot de passe quand demandé
```

### Erreur: "Unknown database 'vite_gourmand'"

**Solution**: La base de données n'existe pas. Créez-la d'abord:
```bash
mysql -u root
# Dans MySQL:
CREATE DATABASE vite_gourmand;
USE vite_gourmand;
# Puis exécutez le script principal d'abord:
source database.sql;
# Puis l'étape 5:
source etape5-rbac.sql;
```

### Le fichier PermissionManager.php n'est pas trouvé

**Solution**: Vérifiez que le fichier existe:
```bash
# Depuis PowerShell
Test-Path "c:\Users\adianzinga\Documents\GitHub\VITE-GOURMAND\backend\config\PermissionManager.php"

# Résultat: True (sinon, le fichier n'a pas été créé correctement)
```

## ✅ Checklist Post-Installation

- [ ] Script SQL exécuté sans erreurs
- [ ] Tableaux `permission` et `role_permission` créées
- [ ] 5 rôles présents (admin, chef, serveur, modérateur, cliente)
- [ ] 50+ permissions définies
- [ ] PermissionManager.php présent
- [ ] AuthHelper.php contient `requirePermission()`
- [ ] test-etape5.html accessible et fonctionnel
- [ ] Comptes de test créés
- [ ] Tests RBAC passent

## 📊 Structure BDD Après Installation

```
vite_gourmand
├── poste (5 rôles)
│   ├── admin
│   ├── cliente
│   ├── chef
│   ├── serveur
│   └── modérateur
│
├── permission (50+ permissions)
│   ├── users.*
│   ├── plats.*
│   ├── menus.*
│   ├── commandes.*
│   ├── avis.*
│   └── contact.*
│
└── role_permission (assignations)
    ├── admin → toutes permissions
    ├── chef → permissions cuisine
    ├── serveur → permissions service
    ├── modérateur → permissions modération
    └── cliente → permissions client
```

## 🚀 Prochaines Étapes

1. **Tester le RBAC** via `test-etape5.html`
2. **Mettre à jour les endpoints** pour utiliser les permissions
3. **Implémenter le dashboard admin** pour gérer les permissions
4. **Ajouter l'audit logging** pour tracer les actions

## Fichiers Importants

- `etape5-rbac.sql` - Script créant les tables et permissions
- `PermissionManager.php` - Classe pour vérifier les permissions
- `AuthHelper.php` - Classe améliorée avec `requirePermission()`
- `test-etape5.html` - Interface de test RBAC
- `ETAPE5-RBAC-GUIDE.md` - Documentation complète

## Support

En cas de problème:
1. Vérifiez que MySQL est en cours d'exécution
2. Vérifiez que la base de données `vite_gourmand` existe
3. Consultez les logs PHP: `error_log = php_errors.log`
4. Vérifiez les permissions fichier du script SQL
5. Réessayez la même commande MySQL in terminal après redémarrage

Bon courage! 🎯
