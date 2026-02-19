# 🚀 Guide de Démarrage Complet

## Prérequis
- ✅ Node.js et npm
- ✅ PHP 8.0+
- ✅ MySQL 5.7+

---

## 🔧 Configuration Initiale (une fois)

### 1. Créer la base de données

```bash
mysql -u root -p
CREATE DATABASE vite_gourmand;
USE vite_gourmand;
SOURCE backend/database.sql;
SOURCE backend/etape5.1-audit.sql;
EXIT;
```

### 2. Installer les dépendances

```bash
npm install
```

### 3. Configurer la connexion BD

Éditer `backend/config/Database.php`:

```php
private $host = 'localhost';
private $db = 'vite_gourmand';
private $user = 'root';
private $password = 'votre_mot_de_passe';  // ← Modifier si besoin
```

---

## ✅ Lancer l'application (tous les jours)

### Terminal 1: Backend (API PHP)

```bash
cd backend
php -S localhost:8000 router.php
```

⚠️ **Important**: 
- Ne pas oublier `router.php` !
- L'API écoute sur `http://localhost:8000`
- Les endpoints sont accessibles via `/api/...` (sans .php)

### Terminal 2: Frontend (Vite)

```bash
npm run dev
```

Le site s'ouvre automatiquement à: **http://localhost:3000**

---

## 🧪 Tester l'API Audit Logging

### Via l'interface web

1. Ouvrir: **http://localhost:3000/test-etape5.1.html**
2. Email: `admin@vite-gourmand.test`
3. Mot de passe: `Admin@123`
4. Cliquer "Se connecter"
5. Consulter les logs, statistiques, exporter en CSV

### Via curl

```bash
# Récupérer un token
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@vite-gourmand.test","password":"Admin@123"}' \
  | grep -o '"token":"[^"]*' | cut -d'"' -f4)

# Consulter les logs
curl -X GET "http://localhost:8000/api/audit-logs" \
  -H "Authorization: Bearer $TOKEN"

# Voir les statistiques
curl -X GET "http://localhost:8000/api/audit-logs?stats=1" \
  -H "Authorization: Bearer $TOKEN"

# Exporter en CSV
curl -X GET "http://localhost:8000/api/audit-logs?export=csv" \
  -H "Authorization: Bearer $TOKEN" \
  > audit-logs.csv
```

---

## 📊 Architecture

```
http://localhost:3000 (Frontend - Vite)
           ↓ proxy /api requests
http://localhost:8000 (Backend - PHP)
           ↓
MySQL (localhost:3306)
```

---

## 🆘 Problèmes courants

### "Endpoint non trouvé"
```bash
# ✅ Bon
cd backend && php -S localhost:8000 router.php

# ❌ Mauvais
php -S localhost:8000          # Manque router.php
php -S localhost:8000 -t .     # Mauvais dossier
```

### "Connection refused"
- ✅ Vérifier que MySQL tourne
- ✅ Vérifier username/password dans Database.php

### "Table doesn't exist"
- ✅ Vous avez importé `etape5.1-audit.sql` ?

### "Permission denied" sur /api/audit-logs
- ✅ Vous êtes connecté avec un compte admin ?
- ✅ Le token JWT n'a pas expiré ?

### CORS errors
- ✅ Le proxy Vite est configuré ? (vite.config.js)
- ✅ Les headers Access-Control sont présents ? (api/index.php)

---

## 📁 Fichiers importants

| Fichier | Rôle |
|---------|------|
| `backend/config/AuditLogger.php` | Classe de logging |
| `backend/etape5.1-audit.sql` | Schéma BD audit |
| `backend/api/index.php` | Router API principal |
| `test-etape5.1.html` | Interface de test |
| `vite.config.js` | Config frontend (proxy) |

---

## 📚 Documentation

- [ÉTAPE5.1-AUDIT-GUIDE.md](ETAPE5.1-AUDIT-GUIDE.md) - Documentation complète
- [ÉTAPE5.1-INSTALLATION.md](ETAPE5.1-INSTALLATION.md) - Installation détaillée
- [ÉTAPE5.1-QUICK-REFERENCE.md](ETAPE5.1-QUICK-REFERENCE.md) - Référence rapide

---

## 🎯 Résumé des endpoints

| Endpoint | Méthode | Authentification | Description |
|----------|---------|------------------|-------------|
| `/api/auth/login` | POST | Non | Se connecter |
| `/api/audit-logs` | GET | Oui (admin) | Consulter les logs |
| `/api/audit-logs?stats=1` | GET | Oui (admin) | Statistiques |
| `/api/audit-logs?export=csv` | GET | Oui (admin) | Export CSV |
| `/api/plats` | GET/POST | POST=Admin | Consulter/créer plats |
| `/api/menus` | GET/POST | POST=Admin | Consulter/créer menus |
| `/api/commandes` | GET/POST | Oui (user) | Consulter/créer commandes |
| `/api/avis` | GET/POST | POST=User | Consulter/créer avis |

---

**Bon développement! 🚀**
