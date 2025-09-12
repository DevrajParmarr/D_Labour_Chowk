<?php
require_once 'config.php';
require_once 'notification_system.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notificationId = $_POST['notification_id'];

    $notificationSystem = NotificationSystem::getInstance();
    $notificationSystem->markAsRead($notificationId);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>