# Étape 5.1 - Audit Logging

## Vue générale

Étape 5.1 ajoute un système complet de journalisation d'audit (audit logging) qui enregistre toutes les opérations sensibles sur l'API pour fins de conformité, sécurité et investigation.

## Architecture

### 1. Base de données

**Tables principales:**

#### `audit_log`
Table primaire stockant tous les événements auditables.

| Colonne | Type | Description |
|---------|------|-------------|
| `audit_id` | INT PRIMARY KEY | Identifiant unique |
| `utilisateur_id` | INT | Référence à l'utilisateur qui a effectué l'action |
| `action` | VARCHAR(50) | Type d'action (login, logout, create, update, delete, access_denied) |
| `ressource` | VARCHAR(50) | Ressource affectée (utilisateurs, plats, menus, commandantes, etc.) |
| `ressource_id` | INT | ID de la ressource spécifique |
| `details` | JSON | Détails flexibles (avant/après pour CRUD, raison de refus, etc.) |
| `ip_address` | VARCHAR(45) | Adresse IP du client (avec support proxy/Cloudflare) |
| `user_agent` | VARCHAR(255) | User-Agent du client |
| `statut` | VARCHAR(20) | Résultat (success, denied, error) |
| `raison_refus` | VARCHAR(255) | Raison si statut=denied |
| `created_at` | TIMESTAMP | Moment de l'événement |

**Indexes:**
- `utilisateur_id` - Recherche par utilisateur
- `action` - Recherche par type d'action
- `ressource` - Recherche par ressource
- `created_at` - Recherche par date
- `statut` - Recherche par résultat

#### `audit_retention_policy`
Configuration flexible des politiques de rétention par ressource/action.

| Colonne | Type | Description |
|---------|------|-------------|
| `policy_id` | INT PRIMARY KEY | Identifiant |
| `ressource` | VARCHAR(50) | Ressource concernée (* = global) |
| `action` | VARCHAR(50) | Action concernée (* = toutes) |
| `retention_days` | INT | Nombre de jours de conservation |

**Politiques par défaut:**
- Logins: 90 jours
- Updates: 180 jours
- Creates/Deletes: 365 jours
- Orders: 730 jours (2 ans - conformité)

### 2. Classe PHP AuditLogger

Fichier: `backend/config/AuditLogger.php`

#### Initialisation
```php
$audit = new AuditLogger($db);
```

#### Méthodes principales

##### `log($user_id, $action, $ressource, $ressource_id, $details, $statut, $raison)`
Média d'enregistrement bas niveau.
```php
$audit->log(
    1,                          // user_id
    'create',                   // action
    'plats',                    // ressource
    123,                        // resource_id
    ['nom' => 'Coq au vin'],   // details (JSON)
    'success',                  // statut
    null                        // raison_refus
);
```

##### `logCreate($user_id, $ressource, $id, $data)`
Enregistre une création.
```php
$audit->logCreate($user_id, 'plats', $new_id, $input_data);
// Exclut automatiquement: password, token, secret
```

##### `logUpdate($user_id, $ressource, $id, $old_data, $new_data)`
Enregistre une mise à jour avec avant/après.
```php
$audit->logUpdate(1, 'plats', 123, $old_values, $new_values);
```

##### `logDelete($user_id, $ressource, $id, $data)`
Enregistre une suppression.
```php
$audit->logDelete($user_id, 'plats', 123, $plat_data);
```

##### `logLogin($user_id)`
Enregistre une connexion réussie.
```php
$audit->logLogin($user_id);
```

##### `logLogout($user_id)`
Enregistre une déconnexion.
```php
$audit->logLogout($user_id);
```

##### `logAccessDenied($user_id, $action, $ressource, $permission)`
Enregistre un accès refusé (pour investigation de sécurité).
```php
$audit->logAccessDenied(
    $user_id,
    'create',
    'utilisateurs',
    'utilisateurs.create'
);
```

##### `logSecurityEvent($user_id, $event, $details)`
Enregistre un événement de sécurité général.
```php
$audit->logSecurityEvent($user_id, 'failed_login', ['attempts' => 3]);
```

##### `logPermissionChange($user_id, $role_id, $permission, $action)`
Enregistre un changement de permission/rôle.
```php
$audit->logPermissionChange(
    admin_id,
    user_role_id,
    'plats.create',
    'grant'  // 'grant' ou 'revoke'
);
```

#### Requêtes

##### `getLogs($filters, $limit, $offset)`
Récupère les logs avec filtres optionnels.

Filtres supportés:
```php
$filters = [
    'utilisateur_id' => 1,          // (int) ID utilisateur
    'action' => 'create',            // (string) Type d'action
    'ressource' => 'plats',          // (string) Ressource
    'statut' => 'success',           // (string) 'success'|'denied'|'error'
    'date_from' => '2024-01-01',    // (string) Date début ISO
    'date_to' => '2024-12-31'       // (string) Date fin ISO
];

$logs = $audit->getLogs($filters, 50, 0);
// Retourne array de logs
```

##### `countLogs($filters)`
Compte les logs correspondant aux filtres.
```php
$total = $audit->countLogs($filters);
```

##### `getStatistics()`
Retourne des statistiques globales.
```php
$stats = $audit->getStatistics();
// {
//   "total_logs": 5234,
//   "by_action": {"login": 1200, "create": 450, ...},
//   "by_ressource": {"plats": 1200, "utilisateurs": 890, ...},
//   "access_denied": 45,
//   "last_24h": 234
// }
```

##### `exportToCSV($filters)`
Exporte les logs en CSV (max 10,000 par requête).
```php
$csv = $audit->exportToCSV(['action' => 'delete']);
// Retourne string CSV avec headers
```

#### Contrôle

##### `setEnabled($enabled)`
Active/désactive la journalisation.
```php
$audit->setEnabled(false);  // Désactiver temporairement
```

##### `isEnabled()`
Vérifie si la journalisation est active.
```php
if ($audit->isEnabled()) { ... }
```

### 3. API Endpoints

#### `GET /api/audit-logs`
Liste tous les logs avec filtres et pagination.

**Paramètres de requête:**
```
GET /api/audit-logs?action=create&ressource=plats&limit=50&offset=0&stats=1
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `user_id` | int | Filtrer par utilisateur |
| `action` | string | Filtrer par action |
| `ressource` | string | Filtrer par ressource |
| `statut` | string | Filtrer par statut |
| `date_from` | string | Date début (ISO) |
| `date_to` | string | Date fin (ISO) |
| `limit` | int | Résultats par page (max 100) |
| `offset` | int | Décalage pour pagination |
| `stats` | int | Si 1, retourne des statistiques |
| `export` | string | Si "csv", exporte au format CSV |

**Réponse (avec pagination):**
```json
{
  "logs": [
    {
      "audit_id": 1,
      "utilisateur_id": 5,
      "action": "create",
      "ressource": "plats",
      "ressource_id": 123,
      "statut": "success",
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-01-15T14:30:00Z",
      "details": {"nom": "Coq au vin", "prix": "18.50"}
    }
  ],
  "total": 456,
  "limit": 50,
  "offset": 0,
  "pages": 10
}
```

**Réponse (statistiques):**
```json
{
  "total_logs": 5234,
  "by_action": {
    "login": 1200,
    "create": 450,
    "update": 320,
    "delete": 45,
    "access_denied": 89
  },
  "by_ressource": {
    "plats": 1200,
    "utilisateurs": 890,
    "menus": 567
  },
  "access_denied": 89,
  "last_24h": 234
}
```

#### `GET /api/audit-logs/{id}`
Récupère les détails d'un log spécifique.

**Réponse:**
```json
{
  "audit_id": 1,
  "utilisateur_id": 5,
  "action": "update",
  "ressource": "plats",
  "ressource_id": 123,
  "statut": "success",
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2024-01-15T14:30:00Z",
  "details": {
    "before": {"nom": "Coq au vin", "prix": "18.50"},
    "after": {"nom": "Coq au vin riche", "prix": "19.50"}
  }
}
```

#### `GET /api/audit-logs?export=csv`
Télécharge un fichier CSV des logs.

**Exemple de contenu CSV:**
```
audit_id,utilisateur_id,action,ressource,ressource_id,statut,ip_address,created_at
1,5,create,plats,123,success,192.168.1.1,2024-01-15T14:30:00Z
2,3,update,plats,123,success,192.168.1.2,2024-01-15T14:35:00Z
```

### 4. Sécurité

#### Authentification & Autorisation
Tous les endpoints d'audit nécessitent:
- **Authentification**: Token JWT valide
- **Autorisation**: 
  - Admin (role_id = 1), OU
  - Permission `audit.read`

#### Protection des données sensibles
- Les mots de passe sont **automatiquement exclus** des logs
- Les tokens et secrets sont filtrés
- Seules les données pertinentes sont journalisées

#### Détection de IP réelle
La méthode `getClientIP()` détecte correctement l'IP client même:
- Derrière un proxy reverse
- Derrière Cloudflare (`CF_CONNECTING_IP`)
- Derrière AWS ELB (`X_FORWARDED_FOR`)

Ordre de vérification:
1. `CF_CONNECTING_IP` (Cloudflare)
2. `X_FORWARDED_FOR` (proxies)
3. `X_FORWARDED_HOST` (proxies)
4. `X_CLIENT_IP` (proxies alternatifs)
5. `REMOTE_ADDR` (IP directe)

## Configuration

### Politiques de rétention

Les politiques de rétention se configurent via la table `audit_retention_policy`:

```sql
-- Exemple: Garder les creates de plats durant 400 jours
INSERT INTO audit_retention_policy (ressource, action, retention_days) 
VALUES ('plats', 'create', 400);

-- Exemple: Garder tous les deletes durant 365 jours
INSERT INTO audit_retention_policy (ressource, action, retention_days) 
VALUES ('*', 'delete', 365);
```

Par défaut après installation:
- Logins/Logouts: 90 jours
- Updates: 180 jours
- Creates/Deletes: 365 jours
- Orders: 730 jours

### Nettoyage automatique

Un stored procedure `cleanup_expired_audit_logs()` supprime les logs expirés basé sur les politiques.

**Lancer manuellement:**
```sql
CALL cleanup_expired_audit_logs();
```

**Setup MySQL Event (automatique chaque jour à minuit):**
```sql
CREATE EVENT IF NOT EXISTS cleanup_audit_logs_daily
ON SCHEDULE EVERY 1 DAY
STARTS CURDATE() + INTERVAL 1 DAY
DO
  CALL cleanup_expired_audit_logs();
```

## Intégration dans les endpoints

À faire: Intégrer les appels d'audit dans chaque endpoint.

### Pattern pour CREATE
```php
case 'plats':
    if ($method == 'POST') {
        try {
            $user = AuthHelper::requireAuth();
            AuthHelper::requireRole($user, 1);
            
            if ($plat->create($input)) {
                $audit = new AuditLogger($db);
                $audit->logCreate($user['utilisateur_id'], 'plats', 
                                 $plat->getLastInsertId(), $input);
                
                http_response_code(201);
                echo json_encode(['message' => 'Plat créé']);
            }
        } catch (Exception $e) {
            $audit->logAccessDenied($user['utilisateur_id'], 'create', 
                                   'plats', 'plats.create');
            AuthHelper::sendError($e->getMessage(), 401);
        }
    }
```

### Pattern pour UPDATE
```php
} elseif ($method == 'PUT' && !empty($resource)) {
    try {
        $user = AuthHelper::requireAuth();
        
        $old_data = $plat->getById($resource);
        if ($plat->update($resource, $input)) {
            $audit = new AuditLogger($db);
            $audit->logUpdate($user['utilisateur_id'], 'plats', $resource,
                            $old_data, $input);
            
            echo json_encode(['message' => 'Plat mis à jour']);
        }
    } catch (Exception $e) {
        $audit->logAccessDenied($user['utilisateur_id'], 'update', 
                              'plats', 'plats.update');
        AuthHelper::sendError($e->getMessage(), 401);
    }
}
```

### Pattern pour DELETE
```php
} elseif ($method == 'DELETE' && !empty($resource)) {
    try {
        $user = AuthHelper::requireAuth();
        
        $data = $plat->getById($resource);
        if ($plat->delete($resource)) {
            $audit = new AuditLogger($db);
            $audit->logDelete($user['utilisateur_id'], 'plats', $resource, $data);
            
            echo json_encode(['message' => 'Plat supprimé']);
        }
    } catch (Exception $e) {
        $audit->logAccessDenied($user['utilisateur_id'], 'delete', 
                              'plats', 'plats.delete');
        AuthHelper::sendError($e->getMessage(), 401);
    }
}
```

## Cas d'usage

### 1. Enquête sur activité suspecte
```php
// Tous les accès refusés au plats
$audit = new AuditLogger($db);
$logs = $audit->getLogs([
    'ressource' => 'plats',
    'statut' => 'denied',
    'date_from' => '2024-01-01'
], 100, 0);
```

### 2. Conformité: Garder trace des modifications
```php
// Tous les updates sur utilisateurs
$logs = $audit->getLogs([
    'ressource' => 'utilisateurs',
    'action' => 'update'
], 100, 0);
```

### 3. Audit: Exporter pour analyse
```php
// Exporter tous les logs du mois
$csv = $audit->exportToCSV([
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31'
]);
file_put_contents('audit-export.csv', $csv);
```

### 4. Tableau de bord: Tendances
```php
$stats = $audit->getStatistics();
echo "Tentatives d'accès refusées: " . $stats['access_denied'];
echo "Logs dernières 24h: " . $stats['last_24h'];
```

## Tests

Utilisez le fichier `test-etape5.1.html` pour:

1. **Se connecter** avec un compte admin
2. **Consulter les logs** avec filtres (utilisateur, action, ressource, statut, période)
3. **Voir les statistiques** globales
4. **Exporter en CSV** pour analyse externe

## Installation

### 1. Importer le schéma de base de données
```bash
mysql -u root -p vite_gourmand < backend/etape5.1-audit.sql
```

### 2. Vérifier que AuditLogger est inclus dans l'API
```php
// Dans backend/api/index.php
require_once __DIR__ . '/../config/AuditLogger.php';
```

### 3. Intégrer les appels de log dans les endpoints
Voir section "Intégration dans les endpoints" ci-dessus.

### 4. Tester avec test-etape5.1.html
Ouvrir le fichier dans le navigateur et tester les filtres.

## Compliance

**Standards supportés:**
- GDPR: Suppression des données expirées via retention policies
- HIPAA/FIN/SOX: Logs d'audit immuables et datés avec IP
- PCI-DSS: Tracabilité complète des modifications sur données sensibles

**Recommandations:**
- Réviewer les logs `access_denied` quotidiennement
- Archiver les logs mensuels sur stockage sécurisé (S3, Azure)
- Configurer alertes sur trop many failed attempts
- Garder 1-2 ans de logs pour conformité réglementaire

## Prochaines étapes

1. ✅ Créer endpoints d'audit (terminé)
2. ⏳ Intégrer logs dans tous les endpoints CRUD
3. ⏳ Créer tableau de bord admin pour visualiser les logs
4. ⏳ Mettre en place alertes sur accès refusés répétés
5. ⏳ Archivage automatique vers stockage externe (S3/Azure Blob)
