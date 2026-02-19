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

                    // Retirer le mot de passe de la réponse
                    unset($user['password']);

                    http_response_code(200);
                    echo json_encode([
                        'message' => 'Authentification réussie',
                        'token' => $token,
                        'user' => $user
                    ]);
                } else {
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
                // Logout
                http_response_code(200);
                echo json_encode(['message' => 'Déconnexion réussie']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint d\'authentification non trouvé']);
            }
            break;
        case 'utilisateurs':
            $utilisateur = new Utilisateur($db);
            
            if ($method == 'POST') {
                // Valider le mot de passe avant de créer l'utilisateur
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
                // Récupérer un utilisateur par ID
                $user = $utilisateur->getById($resource);
                if ($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Utilisateur non trouvé']);
                }
            } elseif ($method == 'GET') {
                // Récupérer tous les utilisateurs
                $users = $utilisateur->getAll();
                echo json_encode($users);
            } elseif ($method == 'PUT' && !empty($resource)) {
                // Mettre à jour un utilisateur
                if ($utilisateur->update($resource, $input)) {
                    echo json_encode(['message' => 'Utilisateur mis à jour']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                // Supprimer un utilisateur
                if ($utilisateur->delete($resource)) {
                    echo json_encode(['message' => 'Utilisateur supprimé']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la suppression']);
                }
            }
            break;

        // ===== MENUS =====
        case 'menus':
            $menu = new Menu($db);
            
            if ($method == 'POST') {
                if ($menu->create($input)) {
                    http_response_code(201);
                    echo json_encode(['message' => 'Menu créé avec succès']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la création']);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                $menuData = $menu->getById($resource);
                if ($menuData) {
                    echo json_encode($menuData);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Menu non trouvé']);
                }
            } elseif ($method == 'GET') {
                $regime = $_GET['regime'] ?? null;
                if ($regime) {
                    $menus = $menu->getByRegime($regime);
                } else {
                    $menus = $menu->getAll();
                }
                echo json_encode($menus);
            } elseif ($method == 'PUT' && !empty($resource)) {
                if ($menu->update($resource, $input)) {
                    echo json_encode(['message' => 'Menu mis à jour']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                if ($menu->delete($resource)) {
                    echo json_encode(['message' => 'Menu supprimé']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la suppression']);
                }
            }
            break;

        // ===== COMMANDES =====
        case 'commandes':
            $commandante = new Commandante($db);
            
            if ($method == 'POST') {
                if ($commandante->create($input)) {
                    http_response_code(201);
                    echo json_encode(['message' => 'Commande créée avec succès']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la création']);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                $commande = $commandante->getById($resource);
                if ($commande) {
                    echo json_encode($commande);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Commande non trouvée']);
                }
            } elseif ($method == 'GET') {
                $commandes = $commandante->getAll();
                echo json_encode($commandes);
            } elseif ($method == 'PUT' && !empty($resource)) {
                if ($commandante->update($resource, $input)) {
                    echo json_encode(['message' => 'Commande mise à jour']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                if ($commandante->delete($resource)) {
                    echo json_encode(['message' => 'Commande supprimée']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la suppression']);
                }
            }
            break;

        // ===== AVIS =====
        case 'avis':
            $avis = new Avis($db);
            
            if ($method == 'POST') {
                if ($avis->create($input)) {
                    http_response_code(201);
                    echo json_encode(['message' => 'Avis créé avec succès']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la création']);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                $aviData = $avis->getById($resource);
                if ($aviData) {
                    echo json_encode($aviData);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Avis non trouvé']);
                }
            } elseif ($method == 'GET') {
                $avisList = $avis->getAll();
                echo json_encode($avisList);
            } elseif ($method == 'DELETE' && !empty($resource)) {
                if ($avis->delete($resource)) {
                    echo json_encode(['message' => 'Avis supprimé']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la suppression']);
                }
            }
            break;

        // ===== CONTACT =====
        case 'contact':
            $contact = new Contact($db);
            
            if ($method == 'POST') {
                if ($contact->create($input)) {
                    http_response_code(201);
                    echo json_encode(['message' => 'Message envoyé avec succès']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de l\'envoi']);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                $contactData = $contact->getById($resource);
                if ($contactData) {
                    echo json_encode($contactData);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Message non trouvé']);
                }
            } elseif ($method == 'GET') {
                $messages = $contact->getAll();
                echo json_encode($messages);
            } elseif ($method == 'DELETE' && !empty($resource)) {
                if ($contact->delete($resource)) {
                    echo json_encode(['message' => 'Message supprimé']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la suppression']);
                }
            }
            break;

        // ===== PLATS =====
        case 'plats':
            $plat = new Plat($db);
            
            if ($method == 'POST') {
                if ($plat->create($input)) {
                    http_response_code(201);
                    echo json_encode(['message' => 'Plat créé avec succès']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la création']);
                }
            } elseif ($method == 'GET' && !empty($resource)) {
                $platData = $plat->getById($resource);
                if ($platData) {
                    $platData['allergenes'] = $plat->getPlatAllergenes($resource);
                    echo json_encode($platData);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Plat non trouvé']);
                }
            } elseif ($method == 'GET') {
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
                if ($plat->update($resource, $input)) {
                    echo json_encode(['message' => 'Plat mis à jour']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                if ($plat->delete($resource)) {
                    echo json_encode(['message' => 'Plat supprimé']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la suppression']);
                }
            }
            break;

        // ===== ALLERGENES =====
        case 'allergenes':
            $allergen = new Allergen($db);
            
            if ($method == 'POST') {
                if ($allergen->create($input)) {
                    http_response_code(201);
                    echo json_encode(['message' => 'Allergène créé avec succès']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la création']);
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
                if ($allergen->update($resource, $input)) {
                    echo json_encode(['message' => 'Allergène mis à jour']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la mise à jour']);
                }
            } elseif ($method == 'DELETE' && !empty($resource)) {
                if ($allergen->delete($resource)) {
                    echo json_encode(['message' => 'Allergène supprimé']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Erreur lors de la suppression']);
                }
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
