<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>The Pantry Receipts Book</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="css/style.css">
</head>

<body>
<header class="header">
    <div class="container header-inner" style="padding-top: 10px; padding-bottom: 10px;">

        <a href="index.php" style="width:480px; max-width: 30%;">
            <img src="res/images/the_pantry_logo.png" style="max-width: 100%; border-radius:10px;">
        </a>

        <div style="display: flex;">
            <a class="btn" href="search.php">Search</a>
            <a class="btn" href="search_by_ingredients.php" style="margin-left: 10px; white-space: nowrap;">By Ingredients</a>
            <!--a class="btn" href="admin_panel.php">Admin</a-->
        </div>
    </div>
</header>

<main class="container">
