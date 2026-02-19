# 🍽️ DÉMARRAGE RAPIDE - Vite & Gourmand

## Pour lancer le site web en local

### 1️⃣ Ouvrir Terminal/PowerShell

Dans VS Code:
- Appuyez sur **Ctrl + `** (backtick)
- Ou allez dans Terminal > Nouveau Terminal

### 2️⃣ Installer les dépendances

Exécutez cette commande:
```powershell
npm install
```

Cela téléchargera tous les packages nécessaires (Vite, etc.)

### 3️⃣ Démarrer le serveur local

```powershell
npm run dev
```

Le site s'ouvrira automatiquement dans votre navigateur à:
**http://localhost:3000**

### 4️⃣ Navigation du site

- **Accueil**: Hero section avec image du plat
- **À propos**: Description et caractéristiques
- **Menu**: Voir et filtrer les plats
- **Contact**: Formulaire de contact
- **Connexion**: Page de login
- **Inscription**: Page d'enregistrement
- **Footer**: Horaires et infos

## 📱 Le site est 100% responsif

✅ Desktop (1200px+)
✅ Tablette (768px - 1200px)
✅ Mobile (< 768px)

---

## 🔧 BACKEND - Développement API

Le backend est en PHP avec MySQL. Vous le configurez par **étapes progressives**:

### Prérequis
- PHP 8.0+ (serveur intégré `php -S`)
- MySQL 5.7+ ou MariaDB
- Un client MySQL (MySQL Workbench, HeidiSQL, cmd, etc.)

### ⚡ Démarrage rapide du Backend

#### 1. Démarrer le serveur PHP
```powershell
cd backend
php -S localhost:8000 router.php
```

⚠️ **Important**: Utiliser `router.php` pour que les URLs d'API fonctionnent!

#### 2. Créer la base de données MySQL
```bash
mysql -u root -p
CREATE DATABASE vite_gourmand;
USE vite_gourmand;

# Importer le schéma initial
SOURCE database.sql;

# Puis ajouter progressivement les étapes
SOURCE etape5.1-audit.sql;    # Audit logging (dernière étape)
```

#### 3. Configurer la connexion BD
Éditer `backend/config/Database.php`:

```php
private $host = 'localhost';
private $db = 'vite_gourmand';
private $user = 'root';
private $password = 'votre_mot_de_passe';
```

#### 4. Tester les endpoints
Ouvrir dans votre navigateur:
- 🧪 **`test-etape5.1.html`** - Audit Logging (dernière étape) ✅

### 📚 Les Étapes du Développement

| Étape | Sujet | Test | Status |
|-------|-------|------|--------|
| Base | Modèles CRUD | `test.html` | ✅ |
| 3 | Authentification JWT | `test-etape3.html` | ✅ |
| 4 | Protection d'endpoints | `test-etape4.html` | ✅ |
| 5 | RBAC Avancé | `test-etape5.html` | ✅ |
| **5.1** | **Audit Logging** | **`test-etape5.1.html`** | **✅** |

### 📖 Documentation Étape 5.1 (Audit Logging)

- 📘 **Guide complet**: `ETAPE5.1-AUDIT-GUIDE.md`
- 📋 **Installation**: `ETAPE5.1-INSTALLATION.md`
- 🧪 **Test interactif**: `test-etape5.1.html`

**Fonctionnalités:**
- ✅ Journalisation d'audit complète
- ✅ Filtrage & recherche de logs
- ✅ Statistiques d'accès
- ✅ Export CSV
- ✅ Détection IP (proxy-aware)
- ✅ Rétention configurable

---

## 🎨 Couleurs principales

- Jaune: #FCD34D
- Noir: #000000
- Blanc: #FFFFFF

## 💻 Fichiers importants

| Fichier | Description |
|---------|-------------|
| `index.html` | Structure HTML complète |
| `style.css` | Tous les styles CSS |
| `main.js` | Interactivité JavaScript |
| `vite.config.js` | Configuration du serveur |

## 🚀 Pour la production

Pour créer une version finale à déployer:

```powershell
npm run build
```

Cela créera un dossier `dist/` optimisé prêt à être hébergé.

## ⚠️ Troubleshooting

**Erreur "can't find npm"**
→ Installer Node.js depuis nodejs.org

**Port 3000 déjà utilisé**
→ Modifier le port dans vite.config.js

**Les styles ne s'affichent pas**
→ Attendre le rechargement du navigateur (Ctrl+Shift+R)

## 📞 Contact & Horaires

- **Email**: hello@reallyreatsite.com
- **Tél**: (123) 456 7890
- **Lun-Ven**: 09h-18h
- **Samedi**: 10h-16h
- **Dimanche**: Fermé

---

Bon développement! 🚀
