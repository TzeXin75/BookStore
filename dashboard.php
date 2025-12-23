<?php
require_once 'db.php'; 

// --- 1. GATHER DATA FROM THE DATABASE ---

// a. Language Data (Pie Chart)
// Counts how many books are in English, Chinese, and Malay
$stmt_lang = $pdo->query("
    SELECT 
        CASE 
            WHEN language = 'English' THEN 'English'
            WHEN language = 'Chinese' THEN 'Chinese'
            WHEN language = 'Malay' THEN 'Malay'
            ELSE 'Other' 
        END AS language, 
        COUNT(id) AS count 
    FROM book 
    WHERE language IN ('English', 'Chinese', 'Malay') 
    GROUP BY language 
    ORDER BY count DESC
");
$language_data = $stmt_lang->fetchAll(PDO::FETCH_ASSOC);

// b. Subcategory Data (Horizontal Bar Chart)
// Finds which subcategories (like "Novel" or "Textbook") have the most items in stock
$stmt_subcat = $pdo->query("SELECT subcategory, SUM(stock) AS total_stock FROM book WHERE subcategory IS NOT NULL GROUP BY subcategory ORDER BY total_stock DESC LIMIT 10");
$subcategory_data = $stmt_subcat->fetchAll(PDO::FETCH_ASSOC);

// c. Category Value (Vertical Column Chart)
// Calculates the potential money tied up in each category (Price x Stock)
$stmt_cat = $pdo->query("SELECT category, SUM(price * stock) AS total_value FROM book GROUP BY category ORDER BY total_value DESC");
$category_data = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// d. Price Ranges (Histogram)
// Groups books into price "bins" (e.g., how many books cost between RM 0 and RM 25)
$stmt_price = $pdo->query("
    SELECT 'RM 0-25' AS tier, COUNT(id) AS count FROM book WHERE price >= 0 AND price <= 25 UNION ALL
    SELECT 'RM 26-50' AS tier, COUNT(id) AS count FROM book WHERE price > 25 AND price <= 50 UNION ALL
    SELECT 'RM 51-100' AS tier, COUNT(id) AS count FROM book WHERE price > 50 AND price <= 100 UNION ALL
    SELECT 'RM 100+' AS tier, COUNT(id) AS count FROM book WHERE price > 100
");
$price_data = $stmt_price->fetchAll(PDO::FETCH_ASSOC);


// --- 2. FORMAT DATA FOR THE CHARTS ---

// This helper function extracts just the names and just the numbers so the charts can read them
function prepareChartData($data, $label_key, $data_key) {
    return [
        'labels' => array_column($data, $label_key),
        'data' => array_column($data, $data_key)
    ];
}

$js_language_data = prepareChartData($language_data, 'language', 'count');
$js_subcategory_data = prepareChartData($subcategory_data, 'subcategory', 'total_stock');
$js_category_data = prepareChartData($category_data, 'category', 'total_value');
$js_price_data = prepareChartData($price_data, 'tier', 'count');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quick Stats Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Layout: Create a responsive grid for the 4 charts */
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
            padding: 20px;
        }
        .chart-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="chart-card">
        <h3>Language Distribution</h3>
        <canvas id="languageChart"></canvas>
    </div>
    
    <div class="chart-card">
        <h3>Top Subcategories (Stock)</h3>
        <canvas id="subcategoryChart"></canvas>
    </div>
    
    <div class="chart-card">
        <h3>Category Value (RM)</h3>
        <canvas id="categoryChart"></canvas>
    </div>
    
    <div class="chart-card">
        <h3>Books per Price Tier</h3>
        <canvas id="priceChart"></canvas>
    </div>
</div>

<script>
// --- Move the PHP data into JavaScript variables ---
const langData = <?= json_encode($js_language_data) ?>;
const subcatData = <?= json_encode($js_subcategory_data) ?>;
const catData = <?= json_encode($js_category_data) ?>;
const priceData = <?= json_encode($js_price_data) ?>;

const backgroundColors = [
    'rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)', 'rgba(255, 206, 86, 0.6)', 
    'rgba(75, 192, 192, 0.6)', 'rgba(153, 102, 255, 0.6)', 'rgba(255, 159, 64, 0.6)'
];

// --- 3. DRAW THE CHARTS ---

// Chart 1: Create a Circular Pie Chart for languages
new Chart(document.getElementById('languageChart'), {
    type: 'pie',
    data: {
        labels: langData.labels,
        datasets: [{
            data: langData.data,
            backgroundColor: backgroundColors
        }]
    }
});

// Chart 2: Create a Horizontal Bar Chart for subcategories
new Chart(document.getElementById('subcategoryChart'), {
    type: 'bar',
    data: {
        labels: subcatData.labels,
        datasets: [{
            label: 'Stock Quantity',
            data: subcatData.data,
            backgroundColor: 'rgba(75, 192, 192, 0.8)'
        }]
    },
    options: {
        indexAxis: 'y' // Makes the bars go from left to right
    }
});

// Chart 3: Create a Vertical Bar Chart for category value
new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: {
        labels: catData.labels,
        datasets: [{
            label: 'Total Value (RM)',
            data: catData.data,
            backgroundColor: 'rgba(54, 162, 235, 0.8)'
        }]
    }
});

// Chart 4: Create a Bar Chart for Price Tiers
new Chart(document.getElementById('priceChart'), {
    type: 'bar',
    data: {
        labels: priceData.labels,
        datasets: [{
            label: 'Count',
            data: priceData.data,
            backgroundColor: 'rgba(255, 159, 64, 0.8)'
        }]
    }
});
</script>
</body>
</html>