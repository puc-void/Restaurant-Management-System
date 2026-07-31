<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="emerald">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . " - Admin Panel" : "Admin Panel"; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- DaisyUI 4 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .font-heading { font-family: 'Outfit', sans-serif; }
    </style>
    <script>
        const savedTheme = localStorage.getItem('rms_daisyui_theme') || 'emerald';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="min-h-screen bg-base-200 text-base-content flex flex-col">
    <!-- Admin Top Navbar -->
    <div class="navbar bg-base-100 border-b border-base-300 px-4 sticky top-0 z-50 shadow-sm">
        <div class="navbar-start">
            <label for="admin-drawer" class="btn btn-ghost btn-circle drawer-button lg:hidden">
                <i class="fa-solid fa-bars text-xl"></i>
            </label>
            <a href="dashboard.php" class="btn btn-ghost text-xl font-heading font-bold gap-2">
                <span class="w-8 h-8 rounded-lg bg-primary text-primary-content flex items-center justify-center text-sm shadow">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <span>Admin Portal</span>
            </a>
        </div>
        <div class="navbar-end gap-2">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle" title="Change Theme">
                    <i class="fa-solid fa-palette text-lg"></i>
                </div>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-52 z-[1] border border-base-200">
                    <li class="menu-title text-xs">Choose Theme</li>
                    <li><button onclick="changeTheme('emerald')">Emerald</button></li>
                    <li><button onclick="changeTheme('dark')">Dark</button></li>
                    <li><button onclick="changeTheme('light')">Light</button></li>
                    <li><button onclick="changeTheme('synthwave')">Synthwave</button></li>
                </ul>
            </div>
            <a href="../index.php" target="_blank" class="btn btn-ghost btn-sm gap-1">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span class="hidden sm:inline">View Site</span>
            </a>
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-9 rounded-full ring ring-primary ring-offset-base-100 ring-offset-1">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin'); ?>&background=0284C7&color=fff" />
                    </div>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-200">
                    <li class="menu-title">
                        <span class="font-bold"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    </li>
                    <div class="divider my-1"></div>
                    <li><a href="logout.php" class="text-error"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Admin Drawer Container -->
    <div class="drawer lg:drawer-open flex-1">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content p-4 md:p-8">
