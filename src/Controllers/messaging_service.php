<?php
/**
 * Messaging Service for D Labour Chowk
 * Handles real-time messaging between clients and workers
 */

require_once 'config.php';

class MessagingService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create or get existing conversation
     */
    public function getOrCreateConversation($participant1, $participant2, $jobPostId = null) {
        // Ensure participants are in correct order (smaller ID first)
        if ($participant1 > $participant2) {
            $temp = $participant1;
            $participant1 = $participant2;
            $participant2 = $temp;
        }

        // Check if conversation exists
        $query = "SELECT conversation_id FROM conversations
                  WHERE participant_1 = ? AND participant_2 = ?
                  AND (job_post_id = ? OR (job_post_id IS NULL AND ? IS NULL))";

        $stmt = $this->db->prepare($query);
        $jobPostIdNull = $jobPostId === null ? null : $jobPostId;
        $stmt->bind_param("iiii", $participant1, $participant2, $jobPostId, $jobPostIdNull);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['conversation_id'];
        }

        // Create new conversation
        $insertQuery = "INSERT INTO conversations (participant_1, participant_2, job_post_id)
                        VALUES (?, ?, ?)";
        $insertStmt = $this->db->prepare($insertQuery);
        $insertStmt->bind_param("iii", $participant1, $participant2, $jobPostId);
        $insertStmt->execute();

        return $this->db->insert_id;
    }

    /**
     * Send a message
     */
    public function sendMessage($conversationId, $senderId, $messageText, $messageType = 'text', $attachmentUrl = null) {
        $query = "INSERT INTO messages (conversation_id, sender_id, message_text, message_type, attachment_url)
                  VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iisss", $conversationId, $senderId, $messageText, $messageType, $attachmentUrl);
        $success = $stmt->execute();

        if ($success) {
            // Update conversation's last message time
            $updateQuery = "UPDATE conversations SET last_message_at = CURRENT_TIMESTAMP
                           WHERE conversation_id = ?";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bind_param("i", $conversationId);
            $updateStmt->execute();
        }

        return $success;
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages($conversationId, $limit = 50, $offset = 0) {
        $query = "SELECT m.*, u.user_name, u.user_type
                  FROM messages m
                  JOIN user u ON m.sender_id = u.user_ID
                  WHERE m.conversation_id = ?
                  ORDER BY m.sent_at DESC
                  LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $conversationId, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get user's conversations
     */
    public function getUserConversations($userId, $limit = 20) {
        $query = "SELECT
                    c.conversation_id,
                    c.participant_1,
                    c.participant_2,
                    c.job_post_id,
                    c.last_message_at,
                    c.created_at,
                    jp.jobTitle,
                    u1.user_name as participant_1_name,
                    u2.user_name as participant_2_name,
                    u1.user_type as participant_1_type,
                    u2.user_type as participant_2_type,
                    (SELECT message_text FROM messages
                     WHERE conversation_id = c.conversation_id
                     ORDER BY sent_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM messages
                     WHERE conversation_id = c.conversation_id
                     AND sender_id != ?
                     AND is_read = FALSE) as unread_count
                  FROM conversations c
                  LEFT JOIN job_post jp ON c.job_post_id = jp.job_post_id
                  JOIN user u1 ON c.participant_1 = u1.user_ID
                  JOIN user u2 ON c.participant_2 = u2.user_ID
                  WHERE (c.participant_1 = ? OR c.participant_2 = ?)
                  AND c.is_active = TRUE
                  ORDER BY c.last_message_at DESC
                  LIMIT ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iiii", $userId, $userId, $userId, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead($conversationId, $userId) {
        $query = "UPDATE messages
                  SET is_read = TRUE, read_at = CURRENT_TIMESTAMP
                  WHERE conversation_id = ? AND sender_id != ? AND is_read = FALSE";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $conversationId, $userId);
        return $stmt->execute();
    }

    /**
     * Get conversation details
     */
    public function getConversationDetails($conversationId, $userId) {
        $query = "SELECT
                    c.*,
                    jp.jobTitle,
                    jp.detail as job_detail,
                    u1.user_name as participant_1_name,
                    u2.user_name as participant_2_name,
                    u1.user_type as participant_1_type,
                    u2.user_type as participant_2_type
                  FROM conversations c
                  LEFT JOIN job_post jp ON c.job_post_id = jp.job_post_id
                  JOIN user u1 ON c.participant_1 = u1.user_ID
                  JOIN user u2 ON c.participant_2 = u2.user_ID
                  WHERE c.conversation_id = ?
                  AND (c.participant_1 = ? OR c.participant_2 = ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $conversationId, $userId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get unread message count for user
     */
    public function getUnreadMessageCount($userId) {
        $query = "SELECT COUNT(*) as unread_count
                  FROM messages m
                  JOIN conversations c ON m.conversation_id = c.conversation_id
                  WHERE (c.participant_1 = ? OR c.participant_2 = ?)
                  AND m.sender_id != ?
                  AND m.is_read = FALSE";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $userId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['unread_count'];
    }

    /**
     * Delete conversation (soft delete)
     */
    public function deleteConversation($conversationId, $userId) {
        $query = "UPDATE conversations
                  SET is_active = FALSE
                  WHERE conversation_id = ?
                  AND (participant_1 = ? OR participant_2 = ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $conversationId, $userId, $userId);
        return $stmt->execute();
    }
}

// API Endpoints for messaging
if (isset($_GET['action'])) {
    $messagingService = new MessagingService();
    header('Content-Type: application/json');

    switch ($_GET['action']) {
        case 'get_conversations':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $result = $messagingService->getUserConversations(getCurrentUserId());
            $conversations = [];

            while ($row = $result->fetch_assoc()) {
                $conversations[] = $row;
            }

            echo json_encode(['success' => true, 'conversations' => $conversations]);
            break;

        case 'get_messages':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $conversationId = $_GET['conversation_id'] ?? 0;
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;

            // Verify user has access to this conversation
            $conversationDetails = $messagingService->getConversationDetails($conversationId, getCurrentUserId());
            if (!$conversationDetails) {
                echo json_encode(['success' => false, 'message' => 'Conversation not found']);
                exit;
            }

            $result = $messagingService->getMessages($conversationId, $limit, $offset);
            $messages = [];

            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            // Mark messages as read
            $messagingService->markMessagesAsRead($conversationId, getCurrentUserId());

            echo json_encode(['success' => true, 'messages' => array_reverse($messages), 'conversation' => $conversationDetails]);
            break;

        case 'send_message':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $conversationId = $_POST['conversation_id'] ?? 0;
            $messageText = trim($_POST['message'] ?? '');
            $messageType = $_POST['message_type'] ?? 'text';

            if (empty($messageText)) {
                echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
                exit;
            }

            // Verify user has access to this conversation
            $conversationDetails = $messagingService->getConversationDetails($conversationId, getCurrentUserId());
            if (!$conversationDetails) {
                echo json_encode(['success' => false, 'message' => 'Conversation not found']);
                exit;
            }

            $success = $messagingService->sendMessage($conversationId, getCurrentUserId(), $messageText, $messageType);
            echo json_encode(['success' => $success]);
            break;

        case 'start_conversation':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $participant2 = $_POST['participant_2'] ?? 0;
            $jobPostId = $_POST['job_post_id'] ?? null;

            if (!$participant2) {
                echo json_encode(['success' => false, 'message' => 'Invalid participant']);
                exit;
            }

            $conversationId = $messagingService->getOrCreateConversation(getCurrentUserId(), $participant2, $jobPostId);
            echo json_encode(['success' => true, 'conversation_id' => $conversationId]);
            break;

        case 'get_unread_count':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $unreadCount = $messagingService->getUnreadMessageCount(getCurrentUserId());
            echo json_encode(['success' => true, 'unread_count' => $unreadCount]);
            break;

        case 'mark_read':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $conversationId = $_POST['conversation_id'] ?? 0;
            $success = $messagingService->markMessagesAsRead($conversationId, getCurrentUserId());
            echo json_encode(['success' => $success]);
            break;
    }
    exit;
}
?>