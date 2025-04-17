<?php
// Database connection
$host = 'localhost';
$dbname = 'online_grocery';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Helper functions
function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProducts($pdo, $category_id = null) {
    $sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
    if ($category_id) {
        $sql .= " WHERE p.category_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id]);
    } else {
        $stmt = $pdo->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAggregateData($pdo) {
    $data = [];
    
    // Average price
    $stmt = $pdo->query("SELECT AVG(price) as avg_price FROM products");
    $data['avg_price'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg_price'];
    
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
    $data['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];
    
    // Highest and lowest priced items
    $stmt = $pdo->query("SELECT product_name, price FROM products ORDER BY price DESC LIMIT 1");
    $data['highest_price'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT product_name, price FROM products ORDER BY price ASC LIMIT 1");
    $data['lowest_price'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Total sales
    $stmt = $pdo->query("SELECT SUM(total_amount) as total_sales FROM transactions");
    $data['total_sales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];
    
    // Sales by category
    $stmt = $pdo->query("
        SELECT c.category_name, SUM(ti.quantity * ti.unit_price) as category_sales 
        FROM transaction_items ti
        JOIN products p ON ti.product_id = p.product_id
        JOIN categories c ON p.category_id = c.category_id
        GROUP BY c.category_name
    ");
    $data['sales_by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $data;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Grocery Database</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="bg-green-600 text-white p-6 rounded-lg shadow-md mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Online Grocery Database</h1>
                    <p class="mt-2">Manage products, categories, and sales data</p>
                </div>
                <img src="https://cdn.pixabay.com/photo/2017/09/30/15/10/grocery-2802482_640.png" alt="Grocery Cart" class="h-20">
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Summary Cards -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                        <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm">Average Product Price</h3>
                        <p class="text-2xl font-bold">$<?= number_format(getAggregateData($pdo)['avg_price'], 2) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <i class="fas fa-shopping-basket text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm">Total Products</h3>
                        <p class="text-2xl font-bold"><?= getAggregateData($pdo)['total_products'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-full mr-4">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm">Total Sales</h3>
                        <p class="text-2xl font-bold">$<?= number_format(getAggregateData($pdo)['total_sales'] ?? 0, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Price Extremes -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Price Extremes</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div>
                            <h3 class="text-sm text-gray-500">Highest Priced Item</h3>
                            <p class="font-medium"><?= getAggregateData($pdo)['highest_price']['product_name'] ?? 'N/A' ?></p>
                        </div>
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                            $<?= number_format(getAggregateData($pdo)['highest_price']['price'] ?? 0, 2) ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div>
                            <h3 class="text-sm text-gray-500">Lowest Priced Item</h3>
                            <p class="font-medium"><?= getAggregateData($pdo)['lowest_price']['product_name'] ?? 'N/A' ?></p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                            $<?= number_format(getAggregateData($pdo)['lowest_price']['price'] ?? 0, 2) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sales by Category -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Sales by Category</h2>
                <div class="space-y-3">
                    <?php foreach (getAggregateData($pdo)['sales_by_category'] as $category): ?>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700"><?= $category['category_name'] ?></span>
                                <span class="text-sm font-medium text-gray-700">$<?= number_format($category['category_sales'], 2) ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" 
                                     style="width: <?= min(100, ($category['category_sales'] / max(1, getAggregateData($pdo)['total_sales'])) * 100) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Products</h2>
                <div class="flex space-x-2">
                    <a href="#" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </a>
                    <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        <i class="fas fa-list mr-2"></i>View All
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach (getProducts($pdo) as $product): ?>
                    <div class="border rounded-lg overflow-hidden hover:shadow-lg transition">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <?php if ($product['image_path']): ?>
                                <img src="<?= $product['image_path'] ?>" alt="<?= $product['product_name'] ?>" class="h-full w-full object-cover">
                            <?php else: ?>
                                <img src="https://cdn.pixabay.com/photo/2016/03/23/15/00/ice-cream-1274894_640.jpg" alt="Default product" class="h-full w-full object-cover">
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-lg mb-1"><?= $product['product_name'] ?></h3>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm font-semibold">
                                    $<?= number_format($product['price'], 2) ?>
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mb-2"><?= $product['category_name'] ?></p>
                            <p class="text-gray-500 text-sm mb-3"><?= substr($product['description'] ?? 'No description available', 0, 60) ?>...</p>
                            <div class="flex justify-between items-center">
                                <span class="text-sm <?= $product['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                </span>
                                <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                    <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Recent Transactions</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $stmt = $pdo->query("
                            SELECT t.transaction_id, t.transaction_date, t.total_amount, 
                                   COUNT(ti.item_id) as item_count
                            FROM transactions t
                            LEFT JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
                            GROUP BY t.transaction_id
                            ORDER BY t.transaction_date DESC
                            LIMIT 5
                        ");
                        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?= $transaction['transaction_id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M j, Y h:i A', strtotime($transaction['transaction_date'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-semibold">$<?= number_format($transaction['total_amount'], 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $transaction['item_count'] ?> items</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>© <?= date('Y') ?> Online Grocery Database System. All rights reserved.</p>
            <div class="flex justify-center space-x-4 mt-4">
                <a href="#" class="hover:text-green-400 transition"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="hover:text-green-400 transition"><i class="fab fa-twitter"></i></a>
                <a href="#" class="hover:text-green-400 transition"><i class="fab fa-instagram"></i></a>
                <a href="#" class="hover:text-green-400 transition"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>