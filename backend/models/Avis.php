<?php

class Avis
{
    private $pdo;
    private $table = 'avis';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET utilisateur_id = :utilisateur_id,
                      titre = :titre,
                      description = :description,
                      note = :note,
                      statut = :statut";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':utilisateur_id', $data['utilisateur_id']);
        $stmt->bindParam(':titre', $data['titre'] ?? null);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':note', $data['note'] ?? 5);
        $stmt->bindParam(':statut', $data['statut'] ?? 'en attente');

        return $stmt->execute();
    }

    public function getById($id)
    {
        $query = "SELECT a.*, u.prenom, u.email 
                  FROM " . $this->table . " a
                  JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
                  WHERE a.avis_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll()
    {
        $query = "SELECT a.*, u.prenom 
                  FROM " . $this->table . " a
                  JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
                  WHERE a.statut = 'approuvé'
                  ORDER BY a.created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllPending()
    {
        $query = "SELECT a.*, u.prenom, u.email 
                  FROM " . $this->table . " a
                  JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
                  WHERE a.statut = 'en attente'
                  ORDER BY a.created_at ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $statut)
    {
        $query = "UPDATE " . $this->table . " SET statut = :statut WHERE avis_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':statut', $statut);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE avis_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getByUtilisateur($utilisateur_id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE utilisateur_id = :utilisateur_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
