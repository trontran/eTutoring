<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Document
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new document record
     *
     * @param array $data Document data
     * @return int|bool The document ID if successful, false otherwise
     */
    public function createDocument($data)
    {
        $query = "INSERT INTO Documents (uploader_id, student_id, tutor_id, file_path, file_name, file_type, file_size) 
                  VALUES (:uploader_id, :student_id, :tutor_id, :file_path, :file_name, :file_type, :file_size)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uploader_id', $data['uploader_id'], PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $data['student_id'], PDO::PARAM_INT);
        $stmt->bindParam(':tutor_id', $data['tutor_id'], PDO::PARAM_INT);
        $stmt->bindParam(':file_path', $data['file_path'], PDO::PARAM_STR);
        $stmt->bindParam(':file_name', $data['file_name'], PDO::PARAM_STR);
        $stmt->bindParam(':file_type', $data['file_type'], PDO::PARAM_STR);
        $stmt->bindParam(':file_size', $data['file_size'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get document by ID with uploader, student, and tutor details
     *
     * @param int $documentId Document ID
     * @return array|false Document data or false if not found
     */
    public function getDocumentById($documentId)
    {
        $query = "SELECT d.*, 
                    uploader.first_name as uploader_first_name, uploader.last_name as uploader_last_name,
                    student.first_name as student_first_name, student.last_name as student_last_name,
                    tutor.first_name as tutor_first_name, tutor.last_name as tutor_last_name
                  FROM Documents d
                  JOIN Users uploader ON d.uploader_id = uploader.user_id
                  JOIN Users student ON d.student_id = student.user_id
                  JOIN Users tutor ON d.tutor_id = tutor.user_id
                  WHERE d.document_id = :document_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get document by student ID
     *
     * @param int $studentId Student ID
     * @return array Documents
     */
    public function getDocumentsByStudentId(int $studentId): array
    {
        $query = "SELECT d.*, 
                    uploader.first_name as uploader_first_name, uploader.last_name as uploader_last_name,
                    tutor.first_name as tutor_first_name, tutor.last_name as tutor_last_name,
                    (SELECT COUNT(*) FROM DocumentComments WHERE document_id = d.document_id) as comment_count
                  FROM Documents d
                  JOIN Users uploader ON d.uploader_id = uploader.user_id
                  JOIN Users tutor ON d.tutor_id = tutor.user_id
                  WHERE d.student_id = :student_id
                  ORDER BY d.uploaded_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get document by tutor ID
     *
     * @param int $tutorId Tutor ID
     * @return array Documents
     */
    public function getDocumentsByTutorId(int $tutorId)
    {
        $query = "SELECT d.*, 
                    uploader.first_name as uploader_first_name, uploader.last_name as uploader_last_name,
                    student.first_name as student_first_name, student.last_name as student_last_name,
                    (SELECT COUNT(*) FROM DocumentComments WHERE document_id = d.document_id) as comment_count
                  FROM Documents d
                  JOIN Users uploader ON d.uploader_id = uploader.user_id
                  JOIN Users student ON d.student_id = student.user_id
                  WHERE d.tutor_id = :tutor_id
                  ORDER BY d.uploaded_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tutor_id', $tutorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all document (for staff)
     *
     * @param string $search Optional search term
     * @return array Documents
     */
    public function getAllDocuments($search = '')
    {
        $query = "SELECT d.*, 
                    uploader.first_name as uploader_first_name, uploader.last_name as uploader_last_name,
                    student.first_name as student_first_name, student.last_name as student_last_name,
                    tutor.first_name as tutor_first_name, tutor.last_name as tutor_last_name,
                    (SELECT COUNT(*) FROM DocumentComments WHERE document_id = d.document_id) as comment_count
                  FROM Documents d
                  JOIN Users uploader ON d.uploader_id = uploader.user_id
                  JOIN Users student ON d.student_id = student.user_id
                  JOIN Users tutor ON d.tutor_id = tutor.user_id";

        $params = [];

        if (!empty($search)) {
            $query .= " WHERE d.file_name LIKE :search OR 
                      student.first_name LIKE :search OR student.last_name LIKE :search OR
                      tutor.first_name LIKE :search OR tutor.last_name LIKE :search";
            $search = "%$search%";
            $params[':search'] = $search;
        }

        $query .= " ORDER BY d.uploaded_at DESC";

        $stmt = $this->db->prepare($query);

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}