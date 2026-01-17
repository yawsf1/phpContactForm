<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'secure_contact';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function check_csrf($token) {
    return isset($token) && $token === $_SESSION['csrf_token'];
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf_token'])) die("CSRF token mismatch!");

    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $message = sanitize($_POST['message']);

    if (strlen($name) < 3) $errors[] = "Name must be at least 3 characters.";
    if (!is_valid_email($email)) $errors[] = "Invalid email address.";
    if (strlen($message) < 5) $errors[] = "Message must be at least 5 characters.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO messages (name,email,message) VALUES (?,?,?)");
        $stmt->bind_param("sss",$name,$email,$message);
        if ($stmt->execute()) $success="Message sent!";
        else $errors[]="Database error!";
        $stmt->close();
    }
}

$result = $conn->query("SELECT name,email,message,created_at FROM messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Secure One-Page Contact Form</title>
<style>
    body { 
        font-family: Arial; 
        background:#f4f4f9; 
        margin:0; 
        padding:0; 
    }
    .container { 
        max-width:700px; 
        margin:50px auto; 
        background:#fff; 
        padding:30px; 
        border-radius:12px; 
        box-shadow:0 5px 20px rgba(0,0,0,0.1);
    }
    h1,h2 { 
        text-align:center; 
        color:#333; 
    }
    input,textarea,button { 
        width:100%; 
        padding:12px; 
        margin:10px 0; 
        border-radius:6px; 
        border:1px solid #ccc; 
        box-sizing:border-box; 
    }
    button { 
        background:#4CAF50; 
        color:#fff; 
        border:none; 
        cursor:pointer; 
    }
    button:hover { 
        background:#45a049; 
    }
    .message { 
        background:#f1f1f1; 
        padding:12px; 
        margin:10px 0; 
        border-left:4px solid #4CAF50; 
    }
    .error { 
        color:red; 
    }
    .success { 
        color:green; 
    }
</style>
</head>
<body>
    <div class="container">
        <h1>Contact Us</h1>

        <?php if($success) echo "<p class='success'>$success</p>"; ?>
        <?php foreach($errors as $err) echo "<p class='error'>$err</p>"; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
            <button type="submit">Send Message</button>
        </form>

        <h2>Messages</h2>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="message">
                <strong><?= $row['name'] ?> (<?= $row['email'] ?>)</strong><br>
                <?= $row['message'] ?><br>
                <small><?= $row['created_at'] ?></small>
            </div>
        <?php endwhile; ?>
    </div>
    </body>
</html>
