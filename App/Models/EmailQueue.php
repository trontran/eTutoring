<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class EmailQueue
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Add an email to the queue
     *
     * @param string $email Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param int $priority Priority (1-10, lower is higher priority)
     * @return int|bool The queue ID if successful, false otherwise
     */
    public function addToQueue($email, $subject, $body, $priority = 5)
    {
        $query = "INSERT INTO EmailQueue (recipient_email, subject, body, priority) 
                  VALUES (:email, :subject, :body, :priority)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
        $stmt->bindParam(':body', $body, PDO::PARAM_STR);
        $stmt->bindParam(':priority', $priority, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Process a batch of emails from the queue
     *
     * @param int $batchSize Number of emails to process
     * @return int Number of emails successfully sent
     */
    public function processBatch($batchSize = 10)
    {
        // Get pending emails, prioritize by priority then by creation date
        $query = "SELECT * FROM EmailQueue 
                  WHERE status = 'pending' AND (attempts < 3 OR last_attempt IS NULL)
                  ORDER BY priority ASC, created_at ASC 
                  LIMIT :batch_size";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':batch_size', $batchSize, PDO::PARAM_INT);
        $stmt->execute();

        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $successCount = 0;

        foreach ($emails as $email) {
            // Update last attempt time and increment attempts counter
            $updateQuery = "UPDATE EmailQueue 
                          SET attempts = attempts + 1, 
                              last_attempt = NOW() 
                          WHERE queue_id = :queue_id";

            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':queue_id', $email['queue_id'], PDO::PARAM_INT);
            $updateStmt->execute();

            // Attempt to send email
            require_once __DIR__ . '/../Helpers/MailHelper.php';
            $result = \MailHelper::sendMail(
                $email['recipient_email'],
                $email['subject'],
                $email['body']
            );

            // Update status based on result
            $status = $result ? 'sent' : 'failed';
            $sentAt = $result ? 'NOW()' : 'NULL';

            $statusQuery = "UPDATE EmailQueue 
                          SET status = :status, 
                              sent_at = " . $sentAt . " 
                          WHERE queue_id = :queue_id";

            $statusStmt = $this->db->prepare($statusQuery);
            $statusStmt->bindParam(':status', $status, PDO::PARAM_STR);
            $statusStmt->bindParam(':queue_id', $email['queue_id'], PDO::PARAM_INT);
            $statusStmt->execute();

            if ($result) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Count pending emails in the queue
     *
     * @return int Number of pending emails
     */
    public function countPendingEmails()
    {
        $query = "SELECT COUNT(*) FROM EmailQueue WHERE status = 'pending'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Get counts of emails by status
     *
     * @return array Associative array with counts of pending, sent, and failed emails
     */
    public function getStatusCounts()
    {
        $counts = [
            'pending' => 0,
            'sent' => 0,
            'failed' => 0
        ];

        // Get count of pending emails
        $pendingQuery = "SELECT COUNT(*) FROM EmailQueue WHERE status = 'pending'";
        $stmt = $this->db->prepare($pendingQuery);
        $stmt->execute();
        $counts['pending'] = $stmt->fetchColumn();

        // Get count of sent emails
        $sentQuery = "SELECT COUNT(*) FROM EmailQueue WHERE status = 'sent'";
        $stmt = $this->db->prepare($sentQuery);
        $stmt->execute();
        $counts['sent'] = $stmt->fetchColumn();

        // Get count of failed emails
        $failedQuery = "SELECT COUNT(*) FROM EmailQueue WHERE status = 'failed'";
        $stmt = $this->db->prepare($failedQuery);
        $stmt->execute();
        $counts['failed'] = $stmt->fetchColumn();

        return $counts;
    }
}