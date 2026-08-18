// Main Customer JavaScript - main.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. LocalStorage Cart Initialization
    let cart = JSON.parse(localStorage.getItem('coffeeshop_cart')) || [];
    
    const cartDrawer = document.getElementById('cartDrawer');
    const cartOverlay = document.getElementById('cartDrawerOverlay');
    const openCartBtn = document.getElementById('openCartBtn');
    const closeCartBtn = document.getElementById('closeCartBtn');
    const cartBadge = document.getElementById('cartBadge');
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartTotalElement = document.getElementById('cartTotal');
    const cartCheckoutBtn = document.getElementById('cartCheckoutBtn');
    const lang = window.APP_LANG || 'id';

    // Parse table number from URL query parameter (e.g. ?table=M-03)
    const urlParams = new URLSearchParams(window.location.search);
    const tableParam = urlParams.get('table');
    if (tableParam) {
        localStorage.setItem('coffeeshop_table', tableParam);
        const tableBadge = document.getElementById('currentTableBadge');
        if (tableBadge) {
            tableBadge.textContent = (lang === 'en' ? 'Table: ' : 'Meja: ') + tableParam;
            tableBadge.style.display = 'inline-flex';
        }
        const tableInput = document.getElementById('tableNumberInput');
        if (tableInput) {
            tableInput.value = tableParam;
        }
    }

    // Toggle Cart Drawer
    if (openCartBtn) {
        openCartBtn.addEventListener('click', () => {
            if (cartDrawer && cartOverlay) {
                cartDrawer.classList.add('active');
                cartOverlay.classList.add('active');
            }
        });
    }

    if (closeCartBtn) {
        closeCartBtn.addEventListener('click', closeCart);
    }
    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCart);
    }

    function closeCart() {
        if (cartDrawer && cartOverlay) {
            cartDrawer.classList.remove('active');
            cartOverlay.classList.remove('active');
        }
    }

    // Add to cart buttons listener
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const card = e.target.closest('.product-card');
            const id = card.dataset.id;
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);

            addToCart(id, name, price);
            const msg = lang === 'en' ? `"${name}" added to cart` : `"${name}" ditambahkan ke keranjang`;
            showToast(msg);
        });
    });

    function addToCart(id, name, price) {
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ id, name, price, quantity: 1 });
        }
        saveCart();
        renderCart();
    }

    function updateQuantity(id, change) {
        const item = cart.find(item => item.id === id);
        if (item) {
            item.quantity += change;
            if (item.quantity <= 0) {
                cart = cart.filter(i => i.id !== id);
            }
        }
        saveCart();
        renderCart();
    }

    function saveCart() {
        localStorage.setItem('coffeeshop_cart', JSON.stringify(cart));
    }

    function renderCart() {
        if (!cartItemsContainer) return;
        
        let totalCount = 0;
        let totalPrice = 0;
        cartItemsContainer.innerHTML = '';

        if (cart.length === 0) {
            const emptyText = lang === 'en' ? 'Your cart is currently empty.' : 'Keranjang pesanan Anda masih kosong.';
            const emptySub = lang === 'en' ? 'Select your favorite items above!' : 'Silakan pilih menu favorit Anda di atas!';
            cartItemsContainer.innerHTML = `
                <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                    <i class="fas fa-shopping-basket" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p style="font-weight: 600;">${emptyText}</p>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">${emptySub}</p>
                </div>
            `;
            if (cartCheckoutBtn) cartCheckoutBtn.disabled = true;
        } else {
            if (cartCheckoutBtn) cartCheckoutBtn.disabled = false;
            cart.forEach(item => {
                totalCount += item.quantity;
                const itemTotal = item.price * item.quantity;
                totalPrice += itemTotal;

                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item';
                itemElement.innerHTML = `
                    <div class="cart-item-details">
                        <div class="cart-item-title">${escapeHtml(item.name)}</div>
                        <div class="cart-item-price">Rp ${formatNumber(item.price)}</div>
                    </div>
                    <div class="qty-control">
                        <button class="qty-btn btn-minus" data-id="${item.id}">-</button>
                        <span class="qty-val">${item.quantity}</span>
                        <button class="qty-btn btn-plus" data-id="${item.id}">+</button>
                    </div>
                `;
                cartItemsContainer.appendChild(itemElement);
            });

            // Bind +/- buttons
            cartItemsContainer.querySelectorAll('.btn-minus').forEach(b => {
                b.addEventListener('click', () => updateQuantity(b.dataset.id, -1));
            });
            cartItemsContainer.querySelectorAll('.btn-plus').forEach(b => {
                b.addEventListener('click', () => updateQuantity(b.dataset.id, 1));
            });
        }

        if (cartBadge) cartBadge.textContent = totalCount;
        if (cartTotalElement) cartTotalElement.textContent = 'Rp ' + formatNumber(totalPrice);
    }

    // Filter Products by Category & Search
    const searchInput = document.getElementById('productSearchInput');
    const categoryBtns = document.querySelectorAll('.category-btn');
    const productCards = document.querySelectorAll('.product-card');

    if (categoryBtns.length > 0) {
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterProducts();
            });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }

    function filterProducts() {
        const activeCategory = document.querySelector('.category-btn.active')?.dataset.category || 'all';
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        productCards.forEach(card => {
            const cardCategory = card.dataset.category;
            const cardTitle = card.dataset.name.toLowerCase();

            const matchesCategory = (activeCategory === 'all' || cardCategory === activeCategory);
            const matchesQuery = cardTitle.includes(query);

            if (matchesCategory && matchesQuery) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Checkout Form Handler (Inside Cart Drawer / Modal)
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (cart.length === 0) {
                showToast(lang === 'en' ? 'Your cart is empty!' : 'Keranjang Anda kosong!', 'error');
                return;
            }

            const customerName = document.getElementById('customerNameInput').value.trim();
            const tableNumber = document.getElementById('tableNumberInput').value.trim();
            const orderType = document.getElementById('orderTypeSelect').value;
            const paymentMethod = document.getElementById('paymentMethodSelect').value;
            const notes = document.getElementById('orderNotesInput').value.trim();

            if (!customerName) {
                showToast(lang === 'en' ? 'Please enter your name!' : 'Harap masukkan nama Anda!', 'error');
                return;
            }

            const submitBtn = checkoutForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (lang === 'en' ? 'Processing...' : 'Memproses...');

            try {
                const response = await fetch('api/process_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        customer_name: customerName,
                        table_number: tableNumber,
                        order_type: orderType,
                        payment_method: paymentMethod,
                        notes: notes,
                        items: cart
                    })
                });

                const result = await response.json();
                if (result.success) {
                    // Clear cart
                    cart = [];
                    saveCart();
                    renderCart();
                    closeCart();
                    window.location.href = `order_status.php?code=${result.order_code}`;
                } else {
                    showToast(result.message || (lang === 'en' ? 'Failed to create order.' : 'Gagal membuat pesanan.'), 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = lang === 'en' ? 'Confirm & Place Order' : 'Konfirmasi & Kirim Pesanan';
                }
            } catch (err) {
                console.error(err);
                showToast(lang === 'en' ? 'Network connection error.' : 'Terjadi kesalahan jaringan.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = lang === 'en' ? 'Confirm & Place Order' : 'Konfirmasi & Kirim Pesanan';
            }
        });
    }

    // Helper Toast
    function showToast(message, type = 'info') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'toast';
        const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
        toast.innerHTML = `<i class="fas ${icon}" style="color: var(--primary);"></i> <span>${escapeHtml(message)}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    function formatNumber(num) {
        return num.toLocaleString('id-ID');
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

    // Initial render
    renderCart();
});
