const searchInput = document.getElementById('search');
const restaurantFilter = document.getElementById('restaurant-filter');
const foodList = document.getElementById('food-list');

function fetchFoods() {
    const searchTerm = searchInput.value.trim();
    const restaurantId = restaurantFilter.value;

    fetch(`manage_foods.php?ajax=1&search=${encodeURIComponent(searchTerm)}&restaurant_id=${restaurantId}`)
        .then(response => response.text())
        .then(html => {
            foodList.innerHTML = html;
        });
}
searchInput.addEventListener('input', fetchFoods);
restaurantFilter.addEventListener('change', fetchFoods);
function deleteFood(id) {
    if (!confirm('Are you sure you want to delete this food?')) return;
    fetch(`manage_foods.php?delete_id=${id}`)
        .then(res => res.text())
        .then(() => fetchFoods());
}

