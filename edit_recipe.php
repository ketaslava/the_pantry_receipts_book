<?php
session_start();

/* ---------- AUTH ---------- */
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit("Access denied");
}

/* ---------- PATHS ---------- */
$baseDir  = __DIR__ . "/data/recipes/";
$listFile = $baseDir . "recipes_list.json";

/* ---------- HELPERS ---------- */
function load_json_array($file) {
    if (!file_exists($file)) return [];
    $raw = trim(file_get_contents($file));
    if ($raw === '') return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function clean($v) {
    return trim(strip_tags($v));
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) rrmdir($path);
        else unlink($path);
    }
    rmdir($dir);
}

/* ---------- VALIDATE ID ---------- */
$id = $_GET['id'] ?? '';
$id = preg_replace('/[^0-9]/', '', $id);

if ($id === '') {
    http_response_code(400);
    exit("Invalid recipe id");
}

/* ---------- DIRECTORIES ---------- */
$recipeDir      = $baseDir . $id . "/";
$dataFile        = $recipeDir . "recipe_data.json";
$imagesDir       = $recipeDir . "images/";
$headerImageDir  = $recipeDir . "header_image/";

/* ---------- DELETE RECEIPT ---------- */
if (isset($_POST['delete']) && $_POST['delete'] === '1') {
    rrmdir($recipeDir);

    // remove from master list
    $list = load_json_array($listFile);
    $list = array_filter($list, fn($v) => $v !== $id);
    save_json($listFile, $list);

    header("Location: admin_panel.php");
    exit;
}

/* ---------- LOAD EXISTING DATA ---------- */
$data = [
    "name" => "",
    "text" => "",
    "small_text" => "",
    "ingredients" => [],
    "tags" => [],
    "images" => [],
    "header_image" => null
];

if (file_exists($dataFile)) {
    $loaded = json_decode(file_get_contents($dataFile), true);
    if (is_array($loaded)) {
        $data = array_merge($data, $loaded);
    }
}

/* ---------- SAVE ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {

    if (!is_dir($imagesDir)) mkdir($imagesDir, 0777, true);
    if (!is_dir($headerImageDir)) mkdir($headerImageDir, 0777, true);

    $name = clean($_POST['name'] ?? '');
    $text = clean($_POST['text'] ?? '');
    $small_text = clean($_POST['small_text'] ?? '');
    $ingredients = array_values(array_filter(array_map('clean', $_POST['ingredients'] ?? [])));
    $tags     = array_values(array_filter(array_map('clean', $_POST['tags'] ?? [])));

    /* ----- HEADER IMAGE ----- */
    $headerImage = $data['header_image'] ?? null;

    if (!empty($_FILES['header_image']['name']) &&
        $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {

        $ext = strtolower(pathinfo($_FILES['header_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {

            foreach (glob($headerImageDir . "header.*") as $old) unlink($old);

            $headerImage = "header." . $ext;
            move_uploaded_file($_FILES['header_image']['tmp_name'], $headerImageDir . $headerImage);
        }
    }

    /* ----- GALLERY IMAGES ----- */
    $gallery = $data['images'] ?? [];

    if (!empty($_FILES['images']['name'][0])) {
        foreach (glob($imagesDir . "*") as $oldImg) unlink($oldImg);
        $gallery = [];

        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;
            $fileName = "image_" . time() . "_$i.$ext";
            move_uploaded_file($tmp, $imagesDir . $fileName);
            $gallery[] = $fileName;
        }
    }

    /* ----- SAVE JSON ----- */
    $data = [
        "name" => $name,
        "text" => $text,
        "small_text" => $small_text,
        "ingredients" => $ingredients,
        "tags" => $tags,
        "images" => $gallery,
        "header_image" => $headerImage
    ];

    save_json($dataFile, $data);

    /* ----- UPDATE MASTER LIST ----- */
    $list = load_json_array($listFile);
    if (!in_array($id, $list, true)) {
        $list[] = $id;
        sort($list);
        save_json($listFile, $list);
    }

    header("Location: admin_panel.php");
    exit;
}

include "patterns/header.php";
?>

<div class="card">
<h2>Edit Recipe #<?= htmlspecialchars($id) ?></h2>

<form method="post" enctype="multipart/form-data">

    <div style="margin-bottom: 25px;">
        <label>Name</label>
        <input name="name" value="<?= htmlspecialchars($data['name'])?>" required>
    </div>

    <div style="margin-bottom: 50px;">
        <label>Main Header Image</label><br><br>
        <?php if ($data['header_image'] && file_exists($headerImageDir . $data['header_image'])): ?>
            <div>
                <img src="data/recipes/<?= $id ?>/header_image/<?= $data['header_image'] ?>" 
                     style="max-width:240px; border-radius:10px;">
            </div>
        <?php endif; ?>
        <input type="file" name="header_image" accept=".jpg,.jpeg,.png,.webp">
    </div>

    <div style="margin-bottom: 50px;">
        <label>Gallery Images</label><br><br>
        <?php if (!empty($data['images'])): ?>
            <?php foreach ($data['images'] as $img): ?>
                <?php if(file_exists($imagesDir . $img)): ?>
                    <div style="display:inline-block; margin:5px;">
                        <img src="data/recipes/<?= $id ?>/images/<?= htmlspecialchars($img) ?>" 
                             style="max-width:240px; border-radius:10px;">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp">
    </div>

    <div style="margin-bottom: 50px;">
        <label>Ingredients</label>
        <div id="ingredients"></div>
        <button type="button" class="btn" onclick="addRow('ingredients')">+ Add</button>
    </div>

    <div style="margin-bottom: 50px;">
        <label>Text</label>
        <textarea name="text" rows="6"><?= htmlspecialchars($data['text']) ?></textarea>
    </div>

    <div style="margin-bottom: 50px;">
        <label>Small Text</label>
        <textarea name="small_text" rows="6"><?= htmlspecialchars($data['small_text']) ?></textarea>
    </div>

    <div style="margin-bottom: 50px;">
        <label>Tags</label>
        <div id="tags"></div>
        <button type="button" class="btn" onclick="addRow('tags')">+ Add</button>
    </div>

    <button class="btn" style="margin-top:20px;">Save</button>

    <!-- Delete button for admins -->
    <button type="submit" name="delete" value="1" class="btn" 
            style="margin-top:20px; background:#e74c3c;"
            onclick="return confirm('Are you sure you want to delete this recipe? This cannot be undone.')">
        Delete Recipe
    </button>

</form>
</div>

<script>
const existing = {
    ingredients: <?= json_encode($data['ingredients']) ?>,
    tags: <?= json_encode($data['tags']) ?>
};

function makeRow(name,value="") {
    const d=document.createElement("div");
    d.style.display="flex";
    d.style.marginBottom="6px";

    const i=document.createElement("input");
    i.name=name+"[]";
    i.value=value;
    i.style.flex="1";

    const b=document.createElement("button");
    b.type="button";
    b.style="min-width: 50px; margin-left: 15px; margin-top: 6px; margin-bottom: 12px;";
    b.textContent="×";
    b.onclick=()=>d.remove();

    d.appendChild(i);
    d.appendChild(b);
    return d;
}

function addRow(name,value=""){
    document.getElementById(name).appendChild(makeRow(name,value));
}

Object.keys(existing).forEach(k=>{
    if(existing[k].length){
        existing[k].forEach(v=>addRow(k,v));
    } else {
        addRow(k);
    }
});
</script>

<?php include "patterns/footer.php"; ?>