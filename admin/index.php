<?php
session_start();

if(isset($_SESSION['admin'])) {
    header("Location: ../admin_panel.php");
} else {
    header("Location: ../admin_auth.php");
}
exit;
