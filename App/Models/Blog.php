<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Blog
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new blog
     *
     * @param array $data Blog data
     * @return int|bool The blog ID if successful, false otherwise
     */
    public function createBlog($data)
    {
        // Verify if we're passing created_by_student in the data
        $createdByStudent = isset($data['created_by_student']) ? $data['created_by_student'] : null;

        // Modify query to include created_by_student
        $query = "INSERT INTO Blogs (tutor_id, title, content, created_by_student) 
              VALUES (:tutor_id, :title, :content, :created_by_student)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tutor_id', $data['tutor_id'], PDO::PARAM_INT);
        $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(':content', $data['content'], PDO::PARAM_STR);
        $stmt->bindParam(':created_by_student', $createdByStudent, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get blog by ID with tutor details
     *
     * @param int $blogId Blog ID
     * @return array|false Blog data or false if not found
     */
    public function getBlogById($blogId)
    {
        $query = "SELECT b.*, 
                u.first_name as tutor_first_name, u.last_name as tutor_last_name,
                b.created_by_student
              FROM Blogs b
              JOIN Users u ON b.tutor_id = u.user_id
              WHERE b.blog_id = :blog_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get blogs created by a specific tutor
     *
     * @param int $tutorId Tutor ID
     * @return array Blogs
     */
    public function getBlogsByTutor($tutorId)
    {
        $query = "SELECT b.*, 
                    u.first_name as tutor_first_name, u.last_name as tutor_last_name,
                    (SELECT COUNT(*) FROM BlogComments WHERE blog_id = b.blog_id) as comment_count,
                    (SELECT COUNT(*) FROM BlogParticipants WHERE blog_id = b.blog_id) as participant_count
                  FROM Blogs b
                  JOIN Users u ON b.tutor_id = u.user_id
                  WHERE b.tutor_id = :tutor_id
                  ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tutor_id', $tutorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get blogs that a student is a participant of
     *
     * @param int $studentId Student ID
     * @return array Blogs
     */
    public function getBlogsForStudent($studentId)
    {
        $query = "SELECT b.*, 
                    u.first_name as tutor_first_name, u.last_name as tutor_last_name,
                    (SELECT COUNT(*) FROM BlogComments WHERE blog_id = b.blog_id) as comment_count
                  FROM Blogs b
                  JOIN Users u ON b.tutor_id = u.user_id
                  JOIN BlogParticipants bp ON b.blog_id = bp.blog_id
                  WHERE bp.student_id = :student_id
                  ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all blogs (for staff)
     *
     * @param string $search Optional search term
     * @return array Blogs
     */
    public function getAllBlogs($search = '')
    {
        $query = "SELECT b.*, 
                    u.first_name as tutor_first_name, u.last_name as tutor_last_name,
                    (SELECT COUNT(*) FROM BlogComments WHERE blog_id = b.blog_id) as comment_count,
                    (SELECT COUNT(*) FROM BlogParticipants WHERE blog_id = b.blog_id) as participant_count
                  FROM Blogs b
                  JOIN Users u ON b.tutor_id = u.user_id";

        $params = [];

        if (!empty($search)) {
            $query .= " WHERE b.title LIKE :search OR 
                      b.content LIKE :search OR
                      u.first_name LIKE :search OR 
                      u.last_name LIKE :search";
            $search = "%$search%";
            $params[':search'] = $search;
        }

        $query .= " ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($query);

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update a blog
     *
     * @param int $blogId Blog ID
     * @param array $data Blog data
     * @return bool True if successful, false otherwise
     */
    public function updateBlog($blogId, $data)
    {
        $query = "UPDATE Blogs 
                  SET title = :title, content = :content
                  WHERE blog_id = :blog_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(':content', $data['content'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Delete a blog and all associated data
     *
     * @param int $blogId Blog ID
     * @return bool True if successful, false otherwise
     */
    public function deleteBlog($blogId)
    {
        try {
            $this->db->beginTransaction();

            // Delete all comments for this blog
            $commentQuery = "DELETE FROM BlogComments WHERE blog_id = :blog_id";
            $commentStmt = $this->db->prepare($commentQuery);
            $commentStmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
            $commentStmt->execute();

            // Delete all participants for this blog
            $participantQuery = "DELETE FROM BlogParticipants WHERE blog_id = :blog_id";
            $participantStmt = $this->db->prepare($participantQuery);
            $participantStmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
            $participantStmt->execute();

            // Delete the blog itself
            $blogQuery = "DELETE FROM Blogs WHERE blog_id = :blog_id";
            $blogStmt = $this->db->prepare($blogQuery);
            $blogStmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
            $blogStmt->execute();

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting blog: " . $e->getMessage());
            return false;
        }
    }
}