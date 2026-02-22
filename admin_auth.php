<?php
session_start();

$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $admins = json_decode(file_get_contents("private/data/admins.json"), true);

    if(isset($admins[$login]) && $admins[$login] === $password) {
        $_SESSION['admin'] = $login;
        header("Location: admin_panel.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}

include "patterns/header.php";
?>

<div class="card">
    <h2>Admin Login</h2>

    <?php if($error): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Login</label>
        <input type="text" name="login" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button class="btn">Login</button>
    </form>
</div>

<?php include "patterns/footer.php"; ?>
