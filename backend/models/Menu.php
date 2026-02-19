<?php

class Menu
{
    private $pdo;
    private $table = 'menu';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET titre = :titre, 
                      nombre_personne_minimum = :nombre_personne_minimum,
                      prix_par_personne = :prix_par_personne,
                      regime = :regime,
                      description = :description,
                      quantite_restante = :quantite_restante";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':titre', $data['titre']);
        $stmt->bindParam(':nombre_personne_minimum', $data['nombre_personne_minimum']);
        $stmt->bindParam(':prix_par_personne', $data['prix_par_personne']);
        $stmt->bindParam(':regime', $data['regime'] ?? null);
        $stmt->bindParam(':description', $data['description'] ?? null);
        $stmt->bindParam(':quantite_restante', $data['quantite_restante'] ?? 0);

        return $stmt->execute();
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE menu_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByRegime($regime)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE regime = :regime ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':regime', $regime);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        $query = "UPDATE " . $this->table . " 
                  SET titre = :titre, 
                      nombre_personne_minimum = :nombre_personne_minimum,
                      prix_par_personne = :prix_par_personne,
                      regime = :regime,
                      description = :description,
                      quantite_restante = :quantite_restante
                  WHERE menu_id = :id";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titre', $data['titre']);
        $stmt->bindParam(':nombre_personne_minimum', $data['nombre_personne_minimum']);
        $stmt->bindParam(':prix_par_personne', $data['prix_par_personne']);
        $stmt->bindParam(':regime', $data['regime'] ?? null);
        $stmt->bindParam(':description', $data['description'] ?? null);
        $stmt->bindParam(':quantite_restante', $data['quantite_restante'] ?? 0);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE menu_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getWithPlats($menu_id)
    {
        $query = "SELECT m.*, p.plat_id, p.titre_plat, t.theme_id, t.libelle as theme_name
                  FROM " . $this->table . " m
                  LEFT JOIN propose pr ON m.menu_id = pr.menu_id
                  LEFT JOIN plat p ON pr.plat_id = p.plat_id
                  LEFT JOIN theme t ON pr.theme_id = t.theme_id
                  WHERE m.menu_id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $menu_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
