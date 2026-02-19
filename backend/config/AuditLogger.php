<?php

/**
 * AuditLogger - Système de logs d'audit pour tracker les opérations sensibles
 * 
 * Enregistre:
 * - Modifications de données (créations, mises à jour, suppressions)
 * - Authentifications (login, logout)
 * - Accès refusés (permissions insuffisantes)
 * - Modifications de permissions
 * - Actions admin sensibles
 */
class AuditLogger
{
    private $db = null;
    private $enabled = true;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Logger une opération
     * 
     * @param int $user_id ID de l'utilisateur (null pour opérations publiques)
     * @param string $action Type d'action (create, read, update, delete, login, etc.)
     * @param string $ressource Type de ressource (utilisateurs, plats, menus, etc.)
     * @param int $ressource_id ID de la ressource modifiée (optionnel)
     * @param array $details Détails des modifications (optionnel)
     * @param string $statut Statut de l'opération (success, error, denied)
     * @param string $raison_refus Raison du refus si denied (optionnel)
     * @return bool Succès de l'enregistrement
     */
    public function log($user_id, $action, $ressource, $ressource_id = null, $details = null, $statut = 'success', $raison_refus = null)
    {
        if (!$this->enabled) {
            return true;
        }

        try {
            $ip_address = $this->getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Convertir les détails en JSON
            $details_json = null;
            if ($details !== null && is_array($details)) {
                $details_json = json_encode($details);
            }

            $query = "
                INSERT INTO audit_log 
                (utilisateur_id, action, ressource, ressource_id, details, ip_address, user_agent, statut, raison_refus) 
                VALUES 
                (:user_id, :action, :ressource, :ressource_id, :details, :ip_address, :user_agent, :statut, :raison_refus)
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':ressource', $ressource);
            $stmt->bindParam(':ressource_id', $ressource_id);
            $stmt->bindParam(':details', $details_json);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            $stmt->bindParam(':statut', $statut);
            $stmt->bindParam(':raison_refus', $raison_refus);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Logger un événement de sécurité
     * 
     * @param int $user_id ID utilisateur
     * @param string $event Type d'événement (login, logout, permission_denied, etc.)
     * @param string $raison Raison détaillée
     * @return bool Succès
     */
    public function logSecurityEvent($user_id, $event, $raison = null)
    {
        // Convertir array en JSON si nécessaire
        if (is_array($raison)) {
            $raison = json_encode($raison);
        }
        return $this->log($user_id, $event, 'security', null, null, 'success', $raison);
    }

    /**
     * Logger une tentative d'accès refusée
     * 
     * @param int $user_id ID utilisateur
     * @param string $action Action tentée
     * @param string $ressource Ressource
     * @param string $permission_required Permission requise
     * @return bool Succès
     */
    public function logAccessDenied($user_id, $action, $ressource, $permission_required)
    {
        $raison = "Permission requise: $permission_required";
        return $this->log($user_id, $action, $ressource, null, null, 'denied', $raison);
    }

    /**
     * Logger une création de ressource
     * 
     * @param int $user_id ID utilisateur
     * @param string $ressource Type de ressource
     * @param int $ressource_id ID créé
     * @param array $data Données insérées
     * @return bool Succès
     */
    public function logCreate($user_id, $ressource, $ressource_id, $data = null)
    {
        // Ne pas logger les mots de passe
        if (isset($data['password'])) {
            unset($data['password']);
        }

        return $this->log($user_id, 'create', $ressource, $ressource_id, $data, 'success');
    }

    /**
     * Logger une modification de ressource
     * 
     * @param int $user_id ID utilisateur
     * @param string $ressource Type de ressource
     * @param int $ressource_id ID modifié
     * @param array $before Données avant (optionnel)
     * @param array $after Données après (optionnel)
     * @return bool Succès
     */
    public function logUpdate($user_id, $ressource, $ressource_id, $before = null, $after = null)
    {
        $details = ['before' => $before, 'after' => $after];

        // Ne pas logger les mots de passe
        if ($before !== null && isset($before['password'])) {
            unset($before['password']);
        }
        if ($after !== null && isset($after['password'])) {
            unset($after['password']);
        }

        return $this->log($user_id, 'update', $ressource, $ressource_id, $details, 'success');
    }

    /**
     * Logger une suppression de ressource
     * 
     * @param int $user_id ID utilisateur
     * @param string $ressource Type de ressource
     * @param int $ressource_id ID supprimé
     * @param array $data Données supprimées (optionnel)
     * @return bool Succès
     */
    public function logDelete($user_id, $ressource, $ressource_id, $data = null)
    {
        return $this->log($user_id, 'delete', $ressource, $ressource_id, $data, 'success');
    }

    /**
     * Logger une authentification réussie
     * 
     * @param int $user_id ID utilisateur
     * @return bool Succès
     */
    public function logLogin($user_id)
    {
        return $this->log($user_id, 'login', 'auth', null, null, 'success');
    }

    /**
     * Logger une déconnexion
     * 
     * @param int $user_id ID utilisateur
     * @return bool Succès
     */
    public function logLogout($user_id)
    {
        return $this->log($user_id, 'logout', 'auth', null, null, 'success');
    }

    /**
     * Logger un changement de permission
     * 
     * @param int $user_id ID admin qui a fait le changement
     * @param int $role_id Rôle modifié
     * @param string $permission_code Permission modifiée
     * @param string $action 'assign' ou 'revoke'
     * @return bool Succès
     */
    public function logPermissionChange($user_id, $role_id, $permission_code, $action)
    {
        $details = [
            'role_id' => $role_id,
            'permission_code' => $permission_code,
            'action' => $action
        ];
        return $this->log($user_id, $action, 'permissions', $role_id, $details, 'success');
    }

    /**
     * Récupérer les logs d'audit filtrés
     * 
     * @param array $filters Filtres: {user_id, action, ressource, statut, date_from, date_to}
     * @param int $limit Nombre de résultats (max 1000)
     * @param int $offset Offset pour pagination
     * @return array Logs trouvés
     */
    public function getLogs($filters = [], $limit = 50, $offset = 0)
    {
        $limit = min($limit, 1000); // Max 1000 pour la perf
        
        $query = "SELECT * FROM audit_log WHERE 1=1";
        $params = [];

        // Filtres
        if (!empty($filters['user_id'])) {
            $query .= " AND utilisateur_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $query .= " AND action = :action";
            $params[':action'] = $filters['action'];
        }

        if (!empty($filters['ressource'])) {
            $query .= " AND ressource = :ressource";
            $params[':ressource'] = $filters['ressource'];
        }

        if (!empty($filters['statut'])) {
            $query .= " AND statut = :statut";
            $params[':statut'] = $filters['statut'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get logs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compter les logs filtrés
     * 
     * @param array $filters Filtres
     * @return int Nombre total
     */
    public function countLogs($filters = [])
    {
        $query = "SELECT COUNT(*) as count FROM audit_log WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $query .= " AND utilisateur_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $query .= " AND action = :action";
            $params[':action'] = $filters['action'];
        }

        if (!empty($filters['ressource'])) {
            $query .= " AND ressource = :ressource";
            $params[':ressource'] = $filters['ressource'];
        }

        if (!empty($filters['statut'])) {
            $query .= " AND statut = :statut";
            $params[':statut'] = $filters['statut'];
        }

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Count logs error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtenir les statistiques d'audit
     * 
     * @return array Statistiques globales
     */
    public function getStatistics()
    {
        try {
            $stats = [];

            // Total logs
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM audit_log");
            $stats['total_logs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Logs par action
            $stmt = $this->db->query("
                SELECT action, COUNT(*) as count
                FROM audit_log
                GROUP BY action
                ORDER BY count DESC
            ");
            $stats['by_action'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Logs par ressource
            $stmt = $this->db->query("
                SELECT ressource, COUNT(*) as count
                FROM audit_log
                GROUP BY ressource
                ORDER BY count DESC
            ");
            $stats['by_ressource'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Logs d'accès refusés
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM audit_log WHERE statut = 'denied'");
            $stats['access_denied'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Logs des 24 dernières heures
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM audit_log WHERE created_at >= NOW() - INTERVAL 1 DAY");
            $stats['last_24h'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return $stats;
        } catch (Exception $e) {
            error_log("Get statistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Exporter les logs en CSV
     * 
     * @param array $filters Filtres
     * @return string Contenu CSV
     */
    public function exportToCSV($filters = [])
    {
        $logs = $this->getLogs($filters, 10000); // Max 10000 pour export

        $csv = "ID,Utilisateur ID,Action,Ressource,Ressource ID,IP,Statut,Créé\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log['audit_id'],
                $log['utilisateur_id'] ?? '',
                $log['action'],
                $log['ressource'],
                $log['ressource_id'] ?? '',
                $log['ip_address'],
                $log['statut'],
                $log['created_at']
            ]) . "\n";
        }

        return $csv;
    }

    /**
     * Obtenir l'adresse IP du client
     * 
     * @return string IP address
     */
    private function getClientIP()
    {
        $ip = '';

        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return trim($ip);
    }

    /**
     * Activer/désactiver le logging
     * 
     * @param bool $enabled État du logging
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Vérifier si le logging est activé
     * 
     * @return bool État du logging
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}
