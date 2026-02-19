<?php

/**
 * AuthHelper - Fonctions utilitaires pour l'authentification et autorisation
 * 
 * Cette classe fournit des méthodes pour:
 * - Vérifier qu'un token JWT valide est fourni
 * - Extraire l'utilisateur authentifié
 * - Vérifier les rôles (RBAC)
 * - Vérifier les permissions granulaires
 */
class AuthHelper
{
    private static $jwt = null;
    private static $permission_manager = null;

    /**
     * Initialiser le JWT Handler
     */
    private static function initJWT()
    {
        if (self::$jwt === null) {
            require_once __DIR__ . '/JWTHandler.php';
            self::$jwt = new JWTHandler();
        }
    }

    /**
     * Initialiser le PermissionManager
     */
    private static function initPermissionManager($db)
    {
        if (self::$permission_manager === null) {
            require_once __DIR__ . '/PermissionManager.php';
            self::$permission_manager = new PermissionManager($db);
        }
    }

    /**
     * Vérifier qu'un token JWT valide est fourni
     * Retourne les données de l'utilisateur après validation
     * 
     * @return array Données décodées du token { utilisateur_id, email, ... }
     * @throws Exception Si pas de token ou token invalide
     */
    public static function requireAuth()
    {
        self::initJWT();

        $token = JWTHandler::getTokenFromRequest();

        if (!$token) {
            http_response_code(401);
            throw new Exception('Missing Authorization header');
        }

        if (!self::$jwt->isValid($token)) {
            http_response_code(401);
            throw new Exception('Invalid or expired token');
        }

        $decoded = self::$jwt->decode($token);

        if (!isset($decoded['utilisateur_id'])) {
            http_response_code(401);
            throw new Exception('Invalid token payload');
        }

        return $decoded;
    }

    /**
     * Optionnel: Vérifier que l'utilisateur a un rôle spécifique
     * 
     * @param array $user Données utilisateur (de requireAuth())
     * @param int $required_role Role ID requis (ex: 1 pour admin)
     * @throws Exception Si utilisateur n'a pas le rôle requis
     */
    public static function requireRole($user, $required_role)
    {
        if (!isset($user['role_id']) || $user['role_id'] !== $required_role) {
            http_response_code(403);
            throw new Exception('Forbidden: Insufficient permissions');
        }
    }

    /**
     * Vérifier que l'utilisateur n'accède qu'à ses propres données
     * Utile pour les commandes personnelles, les profils, etc.
     * 
     * @param array $user Données utilisateur authentifié
     * @param int $resource_user_id ID utilisateur de la ressource à accéder
     * @throws Exception Si l'utilisateur n'a pas accès
     */
    public static function requireOwnershipOrAdmin($user, $resource_user_id)
    {
        $is_admin = isset($user['role_id']) && $user['role_id'] === 1;
        $is_owner = $user['utilisateur_id'] === $resource_user_id;

        if (!$is_admin && !$is_owner) {
            http_response_code(403);
            throw new Exception('Forbidden: You do not have access to this resource');
        }
    }

    /**
     * Vérifier qu'un utilisateur a une permission spécifique (Étape 5)
     * 
     * @param array $user Données utilisateur
     * @param string $permission_code Code de la permission (ex: 'plats.create')
     * @param PDO $db Connexion base de données
     * @throws Exception Si l'utilisateur n'a pas la permission
     */
    public static function requirePermission($user, $permission_code, $db)
    {
        self::initPermissionManager($db);

        if (!self::$permission_manager->hasPermission($user, $permission_code)) {
            http_response_code(403);
            throw new Exception('Forbidden: You do not have the required permission');
        }
    }

    /**
     * Vérifier qu'un utilisateur a au moins une des permissions
     * 
     * @param array $user Données utilisateur
     * @param array $permission_codes Array de codes
     * @param PDO $db Connexion base de données
     * @throws Exception Si aucune permission n'est présente
     */
    public static function requireAnyPermission($user, $permission_codes, $db)
    {
        self::initPermissionManager($db);

        if (!self::$permission_manager->hasAnyPermission($user, $permission_codes)) {
            http_response_code(403);
            throw new Exception('Forbidden: Insufficient permissions');
        }
    }

    /**
     * Récupérer le gestionnaire de permissions (pour usage avancé)
     * 
     * @param PDO $db Connexion base de données
     * @return PermissionManager Instance du gestionnaire
     */
    public static function getPermissionManager($db)
    {
        self::initPermissionManager($db);
        return self::$permission_manager;
    }

    /**
     * Retourner une erreur JSON avec le bon statut HTTP
     * 
     * @param string $message Message d'erreur
     * @param int $status_code HTTP status code (default 400)
     */
    public static function sendError($message, $status_code = 400)
    {
        http_response_code($status_code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit();
    }

    /**
     * Retourner une réponse JSON de succès
     * 
     * @param mixed $data Données à retourner
     * @param int $status_code HTTP status code (default 200)
     */
    public static function sendSuccess($data, $status_code = 200)
    {
        http_response_code($status_code);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit();
    }

    /**
     * Vérifier l'accès à une ressource personnelle
     * Utilise les permissions granulaires (xxx.read, xxx.read.own, xxx.update, xxx.update.own, etc)
     * 
     * @param array $user Données utilisateur
     * @param string $resource_owner_id ID du propriétaire de la ressource
     * @param string $permission_full Permission pour accès complet (ex: 'commandes.read')
     * @param string $permission_own Permission pour accès personnel (ex: 'commandes.read.own')
     * @param PDO $db Connexion base de données
     * @throws Exception Si l'utilisateur n'a pas accès
     */
    public static function requireAccessToResource($user, $resource_owner_id, $permission_full, $permission_own, $db)
    {
        self::initPermissionManager($db);

        // Si c'est le propriétaire ET il a la permission "own"
        if ($user['utilisateur_id'] == $resource_owner_id && 
            self::$permission_manager->hasPermission($user, $permission_own)) {
            return true;
        }

        // Si l'utilisateur a la permission "full" (accès à tous)
        if (self::$permission_manager->hasPermission($user, $permission_full)) {
            return true;
        }

        http_response_code(403);
        throw new Exception('Forbidden: You do not have access to this resource');
    }
}
