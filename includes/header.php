<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="emerald">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Restaurant Management System'; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- DaisyUI 4 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .font-heading { font-family: 'Outfit', sans-serif; }
    </style>
    <script>
        // Apply saved theme immediately before body renders to avoid flashing
        const savedTheme = localStorage.getItem('rms_daisyui_theme') || 'emerald';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="min-h-screen flex flex-col bg-base-200 text-base-content antialiased">
    <!-- Navbar -->
    <div class="sticky top-0 z-50 bg-base-100/90 backdrop-blur border-b border-base-300 shadow-sm">
        <div class="navbar container mx-auto px-4">
            <!-- Navbar Start: Mobile Drawer Toggle & Logo -->
            <div class="navbar-start gap-2">
                <div class="dropdown lg:hidden">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-200">
                        <li><a href="index.php"><i class="fa-solid fa-house text-primary"></i> Home</a></li>
                        <li><a href="search.php"><i class="fa-solid fa-magnifying-glass text-secondary"></i> Search Food</a></li>
                        <li><a href="cart.php"><i class="fa-solid fa-cart-shopping text-accent"></i> Cart (<?= get_cart_count(); ?>)</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="dashboard.php"><i class="fa-solid fa-gauge text-info"></i> Dashboard</a></li>
                            <li><a href="profile.php"><i class="fa-solid fa-user text-warning"></i> My Profile</a></li>
                            <li><a href="logout.php" class="text-error"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php"><i class="fa-solid fa-right-to-bracket text-primary"></i> Login</a></li>
                            <li><a href="register.php"><i class="fa-solid fa-user-plus text-secondary"></i> Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <a href="index.php" class="btn btn-ghost text-xl font-heading font-extrabold normal-case gap-2">
                    <span class="w-9 h-9 rounded-xl bg-primary text-primary-content flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-utensils text-lg"></i>
                    </span>
                    <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">GourmetHub</span>
                </a>
            </div>

            <!-- Navbar Center: Desktop Links -->
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1 gap-1 font-medium">
                    <li><a href="index.php" class="rounded-lg"><i class="fa-solid fa-house"></i> Home</a></li>
                    <li><a href="search.php" class="rounded-lg"><i class="fa-solid fa-magnifying-glass"></i> Explore Menu</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php" class="rounded-lg"><i class="fa-solid fa-receipt"></i> My Orders</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Navbar End: Cart, Theme Switcher & User Avatar -->
            <div class="navbar-end gap-2">
                <!-- Theme Switcher Dropdown -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle" title="Change Theme">
                        <i class="fa-solid fa-palette text-lg"></i>
                    </div>
                    <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-52 z-[1] border border-base-200">
                        <li class="menu-title text-xs">Choose Theme</li>
                        <li><button onclick="changeTheme('emerald')" class="justify-between">Emerald (Default) <span class="badge badge-sm badge-primary">Default</span></button></li>
                        <li><button onclick="changeTheme('light')">Light</button></li>
                        <li><button onclick="changeTheme('dark')">Dark</button></li>
                        <li><button onclick="changeTheme('cupcake')">Cupcake</button></li>
                        <li><button onclick="changeTheme('synthwave')">Synthwave</button></li>
                        <li><button onclick="changeTheme('cyberpunk')">Cyberpunk</button></li>
                        <li><button onclick="changeTheme('retro')">Retro</button></li>
                    </ul>
                </div>

                <!-- Shopping Cart Dropdown -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                        <div class="indicator">
                            <i class="fa-solid fa-cart-shopping text-lg"></i>
                            <?php $cart_count = get_cart_count(); ?>
                            <?php if ($cart_count > 0): ?>
                                <span class="badge badge-sm badge-primary indicator-item font-bold"><?= $cart_count; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div tabindex="0" class="card card-compact dropdown-content w-72 bg-base-100 z-[1] shadow-xl border border-base-200 mt-3">
                        <div class="card-body">
                            <span class="font-bold text-lg"><?= $cart_count; ?> Items in Cart</span>
                            <span class="text-info font-semibold">Subtotal: $<?= number_format(get_cart_total(), 2); ?></span>
                            <div class="card-actions">
                                <a href="cart.php" class="btn btn-primary btn-block btn-sm">View Cart</a>
                                <?php if ($cart_count > 0): ?>
                                    <a href="checkout.php" class="btn btn-accent btn-block btn-sm">Checkout</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Dropdown Menu -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'User'); ?>&background=0D9488&color=fff" alt="User Avatar" />
                            </div>
                        </div>
                        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-lg bg-base-100 rounded-box w-56 border border-base-200">
                            <li class="menu-title px-4 py-2 border-b border-base-200">
                                <span class="font-bold text-base-content"><?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                                <span class="text-xs text-base-content/60"><?= htmlspecialchars($_SESSION['email'] ?? ''); ?></span>
                            </li>
                            <li class="mt-2"><a href="dashboard.php"><i class="fa-solid fa-gauge text-primary"></i> Dashboard & Orders</a></li>
                            <li><a href="profile.php"><i class="fa-solid fa-user-gear text-secondary"></i> Account Profile</a></li>
                            <li><a href="admin/login.php" class="text-xs text-base-content/70"><i class="fa-solid fa-shield-halved text-info"></i> Admin Portal</a></li>
                            <div class="divider my-1"></div>
                            <li><a href="logout.php" class="text-error font-medium"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-ghost btn-sm font-medium">Login</a>
                    <a href="register.php" class="btn btn-primary btn-sm font-medium shadow-md">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
