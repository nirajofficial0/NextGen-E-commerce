/**
 * NextGen E-Commerce Global JavaScript Handler
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. User Profile Menu Toggle
    const userToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userDropdown');

    if (userToggle && userDropdown) {
        userToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            userDropdown.classList.remove('show');
        });
    }

    // 2. Live Search Autocomplete
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');

    if (searchInput && searchDropdown) {
        let timer = null;
        searchInput.addEventListener('input', () => {
            clearTimeout(timer);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                searchDropdown.style.display = 'none';
                return;
            }

            timer = setTimeout(() => {
                fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.results && data.results.length > 0) {
                            let html = '';
                            data.results.forEach(item => {
                                html += `
                                    <a href="${item.url}" class="search-item">
                                        <img src="${item.image_url}" alt="${item.name}">
                                        <div>
                                            <div style="font-weight: 600; color: #fff;">${item.name}</div>
                                            <div style="font-size: 0.8rem; color: #6366f1;">${item.category_name} &bull; ${item.price}</div>
                                        </div>
                                    </a>
                                `;
                            });
                            searchDropdown.innerHTML = html;
                            searchDropdown.style.display = 'block';
                        } else {
                            searchDropdown.innerHTML = `<div style="padding: 1rem; color: #94a3b8; text-align: center;">No matching products found</div>`;
                            searchDropdown.style.display = 'block';
                        }
                    })
                    .catch(() => {
                        searchDropdown.style.display = 'none';
                    });
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });
    }

    // 3. Wishlist AJAX Handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.wishlist-toggle-btn');
        if (btn) {
            e.preventDefault();
            const productId = btn.getAttribute('data-product-id');

            fetch('api/wishlist_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'unauthorized') {
                    showToast('Please log in to manage your wishlist.', 'error');
                    setTimeout(() => window.location.href = 'login.php', 1500);
                    return;
                }
                if (data.status === 'success') {
                    if (data.in_wishlist) {
                        btn.classList.add('active');
                        btn.querySelector('i').className = 'fa-solid fa-heart';
                    } else {
                        btn.classList.remove('active');
                        btn.querySelector('i').className = 'fa-regular fa-heart';
                    }
                    // Update badge
                    const badge = document.getElementById('headerWishlistBadge');
                    if (badge) badge.textContent = data.wishlist_count;
                    showToast(data.message, 'success');
                }
            })
            .catch(() => showToast('Failed to update wishlist', 'error'));
        }
    });

    // 4. Add to Cart AJAX Handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.add-cart-btn');
        if (btn) {
            e.preventDefault();
            const productId = btn.getAttribute('data-product-id');
            const qtyInput = document.getElementById('productQtyInput');
            const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

            fetch('api/cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', product_id: productId, quantity: qty })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const badge = document.getElementById('headerCartBadge');
                    if (badge) badge.textContent = data.cart_count;
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(() => showToast('Error adding to cart', 'error'));
        }
    });

    // Toast Utility Function
    window.showToast = function(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const iconClass = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';
        toast.innerHTML = `<i class="${iconClass}"></i> <span>${message}</span>`;
        
        container.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 3500);
    };

});
