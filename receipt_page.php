<?php
include "patterns/header.php";

$id = $_GET['id'] ?? null;
$path = "data/receipts/$id/receipt_data.json";

if(!$id || !file_exists($path)) {
    echo "<div class='card'>Receipt not found</div>";
    include "patterns/footer.php";
    exit;
}

$data = json_decode(file_get_contents($path), true);
?>

<div class="card">

    <h1 style="font-size:32px; margin-bottom:21px;"><?= htmlspecialchars($data['name']) ?></h1>

    <?php 
    $headerPath = "data/receipts/$id/header_image/" . ($data['header_image'] ?? '');
    if (!empty($data['header_image']) && file_exists($headerPath)): 
    ?>
        <img src="<?= htmlspecialchars($headerPath) ?>" style="max-width:240px;border-radius:10px;">
    <?php endif; ?>

    <?php 
    if (!empty($data['images'])): ?>
        <div style="margin-top:20px;">
            <?php foreach ($data['images'] as $img): 
                $imgPath = "data/receipts/$id/images/" . $img;
                if(file_exists($imgPath)):
            ?>
                <img src="<?= htmlspecialchars($imgPath) ?>" style="max-width:240px; border-radius:10px; margin:5px;">
            <?php endif; endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($data['ingredients'])): ?>
        <p><strong>Ingredients:</strong>
            <?= htmlspecialchars(implode(", ", array_map('trim', $data['ingredients']))) ?>
        </p>
    <?php endif; ?>

    <br>

    <div id="content"><?= htmlspecialchars($data['text']) ?></div>

    <br>

    <?php if (!empty($data['small_text'])): ?>
    <p><strong>Additional:</strong></p>
    <div id="small_content" style="font-size:12px; color:#555;"><?= htmlspecialchars($data['small_text']) ?></div>
    <?php endif; ?>

    <br>

    <?php if (!empty($data['tags'])): ?>
        <p><strong>Tags:</strong>
            <?= htmlspecialchars(implode(", ", array_map('trim', $data['tags']))) ?>
        </p>
    <?php endif; ?>

</div>

<script>
// Simple markdown + link + newline processor
function renderMarkdown(elId) {
    const el = document.getElementById(elId);
    if (!el) return;
    let text = el.textContent;

    // Convert URLs to clickable links
    const urlRegex = /https?:\/\/[^\s<>"')\]]+/g;
    text = text.replace(urlRegex, url => `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`);

    // Convert headings #, ##, ###
    text = text.replace(/^### (.+)$/gm, "<h3>$1</h3>");
    text = text.replace(/^## (.+)$/gm, "<h2>$1</h2>");
    text = text.replace(/^# (.+)$/gm, "<h1>$1</h1>");

    // Convert bold **text**
    text = text.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");

    // Convert line breaks to <br>
    text = text.replace(/\r\n|\r|\n/g, "<br>");

    el.innerHTML = text;
}

renderMarkdown("content");
renderMarkdown("small_content");
</script>

<?php include "patterns/footer.php"; ?>