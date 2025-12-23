<?php
require_once 'config/db_connect.php'; 

// --- 1. DATA FETCHING LOGIC ---

// a. Language Distribution Data (Pie Chart)
$stmt_lang = $pdo->query("
    SELECT 
        CASE 
            WHEN language = 'English' THEN 'English'
            WHEN language = 'Chinese' THEN 'Chinese'
            WHEN language = 'Malay' THEN 'Malay'
            ELSE 'Other/Unspecified'  -- Group any unexpected data
        END AS language, 
        COUNT(id) AS count 
    FROM book 
    WHERE language IN ('English', 'Chinese', 'Malay') -- Filter to only include the relevant languages
    GROUP BY language 
    ORDER BY count DESC
");
$language_data = $stmt_lang->fetchAll(PDO::FETCH_ASSOC);

// b. Subcategory Data (Horizontal Bar Chart - based on Stock)
$stmt_subcat = $pdo->query("SELECT subcategory, SUM(stock) AS total_stock FROM book WHERE subcategory IS NOT NULL GROUP BY subcategory ORDER BY total_stock DESC LIMIT 10");
$subcategory_data = $stmt_subcat->fetchAll(PDO::FETCH_ASSOC);

// c. Category Data (Vertical Column Chart - based on Total Value/Revenue Potential)
// Calculates the total value (price * stock) for each major category
$stmt_cat = $pdo->query("SELECT category, SUM(price * stock) AS total_value FROM book GROUP BY category ORDER BY total_value DESC");
$category_data = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// d. Price Tier Data (Histogram/Binned Chart)
// Use a UNION query to categorize books into price bins
$stmt_price = $pdo->query("
    SELECT 'RM 0-25' AS tier, COUNT(id) AS count FROM book WHERE price >= 0 AND price <= 25 UNION ALL
    SELECT 'RM 26-50' AS tier, COUNT(id) AS count FROM book WHERE price > 25 AND price <= 50 UNION ALL
    SELECT 'RM 51-100' AS tier, COUNT(id) AS count FROM book WHERE price > 50 AND price <= 100 UNION ALL
    SELECT 'RM 100+' AS tier, COUNT(id) AS count FROM book WHERE price > 100
");
$price_data = $stmt_price->fetchAll(PDO::FETCH_ASSOC);


// --- 2. PREPARE DATA FOR JAVASCRIPT ---

// Helper function to extract labels and data values from PHP arrays
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Stats Dashboard</title>
    <link rel="stylesheet" href="style.css"> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
        }
        .chart-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .chart-card h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <div class="chart-card">
        <h3>Language Distribution (Book Count)</h3>
        <canvas id="languageChart"></canvas>
    </div>
    
    <div class="chart-card">
        <h3>Top Subcategories (Total Stock)</h3>
        <canvas id="subcategoryChart"></canvas>
    </div>
    
    <div class="chart-card">
        <h3>Category Revenue Potential (Price * Stock)</h3>
        <canvas id="categoryChart"></canvas>
    </div>
    
    <div class="chart-card">
        <h3>Price Tier Distribution (Book Count)</h3>
        <canvas id="priceChart"></canvas>
    </div>

</div>

<script>
// --- Data from PHP ---
const langData = <?= json_encode($js_language_data) ?>;
const subcatData = <?= json_encode($js_subcategory_data) ?>;
const catData = <?= json_encode($js_category_data) ?>;
const priceData = <?= json_encode($js_price_data) ?>;

// Function to generate random colors
const backgroundColors = [
    'rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)', 'rgba(255, 206, 86, 0.6)', 
    'rgba(75, 192, 192, 0.6)', 'rgba(153, 102, 255, 0.6)', 'rgba(255, 159, 64, 0.6)'
];

// --- 3. CHART RENDERING ---

// Chart 1: Language Distribution (Pie Chart)
new Chart(document.getElementById('languageChart'), {
    type: 'pie',
    data: {
        labels: langData.labels,
        datasets: [{
            label: 'Book Count by Language',
            data: langData.data,
            backgroundColor: backgroundColors.slice(0, langData.labels.length),
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            title: { display: false }
        }
    }
});

// Chart 2: Subcategory Stock Levels (Horizontal Bar Chart)
new Chart(document.getElementById('subcategoryChart'), {
    type: 'bar',
    data: {
        labels: subcatData.labels,
        datasets: [{
            label: 'Total Stock Quantity',
            data: subcatData.data,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y', // Makes it horizontal
        responsive: true,
        scales: {
            x: { beginAtZero: true }
        }
    }
});

// Chart 3: Category Revenue Potential (Vertical Column Chart)
new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: {
        labels: catData.labels,
        datasets: [{
            label: 'Total Value (Price * Stock) in RM',
            data: catData.data,
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});


// Chart 4: Price Tier Distribution (Vertical Column Chart/Histogram)
new Chart(document.getElementById('priceChart'), {
    type: 'bar',
    data: {
        labels: priceData.labels,
        datasets: [{
            label: 'Number of Books',
            data: priceData.data,
            backgroundColor: 'rgba(255, 159, 64, 0.8)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>