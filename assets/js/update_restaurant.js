// assets/js/manage_restaurants.js

const searchInput = document.getElementById('search');
const restaurantList = document.getElementById('restaurant-list');

function fetchRestaurants() {
    const searchTerm = searchInput.value.trim();

    fetch(`manage_restaurants.php?ajax=1&search=${encodeURIComponent(searchTerm)}`)
        .then(res => res.text())
        .then(html => {
            restaurantList.innerHTML = html;
        });
}

// Live search
searchInput.addEventListener('input', fetchRestaurants);

// Delete restaurant
function deleteRestaurant(id) {
    if (!confirm('Are you sure you want to delete this restaurant?')) return;
    fetch(`manage_restaurants.php?delete_id=${id}`)
        .then(res => res.text())
        .then(() => fetchRestaurants());
}
