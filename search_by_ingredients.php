<?php include "patterns/header.php"; ?>

<h2>Search by Ingredients</h2>

<div style="margin-bottom:20px;">
    <label>Ingredients:</label>
    <div id="ingredientsList"></div>
    <button type="button" class="btn" onclick="addProductRow()">+ Add</button>
</div>

<button type="button" class="btn" onclick="searchByIngredients()" style="margin-bottom:30px;">Search</button>

<div id="results"></div>

<?php
// load all receipts as JSON
$list = json_decode(file_get_contents("data/receipts/receipts_list.json"), true) ?? [];
$allData = [];

foreach ($list as $id) {
    $dataFile = "data/receipts/$id/receipt_data.json";
    if (!file_exists($dataFile)) continue;

    $data = json_decode(file_get_contents($dataFile), true);
    if (!is_array($data)) continue;

    $allData[$id] = $data;
}
?>

<script>
const receipts = <?= json_encode($allData, JSON_UNESCAPED_UNICODE) ?>;
const ingredientsListDiv = document.getElementById('ingredientsList');
const resultsDiv = document.getElementById('results');

// ----- Dynamic product inputs -----
function addProductRow(value='') {
    const div = document.createElement('div');
    div.className = 'list-row';

    const input = document.createElement('input');
    input.type = 'text';
    input.value = value;
    input.placeholder = 'Product';

    const delBtn = document.createElement('button');
    delBtn.type = 'button';
    delBtn.className = 'btn-remove';
    delBtn.textContent = '✕';
    delBtn.onclick = () => div.remove();

    div.appendChild(input);
    div.appendChild(delBtn);
    ingredientsListDiv.appendChild(div);
}

// initialize one empty row
addProductRow();

// ----- Search logic -----
function searchByIngredients() {
    const searchIngredients = Array.from(ingredientsListDiv.querySelectorAll('input'))
        .map(i => i.value.trim().toLowerCase())
        .filter(i => i);

    if (searchIngredients.length === 0) {
        resultsDiv.innerHTML = '<p>Please enter at least one product.</p>';
        return;
    }

    // compute match scores (fuzzy / partial match)
    const scored = [];

    for (const id in receipts) {
        const data = receipts[id];
        const receiptIngredients = (data.ingredients || []).map(p => p.toLowerCase());

        let matchCount = 0;

        for (const searchP of searchIngredients) {
            // check if any receipt product contains the search word
            if (receiptIngredients.some(rp => rp.includes(searchP))) {
                matchCount++;
            }
        }

        let category = 4; // default: other ingredients
        if (matchCount === searchIngredients.length && matchCount === receiptIngredients.length) category = 1;
        else if (matchCount === searchIngredients.length && matchCount < receiptIngredients.length) category = 2;
        else if (matchCount > 0) category = 3;

        scored.push({id, data, matchCount, category});
    }
    
    // sort by category, then matchCount descending
    scored.sort((a,b) => a.category - b.category || b.matchCount - a.matchCount);

    renderResults(scored);
}

// ----- Render results -----
function renderResults(list) {
    resultsDiv.innerHTML = '';
    if (!list.length) {
        resultsDiv.innerHTML = '<p>No results found.</p>';
        return;
    }

    for (const item of list) {
        const id = item.id;
        const data = item.data;

        const card = document.createElement('a');
        card.href = 'receipt_page.php?id=' + encodeURIComponent(id);
        card.className = 'card';

        let html = `<h1 style="font-size:32px">${data.name ? data.name.replace(/</g,'&lt;') : ''}</h1>`;

        if (data.header_image) {
            html += `<img src="data/receipts/${id}/header_image/${data.header_image}" 
                         style="max-width:240px;border-radius:10px;">`;
        }

        if (data.ingredients && data.ingredients.length) {
            html += `<p><strong>Ingredients:</strong> ${data.ingredients.map(p=>p.replace(/</g,'&lt;')).join(', ')}</p>`;
        }

        if (data.tags && data.tags.length) {
            html += `<p><strong>Tags:</strong> ${data.tags.map(t=>t.replace(/</g,'&lt;')).join(', ')}</p>`;
        }

        card.innerHTML = html;
        resultsDiv.appendChild(card);
    }
}
</script>

<?php include "patterns/footer.php"; ?>