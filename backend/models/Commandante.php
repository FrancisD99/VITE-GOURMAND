<?php

class Commandante
{
    private $pdo;
    private $table = 'commandante';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET utilisateur_id = :utilisateur_id,
                      date_commandante = :date_commandante,
                      date_livraison = :date_livraison,
                      heure_livraison = :heure_livraison,
                      prix_menu = :prix_menu,
                      nombre_personnes = :nombre_personnes,
                      prix_livraison = :prix_livraison,
                      pret_materiel = :pret_materiel,
                      etat_materiel = :etat_materiel,
                      statut = :statut";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':utilisateur_id', $data['utilisateur_id']);
        $stmt->bindParam(':date_commandante', $data['date_commandante']);
        $stmt->bindParam(':date_livraison', $data['date_livraison']);
        $stmt->bindParam(':heure_livraison', $data['heure_livraison']);
        $stmt->bindParam(':prix_menu', $data['prix_menu']);
        $stmt->bindParam(':nombre_personnes', $data['nombre_personnes']);
        $stmt->bindParam(':prix_livraison', $data['prix_livraison'] ?? 0);
        $stmt->bindParam(':pret_materiel', $data['pret_materiel'] ?? false);
        $stmt->bindParam(':etat_materiel', $data['etat_materiel'] ?? null);
        $stmt->bindParam(':statut', $data['statut'] ?? 'en attente');

        return $stmt->execute();
    }

    public function getById($id)
    {
        $query = "SELECT c.*, u.email, u.prenom, u.telephone 
                  FROM " . $this->table . " c
                  JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
                  WHERE c.commandante_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getByUtilisateur($utilisateur_id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE utilisateur_id = :utilisateur_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAll()
    {
        $query = "SELECT c.*, u.email, u.prenom 
                  FROM " . $this->table . " c
                  JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
                  ORDER BY c.created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        $query = "UPDATE " . $this->table . " 
                  SET statut = :statut,
                      etat_materiel = :etat_materiel,
                      date_livraison = :date_livraison,
                      heure_livraison = :heure_livraison
                  WHERE commandante_id = :id";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':statut', $data['statut']);
        $stmt->bindParam(':etat_materiel', $data['etat_materiel'] ?? null);
        $stmt->bindParam(':date_livraison', $data['date_livraison'] ?? null);
        $stmt->bindParam(':heure_livraison', $data['heure_livraison'] ?? null);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE commandante_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getByStatut($statut)
    {
        $query = "SELECT c.*, u.email, u.prenom 
                  FROM " . $this->table . " c
                  JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
                  WHERE c.statut = :statut
                  ORDER BY c.created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':statut', $statut);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
