<?php
require_once 'config.php';
require_once 'notification_system.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notificationSystem = NotificationSystem::getInstance();
    $notificationSystem->clearNotifications();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>