<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class BlogDocument
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Associate a document with a blog
     *
     * @param int $blogId Blog ID
     * @param int $documentId Document ID
     * @return bool True if successful, false otherwise
     */
    public function addDocumentToBlog($blogId, $documentId)
    {
        $query = "INSERT INTO BlogDocuments (blog_id, document_id) 
                  VALUES (:blog_id, :document_id)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get documents associated with a blog
     *
     * @param int $blogId Blog ID
     * @return array Documents
     */
    public function getDocumentsByBlogId($blogId)
    {
        $query = "SELECT d.*, u.first_name as uploader_first_name, u.last_name as uploader_last_name 
                  FROM BlogDocuments bd
                  JOIN Documents d ON bd.document_id = d.document_id
                  JOIN Users u ON d.uploader_id = u.user_id
                  WHERE bd.blog_id = :blog_id
                  ORDER BY bd.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get blogs associated with a document
     *
     * @param int $documentId Document ID
     * @return array Blogs
     */
    public function getBlogsByDocumentId($documentId)
    {
        $query = "SELECT b.* 
                  FROM BlogDocuments bd
                  JOIN Blogs b ON bd.blog_id = b.blog_id
                  WHERE bd.document_id = :document_id
                  ORDER BY bd.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Remove a document from a blog
     *
     * @param int $blogId Blog ID
     * @param int $documentId Document ID
     * @return bool True if successful, false otherwise
     */
    public function removeDocumentFromBlog($blogId, $documentId)
    {
        $query = "DELETE FROM BlogDocuments 
                  WHERE blog_id = :blog_id AND document_id = :document_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Remove all documents from a blog
     *
     * @param int $blogId Blog ID
     * @return bool True if successful, false otherwise
     */
    public function removeAllDocumentsFromBlog($blogId)
    {
        $query = "DELETE FROM BlogDocuments WHERE blog_id = :blog_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}