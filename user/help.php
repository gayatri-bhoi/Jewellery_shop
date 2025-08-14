<?php
session_start();
require_once('../middleware.php');
require_once('../config/db.php');

checkLogin();
if (!isUser()) header("Location: ../auth/login.php");

$user = $_SESSION['user'];
$userId = (int)$user['id'];

function getChatHistory($conn, $userId) {
    $sql = "
        SELECT id, message AS text, created_at, is_read, 'user' AS sender
        FROM messages
        WHERE user_id = ?
        UNION ALL
        SELECT mr.message_id, mr.reply AS text, mr.created_at,
               m.is_read,
               CASE WHEN mr.admin_id IS NULL THEN 'user' ELSE 'admin' END AS sender
        FROM message_replies mr
        JOIN messages m ON mr.message_id = m.id
        WHERE m.user_id = ?
        ORDER BY created_at ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $userId, $userId);
    $stmt->execute();
    return $stmt->get_result();
}

// For initial server-side render (avoid blank screen on load)
$initialResult = getChatHistory($conn, $userId);

// AJAX: load messages
if (isset($_GET['action']) && $_GET['action'] === 'load') {
    $chat = getChatHistory($conn, $userId);
    while ($row = $chat->fetch_assoc()) {
        $isUser = ($row['sender'] === 'user');
        $cls = $isUser ? 'bubble green right' : 'bubble white left';
        $txt = nl2br(htmlspecialchars($row['text']));
        $t = date('d M Y, h:i A', strtotime($row['created_at']));

        // Add tick marks only for user messages (sent)
        $tickHtml = '';
        if ($isUser) {
            if ($row['is_read']) {
                $tickHtml = ' <span class="tick double">&#10003;&#10003;</span>';  // double tick (read)
            } else {
                $tickHtml = ' <span class="tick single">&#10003;</span>';         // single tick (delivered)
            }
        }

        echo "<div class='$cls'>{$txt}{$tickHtml}<span class='time'>{$t}</span></div>";
    }
    exit;
}

// AJAX: send message
if (isset($_POST['action']) && $_POST['action'] === 'send') {
    $text = trim($_POST['text'] ?? '');
    if ($text !== '') {
        // Find latest message (ticket) for this user
        $q = $conn->prepare("SELECT id FROM messages WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $q->bind_param('i', $userId);
        $q->execute();
        $res = $q->get_result();

        if ($row = $res->fetch_assoc()) {
            $msgId = (int)$row['id'];
            $ins = $conn->prepare("INSERT INTO message_replies (message_id, admin_id, reply, created_at) VALUES (?, NULL, ?, NOW())");
            $ins->bind_param('is', $msgId, $text);
            $ins->execute();
        } else {
            // No existing messages, create a new one
            $ins2 = $conn->prepare("INSERT INTO messages (user_id, subject, message, created_at, is_read) VALUES (?, 'Chat', ?, NOW(), 0)");
            $ins2->bind_param('is', $userId, $text);
            $ins2->execute();
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Support Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Fonts & Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

<!-- Bootstrap 4.6 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />

<!-- RuangAdmin CSS -->
<link href="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/css/ruang-admin.min.css" rel="stylesheet" />

<style>
#messages {
  display: flex;
  flex-direction: column;
  gap: 8px;
  overflow-y: auto;
  height: 100%;
  padding: 15px;
  background: #efeae2;
  border-radius: 0 0 12px 12px;
  box-shadow: inset 0 3px 8px rgb(0 0 0 / 0.1);
}

.bubble {
  max-width: 70%;
  padding: 12px 16px;
  border-radius: 16px;
  box-shadow: 0 1px 0 rgba(0,0,0,0.1);
  word-wrap: break-word;
  font-size: 14px;
  line-height: 1.3;
  position: relative;
}

.bubble.green.right {
  background: #dcf8c6;
  align-self: flex-end;
  border-bottom-right-radius: 4px;
}

.bubble.white.left {
  background: #fff;
  align-self: flex-start;
  border-bottom-left-radius: 4px;
}

.time {
  display: block;
  font-size: 10px;
  color: #666;
  margin-top: 6px;
  text-align: right;
}

.tick.single {
  color: gray;
  margin-left: 6px;
  font-size: 14px;
  vertical-align: middle;
}

.tick.double {
  color: #4fc3f7;
  margin-left: 6px;
  font-size: 14px;
  vertical-align: middle;
}

.input-bar {
  padding: 12px 20px;
  border-top: 1px solid #ccc;
  background: #fafafa;
  display: flex;
  align-items: center;
  gap: 12px;
}

.input-bar input {
  flex: 1;
  padding: 12px 16px;
  font-size: 15px;
  border-radius: 25px;
  border: 1px solid #ddd;
  outline: none;
  transition: border-color 0.2s ease;
}

.input-bar input:focus {
  border-color: #075E54;
}

.input-bar button {
  background: #075E54;
  color: white;
  border: none;
  border-radius: 50%;
  width: 44px;
  height: 44px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s ease;
}

.input-bar button:hover {
  background: #0b806a;
}
.chat-header {
    background: #6777EF;
    color: white;
    padding: 15px;
    font-weight: bold;
    flex-shrink: 0;
}

</style>
</head>
<body id="page-top">

<div id="wrapper">
  <?php include('partials/sidebar.php'); ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include('partials/topbar.php'); ?>

      <div class="container-fluid d-flex flex-column" style="max-width: 900px; margin: 20px auto; height: 75vh;">

        <div class="chat-header">
          <?= htmlspecialchars($user['name']) ?> – Support Chat
        </div>

        <div id="messages" class="flex-grow-1"></div>

        <div class="input-bar">
          <input type="text" id="msg-text" placeholder="Type a message..." autocomplete="off" />
          <button id="send-btn" title="Send"><i class="fas fa-paper-plane"></i></button>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/indrijunanda/RuangAdmin/js/ruang-admin.min.js"></script>

<script>
(function(){
  const $messages = $('#messages');
  const $input = $('#msg-text');

  // Scroll messages container to bottom smoothly
  function scrollBottom() {
    $messages.stop().animate({ scrollTop: $messages[0].scrollHeight }, 300);
  }

  // Load messages via AJAX
  function loadChat() {
    $.get('?action=load', function(data) {
      $messages.html(data);
      scrollBottom();
    });
  }

  // Send message via AJAX
  function sendMessage() {
    const text = $input.val().trim();
    if (!text) return;

    $.post('', { action: 'send', text: text }, function() {
      $input.val('');
      loadChat();
    }).fail(() => {
      alert('Failed to send message, please try again.');
    });
  }

  // Send button click handler
  $('#send-btn').on('click', function(e) {
    e.preventDefault();
    sendMessage();
  });

  // Enter key sends message (without shift)
  $input.on('keypress', function(e) {
    if (e.which === 13 && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  // Poll every 3 seconds for new messages
  setInterval(loadChat, 3000);

  // Initial load + scroll
  $(window).on('load', function() {
    loadChat();
  });
})();
</script>

</body>
</html>
