    <!-- Footer -->
    <footer class="footer p-10 bg-neutral text-neutral-content mt-auto">
        <aside>
            <div class="flex items-center gap-2 text-2xl font-heading font-bold text-white mb-2">
                <span class="w-8 h-8 rounded-lg bg-primary text-primary-content flex items-center justify-center text-sm shadow">
                    <i class="fa-solid fa-utensils"></i>
                </span>
                GourmetHub
            </div>
            <p class="max-w-sm text-neutral-content/70">
                Bringing top-quality cuisine, delicious gourmet dishes, and lightning-fast delivery straight to your doorstep.
            </p>
            <div class="flex gap-4 mt-4 text-xl">
                <a href="#" class="hover:text-primary transition"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" class="hover:text-primary transition"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="hover:text-primary transition"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" class="hover:text-primary transition"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </aside> 
        <nav>
            <h6 class="footer-title text-primary">Quick Navigation</h6> 
            <a href="index.php" class="link link-hover">Home</a>
            <a href="search.php" class="link link-hover">Explore Menu</a>
            <a href="cart.php" class="link link-hover">View Cart</a>
            <a href="dashboard.php" class="link link-hover">My Orders</a>
        </nav> 
        <nav>
            <h6 class="footer-title text-primary">Customer Services</h6> 
            <a href="profile.php" class="link link-hover">Account Profile</a>
            <a href="admin/login.php" class="link link-hover">Admin Panel</a>
            <a href="#" class="link link-hover">Privacy Policy</a>
            <a href="#" class="link link-hover">Terms of Service</a>
        </nav> 
        <nav>
            <h6 class="footer-title text-primary">Contact & Support</h6> 
            <span class="text-sm"><i class="fa-solid fa-phone text-primary mr-2"></i> +880 1700-000111</span>
            <span class="text-sm"><i class="fa-solid fa-envelope text-primary mr-2"></i> support@gourmethub.com</span>
            <span class="text-sm"><i class="fa-solid fa-location-dot text-primary mr-2"></i> Foodie Avenue, Dhaka</span>
        </nav>
    </footer>
    
    <div class="footer footer-center p-4 bg-neutral-900 text-neutral-content/60 text-xs border-t border-neutral-800">
        <p>© <?= date('Y'); ?> GourmetHub Restaurant Management System. All rights reserved.</p>
    </div>

    <!-- Live Theme Switcher Script -->
    <script>
        function changeTheme(themeName) {
            document.documentElement.setAttribute('data-theme', themeName);
            localStorage.setItem('rms_daisyui_theme', themeName);
        }
    </script>
</body>
</html>
