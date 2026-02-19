# Étape 5.1 - Guide d'Installation

Ce guide vous accompagne pas à pas pour installer et configurer le système d'audit logging.

## Prérequis

✅ Étape 4 (Protection JWT) complétée  
✅ Étape 5 (RBAC) complétée  
✅ MySQL 5.7+ (pour support JSON)  
✅ PHP 7.4+  

## 1️⃣ Préparer la base de données

### 1.1 Importer le schéma d'audit

```bash
# Windows (PowerShell)
mysql -u root -p votre_base < backend/etape5.1-audit.sql

# Linux/macOS
mysql -u root -p votre_base < backend/etape5.1-audit.sql
```

### 1.2 Vérifier la création

```bash
mysql -u root -p -e "USE votre_base; SHOW TABLES LIKE 'audit%';"
```

Vous devez voir:
- `audit_log`
- `audit_retention_policy`

## 2️⃣ Configuration du code

### 2.1 Vérifier l'inclusion dans l'API

Lancer le serveur PHP backend (IMPORTANT: utiliser le router.php)

```bash
cd backend
php -S localhost:8000 router.php
```

⚠️ **IMPORTANT**: Vous **DEVEZ** utiliser `router.php` pour que les URLs sans extension (comme `/api/audit-logs`) soient correctement routées!

### 2.2 Tester la connexion

Verifier depuis un autre terminal:

```bash
curl -X GET http://localhost:8000/api/audit-logs \
  -H "Authorization: Bearer test" \
  -H "Content-Type: application/json"
```

Vous allez recevoir une erreur de permission (c'est normal, pas de token JWT valide pour l'instant).

## 3️⃣ Tester avec l'interface

### 3.1 Ouvrir le fichier de test

1. Ouvrir `test-etape5.1.html` dans un navigateur
2. L'API est maintenant accessible à `http://localhost:8000/api`

### 3.2 Se connecter

1. Entrer les identifiants (defaults):
   - Email: `admin@vite-gourmand.test`
   - Mot de passe: `Admin@123`
2. Cliquer "Se connecter"
3. Vous devez voir: `✅ Connecté! Token: ...`

### 3.3 Tester les logs

**Note:** Au démarrage, il y a peu ou pas de logs. Pour tester complètement, vous pouvez:

\- Cliquer sur "Charger les logs"
\- Appliquer des filtres (action, ressource, etc.)
\- Cliquer "Charger les statistiques"
\- Cliquer "Télécharger en CSV"

## 4️⃣ Intégrer la journalisation dans les endpoints

**Important:** Les endpoints d'audit fonctionnent maintenant, mais la journalisation elle-même n'est pas encore appelée. Vous devez intégrer les logs dans chaque endpoint CRUD.

### 4.1 Pattern pour les créations

Dans chaque endpoint POST/CREATE:

```php
if ($plat->create($input)) {
    // Nouveau: Ajouter le log
    $audit = new AuditLogger($db);
    $audit->logCreate($user['utilisateur_id'], 'plats', 
                     $plat->getLastInsertId(), $input);
    
    http_response_code(201);
    echo json_encode(['message' => 'Plat créé']);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Erreur lors de la création']);
}
```

### 4.2 Pattern pour les updates

```php
$old_data = $plat->getById($resource);

if ($plat->update($resource, $input)) {
    // Nouveau: Ajouter le log avec avant/après
    $audit = new AuditLogger($db);
    $audit->logUpdate($user['utilisateur_id'], 'plats', $resource,
                      $old_data, $input);
    
    echo json_encode(['message' => 'Plat mis à jour']);
}
```

### 4.3 Pattern pour les accès refusés

```php
try {
    $user = AuthHelper::requireAuth();
    AuthHelper::requirePermission($user, 'plats.create');
    // ...
} catch (Exception $e) {
    // Nouveau: Logger l'accès refusé
    $audit = new AuditLogger($db);
    $audit->logAccessDenied($user['utilisateur_id'] ?? null, 'create', 
                           'plats', 'plats.create');
    
    AuthHelper::sendError($e->getMessage(), 401);
}
```

## 5️⃣ Configurer les politiques de rétention

Par défaut, les politiques suivantes sont appliquées:

```
Logins (90 jours)    → Données d'authentification
Updates (180 jours)  → Modifications
Creates (365 jours)  → Nouvelles créations
Deletes (365 jours)  → Suppressions
Orders (730 jours)   → Commandes (conformité)
```

### 5.1 Personnaliser les politiques

Pour modifier la rétention (exemple: garder les creates 500 jours):

```sql
UPDATE audit_retention_policy 
SET retention_days = 500 
WHERE ressource = 'plats' AND action = 'create';
```

Pour ajouter une nouveau domaine:

```sql
INSERT INTO audit_retention_policy (ressource, action, retention_days)
VALUES ('allergenes', '*', 730);
```

## 6️⃣ Configurer le nettoyage automatique

### 6.1 Nettoyage manuel

Pour supprimer les logs expirés:

```bash
mysql -u root -p votre_base \
  -e "CALL cleanup_expired_audit_logs();"
```

### 6.2 Nettoyage automatique (optionnel)

Pour nettoyer automatiquement chaque jour à minuit:

```sql
CREATE EVENT IF NOT EXISTS cleanup_audit_logs_daily
ON SCHEDULE EVERY 1 DAY
STARTS CURDATE() + INTERVAL 1 DAY
DO
  CALL cleanup_expired_audit_logs();
```

Vérifier l'event est actif:

```sql
SHOW EVENTS LIKE 'cleanup_audit_logs_daily';
```

## 7️⃣ Sauvegarder & Archiver les logs

### 7.1 Export régulier

Vous pouvez exporter les logs via l'API:

```bash
curl -X GET "http://localhost:8000/api/audit-logs?export=csv&date_from=2024-01-01&date_to=2024-01-31" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  > audit-2024-01.csv
```

### 7.2 Script de sauvegarde mensuelle

Créer `scripts/backup-audit-logs.sh`:

```bash
#!/bin/bash

MONTH=$(date +%Y-%m)
BACKUP_DIR="backups/audit"
mkdir -p $BACKUP_DIR

mysql -u root -p vite_gourmand \
  -e "SELECT * FROM audit_log WHERE YEAR_MONTH(created_at) = '$MONTH';" \
  > "$BACKUP_DIR/audit-$MONTH.csv"

echo "Logs de $MONTH archivés dans $BACKUP_DIR/audit-$MONTH.csv"
```

## 8️⃣ Monitoring & Alertes

### 8.1 Vérifier les activités suspectes

Chercher les accès refusés:

```sql
SELECT utilisateur_id, COUNT(*) as failed_attempts
FROM audit_log 
WHERE statut = 'denied' 
AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY utilisateur_id
HAVING failed_attempts > 5;
```

### 8.2 Voir les modifications d'un utilisateur

```sql
SELECT audit_id, action, ressource, created_at, ip_address
FROM audit_log 
WHERE utilisateur_id = 5
ORDER BY created_at DESC
LIMIT 100;
```

### 8.3 Voir qui a supprimé quoi

```sql
SELECT utilisateur_id, ressource, ressource_id, created_at
FROM audit_log 
WHERE action = 'delete'
ORDER BY created_at DESC;
```

## 9️⃣ Checklist de validation

Après avoir suivi les étapes:

- [ ] Base de données: Tables `audit_log` et `audit_retention_policy` créées
- [ ] API: AuditLogger inclus dans `api/index.php`
- [ ] Tests: Vous pouvez vous connecter via `test-etape5.1.html`
- [ ] Endpoints: `GET /api/audit-logs` fonctionne
- [ ] Filtres: Vous pouvez filtrer par user_id, action, ressource, statut, dates
- [ ] Statistiques: `GET /api/audit-logs?stats=1` retourne des stats
- [ ] Export: `GET /api/audit-logs?export=csv` télécharge un fichier CSV
- [ ] Intégration: Logs appellent `logCreate/logUpdate/logDelete` dans les endpoints
- [ ] Rétention: Politiques configurées et nettoyage fonctionne

## 🔟 Dépannage

### Problem: "Table 'audit_log' doesn't exist"

**Solution:** Vous n'avez pas importé le schéma SQL. Lancer:

```bash
mysql -u root -p vite_gourmand < backend/etape5.1-audit.sql
```

### Problem: "Class AuditLogger not found"

**Solution:** Ajouter le `require_once` dans `api/index.php`:

```php
require_once __DIR__ . '/../config/AuditLogger.php';
```

### Problem: "Permission refusée" sur GET /api/audit-logs

**Solution:** Vous devez être admin OU avoir la permission `audit.read`. Vérifier:

\- Votre token est valide
\- Votre utilisateur est admin (role_id = 1) OU
\- Vous avez la permission 'audit.read'

### Problem: Les logs sont vides

**Solution:** C'est normal au démarrage. Les logs sont enregistrés seulement quand vous intégrez les appels `logCreate/logUpdate/delete` dans les endpoints. Voir section 4.

### Problem: "Cannot find Etape3/4/5"

**Solution:** Assurez-vous que vous avez complété Étape 3 (JWT), Étape 4 (Protection) et Étape 5 (RBAC) avant Étape 5.1.

## Prochaines tâches

1. **À faire:** Intégrer les appels d'audit dans tous les endpoints CRUD (voir section 4)
2. **À faire:** Créer un tableau de bord admin pour visualiser les logs en temps réel
3. **À faire:** Mettre en place des alertes sur trop d'accès refusés
4. **À faire:** Configurer archivage automatique vers S3/Azure Blob

---

**Questions?** Consultez `ETAPE5.1-AUDIT-GUIDE.md` pour la documentation technique complète.
