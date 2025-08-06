<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herbal Bliss - Natural Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿</h1>
        <input type="text" id="search-bar" placeholder="Search products..." oninput="searchProducts()">
        <nav>
           
            <ul>
                <!-- Your existing navigation -->
                <li><a href="#" onclick="showHome()">Home</a></li>
                <li><a href="#" onclick="showCategory('face-care')">Face Care</a></li>
                <li class="dropdown">
                    <a href="#">Hair Care ▼</a>
                    <ul class="dropdown-menu">
                        <li><a href="#" onclick="showCategory('shampoos')">Shampoos</a></li>
                        <li><a href="#" onclick="showCategory('conditioners')">Conditioners</a></li>
                        <li><a href="#" onclick="showCategory('serums')">Hair Serums</a></li>
                        <li><a href="#" onclick="showCategory('hair-wax')">Hair Wax</a></li>
                        <li><a href="#" onclick="showCategory('beard-oil')">Beard Oil</a></li>
                    </ul>
                </li>
                
                <li><a href="#" onclick="showCategory('raw-herbs')">Raw Herbs</a></li>
                <li><a href="#" onclick="showCategory('hair-oils')">Hair Oils</a></li>
                <li class="dropdown">
                    <a href="#">Body Care ▼</a>
                    <ul class="dropdown-menu">
                        <li><a href="#" onclick="showCategory('soap')">Soap</a></li>
                        <li><a href="#" onclick="showCategory('body-wash')">Body Wash</a></li>
                        <li><a href="#" onclick="showCategory('deodorants')">Deodorants</a></li>
                    </ul>
                </li>
                <li><a href="#" onclick="showCart()">Cart (<span id="cart-count">0</span>)</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout (<?php echo $_SESSION['user_name']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        <!-- </nav> -->
        <!-- Add this to your header section, after the navigation -->
            <!-- <div class="category-tabs">
                <button class="tab-btn active" onclick="showCategory('face-care')">Face Care</button>
                <button class="tab-btn" onclick="showCategory('shampoos')">Shampoos</button>
                <button class="tab-btn" onclick="showCategory('conditioners')">Conditioners</button>
                <button class="tab-btn" onclick="showCategory('serums')">Serums</button>
                <button class="tab-btn" onclick="showCategory('hair-wax')">Hair Wax</button>
                <button class="tab-btn" onclick="showCategory('beard-oil')">Beard Oil</button>
                <button class="tab-btn" onclick="showCategory('hair-oils')">Hair Oils</button>
                <button class="tab-btn" onclick="showCategory('raw-herbs')">Raw Herbs</button>
            </div> -->
    </header>

    <section id="content">
        <h2>Welcome to Herbal Bliss</h2>
        <?php if(isset($_SESSION['user_id'])): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Explore our natural products.</p>
        <?php else: ?>
            <p>Explore our natural, chemical-free skincare and haircare products.</p>
        <?php endif; ?>
        
        <!-- Products will be loaded here by JavaScript -->
        <div id="featured-products"></div>
    </section>

    <!-- Add this script to pass PHP data to JavaScript -->
    <script>
        // Pass user data to JavaScript
        const userData = {
            isLoggedIn: <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>,
            name: '<?= isset($_SESSION['user_name']) ? addslashes($_SESSION['user_name']) : '' ?>'
        };
    </script>

    <!-- Your existing scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    
    <!-- add path to script.js file-->
    <script src="script.js"></script>

    <footer>
        <p>&copy; 2025 Herbal Bliss - Safe & Natural Skincare</p>
    </footer>
</body>
</html>