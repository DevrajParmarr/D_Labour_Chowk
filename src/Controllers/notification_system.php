<?php
/**
 * D Labour Chowk Notification System
 * Handles toast notifications, alerts, and user notifications
 */

class NotificationSystem {
    private static $instance = null;
    private $notifications = [];

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add a success notification
     */
    public function addSuccess($message, $title = 'Success') {
        $this->addNotification('success', $title, $message);
    }

    /**
     * Add an error notification
     */
    public function addError($message, $title = 'Error') {
        $this->addNotification('error', $title, $message);
    }

    /**
     * Add a warning notification
     */
    public function addWarning($message, $title = 'Warning') {
        $this->addNotification('warning', $title, $message);
    }

    /**
     * Add an info notification
     */
    public function addInfo($message, $title = 'Information') {
        $this->addNotification('info', $title, $message);
    }

    /**
     * Add a notification
     */
    private function addNotification($type, $title, $message) {
        $notification = [
            'id' => uniqid('notif_'),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'timestamp' => time(),
            'read' => false
        ];

        if (!isset($_SESSION['notifications'])) {
            $_SESSION['notifications'] = [];
        }

        $_SESSION['notifications'][] = $notification;
        $this->notifications[] = $notification;
    }

    /**
     * Get all notifications
     */
    public function getNotifications($unreadOnly = false) {
        $notifications = $_SESSION['notifications'] ?? [];

        if ($unreadOnly) {
            return array_filter($notifications, function($n) {
                return !$n['read'];
            });
        }

        return $notifications;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        if (isset($_SESSION['notifications'])) {
            foreach ($_SESSION['notifications'] as &$notification) {
                if ($notification['id'] === $notificationId) {
                    $notification['read'] = true;
                    break;
                }
            }
        }
    }

    /**
     * Clear all notifications
     */
    public function clearNotifications() {
        $_SESSION['notifications'] = [];
        $this->notifications = [];
    }

    /**
     * Get notification count
     */
    public function getNotificationCount($unreadOnly = true) {
        return count($this->getNotifications($unreadOnly));
    }

    /**
     * Render toast notifications HTML
     */
    public function renderToastContainer() {
        $notifications = $this->getNotifications();
        if (empty($notifications)) return '';

        ob_start();
        ?>
        <!-- Toast Container -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <?php foreach ($notifications as $notification): ?>
                <div class="toast align-items-center text-white bg-<?php echo $this->getBootstrapColor($notification['type']); ?> border-0"
                     role="alert" aria-live="assertive" aria-atomic="true"
                     data-bs-autohide="true" data-bs-delay="5000"
                     id="toast-<?php echo $notification['id']; ?>">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong><?php echo htmlspecialchars($notification['title']); ?>:</strong>
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            // Initialize toasts
            document.addEventListener('DOMContentLoaded', function() {
                var toastElements = document.querySelectorAll('.toast');
                toastElements.forEach(function(toastEl) {
                    var toast = new bootstrap.Toast(toastEl);
                    toast.show();
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Get Bootstrap color class for notification type
     */
    private function getBootstrapColor($type) {
        $colors = [
            'success' => 'success',
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info'
        ];
        return $colors[$type] ?? 'info';
    }

    /**
     * Render notification dropdown for navbar
     */
    public function renderNotificationDropdown() {
        $notifications = $this->getNotifications();
        $unreadCount = $this->getNotificationCount(true);

        ob_start();
        ?>
        <div class="dropdown">
            <button class="btn btn-outline-primary position-relative" type="button"
                    id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $unreadCount; ?>
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                <?php endif; ?>
            </button>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 300px;">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <?php if (empty($notifications)): ?>
                    <li><span class="dropdown-item text-muted">No notifications</span></li>
                <?php else: ?>
                    <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                        <li>
                            <a class="dropdown-item <?php echo !$notification['read'] ? 'fw-bold' : ''; ?>"
                               href="#" onclick="markAsRead('<?php echo $notification['id']; ?>')">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-2">
                                        <i class="fas fa-<?php echo $this->getNotificationIcon($notification['type']); ?> text-<?php echo $this->getBootstrapColor($notification['type']); ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo htmlspecialchars($notification['title']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($notification['message']); ?></div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#" onclick="clearAllNotifications()">Clear All</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <script>
            function markAsRead(notificationId) {
                fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'notification_id=' + notificationId
                });
            }

            function clearAllNotifications() {
                if (confirm('Are you sure you want to clear all notifications?')) {
                    fetch('clear_notifications.php', {
                        method: 'POST'
                    }).then(() => location.reload());
                }
            }
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Get icon for notification type
     */
    private function getNotificationIcon($type) {
        $icons = [
            'success' => 'check-circle',
            'error' => 'exclamation-circle',
            'warning' => 'exclamation-triangle',
            'info' => 'info-circle'
        ];
        return $icons[$type] ?? 'bell';
    }
}

// Helper functions
function addNotification($type, $title, $message) {
    $notificationSystem = NotificationSystem::getInstance();
    switch ($type) {
        case 'success':
            $notificationSystem->addSuccess($message, $title);
            break;
        case 'error':
            $notificationSystem->addError($message, $title);
            break;
        case 'warning':
            $notificationSystem->addWarning($message, $title);
            break;
        case 'info':
            $notificationSystem->addInfo($message, $title);
            break;
    }
}

function getNotificationSystem() {
    return NotificationSystem::getInstance();
}
?>