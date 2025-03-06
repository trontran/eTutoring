<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class BlogComment
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new blog comment
     *
     * @param array $data Comment data
     * @return int|bool The comment ID if successful, false otherwise
     */
    public function createComment($data)
    {
        $query = "INSERT INTO BlogComments (blog_id, user_id, comment) 
                  VALUES (:blog_id, :user_id, :comment)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $data['blog_id'], PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':comment', $data['comment'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get comments for a blog
     *
     * @param int $blogId Blog ID
     * @return array Comments
     */
    public function getCommentsByBlogId($blogId)
    {
        $query = "SELECT bc.*, 
                u.first_name, u.last_name, u.role
              FROM BlogComments bc
              JOIN Users u ON bc.user_id = u.user_id
              WHERE bc.blog_id = :blog_id
              ORDER BY bc.created_at ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->execute();

        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $comments ?: [];
    }

    /**
     * Get comment by ID
     *
     * @param int $commentId Comment ID
     * @return array|false Comment data or false if not found
     */
    public function getCommentById($commentId)
    {
        $query = "SELECT bc.*, 
                    u.first_name, u.last_name, u.role
                  FROM BlogComments bc
                  JOIN Users u ON bc.user_id = u.user_id
                  WHERE bc.comment_id = :comment_id";

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
    public function deleteComment($commentId)
    {
        $query = "DELETE FROM BlogComments WHERE comment_id = :comment_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}