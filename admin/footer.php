        </div> 
        <!-- Drawer Side Sidebar -->
        <div class="drawer-side z-40">
            <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <ul class="menu p-4 w-64 min-h-full bg-base-100 text-base-content border-r border-base-300 gap-1 font-medium">
                <li class="menu-title text-xs tracking-wider uppercase opacity-60 mb-2">Main Navigation</li>
                <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line text-primary"></i> Dashboard</a></li>
                <li><a href="manage_orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'active' : ''; ?>"><i class="fa-solid fa-box text-secondary"></i> Manage Orders</a></li>
                <li><a href="manage_foods.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_foods.php' ? 'active' : ''; ?>"><i class="fa-solid fa-burger text-accent"></i> Manage Dishes</a></li>
                <li><a href="manage_restaurants.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_restaurants.php' ? 'active' : ''; ?>"><i class="fa-solid fa-store text-warning"></i> Manage Restaurants</a></li>
                <li><a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>"><i class="fa-solid fa-users text-info"></i> Manage Users</a></li>
                
                <li class="menu-title text-xs tracking-wider uppercase opacity-60 mt-6 mb-2">Quick Actions</li>
                <li><a href="add_food.php"><i class="fa-solid fa-plus text-success"></i> Add New Dish</a></li>
                <li><a href="add_restaurant.php"><i class="fa-solid fa-folder-plus text-secondary"></i> Add Restaurant</a></li>
                
                <div class="mt-auto pt-6">
                    <a href="logout.php" class="btn btn-error btn-outline btn-block btn-sm">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout Admin
                    </a>
                </div>
            </ul>
        </div>
    </div>

    <!-- Admin Footer -->
    <footer class="footer footer-center p-4 bg-base-100 text-base-content/60 text-xs border-t border-base-300 mt-auto">
        <aside>
            <p>© <?= date('Y'); ?> Restaurant Management System - Admin Control Panel.</p>
        </aside>
    </footer>

    <script>
        function changeTheme(themeName) {
            document.documentElement.setAttribute('data-theme', themeName);
            localStorage.setItem('rms_daisyui_theme', themeName);
        }
    </script>
</body>
</html>
