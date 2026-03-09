<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    $stmt = $db->query('SELECT utilisateur_id, email, nom, prenom, role_id FROM utilisateur ORDER BY utilisateur_id');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "=== Utilisateurs dans la BD ===\n\n";
        foreach ($users as $user) {
            echo "ID: {$user['utilisateur_id']}\n";
            echo "Email: {$user['email']}\n";
            echo "Nom: {$user['nom']}\n";
            echo "Prénom: {$user['prenom']}\n";
            echo "Role ID: {$user['role_id']}\n";
            echo "---\n";
        }
    } else {
        echo "Aucun utilisateur trouvé.\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
