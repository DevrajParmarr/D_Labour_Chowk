<?php
/**
 * Messaging Interface for D Labour Chowk
 * Real-time messaging between clients and workers
 */

require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login_form.php');
}

$userId = getCurrentUserId();
$userType = getCurrentUserType();
$userName = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - D Labour Chowk</title>
    <meta name="description" content="Chat with clients and workers in real-time">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --border-color: #e5e7eb;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            height: 100vh;
            display: flex;
            background: white;
            border-radius: 12px;
            margin: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Conversations Sidebar */
        .conversations-sidebar {
            width: 350px;
            border-right: 1px solid var(--border-color);
            background: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--light-bg);
        }

        .sidebar-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f5f9;
            position: relative;
        }

        .conversation-item:hover,
        .conversation-item.active {
            background: var(--light-bg);
        }

        .conversation-item.active {
            border-left: 4px solid var(--primary-color);
            background: rgba(37, 99, 235, 0.05);
        }

        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .conversation-info {
            flex: 1;
            min-width: 0;
        }

        .conversation-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
            font-size: 0.95rem;
        }

        .conversation-preview {
            color: var(--text-secondary);
            font-size: 0.85rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .conversation-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .conversation-time {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .unread-badge {
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--light-bg);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-partner-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .chat-partner-info h5 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .chat-partner-info p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text fill="%23f1f5f9" font-size="20" y="50%">💬</text></svg>') repeat;
            background-size: 50px 50px;
        }

        .message {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-end;
            gap: 12px;
        }

        .message.sent {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .message.sent .message-avatar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .message-content {
            max-width: 70%;
            position: relative;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 0.9rem;
            line-height: 1.4;
            position: relative;
        }

        .message.received .message-bubble {
            background: #f1f5f9;
            color: var(--text-primary);
            border-bottom-left-radius: 4px;
        }

        .message.sent .message-bubble {
            background: var(--primary-color);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 5px;
            text-align: right;
        }

        .message.sent .message-time {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Message Input */
        .message-input-area {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            background: white;
        }

        .message-input-container {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--light-bg);
            border-radius: 25px;
            padding: 8px 20px;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .message-input-container:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .message-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 0;
            font-size: 0.95rem;
            resize: none;
            min-height: 20px;
            max-height: 100px;
            outline: none;
        }

        .message-input::placeholder {
            color: var(--text-secondary);
        }

        .send-button {
            background: var(--primary-color);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .send-button:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }

        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: var(--text-secondary);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .empty-description {
            font-size: 1rem;
            margin-bottom: 20px;
            max-width: 300px;
        }

        /* Loading */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
        }

        .loading i {
            font-size: 2rem;
            margin-right: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .chat-container {
                margin: 0;
                border-radius: 0;
            }

            .conversations-sidebar {
                width: 100%;
                position: absolute;
                left: -100%;
                top: 0;
                z-index: 100;
                transition: left 0.3s ease;
            }

            .conversations-sidebar.show {
                left: 0;
            }

            .chat-area {
                width: 100%;
            }

            .sidebar-toggle {
                display: block !important;
                position: absolute;
                top: 20px;
                left: 20px;
                z-index: 101;
                background: var(--primary-color);
                color: white;
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }
        }

        .sidebar-toggle {
            display: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle (Mobile) -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="chat-container">
        <!-- Conversations Sidebar -->
        <div class="conversations-sidebar" id="conversationsSidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">
                    <i class="fas fa-comments me-2"></i>Messages
                </div>
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Search conversations..." id="searchInput">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <div class="conversations-list" id="conversationsList">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    Loading conversations...
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area" id="chatArea">
            <div class="empty-state" id="emptyState">
                <div class="empty-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="empty-title">Select a conversation</h3>
                <p class="empty-description">
                    Choose a conversation from the sidebar to start chatting with clients or workers.
                </p>
            </div>

            <!-- Chat Header (Hidden initially) -->
            <div class="chat-header" id="chatHeader" style="display: none;">
                <div class="chat-partner-avatar" id="partnerAvatar">?</div>
                <div class="chat-partner-info">
                    <h5 id="partnerName">Partner Name</h5>
                    <p id="partnerType">Client/Worker</p>
                </div>
            </div>

            <!-- Messages Container (Hidden initially) -->
            <div class="chat-messages" id="messagesContainer" style="display: none;">
                <!-- Messages will be loaded here -->
            </div>

            <!-- Message Input (Hidden initially) -->
            <div class="message-input-area" id="messageInputArea" style="display: none;">
                <div class="message-input-container">
                    <textarea
                        class="message-input"
                        id="messageInput"
                        placeholder="Type your message..."
                        rows="1"
                        maxlength="500"></textarea>
                    <button class="send-button" id="sendButton">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Global variables
        let currentConversationId = null;
        let currentPartner = null;
        let messageCheckInterval = null;

        // Initialize the messaging interface
        document.addEventListener('DOMContentLoaded', function() {
            loadConversations();
            setupEventListeners();
        });

        // Setup event listeners
        function setupEventListeners() {
            // Sidebar toggle for mobile
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                document.getElementById('conversationsSidebar').classList.toggle('show');
            });

            // Search conversations
            document.getElementById('searchInput').addEventListener('input', function() {
                filterConversations(this.value);
            });

            // Send message
            document.getElementById('sendButton').addEventListener('click', sendMessage);
            document.getElementById('messageInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Auto-resize textarea
            document.getElementById('messageInput').addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
        }

        // Load conversations
        function loadConversations() {
            fetch('messaging_service.php?action=get_conversations')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayConversations(data.conversations);
                    } else {
                        console.error('Failed to load conversations:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading conversations:', error);
                });
        }

        // Display conversations in sidebar
        function displayConversations(conversations) {
            const container = document.getElementById('conversationsList');

            if (conversations.length === 0) {
                container.innerHTML = `
                    <div class="empty-state" style="height: auto; padding: 40px 20px;">
                        <div class="empty-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h4 class="empty-title">No conversations yet</h4>
                        <p class="empty-description">
                            Start a conversation by contacting a client or worker from the dashboard.
                        </p>
                    </div>
                `;
                return;
            }

            let html = '';
            conversations.forEach(conv => {
                const otherParticipant = conv.participant_1_name === '<?php echo $userName; ?>' ?
                    { name: conv.participant_2_name, type: conv.participant_2_type } :
                    { name: conv.participant_1_name, type: conv.participant_1_type };

                const initials = otherParticipant.name.split(' ').map(n => n[0]).join('').toUpperCase();
                const timeAgo = formatTimeAgo(new Date(conv.last_message_at));
                const hasUnread = conv.unread_count > 0;

                html += `
                    <div class="conversation-item ${hasUnread ? 'unread' : ''}" onclick="openConversation(${conv.conversation_id})">
                        <div class="conversation-avatar">${initials}</div>
                        <div class="conversation-info">
                            <div class="conversation-name">${otherParticipant.name}</div>
                            <div class="conversation-preview">${conv.last_message || 'No messages yet'}</div>
                        </div>
                        <div class="conversation-meta">
                            <div class="conversation-time">${timeAgo}</div>
                            ${hasUnread ? `<div class="unread-badge">${conv.unread_count}</div>` : ''}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Open a conversation
        function openConversation(conversationId) {
            currentConversationId = conversationId;

            // Hide empty state
            document.getElementById('emptyState').style.display = 'none';

            // Show chat interface
            document.getElementById('chatHeader').style.display = 'flex';
            document.getElementById('messagesContainer').style.display = 'block';
            document.getElementById('messageInputArea').style.display = 'block';

            // Load conversation details and messages
            loadConversationDetails(conversationId);
            loadMessages(conversationId);

            // Mark as active in sidebar
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');

            // Hide sidebar on mobile
            if (window.innerWidth <= 768) {
                document.getElementById('conversationsSidebar').classList.remove('show');
            }

            // Start checking for new messages
            if (messageCheckInterval) {
                clearInterval(messageCheckInterval);
            }
            messageCheckInterval = setInterval(() => {
                loadMessages(conversationId, true);
            }, 5000);
        }

        // Load conversation details
        function loadConversationDetails(conversationId) {
            fetch(`messaging_service.php?action=get_messages&conversation_id=${conversationId}&limit=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.conversation) {
                        const conv = data.conversation;
                        const otherParticipant = conv.participant_1_name === '<?php echo $userName; ?>' ?
                            { name: conv.participant_2_name, type: conv.participant_2_type } :
                            { name: conv.participant_1_name, type: conv.participant_1_type };

                        const initials = otherParticipant.name.split(' ').map(n => n[0]).join('').toUpperCase();

                        document.getElementById('partnerAvatar').textContent = initials;
                        document.getElementById('partnerName').textContent = otherParticipant.name;
                        document.getElementById('partnerType').textContent = otherParticipant.type;

                        currentPartner = otherParticipant;
                    }
                });
        }

        // Load messages
        function loadMessages(conversationId, silent = false) {
            const container = document.getElementById('messagesContainer');

            if (!silent) {
                container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>';
            }

            fetch(`messaging_service.php?action=get_messages&conversation_id=${conversationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMessages(data.messages);
                    }
                });
        }

        // Display messages
        function displayMessages(messages) {
            const container = document.getElementById('messagesContainer');

            if (messages.length === 0) {
                container.innerHTML = `
                    <div class="empty-state" style="height: auto; padding: 40px 20px;">
                        <div class="empty-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4 class="empty-title">No messages yet</h4>
                        <p class="empty-description">
                            Start the conversation by sending the first message.
                        </p>
                    </div>
                `;
                return;
            }

            let html = '';
            messages.forEach(message => {
                const isSent = message.sender_id == <?php echo $userId; ?>;
                const time = formatMessageTime(new Date(message.sent_at));
                const initials = message.user_name.split(' ').map(n => n[0]).join('').toUpperCase();

                html += `
                    <div class="message ${isSent ? 'sent' : 'received'} fade-in">
                        <div class="message-avatar">${initials}</div>
                        <div class="message-content">
                            <div class="message-bubble">${escapeHtml(message.message_text)}</div>
                            <div class="message-time">${time}</div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;

            // Scroll to bottom
            container.scrollTop = container.scrollHeight;
        }

        // Send message
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message || !currentConversationId) {
                return;
            }

            // Disable input while sending
            input.disabled = true;
            document.getElementById('sendButton').disabled = true;

            const formData = new FormData();
            formData.append('conversation_id', currentConversationId);
            formData.append('message', message);

            fetch('messaging_service.php?action=send_message', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    input.style.height = 'auto';
                    loadMessages(currentConversationId);
                    loadConversations(); // Refresh conversation list
                } else {
                    alert('Failed to send message: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
            })
            .finally(() => {
                input.disabled = false;
                document.getElementById('sendButton').disabled = false;
                input.focus();
            });
        }

        // Filter conversations
        function filterConversations(query) {
            const items = document.querySelectorAll('.conversation-item');
            const lowerQuery = query.toLowerCase();

            items.forEach(item => {
                const name = item.querySelector('.conversation-name').textContent.toLowerCase();
                const preview = item.querySelector('.conversation-preview').textContent.toLowerCase();

                if (name.includes(lowerQuery) || preview.includes(lowerQuery)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Utility functions
        function formatTimeAgo(date) {
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;

            return date.toLocaleDateString();
        }

        function formatMessageTime(date) {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const messageDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            if (messageDate.getTime() === today.getTime()) {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (messageCheckInterval) {
                clearInterval(messageCheckInterval);
            }
        });
    </script>
</body>
</html>