<?php

/**
 * PermissionManager - Gestion des permissions granulaires et RBAC
 * 
 * Classe pour vérifier les permissions basées sur les rôles
 */
class PermissionManager
{
    private $db = null;
    private $permissions_cache = [];

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Vérifier si un utilisateur a une permission spécifique
     * 
     * @param array $user Données de l'utilisateur (from JWT)
     * @param string $permission_code Code de la permission (ex: 'plats.create')
     * @return bool Vrai si l'utilisateur a la permission
     */
    public function hasPermission($user, $permission_code)
    {
        if (!isset($user['role_id'])) {
            return false;
        }

        // Vérifier dans le cache
        $cache_key = $user['role_id'] . ':' . $permission_code;
        if (isset($this->permissions_cache[$cache_key])) {
            return $this->permissions_cache[$cache_key];
        }

        try {
            $query = "
                SELECT COUNT(*) as count
                FROM role_permission rp
                JOIN permission p ON rp.permission_id = p.permission_id
                WHERE rp.role_id = :role_id
                AND p.code = :permission_code
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':role_id', $user['role_id']);
            $stmt->bindParam(':permission_code', $permission_code);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $has_permission = $result['count'] > 0;

            // Mettre en cache
            $this->permissions_cache[$cache_key] = $has_permission;

            return $has_permission;
        } catch (Exception $e) {
            error_log("Permission check error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un utilisateur a au moins une des permissions
     * 
     * @param array $user Données utilisateur
     * @param array $permission_codes Array de codes de permission
     * @return bool Vrai si au moins une permission est présente
     */
    public function hasAnyPermission($user, $permission_codes)
    {
        foreach ($permission_codes as $code) {
            if ($this->hasPermission($user, $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifier si un utilisateur a toutes les permissions
     * 
     * @param array $user Données utilisateur
     * @param array $permission_codes Array de codes de permission
     * @return bool Vrai si toutes les permissions sont présentes
     */
    public function hasAllPermissions($user, $permission_codes)
    {
        foreach ($permission_codes as $code) {
            if (!$this->hasPermission($user, $code)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Récupérer toutes les permissions d'un utilisateur
     * 
     * @param array $user Données utilisateur
     * @return array Liste des codes de permission
     */
    public function getUserPermissions($user)
    {
        if (!isset($user['role_id'])) {
            return [];
        }

        try {
            $query = "
                SELECT p.code
                FROM role_permission rp
                JOIN permission p ON rp.permission_id = p.permission_id
                WHERE rp.role_id = :role_id
                ORDER BY p.code
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':role_id', $user['role_id']);
            $stmt->execute();

            $permissions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permissions[] = $row['code'];
            }

            return $permissions;
        } catch (Exception $e) {
            error_log("Get permissions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les permissions détaillées d'un rôle
     * 
     * @param int $role_id ID du rôle
     * @return array Permissions avec détails
     */
    public function getRolePermissions($role_id)
    {
        try {
            $query = "
                SELECT 
                    p.permission_id,
                    p.code,
                    p.libelle,
                    p.description,
                    p.categorie
                FROM role_permission rp
                JOIN permission p ON rp.permission_id = p.permission_id
                WHERE rp.role_id = :role_id
                ORDER BY p.categorie, p.code
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':role_id', $role_id);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get role permissions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lister toutes les permissions disponibles
     * 
     * @return array Toutes les permissions groupées par catégorie
     */
    public function getAllPermissions()
    {
        try {
            $query = "
                SELECT 
                    permission_id,
                    code,
                    libelle,
                    description,
                    categorie
                FROM permission
                ORDER BY categorie, code
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $permissions_by_category = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $category = $row['categorie'] ?? 'other';
                if (!isset($permissions_by_category[$category])) {
                    $permissions_by_category[$category] = [];
                }
                $permissions_by_category[$category][] = $row;
            }

            return $permissions_by_category;
        } catch (Exception $e) {
            error_log("Get all permissions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Assigner une permission à un rôle
     * 
     * @param int $role_id ID du rôle
     * @param string $permission_code Code de la permission
     * @return bool Succès de l'opération
     */
    public function assignPermission($role_id, $permission_code)
    {
        try {
            // Récupérer l'ID de la permission
            $stmt = $this->db->prepare("SELECT permission_id FROM permission WHERE code = :code");
            $stmt->bindParam(':code', $permission_code);
            $stmt->execute();
            $permission = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$permission) {
                return false;
            }

            // Assigner la permission
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO role_permission (role_id, permission_id)
                VALUES (:role_id, :permission_id)
            ");
            $stmt->bindParam(':role_id', $role_id);
            $stmt->bindParam(':permission_id', $permission['permission_id']);

            // Vider le cache
            $this->permissions_cache = [];

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Assign permission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Révoquer une permission d'un rôle
     * 
     * @param int $role_id ID du rôle
     * @param string $permission_code Code de la permission
     * @return bool Succès de l'opération
     */
    public function revokePermission($role_id, $permission_code)
    {
        try {
            // Récupérer l'ID de la permission
            $stmt = $this->db->prepare("SELECT permission_id FROM permission WHERE code = :code");
            $stmt->bindParam(':code', $permission_code);
            $stmt->execute();
            $permission = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$permission) {
                return false;
            }

            // Révoquer la permission
            $stmt = $this->db->prepare("
                DELETE FROM role_permission
                WHERE role_id = :role_id AND permission_id = :permission_id
            ");
            $stmt->bindParam(':role_id', $role_id);
            $stmt->bindParam(':permission_id', $permission['permission_id']);

            // Vider le cache
            $this->permissions_cache = [];

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Revoke permission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cloner les permissions d'un rôle vers un autre
     * 
     * @param int $source_role_id Rôle source
     * @param int $target_role_id Rôle cible
     * @return bool Succès de l'opération
     */
    public function cloneRolePermissions($source_role_id, $target_role_id)
    {
        try {
            // Récupérer les permissions du rôle source
            $stmt = $this->db->prepare("
                SELECT permission_id FROM role_permission WHERE role_id = :role_id
            ");
            $stmt->bindParam(':role_id', $source_role_id);
            $stmt->execute();
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Assigner au rôle cible
            $insert_stmt = $this->db->prepare("
                INSERT IGNORE INTO role_permission (role_id, permission_id)
                VALUES (:role_id, :permission_id)
            ");

            foreach ($permissions as $perm) {
                $insert_stmt->bindParam(':role_id', $target_role_id);
                $insert_stmt->bindParam(':permission_id', $perm['permission_id']);
                $insert_stmt->execute();
            }

            // Vider le cache
            $this->permissions_cache = [];

            return true;
        } catch (Exception $e) {
            error_log("Clone permissions error: " . $e->getMessage());
            return false;
        }
    }
}
