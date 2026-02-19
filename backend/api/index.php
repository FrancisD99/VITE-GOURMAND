<?php
// Activer l'affichage des erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inclusion des fichiers de configuration et modèles
try {
    require_once __DIR__ . '/../config/Database.php';
    require_once __DIR__ . '/../config/PasswordValidator.php';
    require_once __DIR__ . '/../config/JWTHandler.php';
    require_once __DIR__ . '/../config/AuthHelper.php';
    require_once __DIR__ . '/../config/PermissionManager.php';
    require_once __DIR__ . '/../config/AuditLogger.php';
    require_once __DIR__ . '/../models/Utilisateur.php';
    require_once __DIR__ . '/../models/Menu.php';
    require_once __DIR__ . '/../models/Commandante.php';
    require_once __DIR__ . '/../models/Avis.php';
    require_once __DIR__ . '/../models/Contact.php';
    require_once __DIR__ . '/../models/Plat.php';
    require_once __DIR__ . '/../models/Allergen.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur d\'inclusion: ' . $e->getMessage()]);
    exit();
}

// Connexion à la base de données
try {
    $database = new Database();
    $db = $database->connect();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion BD: ' . $e->getMessage()]);
    exit();
}

// Récupérer la méthode et le chemin de la requête
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(str_replace('/api', '', $path), '/');

// Debug: log le chemin
error_log("PATH: " . $path . " | METHOD: " . $method);

$pathParts = explode('/', $path);
$action = $pathParts[0] ?? '';
$resource = $pathParts[1] ?? '';

// Debug: log l'action
error_log("ACTION: " . $action . " | RESOURCE: " . $resource);

// Récupérer le body (JSON)
$input = json_decode(file_get_contents('php://input'), true);

// Routing
try {
    switch ($action) {
        // ===== AUTHENTIFICATION =====
        case 'auth':
            if ($method == 'POST' && $resource == 'login') {
                // Login
                if (empty($input['email']) || empty($input['password'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Email et mot de passe requis']);
                    break;
                }

                $utilisateur = new Utilisateur($db);
                $user = $utilisateur->authenticate($input['email'], $input['password']);

                if ($user) {
                    // Générer un JWT
                    $jwt = new JWTHandler();
                    $token = $jwt->encode([
                        'utilisateur_id' => $user['utilisateur_id'],
                        'email' => $user['email'],
                        'nom' => $user['nom'],
                        'prenom' => $user['prenom'],
                        'role_id' => $user['role_id']
                    ]);

                    // Log successful login
                    $audit = new AuditLogger($db);
                    $audit->logLogin($user['utilisateur_id']);

                    // Retirer le mot de passe de la réponse
                    unset($user['password']);

                    http_response_code(200);
                    echo json_encode([
                        'message' => 'Authentification réussie',
                        'token' => $token,
                        'user' => $user
                    ]);
                } else {
                    // Log failed login attempt
                    $audit = new AuditLogger($db);
                    $audit->logSecurityEvent(null, 'failed_login', ['email' => $input['email']]);

                    http_response_code(401);
                    echo json_encode(['error' => 'Email ou mot de passe incorrect']);
                }
            } elseif ($method == 'GET' && $resource == 'me') {
                // Récupérer l'utilisateur connecté
                $jwt = new JWTHandler();
                $token = JWTHandler::getTokenFromRequest();

                if (!$token || !$jwt->isValid($token)) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Token invalide ou expiré']);
                    break;
                }

                $decoded = $jwt->decode($token);
                $utilisateur = new Utilisateur($db);
                $user = $utilisateur->getById($decoded['utilisateur_id']);

                if ($user) {
                    unset($user['password']);
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Utilisateur non trouvé']);
                }
            } elseif ($method == 'POST' && $resource == 'logout') {
                // Logout - Log the event
                try {
                    $user = AuthHelper::requireAuth();
                    $audit = new AuditLogger($db);
                    $audit->logLogout($user['utilisateur_id']);
                    
                    http_response_code(200);
                    echo json_encode(['message' => 'Déconnexion réussie']);
                } catch (Exception $e) {
                    // Even if token is invalid, logout is considered successful
                    http_response_code(200);
                    echo json_encode(['message' => 'Déconnexion réussie']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint d\'authentification non trouvé']);
            }
            break;
        case 'utilisateurs':
            $utilisateur = new Utilisateur($db);
            
            if ($method == 'POST') {
                // POST: Création de compte - PUBLIC (inscription)
                if (empty($input['password'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Mot de passe obligatoire']);
                } else {
                    $passwordValidation = PasswordValidator::validate($input['password']);
                    
                    if (!$passwordValidation['isValid']) {
                        http_response_code(400);
                        echo json_encode([
                            'error' => 'Mot de passe non sécurisé',
                            'details' => $passwordValidation['errors'],
                            'requirements' => PasswordValidator::getRequirements()
                        ]);
                    } else if ($utilisateur->create($input)) {
                        http_response_code(201);
                        echo json_encode(['message' => 'Utilisateur créé avec succès']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la création']);
                    }
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                // GET ID: Récupérer un utilisateur par ID - PUBLIC
                $user = $utilisateur->getById($resource);
                if ($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Utilisateur non trouvé']);
                }
            } elseif ($method == 'GET') {
                // GET: Récupérer tous les utilisateurs - PUBLIC
                $users = $utilisateur->getAll();
                echo json_encode($users);
            } elseif ($method == 'PUT' && !empty($resource)) {
                // PUT: Mettre à jour un utilisateur - PROTÉGÉ (JWT requis)
                try {
                    $user = AuthHelper::requireAuth();
                    // Utilisateur ne peut modifier que son profil, sauf s'il a la permission users.update
                    AuthHelper::requireAccessToResource($user, $resource, 'users.update', 'users.update.own', $db);
                    
                    $old_data = $utilisateur->getById($resource);
                    if ($utilisateur->update($resource, $input)) {
                        $audit = new AuditLogger($db);
                        $audit->logUpdate($user['utilisateur_id'], 'utilisateurs', $resource, $old_data, $input);
                        echo json_encode(['message' => 'Utilisateur mis à jour']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'update', 'utilisateurs', 'utilisateurs.update');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                // DELETE: Supprimer un utilisateur - PROTÉGÉ (Admin uniquement)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'users.delete', $db);
                    
                    $data = $utilisateur->getById($resource);
                    if ($utilisateur->delete($resource)) {
                        $audit = new AuditLogger($db);
                        $audit->logDelete($user['utilisateur_id'], 'utilisateurs', $resource, $data);
                        echo json_encode(['message' => 'Utilisateur supprimé']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la suppression']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'delete', 'utilisateurs', 'utilisateurs.delete');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            }
            break;

        // ===== MENUS =====
        case 'menus':
            $menu = new Menu($db);
            
            if ($method == 'POST') {
                // POST: Créer menu - PROTÉGÉ (Admin uniquement)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'menus.create', $db);
                    
                    if ($menu->create($input)) {
                        $audit = new AuditLogger($db);
                        $lastId = $db->lastInsertId();
                        $audit->logCreate($user['utilisateur_id'], 'menus', $lastId, $input);
                        http_response_code(201);
                        echo json_encode(['message' => 'Menu créé avec succès']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la création']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'create', 'menus', 'menus.create');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                // GET ID: Récupérer un menu par ID - PUBLIC
                $menuData = $menu->getById($resource);
                if ($menuData) {
                    echo json_encode($menuData);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Menu non trouvé']);
                }
            } elseif ($method == 'GET') {
                // GET: Récupérer tous les menus - PUBLIC
                $regime = $_GET['regime'] ?? null;
                if ($regime) {
                    $menus = $menu->getByRegime($regime);
                } else {
                    $menus = $menu->getAll();
                }
                echo json_encode($menus);
            } elseif ($method == 'PUT' && !empty($resource)) {
                // PUT: Mettre à jour menu - PROTÉGÉ (Admin uniquement)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'menus.update', $db);
                    
                    $old_data = $menu->getById($resource);
                    if ($menu->update($resource, $input)) {
                        $audit = new AuditLogger($db);
                        $audit->logUpdate($user['utilisateur_id'], 'menus', $resource, $old_data, $input);
                        echo json_encode(['message' => 'Menu mis à jour']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'update', 'menus', 'menus.update');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                // DELETE: Supprimer menu - PROTÉGÉ (Admin uniquement)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'menus.delete', $db);
                    
                    $data = $menu->getById($resource);
                    if ($menu->delete($resource)) {
                        $audit = new AuditLogger($db);
                        $audit->logDelete($user['utilisateur_id'], 'menus', $resource, $data);
                        echo json_encode(['message' => 'Menu supprimé']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la suppression']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'delete', 'menus', 'menus.delete');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            }
            break;

        // ===== COMMANDES =====
        case 'commandes':
            $commandante = new Commandante($db);
            
            if ($method == 'POST') {
                // POST: Créer commande - PROTÉGÉ (JWT requis)
                try {
                    $user = AuthHelper::requireAuth();
                    // Ajouter automatiquement l'ID utilisateur à la commande
                    $input['utilisateur_id'] = $user['utilisateur_id'];
                    
                    if ($commandante->create($input)) {
                        $audit = new AuditLogger($db);
                        $lastId = $db->lastInsertId();
                        $audit->logCreate($user['utilisateur_id'], 'commandantes', $lastId, $input);
                        http_response_code(201);
                        echo json_encode(['message' => 'Commande créée avec succès']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la création']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'create', 'commandantes', 'commandantes.create');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                // GET ID: Récupérer une commande par ID - Protégé
                try {
                    $user = AuthHelper::requireAuth();
                    $commande = $commandante->getById($resource);
                    
                    if ($commande) {
                        // Vérifier l'accès avec les permissions granulaires
                        AuthHelper::requireAccessToResource($user, $commande['utilisateur_id'], 'commandes.read', 'commandes.read.own', $db);
                        echo json_encode($commande);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Commande non trouvée']);
                    }
                } catch (Exception $e) {
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'GET') {
                // GET: Récupérer les commandes - Protégé
                try {
                    $user = AuthHelper::requireAuth();
                    $pm = AuthHelper::getPermissionManager($db);
                    // Admin voit toutes les commandes (commandes.list), clients voient les leurs (commandes.list.own)
                    if ($pm->hasPermission($user, 'commandes.list')) {
                        $commandes = $commandante->getAll();
                    } elseif ($pm->hasPermission($user, 'commandes.list.own')) {
                        // À améliorer: getByUserId() pour récupérer les commandes de l'utilisateur
                        $commandes = $commandante->getAll(); // Temporaire: à améliorer
                    } else {
                        AuthHelper::sendError('Vous n\'avez pas accès aux commandes', 403);
                    }
                    echo json_encode($commandes);
                } catch (Exception $e) {
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'PUT' && !empty($resource)) {
                // PUT: Mettre à jour commande - Protégé
                try {
                    $user = AuthHelper::requireAuth();
                    $commande = $commandante->getById($resource);
                    
                    if (!$commande) {
                        http_response_code(404);
                        echo json_encode(['error' => 'Commande non trouvée']);
                        break;
                    }
                    
                    // Vérifier l'ownership ou les permissions granulaires
                    AuthHelper::requireAccessToResource($user, $commande['utilisateur_id'], 'commandes.update', 'commandes.update.own', $db);
                    
                    $old_data = $commande;
                    if ($commandante->update($resource, $input)) {
                        $audit = new AuditLogger($db);
                        $audit->logUpdate($user['utilisateur_id'], 'commandantes', $resource, $old_data, $input);
                        echo json_encode(['message' => 'Commande mise à jour']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'update', 'commandantes', 'commandantes.update');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                // DELETE: Supprimer commande - Protégé
                try {
                    $user = AuthHelper::requireAuth();
                    $commande = $commandante->getById($resource);
                    
                    if (!$commande) {
                        http_response_code(404);
                        echo json_encode(['error' => 'Commande non trouvée']);
                        break;
                    }
                    
                    // Vérifier l'ownership ou les permissions granulaires
                    AuthHelper::requireAccessToResource($user, $commande['utilisateur_id'], 'commandes.delete', 'commandes.delete.own', $db);
                    
                    $data = $commande;
                    if ($commandante->delete($resource)) {
                        $audit = new AuditLogger($db);
                        $audit->logDelete($user['utilisateur_id'], 'commandantes', $resource, $data);
                        echo json_encode(['message' => 'Commande supprimée']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la suppression']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'delete', 'commandantes', 'commandantes.delete');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            }
            break;

        // ===== AVIS =====
        case 'avis':
            $avis = new Avis($db);
            
            if ($method == 'POST') {
                // POST: Créer avis - PROTÉGÉ (JWT requis)
                try {
                    $user = AuthHelper::requireAuth();
                    // Ajouter automatiquement l'ID utilisateur à l'avis
                    $input['utilisateur_id'] = $user['utilisateur_id'];
                    
                    if ($avis->create($input)) {
                        $audit = new AuditLogger($db);
                        $lastId = $db->lastInsertId();
                        $audit->logCreate($user['utilisateur_id'], 'avis', $lastId, $input);
                        http_response_code(201);
                        echo json_encode(['message' => 'Avis créé avec succès']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la création']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'create', 'avis', 'avis.create');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                // GET ID: Récupérer un avis par ID - PUBLIC
                $aviData = $avis->getById($resource);
                if ($aviData) {
                    echo json_encode($aviData);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Avis non trouvé']);
                }
            } elseif ($method == 'GET') {
                // GET: Récupérer tous les avis - PUBLIC
                $avisList = $avis->getAll();
                echo json_encode($avisList);
            } elseif ($method == 'DELETE' && !empty($resource)) {
                // DELETE: Supprimer avis - Protégé (owner ou admin)
                try {
                    $user = AuthHelper::requireAuth();
                    $aviData = $avis->getById($resource);
                    
                    if (!$aviData) {
                        http_response_code(404);
                        echo json_encode(['error' => 'Avis non trouvé']);
                        break;
                    }
                    
                    // Vérifier l'ownership ou les permissions granulaires
                    AuthHelper::requireAccessToResource($user, $aviData['utilisateur_id'], 'avis.delete', 'avis.delete.own', $db);
                    
                    $data = $aviData;
                    if ($avis->delete($resource)) {
                        $audit = new AuditLogger($db);
                        $audit->logDelete($user['utilisateur_id'], 'avis', $resource, $data);
                        echo json_encode(['message' => 'Avis supprimé']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la suppression']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'delete', 'avis', 'avis.delete');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            }
            break;

        // ===== CONTACT =====
        case 'contact':
            $contact = new Contact($db);
            
            if ($method == 'POST') {
                // POST: Envoyer message contact - PUBLIC
                if ($contact->create($input)) {
                    http_response_code(201);
                    echo json_encode(['message' => 'Message envoyé avec succès']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de l\'envoi']);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                // GET ID: Récupérer un message contact - PROTÉGÉ (Admin)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'contact.read', $db);
                    
                    $contactData = $contact->getById($resource);
                    if ($contactData) {
                        echo json_encode($contactData);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Message non trouvé']);
                    }
                } catch (Exception $e) {
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'GET') {
                // GET: Récupérer tous les messages - PROTÉGÉ (Admin)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'contact.list', $db);
                    
                    $messages = $contact->getAll();
                    echo json_encode($messages);
                } catch (Exception $e) {
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                // DELETE: Supprimer message - PROTÉGÉ (Admin)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'contact.delete', $db);
                    
                    $data = $contact->getById($resource);
                    if ($contact->delete($resource)) {
                        $audit = new AuditLogger($db);
                        $audit->logDelete($user['utilisateur_id'], 'contact', $resource, $data);
                        echo json_encode(['message' => 'Message supprimé']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la suppression']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'delete', 'contact', 'contact.delete');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            }
            break;

        // ===== PLATS =====
        case 'plats':
            $plat = new Plat($db);
            
            if ($method == 'POST') {
                // POST: Créer plat - PROTÉGÉ (Admin uniquement)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'plats.create', $db);
                    
                    if ($plat->create($input)) {
                        $audit = new AuditLogger($db);
                        $lastId = $db->lastInsertId();
                        $audit->logCreate($user['utilisateur_id'], 'plats', $lastId, $input);
                        http_response_code(201);
                        echo json_encode(['message' => 'Plat créé avec succès']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la création']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'create', 'plats', 'plats.create');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                // GET ID: Récupérer un plat par ID - PUBLIC
                $platData = $plat->getById($resource);
                if ($platData) {
                    $platData['allergenes'] = $plat->getPlatAllergenes($resource);
                    echo json_encode($platData);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Plat non trouvé']);
                }
            } elseif ($method == 'GET') {
                // GET: Récupérer tous les plats - PUBLIC
                $regime_id = $_GET['regime_id'] ?? null;
                $search = $_GET['search'] ?? null;
                
                if ($regime_id) {
                    $plats = $plat->getByRegime($regime_id);
                } elseif ($search) {
                    $plats = $plat->search($search);
                } else {
                    $plats = $plat->getAll();
                }
                echo json_encode($plats);
            } elseif ($method == 'PUT' && !empty($resource)) {
                // PUT: Mettre à jour plat - PROTÉGÉ (Admin uniquement)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'plats.update', $db);
                    
                    $old_data = $plat->getById($resource);
                    if ($plat->update($resource, $input)) {
                        $audit = new AuditLogger($db);
                        $audit->logUpdate($user['utilisateur_id'], 'plats', $resource, $old_data, $input);
                        echo json_encode(['message' => 'Plat mis à jour']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'update', 'plats', 'plats.update');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                // DELETE: Supprimer plat - PROTÉGÉ (Admin uniquement)
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'plats.delete', $db);
                    
                    $data = $plat->getById($resource);
                    if ($plat->delete($resource)) {
                        $audit = new AuditLogger($db);
                        $audit->logDelete($user['utilisateur_id'], 'plats', $resource, $data);
                        echo json_encode(['message' => 'Plat supprimé']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la suppression']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'delete', 'plats', 'plats.delete');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            }
            break;

        // ===== ALLERGENES =====
        case 'allergenes':
            $allergen = new Allergen($db);
            
            if ($method == 'POST') {
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'allergenes.create', $db);
                    
                    if ($allergen->create($input)) {
                        $audit = new AuditLogger($db);
                        $lastId = $db->lastInsertId();
                        $audit->logCreate($user['utilisateur_id'], 'allergenes', $lastId, $input);
                        http_response_code(201);
                        echo json_encode(['message' => 'Allergène créé avec succès']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la création']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'create', 'allergenes', 'allergenes.create');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                $allergenData = $allergen->getById($resource);
                if ($allergenData) {
                    echo json_encode($allergenData);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Allergène non trouvé']);
                }
            } elseif ($method == 'GET') {
                $allergenes = $allergen->getAll();
                echo json_encode($allergenes);
            } elseif ($method == 'PUT' && !empty($resource)) {
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'allergenes.update', $db);
                    
                    $old_data = $allergen->getById($resource);
                    if ($allergen->update($resource, $input)) {
                        $audit = new AuditLogger($db);
                        $audit->logUpdate($user['utilisateur_id'], 'allergenes', $resource, $old_data, $input);
                        echo json_encode(['message' => 'Allergène mis à jour']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'update', 'allergenes', 'allergenes.update');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                try {
                    $user = AuthHelper::requireAuth();
                    AuthHelper::requirePermission($user, 'allergenes.delete', $db);
                    
                    $data = $allergen->getById($resource);
                    if ($allergen->delete($resource)) {
                        $audit = new AuditLogger($db);
                        $audit->logDelete($user['utilisateur_id'], 'allergenes', $resource, $data);
                        echo json_encode(['message' => 'Allergène supprimé']);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Erreur lors de la suppression']);
                    }
                } catch (Exception $e) {
                    if (isset($user)) {
                        $audit = new AuditLogger($db);
                        $audit->logAccessDenied($user['utilisateur_id'], 'delete', 'allergenes', 'allergenes.delete');
                    }
                    AuthHelper::sendError($e->getMessage(), 401);
                }
            }
            break;

        // ===== AUDIT LOGS - ADMIN ONLY =====
        case 'audit-logs':
            try {
                // Vérifier authentification et autorisation
                $user = AuthHelper::requireAuth();
                
                // Vérifier permission admin.logs (voir les logs d'audit)
                AuthHelper::requirePermission($user, 'admin.logs', $db);
                
                $audit = new AuditLogger($db);
                
                if ($method == 'GET' && !empty($resource)) {
                    // GET /audit-logs/{id} - Détail d'un log
                    $filters = ['audit_id' => (int)$resource];
                    $logs = $audit->getLogs($filters, 1, 0);
                    
                    if (!empty($logs)) {
                        echo json_encode($logs[0]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Log d\'audit non trouvé']);
                    }
                } elseif ($method == 'GET') {
                    // GET /audit-logs - Lister les logs avec filtres
                    $filters = [];
                    
                    // Filtres optionnels
                    if (!empty($_GET['user_id'])) {
                        $filters['utilisateur_id'] = (int)$_GET['user_id'];
                    }
                    if (!empty($_GET['action'])) {
                        $filters['action'] = $_GET['action'];
                    }
                    if (!empty($_GET['ressource'])) {
                        $filters['ressource'] = $_GET['ressource'];
                    }
                    if (!empty($_GET['statut'])) {
                        $filters['statut'] = $_GET['statut']; // success, denied, error
                    }
                    if (!empty($_GET['date_from'])) {
                        $filters['date_from'] = $_GET['date_from'];
                    }
                    if (!empty($_GET['date_to'])) {
                        $filters['date_to'] = $_GET['date_to'];
                    }
                    
                    // Paramètres de pagination
                    $limit = (int)($_GET['limit'] ?? 50);
                    $offset = (int)($_GET['offset'] ?? 0);
                    
                    // Limiter à 100 logs max par requête
                    $limit = min($limit, 100);
                    
                    // Gestion des statistiques
                    if (!empty($_GET['stats'])) {
                        $stats = $audit->getStatistics();
                        echo json_encode($stats);
                    } 
                    // Gestion de l'export CSV
                    elseif (!empty($_GET['export']) && $_GET['export'] === 'csv') {
                        header('Content-Type: text/csv; charset=utf-8');
                        header('Content-Disposition: attachment; filename="audit-logs-' . date('Y-m-d-His') . '.csv"');
                        
                        $csv = $audit->exportToCSV($filters);
                        echo $csv;
                    } 
                    // Récupération normale avec pagination
                    else {
                        $logs = $audit->getLogs($filters, $limit, $offset);
                        $total = $audit->countLogs($filters);
                        
                        echo json_encode([
                            'logs' => $logs,
                            'total' => $total,
                            'limit' => $limit,
                            'offset' => $offset,
                            'pages' => ceil($total / $limit)
                        ]);
                    }
                } else {
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
                }
            } catch (Exception $e) {
                AuthHelper::sendError($e->getMessage(), 401);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint non trouvé']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
