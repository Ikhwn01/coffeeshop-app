<?php
// config/lang.php - Comprehensive Multi-Language Dictionary (ID & EN)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle language switch via GET parameter
if (isset($_GET['lang'])) {
    $allowed_langs = ['id', 'en'];
    $selected_lang = strtolower(trim($_GET['lang']));
    if (in_array($selected_lang, $allowed_langs)) {
        $_SESSION['lang'] = $selected_lang;
    }
}

// Default language: Indonesian (id) or English (en)
$current_lang = $_SESSION['lang'] ?? 'id';

$translations = [
    'id' => [
        // Meta & Titles
        'app_name' => 'Warm Brew Coffee Shop',
        'meta_title_home' => 'Menu & Pesan Online',
        'meta_title_reservation' => 'Reservasi Meja Online',
        'meta_title_status' => 'Cek Status Pesanan',
        'meta_title_admin_login' => 'Login Staf',
        'meta_title_admin_dashboard' => 'Dashboard Staf & Keuangan',

        // Navbar
        'nav_menu' => 'Menu & Pesan',
        'nav_reservation' => 'Reservasi Meja',
        'nav_order_status' => 'Cek Status Pesanan',
        'nav_staff_login' => 'Staf Login',
        'nav_table' => 'Meja',
        'nav_view_cart' => 'Lihat Keranjang',

        // Hero Banner
        'hero_subtitle' => 'Speciality Craft Coffee',
        'hero_title' => 'Nikmati Cita Rasa Kopi Sejati & Makanan Lezat',
        'hero_desc' => 'Pesan langsung dari meja Anda tanpa perlu mengantre. Pilih menu favorit, isi nama & nomor meja Anda, lalu biarkan barista kami melayani Anda.',
        'hero_table_indicator' => 'Anda sedang berada di',

        // Filter & Search
        'all_menu' => 'Semua Menu',
        'search_placeholder' => 'Cari menu favorit...',
        'btn_add' => 'Tambah',

        // Cart Modal & Checkout
        'cart_title' => 'Pesanan Saya',
        'cart_empty' => 'Keranjang Anda masih kosong.',
        'cart_empty_sub' => 'Silakan pilih menu favorit Anda di atas!',
        'subtotal' => 'Subtotal',
        'customer_name' => 'Nama Pemesan *',
        'customer_name_placeholder' => 'Masukkan nama Anda...',
        'table_number' => 'No. Meja',
        'table_number_placeholder' => 'M-01 / Takeaway',
        'order_type' => 'Tipe Pesanan',
        'dine_in' => 'Dine In (Makan di Sini)',
        'takeaway' => 'Takeaway (Bawa Pulang)',
        'payment_method' => 'Metode Pembayaran',
        'pay_qris' => 'QRIS / E-Wallet',
        'pay_cash' => 'Cash (Bayar di Kasir)',
        'pay_card' => 'Kartu Debit/Kredit',
        'order_notes' => 'Catatan Khusus (Opsional)',
        'order_notes_placeholder' => 'Misal: Less sugar, ekstra es...',
        'total_payment' => 'Total Pembayaran',
        'btn_confirm_order' => 'Konfirmasi & Kirim Pesanan',
        'btn_continue_shopping' => 'Pilih Menu',

        // Footer
        'footer_about' => 'Menyajikan racikan kopi biji pilihan nusantara dengan suasana hangat dan elegan untuk momen spesial Anda.',
        'footer_hours' => 'Jam Operasional',
        'footer_hours_weekday' => 'Senin - Jumat: 08.00 - 23.00 WIB',
        'footer_hours_weekend' => 'Sabtu - Minggu: 08.00 - 00.00 WIB',
        'footer_contact' => 'Lokasi & Kontak',
        'footer_address' => 'Jl. Senopati No. 42, Jakarta Selatan',
        'footer_copyright' => 'Warm Brew Coffee Shop Management System. Hak Cipta Dilindungi.',

        // Reservation Page
        'res_title' => 'Reservasi Meja Cafe',
        'res_subtitle' => 'Online Booking',
        'res_desc' => 'Amankan tempat terbaik Anda untuk kumpul keluarga, rapat bisnis, atau sekadar bersantai.',
        'res_select_table' => 'Pilih Meja *',
        'res_select_table_opt' => '-- Pilih Meja --',
        'res_name' => 'Nama Lengkap Pemesan *',
        'res_name_placeholder' => 'Nama lengkap Anda...',
        'res_phone' => 'Nomor WhatsApp / HP *',
        'res_phone_placeholder' => '08xxxxxxxxxx',
        'res_email' => 'Email (Opsional)',
        'res_email_placeholder' => 'nama@email.com',
        'res_date' => 'Tanggal Kedatangan *',
        'res_time' => 'Jam Kedatangan *',
        'res_guests' => 'Jumlah Tamu (Orang) *',
        'res_notes' => 'Catatan Khusus / Permintaan Meja',
        'res_notes_placeholder' => 'Misal: Meja dekat jendela, ada anak kecil...',
        'res_btn_submit' => 'Kirim Reservasi Sekarang',
        'res_success_title' => 'Reservasi Berhasil Dikirim!',
        'res_success_desc' => 'Simpan kode reservasi di bawah ini untuk konfirmasi saat tiba di lokasi.',
        'res_code' => 'Kode Reservasi',
        'res_btn_new' => 'Buat Reservasi Baru',
        'res_btn_menu' => 'Lihat Menu Coffee Shop',

        // Order Status Page
        'status_page_title' => 'Status Pesanan Anda',
        'status_page_subtitle' => 'Live Status Tracking',
        'status_page_desc' => 'Masukkan kode unik pesanan yang Anda dapatkan saat memesan.',
        'status_input_placeholder' => 'Masukkan Kode Pesanan (contoh: ORD-20260801-01)...',
        'status_btn_search' => 'Cek Status',
        'status_not_found_title' => 'Pesanan Tidak Ditemukan',
        'status_not_found_desc' => 'tidak terdaftar di sistem kami.',
        'status_order_code' => 'Kode Pesanan',
        'status_payment_status' => 'Status Pembayaran',
        'status_customer_name' => 'Nama Pemesan',
        'status_location_table' => 'Lokasi / Meja',
        'status_order_type' => 'Tipe Pesanan',
        'status_order_time' => 'Waktu Pesan',
        'status_prep_status' => 'Status Pembuatan',
        'status_order_items' => 'Rincian Menu Pesanan',
        'status_product' => 'Produk',
        'status_price' => 'Harga Satuan',
        'status_qty' => 'Qty',
        'status_total' => 'Total',
        'status_back_menu' => 'Pesan Menu Lagi',
        'status_pending_label' => 'Menunggu Konfirmasi Barista',
        'status_processing_label' => 'Sedang Disiapkan / Diseduh',
        'status_ready_label' => 'Pesanan Siap Disajikan!',
        'status_completed_label' => 'Pesanan Selesai',
        'status_cancelled_label' => 'Pesanan Dibatalkan',

        // Admin & POS
        'admin_login_title' => 'Login Staf & Admin',
        'admin_login_subtitle' => 'Silakan masuk menggunakan akun kasir atau administrator.',
        'admin_username' => 'Username',
        'admin_password' => 'Password',
        'admin_btn_login' => 'Masuk ke Sistem',
        'admin_dashboard' => 'Dashboard',
        'admin_orders' => 'Pesanan & Transaksi',
        'admin_reservations' => 'Reservasi Meja',
        'admin_products' => 'Menu & Kategori',
        'admin_tables' => 'Meja & QR Code',
        'admin_expenses' => 'Pengeluaran Kas',
        'admin_reports' => 'Laporan Keuangan',
        'admin_logout' => 'Keluar (Logout)',
        'admin_customer_web' => 'Lihat Web Customer',
        'admin_today_revenue' => 'Pendapatan Hari Ini',
        'admin_today_orders' => 'Pesanan Hari Ini',
        'admin_pending_orders' => 'Menunggu Proses',
        'admin_active_reservations' => 'Reservasi Aktif',
        'admin_recent_orders' => 'Pesanan Terbaru',
        'admin_view_all' => 'Lihat Semua',
        'admin_print_receipt' => 'Cetak Struk',
        'admin_status_update' => 'Ubah Status',
        'admin_action' => 'Aksi',
        'admin_add_product' => 'Tambah Menu Baru',
        'admin_add_table' => 'Tambah Meja Baru',
        'admin_add_expense' => 'Catat Kas Keluar',
        'admin_filter' => 'Filter',
        'admin_export' => 'Ekspor Laporan'
    ],
    'en' => [
        // Meta & Titles
        'app_name' => 'Warm Brew Coffee Shop',
        'meta_title_home' => 'Menu & Online Ordering',
        'meta_title_reservation' => 'Online Table Reservation',
        'meta_title_status' => 'Track Order Status',
        'meta_title_admin_login' => 'Staff Login',
        'meta_title_admin_dashboard' => 'Staff & Financial Dashboard',

        // Navbar
        'nav_menu' => 'Menu & Order',
        'nav_reservation' => 'Table Reservation',
        'nav_order_status' => 'Track Order',
        'nav_staff_login' => 'Staff Login',
        'nav_table' => 'Table',
        'nav_view_cart' => 'View Cart',

        // Hero Banner
        'hero_subtitle' => 'Speciality Craft Coffee',
        'hero_title' => 'Experience True Artisan Coffee & Delicious Food',
        'hero_desc' => 'Order directly from your table with zero queuing. Pick your favorite items, fill in your table number, and let our baristas serve you.',
        'hero_table_indicator' => 'You are currently at',

        // Filter & Search
        'all_menu' => 'All Menu',
        'search_placeholder' => 'Search favorite item...',
        'btn_add' => 'Add',

        // Cart Modal & Checkout
        'cart_title' => 'My Order Cart',
        'cart_empty' => 'Your cart is currently empty.',
        'cart_empty_sub' => 'Please select delicious coffee or food from our menu above!',
        'subtotal' => 'Subtotal',
        'customer_name' => 'Customer Name *',
        'customer_name_placeholder' => 'Enter your name...',
        'table_number' => 'Table No.',
        'table_number_placeholder' => 'M-01 / Takeaway',
        'order_type' => 'Order Type',
        'dine_in' => 'Dine In',
        'takeaway' => 'Takeaway',
        'payment_method' => 'Payment Method',
        'pay_qris' => 'QRIS / Digital E-Wallet',
        'pay_cash' => 'Cash at Cashier',
        'pay_card' => 'Debit / Credit Card',
        'order_notes' => 'Special Instructions (Optional)',
        'order_notes_placeholder' => 'e.g. Less sugar, extra ice...',
        'total_payment' => 'Total Payment',
        'btn_confirm_order' => 'Confirm & Place Order',
        'btn_continue_shopping' => 'Select Items',

        // Footer
        'footer_about' => 'Serving fine artisan coffee beans with a warm and elegant ambiance for your memorable moments.',
        'footer_hours' => 'Opening Hours',
        'footer_hours_weekday' => 'Monday - Friday: 08:00 AM - 11:00 PM',
        'footer_hours_weekend' => 'Saturday - Sunday: 08:00 AM - 12:00 AM',
        'footer_contact' => 'Location & Contact',
        'footer_address' => '42 Senopati Street, South Jakarta',
        'footer_copyright' => 'Warm Brew Coffee Shop Management System. All Rights Reserved.',

        // Reservation Page
        'res_title' => 'Cafe Table Reservation',
        'res_subtitle' => 'Online Booking',
        'res_desc' => 'Reserve your favorite spot in advance for family gatherings, business meetings, or relaxed afternoons.',
        'res_select_table' => 'Select Table *',
        'res_select_table_opt' => '-- Select Table --',
        'res_name' => 'Full Name *',
        'res_name_placeholder' => 'Your full name...',
        'res_phone' => 'WhatsApp / Phone Number *',
        'res_phone_placeholder' => '+62 8xxxxxxxxxx',
        'res_email' => 'Email Address (Optional)',
        'res_email_placeholder' => 'name@email.com',
        'res_date' => 'Arrival Date *',
        'res_time' => 'Arrival Time *',
        'res_guests' => 'Number of Guests (Persons) *',
        'res_notes' => 'Special Requests / Table Notes',
        'res_notes_placeholder' => 'e.g. Table near window, birthday event...',
        'res_btn_submit' => 'Submit Reservation Now',
        'res_success_title' => 'Reservation Submitted Successfully!',
        'res_success_desc' => 'Please save your reservation code below and present it upon arrival.',
        'res_code' => 'Reservation Code',
        'res_btn_new' => 'Make Another Reservation',
        'res_btn_menu' => 'View Coffee Shop Menu',

        // Order Status Page
        'status_page_title' => 'Your Order Status',
        'status_page_subtitle' => 'Live Status Tracking',
        'status_page_desc' => 'Enter the unique order code you received when placing your order.',
        'status_input_placeholder' => 'Enter Order Code (e.g. ORD-20260801-01)...',
        'status_btn_search' => 'Track Order',
        'status_not_found_title' => 'Order Not Found',
        'status_not_found_desc' => 'is not registered in our system.',
        'status_order_code' => 'Order Code',
        'status_payment_status' => 'Payment Status',
        'status_customer_name' => 'Customer Name',
        'status_location_table' => 'Location / Table',
        'status_order_type' => 'Order Type',
        'status_order_time' => 'Order Time',
        'status_prep_status' => 'Preparation Status',
        'status_order_items' => 'Ordered Items Breakdown',
        'status_product' => 'Product',
        'status_price' => 'Unit Price',
        'status_qty' => 'Qty',
        'status_total' => 'Total',
        'status_back_menu' => 'Order More Items',
        'status_pending_label' => 'Awaiting Barista Confirmation',
        'status_processing_label' => 'Preparing / Brewing in Progress',
        'status_ready_label' => 'Order Ready to Serve!',
        'status_completed_label' => 'Order Completed',
        'status_cancelled_label' => 'Order Cancelled',

        // Admin & POS
        'admin_login_title' => 'Staff & Admin Login',
        'admin_login_subtitle' => 'Please sign in using your cashier or administrator credentials.',
        'admin_username' => 'Username',
        'admin_password' => 'Password',
        'admin_btn_login' => 'Sign In to System',
        'admin_dashboard' => 'Dashboard',
        'admin_orders' => 'Orders & POS',
        'admin_reservations' => 'Table Reservations',
        'admin_products' => 'Menu & Categories',
        'admin_tables' => 'Tables & QR Codes',
        'admin_expenses' => 'Cash Expenses',
        'admin_reports' => 'Financial Reports',
        'admin_logout' => 'Sign Out',
        'admin_customer_web' => 'View Customer Web',
        'admin_today_revenue' => "Today's Revenue",
        'admin_today_orders' => "Today's Orders",
        'admin_pending_orders' => 'Pending Queue',
        'admin_active_reservations' => 'Active Bookings',
        'admin_recent_orders' => 'Recent Orders',
        'admin_view_all' => 'View All',
        'admin_print_receipt' => 'Print Receipt',
        'admin_status_update' => 'Update Status',
        'admin_action' => 'Action',
        'admin_add_product' => 'Add New Menu Item',
        'admin_add_table' => 'Add New Table',
        'admin_add_expense' => 'Record Expense',
        'admin_filter' => 'Filter',
        'admin_export' => 'Export Report'
    ]
];

// Translation helper function
if (!function_exists('__')) {
    function __($key, $default = '') {
        global $translations, $current_lang;
        if (isset($translations[$current_lang][$key])) {
            return $translations[$current_lang][$key];
        }
        if (isset($translations['en'][$key])) {
            return $translations['en'][$key];
        }
        return !empty($default) ? $default : $key;
    }
}

// Function to get current language code
if (!function_exists('get_current_lang')) {
    function get_current_lang() {
        global $current_lang;
        return $current_lang;
    }
}
