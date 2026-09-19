<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
$compareCount = count($_SESSION['compare_list'] ?? []);
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXTGEN | AI-Powered Future E-Commerce</title>
    <meta name="description" content="NextGen E-Commerce powered by AI product recommendation, voice shopping, image vision search, and dynamic shopping experience.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- App CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Top Announcement Bar -->
    <div class="top-announcement">
        <div class="container announcement-content">
            <span><i class="fa-solid fa-sparkles text-glow"></i> Voice Shopping & AI Vision Image Search is Live! Talk or upload photos to shop.</span>
            <div class="top-links">
                <a href="ai-assistant.php"><i class="fa-solid fa-robot"></i> Ask AI Assistant</a>
                <span class="divider">|</span>
                <a href="compare.php"><i class="fa-solid fa-code-compare"></i> Compare Studio (<span id="topCompareCount"><?= $compareCount ?></span>)</a>
                <span class="divider">|</span>
                <a href="orders.php"><i class="fa-solid fa-truck"></i> Track Order</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="main-header">
        <div class="container header-container">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-logo">
                <div class="logo-icon"><i class="fa-solid fa-bolt"></i></div>
                <div class="logo-text">
                    <span class="brand-name">NEXTGEN</span>
                    <span class="brand-tag">AI STORE</span>
                </div>
            </a>

            <!-- Search Bar with Live AJAX, Voice Mic & Camera Vision -->
            <div class="header-search">
                <form action="products.php" method="GET" class="search-form">
                    <input type="text" name="q" id="searchInput" placeholder="Search tech, e.g. laptop for coding under 60k..." autocomplete="off">
                    
                    <!-- Voice Shopping Mic Button -->
                    <button type="button" id="voiceSearchBtn" class="search-tool-btn" title="Voice Shopping (Click & Speak)" style="background: none; border: none; color: var(--primary); font-size: 1.1rem; cursor: pointer; padding: 0 0.5rem;">
                        <i class="fa-solid fa-microphone"></i>
                    </button>

                    <!-- Camera Image Search Button -->
                    <button type="button" id="imageSearchTrigger" class="search-tool-btn" title="AI Vision Image Search (Upload Photo)" style="background: none; border: none; color: var(--neon-pink); font-size: 1.1rem; cursor: pointer; padding: 0 0.5rem;">
                        <i class="fa-solid fa-camera"></i>
                    </button>

                    <button type="submit" class="search-btn" title="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <div id="searchDropdown" class="search-dropdown-menu"></div>
            </div>

            <!-- Action Buttons (AI Assistant, Compare, Wishlist, Cart, Profile) -->
            <div class="header-actions">
                <a href="ai-assistant.php" class="action-btn ai-action-btn" title="AI Assistant">
                    <i class="fa-solid fa-brain"></i>
                    <span class="btn-text">AI Shop</span>
                    <span class="pulse-ring"></span>
                </a>

                <a href="compare.php" class="action-btn" title="Comparison Studio">
                    <i class="fa-solid fa-code-compare"></i>
                    <span class="badge-count" id="headerCompareBadge"><?= $compareCount ?></span>
                </a>

                <a href="wishlist.php" class="action-btn" title="Wishlist">
                    <i class="fa-regular fa-heart"></i>
                    <span class="badge-count" id="headerWishlistBadge"><?= $wishlistCount ?></span>
                </a>

                <a href="cart.php" class="action-btn cart-btn" title="Shopping Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="badge-count" id="headerCartBadge"><?= $cartCount ?></span>
                </a>

                <!-- User Dropdown -->
                <div class="user-menu-wrapper">
                    <?php if ($currentUser): ?>
                        <div class="user-avatar-btn" id="userMenuToggle">
                            <div class="avatar-circle"><?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?></div>
                            <span class="user-first-name"><?= htmlspecialchars(explode(' ', $currentUser['full_name'])[0]) ?></span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-header">
                                <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong>
                                <small><?= htmlspecialchars($currentUser['email']) ?></small>
                            </div>
                            <hr>
                            <a href="profile.php"><i class="fa-regular fa-user"></i> My Profile</a>
                            <a href="orders.php"><i class="fa-solid fa-box-archive"></i> My Orders</a>
                            <a href="wishlist.php"><i class="fa-regular fa-heart"></i> Wishlist</a>
                            <a href="compare.php"><i class="fa-solid fa-code-compare"></i> Compare Studio</a>
                            <?php if (isAdmin()): ?>
                                <hr>
                                <a href="admin/dashboard.php" class="admin-link"><i class="fa-solid fa-gauge-high"></i> Admin Dashboard</a>
                            <?php endif; ?>
                            <hr>
                            <a href="login.php?action=logout" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="login-pill-btn"><i class="fa-regular fa-user"></i> Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Navigation Menu Bar -->
        <nav class="nav-bar">
            <div class="container nav-container">
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                    <li><a href="products.php"><i class="fa-solid fa-grid-2"></i> All Products</a></li>
                    <li><a href="products.php?cat=laptops-computers"><i class="fa-solid fa-laptop"></i> Laptops</a></li>
                    <li><a href="products.php?cat=smartphones-mobile"><i class="fa-solid fa-mobile-screen-button"></i> Mobile</a></li>
                    <li><a href="products.php?cat=audio-headphones"><i class="fa-solid fa-headphones"></i> Audio</a></li>
                    <li><a href="products.php?cat=gaming-consoles"><i class="fa-solid fa-gamepad"></i> Gaming</a></li>
                    <li><a href="products.php?cat=smart-wearables"><i class="fa-solid fa-clock"></i> Wearables</a></li>
                    <li><a href="compare.php"><i class="fa-solid fa-code-compare"></i> Compare</a></li>
                    <li class="highlight-link"><a href="ai-assistant.php"><i class="fa-solid fa-sparkles"></i> AI Studio</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Global Toast Container -->
    <div id="toastContainer" class="toast-container"></div>
