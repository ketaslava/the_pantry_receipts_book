<?php
session_start();

if(isset($_SESSION['admin'])) {
    header("Location: /the_pantry_receipts_book/admin_panel.php");
} else {
    header("Location: /the_pantry_receipts_book/admin_auth.php");
}
exit;
