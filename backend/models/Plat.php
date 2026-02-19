<?php

class Plat
{
    private $pdo;
    private $table = 'plat';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET titre_plat = :titre_plat,
                      description = :description,
                      prix = :prix,
                      regime_id = :regime_id";

        $stmt = $this->pdo->prepare($query);

        $titre_plat = $data['titre_plat'];
        $description = $data['description'] ?? null;
        $prix = $data['prix'] ?? 0;
        $regime_id = $data['regime_id'] ?? null;

        $stmt->bindParam(':titre_plat', $titre_plat);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':regime_id', $regime_id);

        return $stmt->execute();
    }

    public function getById($id)
    {
        $query = "SELECT p.*, r.libelle as regime_name FROM " . $this->table . " p
                  LEFT JOIN regime r ON p.regime_id = r.regime_id
                  WHERE p.plat_id = :id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function getAll()
    {
        $query = "SELECT p.*, r.libelle as regime_name FROM " . $this->table . " p
                  LEFT JOIN regime r ON p.regime_id = r.regime_id
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getByRegime($regime_id)
    {
        $query = "SELECT p.*, r.libelle as regime_name FROM " . $this->table . " p
                  LEFT JOIN regime r ON p.regime_id = r.regime_id
                  WHERE p.regime_id = :regime_id
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':regime_id', $regime_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        $query = "UPDATE " . $this->table . " 
                  SET titre_plat = :titre_plat,
                      description = :description,
                      prix = :prix,
                      regime_id = :regime_id
                  WHERE plat_id = :id";

        $stmt = $this->pdo->prepare($query);

        $id_val = $id;
        $titre_plat = $data['titre_plat'];
        $description = $data['description'] ?? null;
        $prix = $data['prix'] ?? 0;
        $regime_id = $data['regime_id'] ?? null;

        $stmt->bindParam(':id', $id_val);
        $stmt->bindParam(':titre_plat', $titre_plat);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':regime_id', $regime_id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE plat_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getPlatAllergenes($plat_id)
    {
        $query = "SELECT a.* FROM allergen a
                  JOIN plat_allergen pa ON a.allergen_id = pa.allergen_id
                  WHERE pa.plat_id = :plat_id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':plat_id', $plat_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function addAllergen($plat_id, $allergen_id)
    {
        $query = "INSERT IGNORE INTO plat_allergen SET plat_id = :plat_id, allergen_id = :allergen_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':plat_id', $plat_id);
        $stmt->bindParam(':allergen_id', $allergen_id);
        return $stmt->execute();
    }

    public function removeAllergen($plat_id, $allergen_id)
    {
        $query = "DELETE FROM plat_allergen WHERE plat_id = :plat_id AND allergen_id = :allergen_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':plat_id', $plat_id);
        $stmt->bindParam(':allergen_id', $allergen_id);
        return $stmt->execute();
    }

    public function search($searchTerm)
    {
        $query = "SELECT p.*, r.libelle as regime_name FROM " . $this->table . " p
                  LEFT JOIN regime r ON p.regime_id = r.regime_id
                  WHERE p.titre_plat LIKE :search OR p.description LIKE :search
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
