<?php
// api/process_order.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['customer_name']) || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak lengkap.']);
    exit;
}

$customer_name  = sanitize($input['customer_name']);
$table_number   = !empty($input['table_number']) ? sanitize($input['table_number']) : 'Takeaway';
$order_type     = !empty($input['order_type']) ? sanitize($input['order_type']) : 'dine_in';
$payment_method = !empty($input['payment_method']) ? sanitize($input['payment_method']) : 'Cash';
$notes          = !empty($input['notes']) ? sanitize($input['notes']) : '';
$items          = $input['items'];

// Generate unique order code ORD-YYYYMMDD-XX
$dateStr = date('Ymd');
$randNum = rand(10, 99);
$order_code = "ORD-{$dateStr}-{$randNum}";

try {
    $pdo->beginTransaction();

    // Calculate total amount from items in DB to prevent tampering
    $total_amount = 0;
    $order_items_to_insert = [];

    foreach ($items as $item) {
        $product_id = intval($item['id']);
        $qty        = max(1, intval($item['quantity']));

        $stmtProd = $pdo->prepare("SELECT price FROM products WHERE id = ? AND is_available = 1");
        $stmtProd->execute([$product_id]);
        $prod = $stmtProd->fetch();

        if ($prod) {
            $price = floatval($prod['price']);
            $subtotal = $price * $qty;
            $total_amount += $subtotal;

            $order_items_to_insert[] = [
                'product_id' => $product_id,
                'quantity'   => $qty,
                'price'      => $price,
                'subtotal'   => $subtotal
            ];
        }
    }

    if (empty($order_items_to_insert)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Menu tidak valid atau tidak tersedia.']);
        exit;
    }

    // Insert Order
    $payment_status = ($payment_method === 'QRIS' || $payment_method === 'Debit/Credit Card') ? 'paid' : 'unpaid';
    $stmtOrder = $pdo->prepare("INSERT INTO orders (order_code, customer_name, table_number, order_type, total_amount, payment_status, order_status, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
    $stmtOrder->execute([
        $order_code, $customer_name, $table_number, $order_type, $total_amount, $payment_status, $payment_method, $notes
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert Order Items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
    foreach ($order_items_to_insert as $oi) {
        $stmtItem->execute([
            $order_id, $oi['product_id'], $oi['quantity'], $oi['price'], $oi['subtotal']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success'    => true,
        'message'    => 'Pesanan berhasil dibuat.',
        'order_code' => $order_code
    ]);

} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Gagal memproses ke database: ' . $e->getMessage()]);
}
