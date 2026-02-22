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

<link rel="stylesheet" href="/the_pantry_receipts_book/css/style.css">
</head>

<body>
<header class="header">
    <div class="container header-inner">
        <a class="logo" href="/the_pantry_receipts_book/index.php">
            The Pantry
        </a>

        <div class="header-actions">
            <a class="btn" href="/the_pantry_receipts_book/search.php">Search</a>
            <a class="btn" href="/the_pantry_receipts_book/search_by_ingredients.php">Search By Ingredients</a>
            <!--a class="btn" href="/the_pantry_receipts_book/admin_panel.php">Admin</a-->
        </div>
    </div>
</header>

<main class="container">
