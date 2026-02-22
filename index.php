<?php include "patterns/header.php"; ?>

<h2>Recipes</h2>

<?php 
$list = json_decode(file_get_contents("data/recipes/recipes_list.json"), true) ?? [];

foreach($list as $id):
$data = json_decode(file_get_contents("data/recipes/$id/recipe_data.json"), true);
?>

<a href="recipe_page.php?id=<?= $id ?>" class="card">

    <h1 style="font-size:32px"><?= htmlspecialchars($data['name']) ?></h1>

    <?php $headerPath = "data/recipes/$id/header_image/" . ($data['header_image'] ?? '');
    if (!empty($data['header_image']) && file_exists($headerPath)): 
    ?>
        <img src="/the_pantry_recipes_book/data/recipes/<?= $id ?>/header_image/<?= htmlspecialchars($data['header_image']) ?>"
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
    
</a>

<?php endforeach; ?>

<?php include "/the_pantry_recipes_book/patterns/footer.php"; ?>
