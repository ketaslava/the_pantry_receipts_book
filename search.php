<?php include "patterns/header.php"; ?>

<h2>Search Recipes</h2>

<input type="text" id="searchInput" placeholder="Enter keyword(s)..." 
       style="width: 80%; padding:10px; margin-bottom:20px; border-radius:6px; border:1px solid #ccc;">

<div id="results"></div>

<?php
// Load all recipes once and pass as JSON to JS
$list = json_decode(file_get_contents("data/recipes/recipes_list.json"), true) ?? [];
$allData = [];

foreach ($list as $id) {
    $dataFile = "data/recipes/$id/recipe_data.json";
    if (!file_exists($dataFile)) continue;

    $data = json_decode(file_get_contents($dataFile), true);
    if (!is_array($data)) continue;

    $allData[$id] = $data;
}
?>

<script>
const recipes = <?= json_encode($allData, JSON_UNESCAPED_UNICODE) ?>;

const resultsContainer = document.getElementById('results');
const input = document.getElementById('searchInput');

function renderResults(list) {
    resultsContainer.innerHTML = '';

    if (Object.keys(list).length === 0) {
        resultsContainer.innerHTML = '<p>No results found.</p>';
        return;
    }

    for (const id in list) {
        const data = list[id];
        const card = document.createElement('a');
        card.href = 'recipe_page.php?id=' + encodeURIComponent(id);
        card.className = 'card';

        let html = `<h1 style="font-size:32px">${data.name ? data.name.replace(/</g,'&lt;') : ''}</h1>`;

        if (data.header_image) {
            html += `<img src="data/recipes/${id}/header_image/${data.header_image}" 
                         style="max-width:240px;border-radius:10px;">`;
        }

        if (data.ingredients && data.ingredients.length) {
            html += `<p><strong>Ingredients:</strong> ${data.ingredients.map(p => p.replace(/</g,'&lt;')).join(', ')}</p>`;
        }

        if (data.tags && data.tags.length) {
            html += `<p><strong>Tags:</strong> ${data.tags.map(t => t.replace(/</g,'&lt;')).join(', ')}</p>`;
        }

        card.innerHTML = html;
        resultsContainer.appendChild(card);
    }
}

function searchRecipes() {
    const query = input.value.trim().toLowerCase().split(/\s+/);
    if (!query[0]) {
        renderResults(recipes);
        return;
    }

    const filtered = {};
    for (const id in recipes) {
        const data = recipes[id];
        const haystack = (
            (data.name || '') + ' ' +
            (data.text || '') + ' ' +
            ((data.ingredients || []).join(' ')) + ' ' +
            ((data.tags || []).join(' '))
        ).toLowerCase();

        let match = true;
        for (const word of query) {
            if (!word) continue;
            if (!haystack.includes(word)) {
                match = false;
                break;
            }
        }

        if (match) filtered[id] = data;
    }

    renderResults(filtered);
}

// initial render: show all recipes
renderResults(recipes);

// search on input
input.addEventListener('input', searchRecipes);
</script>

<?php include "patterns/footer.php"; ?>