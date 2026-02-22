<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_auth.php");
    exit;
}

$baseDir  = __DIR__ . "/data/recipes/";
$listFile = $baseDir . "recipes_list.json";

/* ---------- SAFE LOAD ---------- */
function load_recipe_list($file) {
    if (!file_exists($file)) return [];

    $content = file_get_contents($file);
    if (trim($content) === '') return [];

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

$recipes = load_recipe_list($listFile);

/* ---------- RANDOM UNIQUE ID ---------- */
function generate_unique_id($existing, $baseDir) {

    // turn list into a fast lookup set
    $set = array_flip($existing);

    do {
        // 6-digit random ID. Increase range if you ever reach scale.
        $id = (string) random_int(100000, 999999);

        $existsInList = isset($set[$id]);
        $existsOnDisk = is_dir($baseDir . $id);

        // loop until we find an unused one
    } while ($existsInList || $existsOnDisk);

    return $id;
}

$newId = generate_unique_id($recipes, $baseDir); ?>

<?php include "patterns/header.php"; ?>

<h1>Admin Panel</h1>

<h2>Recepites</h2>

<div style="display: flex; margin-bottom: 25px;">
    <a class="btn" href="edit_recipe.php?id=<?= $newId ?>"> Add Recipe </a>
</div>

<?php 
$list = json_decode(file_get_contents("data/recipes/recipes_list.json"), true) ?? [];

foreach($list as $id):
$data = json_decode(file_get_contents("data/recipes/$id/recipe_data.json"), true);
?>

<div class="card">
    <h1 style="font-size:32px"><?= htmlspecialchars($data['name']) ?></h1>

    <?php $headerPath = "data/recipes/$id/header_image/" . ($data['header_image'] ?? '');
    if (!empty($data['header_image']) && file_exists($headerPath)): 
    ?>
        <img src="data/recipes/<?= $id ?>/header_image/<?= htmlspecialchars($data['header_image']) ?>"
         style="max-width:240px;border-radius:10px;">
    <?php endif; ?>

    <?php if (!empty($data['ingredients'])): ?>
        <p><strong>Ingredients:</strong>
            <?= htmlspecialchars(implode(", ", array_map('trim', $data['ingredients']))) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($data['tags'])): ?>
        <p><strong>Tags:</strong>
            <?= htmlspecialchars(implode(", ", array_map('trim', $data['tags']))) ?>
        </p>
    <?php endif; ?>

    <div style="display: flex;">
        <a class="btn" href="edit_recipe.php?id=<?= urlencode($id) ?>">Edit</a>
        <a class="btn" href="recipe_page.php?id=<?= urlencode($id) ?>", style="margin-left: 15px;">View</a>
    </div>
</div>

<?php endforeach; ?>

<?php include "patterns/footer.php"; ?>
