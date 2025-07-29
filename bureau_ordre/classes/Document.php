<?php
class Document {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

   
 
    

   
    public function addDocument($data) {
        try {
            $this->db->query('INSERT INTO documents 
                            (reference, title, type, sender, recipient, date_reception, 
                             date_creation, subject, keywords, status, file_path, created_by) 
                            VALUES 
                            (:reference, :title, :type, :sender, :recipient, :date_reception, 
                             :date_creation, :subject, :keywords, :status, :file_path, :created_by)');
            
            $this->db->bind(':reference', $data['reference']);
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':type', $data['type']);
            $this->db->bind(':sender', $data['sender']);
            $this->db->bind(':recipient', $data['recipient']);
            $this->db->bind(':date_reception', $data['date_reception']);
            $this->db->bind(':date_creation', $data['date_creation']);
            $this->db->bind(':subject', $data['subject']);
            $this->db->bind(':keywords', $data['keywords']);
            $this->db->bind(':status', $data['status']);
            $this->db->bind(':file_path', $data['file_path']);
            $this->db->bind(':created_by', $data['created_by']);
            
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error adding document: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateDocument($data) {
        try {
            $this->db->query('UPDATE documents SET 
                            title = :title, 
                            type = :type, 
                            sender = :sender, 
                            recipient = :recipient, 
                            date_reception = :date_reception, 
                            date_creation = :date_creation, 
                            subject = :subject, 
                            keywords = :keywords, 
                            status = :status, 
                            file_path = :file_path,
                            updated_at = NOW() 
                            WHERE id = :id');
            
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':type', $data['type']);
            $this->db->bind(':sender', $data['sender']);
            $this->db->bind(':recipient', $data['recipient']);
            $this->db->bind(':date_reception', $data['date_reception']);
            $this->db->bind(':date_creation', $data['date_creation']);
            $this->db->bind(':subject', $data['subject']);
            $this->db->bind(':keywords', $data['keywords']);
            $this->db->bind(':status', $data['status']);
            $this->db->bind(':file_path', $data['file_path']);
            
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error updating document: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteDocument($id) {
        try {
            $this->db->query('DELETE FROM documents WHERE id = :id');
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error deleting document: " . $e->getMessage());
            return false;
        }
    }

    public function getDocuments($limit = null) {
        try {
            $sql = 'SELECT d.*, u.username as created_by_name 
                   FROM documents d 
                   JOIN users u ON d.created_by = u.id ';
            
            if ($_SESSION['user_role'] != 'admin') {
                $sql .= ' WHERE d.created_by = :user_id ';
            }
            
            $sql .= ' ORDER BY d.date_reception DESC';
            
            if($limit) {
                $sql .= ' LIMIT :limit';
            }
            
            $this->db->query($sql);
            
            if ($_SESSION['user_role'] != 'admin') {
                $this->db->bind(':user_id', $_SESSION['user_id']);
            }
            
            if($limit) {
                $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
            }
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error getting documents: " . $e->getMessage());
            return [];
        }
    }
    
   public function getDocumentById($id) {
    try {
        // Requête de base pour récupérer le document
        $sql = 'SELECT d.*, u.username as created_by_name 
               FROM documents d 
               JOIN users u ON d.created_by = u.id 
               WHERE d.id = :id';
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        
        if (!$result) {
            return false;
        }

        // Vérification des permissions
        $is_owner = ($result->created_by == $_SESSION['user_id']);
        $is_recipient = $this->isRecipient($id, $_SESSION['user_id']);
        $is_admin = ($_SESSION['user_role'] == 'admin');

        if (!$is_admin && !$is_owner && !$is_recipient) {
            return false;
        }

        return $result;
    } catch (PDOException $e) {
        error_log("Error getting document by ID: " . $e->getMessage());
        return false;
    }
}
    
    public function sendDocumentToUser($document_id, $recipient_id, $sender_id) {
        try {
            $this->db->query('INSERT INTO document_recipients 
                            (document_id, recipient_id, sender_id, created_at) 
                            VALUES 
                            (:document_id, :recipient_id, :sender_id, NOW())');
            
            $this->db->bind(':document_id', $document_id);
            $this->db->bind(':recipient_id', $recipient_id);
            $this->db->bind(':sender_id', $sender_id);
            
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error sending document to user: " . $e->getMessage());
            return false;
        }
    }

    public function getReceivedDocuments($user_id) {
        try {
            $this->db->query('SELECT d.*, u.username as sender_name, dr.created_at as received_at 
                            FROM documents d 
                            JOIN document_recipients dr ON d.id = dr.document_id 
                            JOIN users u ON dr.sender_id = u.id 
                            WHERE dr.recipient_id = :user_id 
                            ORDER BY dr.created_at DESC');
            
            $this->db->bind(':user_id', $user_id);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error getting received documents: " . $e->getMessage());
            return [];
        }
    }

    public function isRecipient($document_id, $user_id) {
    try {
        $this->db->query('SELECT id FROM document_recipients 
                         WHERE document_id = :document_id 
                         AND recipient_id = :user_id
                         LIMIT 1');
        $this->db->bind(':document_id', $document_id);
        $this->db->bind(':user_id', $user_id);
        
        $result = $this->db->single();
        return $result ? true : false;
    } catch (PDOException $e) {
        error_log("Error checking recipient: " . $e->getMessage());
        return false;
    }
}
    public function generateReference($type) {
        try {
            $prefix = '';
            switch($type) {
                case 'entrant': $prefix = 'ENT'; break;
                case 'sortant': $prefix = 'SOR'; break;
                case 'interne': $prefix = 'INT'; break;
            }
            
            $year = date('Y');
            $this->db->query('SELECT MAX(CAST(SUBSTRING(reference, 8) AS UNSIGNED)) as max_num 
                             FROM documents 
                             WHERE type = :type 
                             AND YEAR(date_reception) = :year');
            $this->db->bind(':type', $type);
            $this->db->bind(':year', $year);
            $result = $this->db->single();
            
            $number = ($result->max_num ?? 0) + 1;
            return $prefix . $year . str_pad($number, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log("Error generating reference: " . $e->getMessage());
            return $prefix . date('YmdHis');
        }
    }

    public function countDocumentsByType() {
        try {
            $this->db->query('SELECT type, COUNT(*) as count FROM documents GROUP BY type');
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error counting documents by type: " . $e->getMessage());
            return [];
        }
    }

    public function countDocumentsByStatus() {
        try {
            $this->db->query('SELECT status, COUNT(*) as count FROM documents GROUP BY status');
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error counting documents by status: " . $e->getMessage());
            return [];
        }
    }

    public function getDocumentsByMonth() {
        try {
            $this->db->query('SELECT DATE_FORMAT(date_reception, "%Y-%m") as month, COUNT(*) as count 
                             FROM documents 
                             WHERE date_reception >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                             GROUP BY month 
                             ORDER BY month');
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error getting documents by month: " . $e->getMessage());
            return [];
        }
    }

    public function getDocumentsByUser() {
        try {
            $this->db->query('SELECT u.username, COUNT(d.id) as count 
                             FROM users u 
                             LEFT JOIN documents d ON u.id = d.created_by 
                             GROUP BY u.username 
                             ORDER BY count DESC');
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error getting documents by user: " . $e->getMessage());
            return [];
        }
    }
public function searchByKeyword($keywords, $user_id = null, $is_admin = false) {
    try {
        $searchTerms = array_unique(preg_split('/[\s,]+/', trim($keywords)));
        $searchTerms = array_filter($searchTerms, function($term) {
            return !empty(trim($term));
        });

        if (empty($searchTerms)) {
            return [];
        }

        // Requête FULLTEXT séparée
        $this->db->query('SELECT d.*, u.username as created_by_name 
                         FROM documents d 
                         JOIN users u ON d.created_by = u.id 
                         WHERE MATCH(d.title, d.keywords, d.sender, d.recipient) 
                         AGAINST(:keywords IN BOOLEAN MODE) 
                         '. (!$is_admin ? 'AND (d.created_by = :user_id OR EXISTS (
                             SELECT 1 FROM document_recipients dr 
                             WHERE dr.document_id = d.id AND dr.recipient_id = :user_id
                         ))' : '').'
                         ORDER BY d.date_reception DESC');

        $this->db->bind(':keywords', implode(' ', $searchTerms));
        if (!$is_admin) {
            $this->db->bind(':user_id', $user_id);
        }
        
        return $this->db->resultSet();
    } catch (PDOException $e) {
        error_log("Keyword search error: " . $e->getMessage());
        return [];
    }
}
 public function searchByReference($reference, $user_id = null, $is_admin = false) {
    try {
        $sql = 'SELECT d.*, u.username as created_by_name 
               FROM documents d 
               JOIN users u ON d.created_by = u.id 
               WHERE d.reference = :reference';
        
        if (!$is_admin) {
            $sql .= ' AND (d.created_by = :user_id OR EXISTS (
                SELECT 1 FROM document_recipients dr 
                WHERE dr.document_id = d.id AND dr.recipient_id = :user_id
            ))';
        }
        
        $this->db->query($sql);
        $this->db->bind(':reference', $reference);
        
        if (!$is_admin) {
            $this->db->bind(':user_id', $user_id);
        }
        
        return $this->db->resultSet();
    } catch (PDOException $e) {
        error_log("Reference search error: " . $e->getMessage());
        return [];
    }
}
public function getDocumentRecipients($document_id) {
    try {
        $this->db->query('SELECT dr.*, u.username as recipient_name 
                         FROM document_recipients dr
                         JOIN users u ON dr.recipient_id = u.id
                         WHERE dr.document_id = :document_id
                         ORDER BY dr.created_at DESC');
        $this->db->bind(':document_id', $document_id);
        return $this->db->resultSet();
    } catch (PDOException $e) {
        error_log("Error getting document recipients: " . $e->getMessage());
        return [];
    }
}
}