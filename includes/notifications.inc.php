<?php
function getUnreadCount($user_id, $conn) {
    $sql = "SELECT COUNT(*) AS unread FROM notifications WHERE user_id=? AND is_read=0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['unread'];
    return $count;
}

function getUserNotifications($user_id, $conn) {
    $sql = "SELECT notification_id, message, is_read, date_sent
            FROM notifications
            WHERE user_id=? ORDER BY date_sent DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
