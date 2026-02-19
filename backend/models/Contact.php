<?php

class Contact
{
    private $pdo;
    private $table = 'contact';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET email = :email,
                      titre = :titre,
                      message = :message,
                      statut = :statut";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':titre', $data['titre']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':statut', $data['statut'] ?? 'non lu');

        return $stmt->execute();
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE contact_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll($limit = null, $offset = 0)
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllUnread()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE statut = 'non lu' ORDER BY created_at ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markAsRead($id)
    {
        $query = "UPDATE " . $this->table . " SET statut = 'lu' WHERE contact_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE contact_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getUnreadCount()
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE statut = 'non lu'";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
}
