<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class DocumentComment
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new comment
     *
     * @param array $data Comment data
     * @return int|bool The comment ID if successful, false otherwise
     */
    public function createComment(array $data)
    {
        $query = "INSERT INTO DocumentComments (document_id, commenter_id, comment_text) 
                  VALUES (:document_id, :commenter_id, :comment_text)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $data['document_id'], PDO::PARAM_INT);
        $stmt->bindParam(':commenter_id', $data['commenter_id'], PDO::PARAM_INT);
        $stmt->bindParam(':comment_text', $data['comment_text'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get comments for a document
     *
     * @param int $documentId Document ID
     * @return array Comments
     */
    public function getCommentsByDocumentId(int $documentId)
    {
        $query = "SELECT dc.*, 
                    u.first_name, u.last_name, u.role
                  FROM DocumentComments dc
                  JOIN Users u ON dc.commenter_id = u.user_id
                  WHERE dc.document_id = :document_id
                  ORDER BY dc.commented_at ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get comment by ID
     *
     * @param int $commentId Comment ID
     * @return array|false Comment data or false if not found
     */
    public function getCommentById(int $commentId)
    {
        $query = "SELECT dc.*, 
                    u.first_name, u.last_name, u.role
                  FROM DocumentComments dc
                  JOIN Users u ON dc.commenter_id = u.user_id
                  WHERE dc.comment_id = :comment_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a comment
     *
     * @param int $commentId Comment ID
     * @return bool True if successful, false otherwise
     */
    public function deleteComment(int $commentId)
    {
        $query = "DELETE FROM DocumentComments WHERE comment_id = :comment_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}