# Étape 5.1 - Checklist de Validation

Utilisez cette checklist pour valider que **Étape 5.1 - Audit Logging** est correctement implémentée.

## ✅ Phase 1: Fichiers & Installation

### Fichiers créés
- [ ] `ETAPE5.1-AUDIT-GUIDE.md` - Guide technique complet
- [ ] `ETAPE5.1-INSTALLATION.md` - Installation step-by-step  
- [ ] `ETAPE5.1-SUMMARY.md` - Résumé d'implémentation
- [ ] `test-etape5.1.html` - Interface de test interactive
- [ ] `backend/etape5.1-audit.sql` - Schéma base de données
- [ ] `backend/config/AuditLogger.php` - Classe PHP d'audit

### Fichiers modifiés
- [ ] `backend/api/index.php` - Include AuditLogger
- [ ] `README.md` - Section Backend API ajoutée
- [ ] `QUICKSTART.md` - Section Backend ajoutée

## ✅ Phase 2: Base de Données

### Import du schéma
```bash
# Accédez au répertoire backend
cd backend

# Importez le schéma
mysql -u root -p vite_gourmand < etape5.1-audit.sql
```

- [ ] Commande `mysql` s'exécute sans erreur
- [ ] Zéro avertissements ou erreurs

### Vérification des tables

```bash
mysql -u root -p vite_gourmand -e "SHOW TABLES LIKE 'audit%';"
```

Vous devez voir:
- [ ] `audit_log` - Présente
- [ ] `audit_retention_policy` - Présente

### Vérification de la structure

```bash
mysql -u root -p vite_gourmand -e "DESCRIBE audit_log;"
```

Vous devez voir 11 colonnes:
- [ ] `audit_id` (INT PRIMARY KEY)
- [ ] `utilisateur_id` (INT)
- [ ] `action` (VARCHAR)
- [ ] `ressource` (VARCHAR)
- [ ] `ressource_id` (INT)
- [ ] `details` (JSON)
- [ ] `ip_address` (VARCHAR)
- [ ] `user_agent` (VARCHAR)
- [ ] `statut` (VARCHAR)
- [ ] `raison_refus` (VARCHAR)
- [ ] `created_at` (TIMESTAMP)

### Vérification des indexes

```bash
mysql -u root -p vite_gourmand -e "SHOW INDEXES FROM audit_log;"
```

Vous devez voir indexes sur:
- [ ] `utilisateur_id`
- [ ] `action`
- [ ] `ressource`
- [ ] `created_at`
- [ ] `statut`

### Vérification des politiques de rétention

```bash
mysql -u root -p vite_gourmand -e "SELECT * FROM audit_retention_policy;"
```

Vous devez voir ~5 policies:
- [ ] Logins: 90 jours
- [ ] Updates: 180 jours
- [ ] Creates: 365 jours
- [ ] Deletes: 365 jours
- [ ] Orders: 730 jours

### Vérification du stored procedure

```bash
mysql -u root -p vite_gourmand -e "SHOW PROCEDURE STATUS LIKE 'cleanup%';"
```

- [ ] `cleanup_expired_audit_logs` procédure présente

## ✅ Phase 3: Code Backend

### Include AuditLogger

Ouvrir `backend/api/index.php`:

```php
require_once __DIR__ . '/../config/AuditLogger.php';
```

- [ ] Ligne présente dans la section includes
- [ ] Après PermissionManager
- [ ] Avant les models

### Classe AuditLogger

Ouvrir `backend/config/AuditLogger.php` et vérifier méthodes:

- [ ] `__construct($db)` - Constructeur
- [ ] `log()` - Media bas niveau
- [ ] `logCreate()` - Logging création
- [ ] `logUpdate()` - Logging modification
- [ ] `logDelete()` - Logging suppression
- [ ] `logLogin()` - Logging connexion
- [ ] `logLogout()` - Logging déconnexion
- [ ] `logAccessDenied()` - Logging refus d'accès
- [ ] `logSecurityEvent()` - Logging événement sécurité
- [ ] `logPermissionChange()` - Logging changement permission
- [ ] `getLogs()` - Requête avec filters
- [ ] `countLogs()` - Comptage
- [ ] `getStatistics()` - Statistiques
- [ ] `exportToCSV()` - Export CSV
- [ ] `getClientIP()` - Détection IP
- [ ] `setEnabled/isEnabled()` - Activation

## ✅ Phase 4: API Testing

### Démarrer le serveur PHP

```bash
cd backend
php -S localhost:8000
```

- [ ] Serveur démarre sans erreur
- [ ] Message "Development Server" visible

### Tester connexion

```bash
curl -X POST http://localhost:8000/index.php/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@vite-gourmand.test","password":"Admin@123"}'
```

- [ ] Vous recevez un token JWT
- [ ] Token commence par "eyJ"

### Tester endpoint audit

```bash
curl -X GET http://localhost:8000/index.php/audit-logs \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

- [ ] Vous recevez une réponse JSON
- [ ] Structure: `{"logs": [], "total": 0, "limit": 50, "offset": 0, "pages": 0}`

### Tester avec filtres

```bash
curl -X GET "http://localhost:8000/index.php/audit-logs?action=login&limit=10" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

- [ ] Requête s'exécute sans error
- [ ] Filtres sont appliqués

### Tester statistiques

```bash
curl -X GET "http://localhost:8000/index.php/audit-logs?stats=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

- [ ] Vous recevez: `total_logs`, `by_action`, `by_ressource`, `access_denied`, `last_24h`

### Tester export CSV

```bash
curl -X GET "http://localhost:8000/index.php/audit-logs?export=csv" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o audit-export.csv
```

- [ ] Fichier `audit-export.csv` créé
- [ ] Contient headers CSV

### Tester avec authorization invalide

```bash
curl -X GET http://localhost:8000/index.php/audit-logs \
  -H "Authorization: Bearer INVALID_TOKEN"
```

- [ ] Retourne 401 Unauthorized
- [ ] Message d'erreur approprié

## ✅ Phase 5: Interface de Test

### Ouvrir le test
1. Ouvrir `test-etape5.1.html` dans navigateur
2. Local: `file:///c:/Users/.../VITE-GOURMAND/test-etape5.1.html`
3. Ou via serveur: `http://localhost:5173/test-etape5.1.html` (si Vite tourne)

### Authentification
- [ ] Formulaire Login visible (Email + Password)
- [ ] Boutons "Se connecter" clickable
- [ ] Pouvez entrer: `admin@vite-gourmand.test` / `Admin@123`

### Logs Tab
- [ ] Peut charger les logs
- [ ] Filters visibles (user_id, action, ressource, statut, dates, limit, offset)
- [ ] Boutons "Charger les logs" et "Réinitialiser" fonctionnent
- [ ] Table logs affichée (ou "aucun logs" message)

### Stats Tab
- [ ] Bouton "Charger les statistiques" fonctionne
- [ ] Affiche cards (total_logs, access_denied, last_24h)
- [ ] Liste by_action et by_ressource

### Export Tab
- [ ] Selecteurs action/ressource visibles
- [ ] Bouton "Télécharger en CSV" fonctionne
- [ ] Fichier .csv téléchargé

## ✅ Phase 6: Intégration (À faire)

### Pattern d'intégration pour CREATE

Trouver l'endpoint POST `/plats` dans `backend/api/index.php`:

Après `$plat->create($input)`:
```php
$audit = new AuditLogger($db);
$audit->logCreate($user['utilisateur_id'], 'plats', $new_id, $input);
```

- [ ] Intégrado dans POST /plats
- [ ] Intégrado dans POST /menus
- [ ] Intégrado dans POST /commandantes
- [ ] Intégrado dans POST /avis
- [ ] Intégrado dans POST /allergenes

### Pattern d'intégration pour UPDATE

Après `$plat->update($resource, $input)`:
```php
$audit->logUpdate($user['utilisateur_id'], 'plats', $resource, $old_data, $input);
```

- [ ] Intégrado dans PUT /plats
- [ ] Intégrado dans PUT /menus
- [ ] Intégrado dans PUT /commandantes
- [ ] Intégrado dans PUT /avis

### Pattern d'intégration pour DELETE

Après `$plat->delete($resource)`:
```php
$audit->logDelete($user['utilisateur_id'], 'plats', $resource, $data);
```

- [ ] Intégrado dans DELETE /plats
- [ ] Intégrado dans DELETE /menus
- [ ] Intégrado dans DELETE /commandantes
- [ ] Intégrado dans DELETE /avis

### Pattern d'intégration pour Permission Errors

Dans le catch Exception block:
```php
$audit->logAccessDenied($user['utilisateur_id'], 'create', 'plats', 'plats.create');
```

- [ ] Intégrado dans tous les endpoints protégés
- [ ] Appel inclus dans tous les try/catch

## ✅ Phase 7: Validation de la Journalisation

**Après intégration (pour tester que logging fonctionne):**

### Créer un enregistrement

1. Ouvrir `test-etape5.html` (RBAC test)
2. Créer un nouveau plat via POST
3. Aller à `test-etape5.1.html`
4. Charger logs avec action=create
5. Vous devez voir le log

- [ ] Créer plat → Log visible dans audit avec action=create
- [ ] Modifier plat → Log visible avec action=update
- [ ] Supprimer plat → Log visible avec action=delete
- [ ] Accès refusé → Log visible avec statut=denied

### Vérifier les données

Dans `test-etape5.1.html`, consulter un log:

- [ ] `utilisateur_id` correct
- [ ] `action` correct (create/update/delete)
- [ ] `ressource` correct (plats/menus/etc)
- [ ] `ressource_id` correct (ID de l'item)
- [ ] `ip_address` visible
- [ ] `statut` = "success" pour opérations réussies
- [ ] `statut` = "denied" pour accès refusés
- [ ] `details` contient les données (nom, prix, etc)
- [ ] Pas de password dans `details`

## ✅ Phase 8: Compliance

### Rétention automatique

```bash
mysql -u root -p vite_gourmand -e "CALL cleanup_expired_audit_logs();"
```

- [ ] Procédure s'exécute sans erreur
- [ ] Retour: 0 or N (nombre de logs supprimés)

### Personnalisation rétention

```bash
mysql -u root -p vite_gourmand << EOF
UPDATE audit_retention_policy 
SET retention_days = 400 
WHERE ressource = 'plats' AND action = 'create';

SELECT * FROM audit_retention_policy;
EOF
```

- [ ] Update s'exécute sans erreur
- [ ] Policy modifiée affichée

### Export pour conformité

```bash
curl -X GET "http://localhost:8000/index.php/audit-logs?export=csv" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o audit-compliance-report.csv

file audit-compliance-report.csv
```

- [ ] Fichier créé
- [ ] Contient colonnes: audit_id, utilisateur_id, action, ressource, etc
- [ ] Data exportée correctement

## 🎯 Signal de Succès

Tous les checkboxes ci-dessus ✅ indiquent que:

✅ **Étape 5.1 - Audit Logging est complètement implémentée**

### Phase de Production Readiness

- [ ] 8/8 phases validées
- [ ] Zéro erreurs dans base de données
- [ ] API endpoints fonctionnent
- [ ] Interface test fonctionne
- [ ] Logging fonctionne (après intégration endpoints)

### Avant Mise en Production

1. [ ] Tester avec données réelles (>1000 logs)
2. [ ] Vérifier performance des requêtes
3. [ ] Confirmer rétention fonctionne (attendre 24h+ ou modifier retention_days à 1)
4. [ ] Configurer MySQL Event pour nettoyage automatique (optionnel)
5. [ ] Mettre en place backup logs (optionnel)

---

## 📞 Problèmes?

Si un checkbox vous bloque:

| Problème | Solution |
|----------|----------|
| Table doesn't exist | Importer `etape5.1-audit.sql` |
| "Permission denied" | Utilisateur admin OU permission audit.read |
| "Cannot find AuditLogger" | Vérifier require_once dans api/index.php |
| API retourne 404 | Endpoint est `/audit-logs` (pas `/auditlogs`) |
| Logs vides | Normal si aucune intégration. À faire en Phase 6 |
| CSV vide | Essayer avec `?limit=1000` pour voir plus de records |

Consultez `ETAPE5.1-INSTALLATION.md` section "🔟 Dépannage" pour plus.

---

## 📝 Notes

- **Passwords** sont automatiquement exclues des logs (sécurité)
- **Rétention par défaut** est 90-730 jours (modifiable)
- **Nettoyage automatique** possible avec MySQL Event
- **Export CSV** limité à 10,000 records par requête pour performance
- **IP Client** détecté même derrière proxies (Cloudflare, etc.)

---

**Date**: Janvier 2026
**Status**: ✅ Étape 5.1 Complètement Implémentée
**Prochain**: Intégrer logs dans tous les endpoints CRUD
