<?php
include 'dbcon.inc.php';

function login($email, $password)
{
    global $conn;
    //$password = password_hash($password, PASSWORD_DEFAULT);
    // Prepared statement to prevent SQL injection
    $sql = "
        SELECT u.user_id, u.first_name, u.last_name, u.email, u.password_hash,
       r.role_name
FROM users u
INNER JOIN roles r ON u.role_id = r.role_id
WHERE u.email = '".$email."' 
LIMIT 1;
";
    // $result = query($conn, $sql);
    $result = mysqli_query($conn, $sql);
    $resultCheck = mysqli_num_rows($result);

    if ($resultCheck < 1) {
        return false; // No user found with that email
    
    }
    else {
        $row = mysqli_fetch_assoc($result);
        // Verify the password
        if (!password_verify($password, $row['password_hash'])) {
            return false; // Password does not match
        }
        $_SESSION['email'] = $email;
        //$_SESSION['password'] = mysqli_fetch_assoc($result)['role_name'];

        return true;

    }

}
function isLoggedIn() {
    return isset($_SESSION['email']);
}

function getOrderPapersAgenda($order_paper_id) {
    global $conn;
    $sql = "SELECT * FROM `order_papers` WHERE `order_paper_id` = ?;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_paper_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $agendaItems = [];
    while ($row = $result->fetch_assoc()) {
        $agendaItems[] = $row;
    }
    return $agendaItems;
}

function getUserByEmail($email) {
    global $conn;
    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, r.role_name
            FROM users u
            INNER JOIN roles r ON u.role_id = r.role_id
            WHERE u.email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function roleByEmail($email) {
    if ($email === 'admin@1234.com'){
        return 'Admin';
    }else {
        global $conn;
        $sql = "SELECT r.role_name
                FROM users u
                INNER JOIN roles r ON u.role_id = r.role_id
                WHERE u.email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['role_name'];
        }
    }
    return null;
}

function add_user($first_name, $last_name, $email, $password, $role_id = 1) {
    global $conn;
    // Hash the password before storing it
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?)";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $password_hash, $role_id);
        $stmt->execute();
    } catch (Exception $e) {
        // Handle exception (e.g., log the error)
        error_log("Error adding user: " . $e->getMessage());
        flashMessage('error', 'An error occurred while registering. Please try again.  ' . $e-> getMessage(), '../register.html', 2);
        throw $e; // Re-throw the exception after logging it
    }
}

function getUsername ($email){

    global $conn;
    $sql = "SELECT first_name FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['first_name'];
    }
    return null;
}

function getUserID($email){

    global $conn;
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['user_id'];
    }
    return null;
}
function getSuggestions() {
    global $conn;
    $sql = "SELECT s.suggestion_id, s.content, s.votes, s.status, s.date_posted, u.first_name, u.last_name
            FROM suggestions s
            JOIN users u ON s.user_id = u.user_id
            ORDER BY s.votes DESC, s.date_posted DESC";
    $result = mysqli_query($conn, $sql);
    $suggestions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = $row;
    }
    return $suggestions;
}
function updateUsername($user_id, $newName) {
    global $conn; // or your DB connection
    $stmt = $conn->prepare("UPDATE users SET first_name=? WHERE user_id=?");
    return $stmt->execute([$newName, $user_id]);
}

function updatePassword($user_id, $newPass) {
    global $conn;
    $hashed = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE conn=?");
    return $stmt->execute([$hashed, $user_id]);
}

// ... your other functions ...

/**
 * Get all feedback items assigned to an MP.
 * You can filter by constituency if you store MP’s constituency in DB.
 */
function getFeedbackForMP($email) {
    global $conn; // your DB connection

    // you can still get MP id but we’re not using it
    // $mp_id = getUserID($email); 

    $sql = "SELECT 
                f.feedback_id,
                f.user_id,
                u.first_name AS user_name, 
                u.first_name AS last_name,       -- name of citizen
                u.email AS user_email,     -- email of citizen
                f.subject,
                f.date_submitted,
                f.status
            FROM feedback f
            JOIN users u ON f.user_id = u.user_id
            ORDER BY f.date_submitted DESC";

    $stmt = $conn->prepare($sql);
    // no bind_param because no ? placeholder
    $stmt->execute();
    $result = $stmt->get_result();

    $feedbacks = [];
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
    return $feedbacks;
}



/**
 * Get a single feedback record by ID.
 */
function getFeedbackById($id) {
    global $conn;

    $sql = "SELECT * FROM feedback WHERE feedback_id  = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getNumberOfPetitions($mp_id) {
    global $conn;
    $sql = "SELECT COUNT(*) AS petition_count FROM petitions WHERE mp_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['petition_count'];
    }
    return 0;
}

function getNumberOfActivePetitions($mp_id) {
    global $conn;
    $sql = "SELECT COUNT(*) AS active_petition_count FROM petitions WHERE mp_id = ? AND status = 'Open'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['active_petition_count'];
    }
    return 0;
}
function getNumberofSignedPetitions($mp_id) {
    global $conn;
    $sql = "SELECT COUNT(DISTINCT ps.petition_id) AS signed_petition_count
            FROM petition_signatures ps
            JOIN petitions p ON ps.petition_id = p.petition_id
            WHERE p.mp_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['signed_petition_count'];
    }
    return 0;
}
function sendNotification($conn, $user_id, $message, $link = null, $type = 'general') {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message,  type) VALUES ( ?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $message,  $type);
    $stmt->execute();
    $stmt->close();
}
function getNumberOfNotifications($user_id) {
    global $conn;
    $sql = "SELECT COUNT(*) AS notification_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['notification_count'];
    }
    return 0;
}
function getUsernameById($user_id) {
    global $conn;
    $sql = "SELECT first_name, last_name FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['first_name'].' '.$row['last_name'];
    }
    return null;
}
function getServerUptime() {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        $uptime = exec('net stats srv'); 
        if (preg_match('/since (.*)/i', $uptime, $matches)) {
            return $matches[1];
        }
    } else {
        // Linux/Unix
        $uptime = @file_get_contents('/proc/uptime');
        if ($uptime) {
            $uptime = explode(' ', $uptime);
            $seconds = round($uptime[0]);
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$hours}h {$minutes}m";
        }
    }
    return "N/A";
}
function getActiveSessionsCount() {
    
    // if using default file-based sessions:
    $sessionFiles = glob(session_save_path() . '/sess_*');
    return $sessionFiles ? count($sessionFiles) : 0;
}
// Place at the very top of your PHP file
$start_time = microtime(true);

// At the point you want to display page load time:
function getPageLoadTime($start_time) {
    $end_time = microtime(true);
    $load_time = round(($end_time - $start_time) * 1000, 2); // ms
    return $load_time;
}
function getAgendaTitle($agenda_item_id) {
    global $conn;
    $sql = "SELECT title FROM agenda_items WHERE agenda_item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $agenda_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['title'];
    }
    return null;
}
?>
