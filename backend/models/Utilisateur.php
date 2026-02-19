<?php

class Utilisateur
{
    private $pdo;
    private $table = 'utilisateur';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET email = :email, 
                      password = :password, 
                      nom = :nom,
                      prenom = :prenom, 
                      telephone = :telephone, 
                      ville = :ville, 
                      pays = :pays,
                      adresse_postale = :adresse_postale,
                      role_id = :role_id";

        $stmt = $this->pdo->prepare($query);

        // Créer les variables avant de les binder
        $email = $data['email'];
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $telephone = $data['telephone'] ?? null;
        $ville = $data['ville'] ?? null;
        $pays = $data['pays'] ?? null;
        $adresse_postale = $data['adresse_postale'] ?? null;
        $role_id = $data['role_id'] ?? 2;

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':pays', $pays);
        $stmt->bindParam(':adresse_postale', $adresse_postale);
        $stmt->bindParam(':role_id', $role_id);

        return $stmt->execute();
    }

    public function getById($id)
    {
        $query = "SELECT u.*, p.libelle as role_name FROM " . $this->table . " u
                  JOIN poste p ON u.role_id = p.role_id
                  WHERE u.utilisateur_id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll()
    {
        $query = "SELECT u.*, p.libelle as role_name FROM " . $this->table . " u
                  JOIN poste p ON u.role_id = p.role_id
                  ORDER BY u.created_at DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        $query = "UPDATE " . $this->table . " 
                  SET email = :email, 
                      nom = :nom,
                      prenom = :prenom, 
                      telephone = :telephone, 
                      ville = :ville, 
                      pays = :pays,
                      adresse_postale = :adresse_postale,
                      role_id = :role_id
                  WHERE utilisateur_id = :id";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':nom', $data['nom']);
        $stmt->bindParam(':prenom', $data['prenom']);
        $stmt->bindParam(':telephone', $data['telephone'] ?? null);
        $stmt->bindParam(':ville', $data['ville'] ?? null);
        $stmt->bindParam(':pays', $data['pays'] ?? null);
        $stmt->bindParam(':adresse_postale', $data['adresse_postale'] ?? null);
        $stmt->bindParam(':role_id', $data['role_id']);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE utilisateur_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function authenticate($email, $password)
    {
        $user = $this->getByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
