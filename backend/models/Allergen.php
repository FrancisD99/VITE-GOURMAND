<?php

class Allergen
{
    private $pdo;
    private $table = 'allergen';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET libelle = :libelle";

        $stmt = $this->pdo->prepare($query);

        $libelle = $data['libelle'];
        $stmt->bindParam(':libelle', $libelle);

        return $stmt->execute();
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE allergen_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY libelle ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        $query = "UPDATE " . $this->table . " 
                  SET libelle = :libelle
                  WHERE allergen_id = :id";

        $stmt = $this->pdo->prepare($query);

        $id_val = $id;
        $libelle = $data['libelle'];

        $stmt->bindParam(':id', $id_val);
        $stmt->bindParam(':libelle', $libelle);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE allergen_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getByPlat($plat_id)
    {
        $query = "SELECT a.* FROM " . $this->table . " a
                  JOIN plat_allergen pa ON a.allergen_id = pa.allergen_id
                  WHERE pa.plat_id = :plat_id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':plat_id', $plat_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function search($searchTerm)
    {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE libelle LIKE :search
                  ORDER BY libelle ASC";
        
        $stmt = $this->pdo->prepare($query);
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
