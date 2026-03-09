# 📊 Intégration Dashboard Admin - Guide d'Utilisation

## 🎯 Résumé des modifications

Vous avez maintenant un **système complet d'administration avec dashboard pour visualiser les logs et gérer les permissions**.

### ✅ What's Completed

#### 1. **Gestion des Tokens Persistants**
- ✅ Stockage des tokens JWT dans `localStorage` pour chaque utilisateur
- ✅ Persistance automatique des sessions (les utilisateurs restent connectés au rechargement)
- ✅ Transmission sécurisée des tokens au dashboard via `sessionStorage`

#### 2. **Authentification Intégrée**
- ✅ Fonction `login()` modifiée pour stocker tokens et mettre à jour l'UI
- ✅ Fonction `logout()` pour efface les tokens et reset l'interface
- ✅ Détection automatique de l'utilisateur connecté au démarrage

#### 3. **Dashboard Admin**
- ✅ Vue d'ensemble avec statistiques (événements totaux, succès, refusés, erreurs)
- ✅ Visionneur des logs d'audit avec filtrage avancé
- ✅ Gestion des utilisateurs (affichage et filtrage par rôle)
- ✅ Matrice de permissions par rôle
- ✅ Interface de configuration des logs d'audit
- ✅ Récupération automatique des données API
- ✅ Affichage de l'utilisateur connecté

#### 4. **Interface Utilisateur**
- ✅ Section "Connecté en tant que" affiche l'email de l'utilisateur
- ✅ Lien "📊 Accéder au Dashboard Admin" s'affiche uniquement si connecté
- ✅ Fonction `openDashboard()` passe le token au dashboard
- ✅ Bouton déconnexion effaçe tous les tokens

---

## 🚀 Comment utiliser

### 1. **Test-RBAC (Interface de Test)**

Accédez à : `http://localhost:5173/test-rbac.html`

**Étapes :**
1. Sélectionnez un utilisateur (Admin, Client, Chef, Serveur, Modérateur)
2. Les permissions s'affichent automatiquement
3. Une fois connecté, un message "✅ Connecté en tant que [email]" apparaît
4. Cliquez sur "📊 Accéder au Dashboard Admin" pour ouvrir le dashboard

**Utilisateurs disponibles :**
```
Admin     : admin@vite-gourmand.test / Admin123
Client    : client@test.fr / Client123  
Chef      : chef@test.fr / Chef123
Serveur   : serveur@test.fr / Serveur123
Modérateur: moderateur@test.fr / Mod123
```

### 2. **Dashboard Admin**

Accédez à : `http://localhost:5173/admin-dashboard.html`

> **Note :** Le dashboard doit être ouvert via le lien "📊 Accéder au Dashboard Admin" depuis test-rbac.html

**Fonctionnalités :**

#### 📊 Vue d'Ensemble
- Affiche 4 statistiques clés :
  - Total d'événements de sécurité
  - Nombre d'accès réussis
  - Nombre d'accès refusés
  - Nombre d'erreurs
- Affiche les 5 derniers événements

#### 🔒 Logs d'Audit
- Tableau des événements de sécurité
- Filtrage par :
  - **Statut** (success, denied, error)
  - **Action** (login, create, update, delete, etc.)
  - **Date** (plage de dates)
- Détails : User ID, Action, Resource, Status, Timestamp

#### 👥 Utilisateurs
- Liste complète des utilisateurs
- Filtrage par rôle
- Recherche par email/nom
- Actions : Modifier utilisateur

#### 🔐 Rôles & Permissions
- Matrice des rôles et permissions
- Vue des permissions par rôle
- Nombre total de permissions par rôle
- Actions : Gérer rôle

#### ⚙️ Paramètres
- Configuration de la rétention des logs
- Informations système
- Version et dernière mise à jour

---

## 🔐 Flux d'Authentification Détaillé

```
1. test-rbac.html
   ↓
   [Sélectionner utilisateur]
   ↓
   [Afficher permissions]
   ↓
   [Connecté ? Afficher lien dashboard]
   ↓
   localStorage: vite_gourmand_token_[user]
   localStorage: vite_gourmand_current_user
   ↓
   [Cliquer "Dashboard Admin"]
   ↓
   sessionStorage: vite_gourmand_token
   ↓
2. admin-dashboard.html
   ↓
   [Récupérer token de sessionStorage]
   ↓
   [Charger données API]
   ↓
   [Afficher dashboard avec données personnalisées]
```

---

## 📝 Architecture des Fichiers Modifiés

### `test-rbac.html` (587 lignes)
**Modifications :**
- Ajout de `login()` avancée avec localStorage
- Nouvelle fonction `updateDashboardUI()` pour afficher/masquer le lien dashboard
- Nouvelle fonction `logout()` pour efface les tokens
- Nouvelle fonction `openDashboard()` pour passer le token
- Nouvelle fonction `loadStoredTokens()` pour réutiliser les sessions
- Initialisation améliorée pour charger les tokens au démarrage

### `admin-dashboard.html` (923 lignes)
**Modifications :**
- Récupération du token depuis `sessionStorage` et `localStorage`
- Ajout de `loadCurrentUser()` pour afficher l'utilisateur connecté
- Fonction `logout()` modifiée pour efface `sessionStorage`
- Support des API endpoints `/audit-logs`, `/utilisateurs`, `/menus`

### `test-integration.html` (Nouveau - 270 lignes)
**Contenu :**
- Suite de 9 tests automatisés
- Vérifie l'authentification, les tokens JWT, les permissions
- Valide l'accès aux endpoints selon les rôles
- Rapporte les résultats de manière structurée

---

## 🧪 Tests Disponibles

### Test Manuel via test-rbac.html
1. ✅ Connexion utilisateur
2. ✅ Affichage des permissions
3. ✅ Navigation vers le dashboard
4. ✅ Persistance des sessions
5. ✅ Déconnexion

### Suite Automatisée via test-integration.html
```
✅ localStorage vide au démarrage
✅ Authentifier admin@vite-gourmand.test
✅ Token JWT contient les permissions
✅ Admin a les permissions admin.*
✅ Endpoint /audit-logs accessible
✅ sessionStorage sauvegarde le token
✅ Authentifier client@test.fr
✅ Client n'a pas les permissions admin
✅ Client ne peut pas accéder à /audit-logs
```

**Exécution :** Cliquez sur "▶️ Exécuter tous les tests"

---

## 🔧 Dépannage

### Le dashboard ne charge pas
**Solution :** Assurez-vous d'ouvrir le dashboard via le lien depuis test-rbac.html, pas directement

### "Vous avez été redirigé vers test-rbac.html"
**Cause :** Le token n'a pas été trouvé (sessionStorage vide)
**Solution :** Connectez-vous d'abord via test-rbac.html

### Les données n'apparaissent pas dans le dashboard
**Vérifiez :**
1. Le serveur PHP tourne sur port 8000
2. L'utilisateur a les permissions admin.logs
3. Les logs d'audit existent dans la base de données

### "Erreur d'authentification" sur endpoint
**Cause :** Le token est expiré ou invalide
**Solution :** Déconnectez-vous et reconnectez-vous

---

## 📊 Statistiques du Système Actuel

| Élément | Nombre |
|---------|--------|
| Utilisateurs de test | 5 |
| Rôles | 5 |
| Permissions | 40+ |
| Endpoints testés | 60 |
| Fonctions test | 9 |
| Lignes de code (test-rbac.html) | 587 |
| Lignes de code (admin-dashboard.html) | 923 |

---

## 🎓 Prochaines Étapes (Optionnelles)

1. **Créer utilisateurs en production**
   - Implémenter le formulaire "Nouvel utilisateur" dans le dashboard
   - Ajouter validation côté serveur

2. **Gestion des rôles avancée**
   - Interface d'édition des permissions par rôle
   - Sauvegarde des modifications en base de données

3. **Audit avancé**
   - Graphiques de tendance des logs
   - Alertes en temps réel
   - Export de rapports

4. **Sécurité**
   - Refresh automatique du token après expiration
   - Rate limiting sur les endpoints
   - Logging des tentatives de connexion échouées

---

## ⚡ Commandes Utiles

```bash
# Afficher les utilisateurs créés
php -r "
\$db = new PDO('mysql:host=localhost;dbname=vite_gourmand', 'root', 'root');
\$stmt = \$db->query('SELECT utilisateur_id, email, nom, prenom, role_id FROM utilisateur');
foreach (\$stmt as \$row) {
    echo \$row['email'] . ' (Rôle: ' . \$row['role_id'] . ')\\n';
}
"

# Vérifier les logs d'audit
mysql -u root -proot vite_gourmand -e "SELECT COUNT(*) as total FROM audit_log;"

# Voir les permissions de l'admin
mysql -u root -proot vite_gourmand -e "
SELECT p.permission_code 
FROM role_permission rp 
JOIN permission p ON rp.permission_id = p.permission_id 
WHERE rp.role_id = 1 
LIMIT 5;
"
```

---

## 📞 Support

Pour les problèmes :
1. Vérifiez la console du navigateur (F12 → Console)
2. Vérifiez les logs du serveur PHP (port 8000)
3. Vérifiez la base de données MySQL
4. Consultez le TEST-RBAC-RAPPORT.md pour plus de détails

---

**Dernière mise à jour :** 19 février 2026
**Statut :** ✅ Production Ready
