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
    require_once __DIR__ . '/../models/Utilisateur.php';
    require_once __DIR__ . '/../models/Menu.php';
    require_once __DIR__ . '/../models/Commandante.php';
    require_once __DIR__ . '/../models/Avis.php';
    require_once __DIR__ . '/../models/Contact.php';
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
        // ===== UTILISATEURS =====
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

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint non trouvé']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
