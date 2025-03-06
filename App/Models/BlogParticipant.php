<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class BlogParticipant
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Add a student as a participant to a blog
     *
     * @param int $blogId Blog ID
     * @param int $studentId Student ID
     * @return bool True if successful, false otherwise
     */
    public function addParticipant($blogId, $studentId)
    {
        // First check if the participant already exists
        if ($this->isParticipant($blogId, $studentId)) {
            return true; // Already a participant
        }

        $query = "INSERT INTO BlogParticipants (blog_id, student_id) 
                  VALUES (:blog_id, :student_id)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Remove a student as a participant from a blog
     *
     * @param int $blogId Blog ID
     * @param int $studentId Student ID
     * @return bool True if successful, false otherwise
     */
    public function removeParticipant($blogId, $studentId)
    {
        $query = "DELETE FROM BlogParticipants 
                  WHERE blog_id = :blog_id AND student_id = :student_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Check if a student is a participant of a blog
     *
     * @param int $blogId Blog ID
     * @param int $studentId Student ID
     * @return bool True if participant, false otherwise
     */
    public function isParticipant($blogId, $studentId)
    {
        $query = "SELECT COUNT(*) FROM BlogParticipants 
                  WHERE blog_id = :blog_id AND student_id = :student_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        return ($stmt->fetchColumn() > 0);
    }

    /**
     * Get all participants for a blog
     *
     * @param int $blogId Blog ID
     * @return array Participants with user details
     */
    public function getParticipantsByBlogId($blogId)
    {
        $query = "SELECT bp.*, 
                    u.first_name, u.last_name, u.email
                  FROM BlogParticipants bp
                  JOIN Users u ON bp.student_id = u.user_id
                  WHERE bp.blog_id = :blog_id
                  ORDER BY u.first_name, u.last_name";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all blogs that a student is participating in
     *
     * @param int $studentId Student ID
     * @return array Blog IDs
     */
    public function getBlogsByStudentId($studentId)
    {
        $query = "SELECT blog_id FROM BlogParticipants 
                  WHERE student_id = :student_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Count participants for a blog
     *
     * @param int $blogId Blog ID
     * @return int Number of participants
     */
    public function countParticipants($blogId)
    {
        $query = "SELECT COUNT(*) FROM BlogParticipants 
                  WHERE blog_id = :blog_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':blog_id', $blogId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }
}