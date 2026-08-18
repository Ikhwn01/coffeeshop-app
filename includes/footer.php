<?php
// includes/footer.php
?>
    <!-- Footer -->
    <footer>
        <div class="container footer-content">
            <div class="footer-col">
                <div class="logo" style="margin-bottom: 1rem;">
                    <i class="fas fa-mug-hot"></i>
                    <span>WARM BREW</span>
                </div>
                <p style="font-size: 0.9rem; margin-bottom: 1rem;"><?php echo __('footer_about', 'Menyajikan racikan kopi biji pilihan nusantara dengan suasana hangat dan elegan untuk momen spesial Anda.'); ?></p>
                <div style="display: flex; gap: 1rem; font-size: 1.2rem; color: var(--primary);">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4><?php echo __('footer_hours', 'Jam Operasional'); ?></h4>
                <p style="font-size: 0.9rem;"><i class="far fa-clock" style="color: var(--primary);"></i> <?php echo __('footer_hours_weekday', 'Senin - Jumat: 08.00 - 23.00 WIB'); ?></p>
                <p style="font-size: 0.9rem; margin-top: 0.5rem;"><i class="far fa-clock" style="color: var(--primary);"></i> <?php echo __('footer_hours_weekend', 'Sabtu - Minggu: 08.00 - 00.00 WIB'); ?></p>
            </div>

            <div class="footer-col">
                <h4><?php echo __('footer_contact', 'Lokasi & Kontak'); ?></h4>
                <p style="font-size: 0.9rem;"><i class="fas fa-map-marker-alt" style="color: var(--primary);"></i> <?php echo __('footer_address', 'Jl. Senopati No. 42, Jakarta Selatan'); ?></p>
                <p style="font-size: 0.9rem; margin-top: 0.5rem;"><i class="fas fa-phone" style="color: var(--primary);"></i> +62 812-3456-7890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> <?php echo __('footer_copyright', 'Warm Brew Coffee Shop Management System. Hak Cipta Dilindungi.'); ?></p>
            </div>
        </div>
    </footer>

    <!-- Cart Drawer & Overlay -->
    <div class="cart-drawer-overlay" id="cartDrawerOverlay"></div>
    <div class="cart-drawer" id="cartDrawer">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-bag" style="color: var(--primary);"></i> <?php echo __('cart_title', 'Pesanan Saya'); ?></h3>
            <button class="close-btn" id="closeCartBtn"><i class="fas fa-times"></i></button>
        </div>

        <div class="cart-body" id="cartItemsContainer">
            <!-- Items rendered dynamically via main.js -->
        </div>

        <div class="cart-footer">
            <div class="cart-summary-row">
                <span><?php echo __('subtotal', 'Subtotal'); ?></span>
                <span id="cartTotal">Rp 0</span>
            </div>

            <!-- Checkout Form -->
            <form id="checkoutForm" style="margin-top: 1rem;">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> <?php echo __('customer_name', 'Nama Pemesan *'); ?></label>
                    <input type="text" id="customerNameInput" class="form-control" placeholder="<?php echo __('customer_name_placeholder', 'Masukkan nama Anda...'); ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-chair"></i> <?php echo __('table_number', 'No. Meja'); ?></label>
                        <input type="text" id="tableNumberInput" class="form-control" placeholder="<?php echo __('table_number_placeholder', 'M-01 / Takeaway'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-utensils"></i> <?php echo __('order_type', 'Tipe Pesanan'); ?></label>
                        <select id="orderTypeSelect" class="form-control">
                            <option value="dine_in"><?php echo __('dine_in', 'Dine In (Makan di Sini)'); ?></option>
                            <option value="takeaway"><?php echo __('takeaway', 'Takeaway (Bawa Pulang)'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-wallet"></i> <?php echo __('payment_method', 'Metode Pembayaran'); ?></label>
                    <select id="paymentMethodSelect" class="form-control">
                        <option value="QRIS"><?php echo __('pay_qris', 'QRIS / E-Wallet'); ?></option>
                        <option value="Cash"><?php echo __('pay_cash', 'Cash (Bayar di Kasir)'); ?></option>
                        <option value="Debit/Credit Card"><?php echo __('pay_card', 'Kartu Debit/Kredit'); ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="far fa-comment-dots"></i> <?php echo __('order_notes', 'Catatan Khusus (Opsional)'); ?></label>
                    <input type="text" id="orderNotesInput" class="form-control" placeholder="<?php echo __('order_notes_placeholder', 'Misal: Less sugar, ekstra es...'); ?>">
                </div>

                <button type="submit" id="cartCheckoutBtn" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;" disabled>
                    <i class="fas fa-paper-plane"></i> <?php echo __('btn_confirm_order', 'Konfirmasi & Kirim Pesanan'); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Pass language config to JS -->
    <script>
        window.APP_LANG = "<?php echo get_current_lang(); ?>";
        window.APP_I18N = {
            cart_empty: "<?php echo addslashes(__('cart_empty', 'Keranjang Anda masih kosong.')); ?>",
            cart_empty_sub: "<?php echo addslashes(__('cart_empty_sub', 'Silakan pilih menu favorit Anda di atas!')); ?>",
            processing: "<?php echo addslashes(get_current_lang() == 'en' ? 'Processing...' : 'Memproses...'); ?>"
        };
    </script>
    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>
