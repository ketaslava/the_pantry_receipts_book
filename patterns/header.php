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
    <div class="container header-inner">
        <a class="logo" href="index.php">
            The Pantry
        </a>

        <div class="header-actions">
            <a class="btn" href="search.php">Search</a>
            <a class="btn" href="search_by_ingredients.php">Search By Ingredients</a>
            <!--a class="btn" href="admin_panel.php">Admin</a-->
        </div>
    </div>
</header>

<main class="container">
