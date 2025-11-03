document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");

    if (!searchInput) return; // Prevent error if input doesn't exist

    searchInput.addEventListener("input", function () {
        const query = this.value.trim();
        fetch("search.php?q=" + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                // Restaurants
                const restaurantContainer = document.getElementById("restaurantResults");
                restaurantContainer.innerHTML = "";
                if (data.restaurants.length > 0) {
                    data.restaurants.forEach(r => {
                        const div = document.createElement("div");
                        div.className = "col-md-3 mb-4";
                        div.innerHTML = `
                            <a href="restaurant.php?id=${r.id}" class="card-link">
                                <div class="card h-100 shadow-sm">
                                    <img src="${r.image_url || 'https://via.placeholder.com/300x180?text=No+Image'}" class="card-img-top" style="height:180px; object-fit:cover;">
                                    <div class="card-body">
                                        <h5 class="card-title">${r.name}</h5>
                                        <p class="card-text text-muted mb-0">${r.address.length > 40 ? r.address.substr(0,40)+'...' : r.address}</p>
                                    </div>
                                </div>
                            </a>`;
                        restaurantContainer.appendChild(div);
                    });
                } else {
                    restaurantContainer.innerHTML = '<p class="text-muted">No restaurants found.</p>';
                }

                // Dishes
                const dishContainer = document.getElementById("dishResults");
                dishContainer.innerHTML = "";
                if (data.dishes.length > 0) {
                    data.dishes.forEach(d => {
                        const div = document.createElement("div");
                        div.className = "col-md-3 mb-4";
                        div.innerHTML = `
                            <a href="dish.php?id=${d.id}" class="card-link">
                                <div class="card h-100 shadow-sm dish-card">
                                    <img src="${d.image_url || 'https://via.placeholder.com/300x180?text=No+Image'}" class="card-img-top" style="height:180px; object-fit:cover;">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">${d.name}</h5>
                                        <p class="card-text mb-1"><small class="text-muted">${d.restaurant_name}</small></p>
                                        <p class="card-text fw-bold mb-0">$${parseFloat(d.price).toFixed(2)}</p>
                                    </div>
                                </div>
                            </a>`;
                        dishContainer.appendChild(div);
                    });
                } else {
                    dishContainer.innerHTML = '<p class="text-muted">No dishes found.</p>';
                }
            });
    });
});
