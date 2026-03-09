# 🚀 COMMANDES POUR DÉMARRER LE PROJET

> ⚠️ **ATTENTION:** Vous utilisez **PowerShell**  
> Syntax: `;` (point-virgule) au lieu de `&&`

## ⚡ Version Rapide (2 lignes)

```powershell
# Terminal 1 (PHP Backend) - Syntaxe PowerShell
cd backend; php -S localhost:8000

# Terminal 2 (Vite Frontend)
npm run dev
```

Puis ouvrir: **http://localhost:5173/test-rbac.html**

---

## � Important: Syntaxe PowerShell vs Bash

Vous utilisez **PowerShell** sur Windows.  
Il y a une différence dans les séparateurs de commandes:

| Opération | PowerShell | Bash/Git Bash | CMD |
|-----------|-----------|---|---|
| Enchainer 2 commandes | `;` | `&&` | `&` |
| Exemple | `cd foldeur; commande` | `cd folder && commande` | `cd folder & commande` |

**ERREUR courante:**
```powershell
❌ cd backend && php -S localhost:8000  # Ne fonctionne PAS
✅ cd backend; php -S localhost:8000   # OK - Correct pour PowerShell
```

---

### 1️⃣ Se positionner dans le dossier du projet

```bash
cd c:\Users\adianzinga\Documents\GitHub\VITE-GOURMAND
```

### 2️⃣ Démarrer le serveur PHP (Backend sur port 8000)

Ouvrir un **nouveau Terminal PowerShell** et exécuter:

```powershell
cd backend
php -S localhost:8000
```

Ou en une ligne:
```powershell
cd backend; php -S localhost:8000
```

**Résultat attendu:**
```
Development Server (http://localhost:8000) started
```

> ⚠️ **NE PAS FERMER CE TERMINAL** - Laissez le serveur tourner

### 3️⃣ Démarrer le serveur Vite (Frontend sur port 5173)

Ouvrir un **deuxième Terminal PowerShell** et exécuter:

```powershell
npm run dev
```

**Résultat attendu:**
```
 VITE v5.x.x  ready in xxx ms

 ➜  Local:   http://localhost:5173/
```

> ⚠️ **NE PAS FERMER CE TERMINAL** - Laissez le serveur tourner

### 4️⃣ Accéder au projet

Une fois les 2 serveurs lancés, ouvrir votre navigateur:

```
http://localhost:5173/test-rbac.html
```

---

## 🔍 Dépannage Rapide

### Le serveur PHP ne démarre pas

```powershell
# Vérifier que PHP est installé
php -v

# Vérifier le port 8000 est libre
netstat -ano | findstr :8000

# Si le port est occupé, utiliser un autre port
php -S localhost:9000
```

### Vite ne démarre pas

```bash
# Vérifier que npm est installé
npm -v

# Réinstaller les dépendances
npm install

# Puis relancer
npm run dev
```

### Ma base de données MySQL ne fonctionne pas

```bash
# Vérifier que MySQL tourne
# Tester une connexion simple
mysql -u root -proot -e "SELECT 1;"

# Si erreur, démarrer MySQL (dépend de votre installation)
# Généralement: Services Windows → MySQL
```

---

## 📊 Vérifications

### ✅ Vérifier que tout fonctionne

**Serveurs actifs:**
- PHP: http://localhost:8000
- Vite: http://localhost:5173
- MySQL: mysql -u root -proot

**Interface accessible:**
- http://localhost:5173/test-rbac.html ✅

**Tests automatisés:**
- http://localhost:5173/test-integration.html ✅

---

## 🎯 Résumé des 3 Serveurs

| Serveur | Port | Commande | Statut |
|---------|------|----------|--------|
| PHP Backend | 8000 | `cd backend && php -S localhost:8000` | 🟢 Doit tourner |
| Vite Frontend | 5173 | `npm run dev` | 🟢 Doit tourner |
| MySQL | 3306 | Lancé séparément | 🟢 Doit tourner |

---

## 💾 Configuration Actuelle

```
Project:  VITE-GOURMAND
Location: c:\Users\adianzinga\Documents\GitHub\VITE-GOURMAND
Backend:  php/MySQL
Frontend: Vite/HTML/JS

Interface:  http://localhost:5173/test-rbac.html
API:        http://localhost:8000/api/
Dashboard:  http://localhost:5173/admin-dashboard.html
```

---

## 🎓 Prêt?

1. **Démarrer les 2 serveurs** (php + npm)
2. **Ouvrir** http://localhost:5173/test-rbac.html
3. **Sélectionner un utilisateur** (Admin, Cliente, Chef, etc)
4. **Explorer le dashboard**

**C'est parti! 🚀**
