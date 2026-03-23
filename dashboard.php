<?php
session_start();
require_once 'database.php';

// Admin ellenőrzés
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Statisztikák lekérése - JAVÍTVA: order_date helyett created_at
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Mai rendelések - JAVÍTVA: order_date helyett created_at
$todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Rendelések lekérése szűréssel - JAVÍTVA: order_date helyett created_at
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : '';

$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
          FROM orders o WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $query .= " AND o.status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($searchFilter)) {
    $query .= " AND (o.customer_name LIKE :search OR o.customer_email LIKE :search OR o.id LIKE :search)";
    $params[':search'] = "%$searchFilter%";
}

if (!empty($dateFilter)) {
    $query .= " AND DATE(o.created_at) = :date";  // JAVÍTVA: created_at
    $params[':date'] = $dateFilter;
}

$query .= " ORDER BY o.created_at DESC";  // JAVÍTVA: created_at
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kickstar</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Dashboard specifikus stílusok */
        .welcome-section {
            background: linear-gradient(135deg, black, var(--primary-color));
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .welcome-section h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .welcome-section .date {
            opacity: 0.9;
            font-size: 1rem;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-avatar {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-content h3 {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-trend {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .trend-up {
            color: #27ae60;
        }

        .trend-down {
            color: #e74c3c;
        }

        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .filters-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .filters-title h3 {
            color: #2c3e50;
        }

        .export-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .export-btn {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            background: #e9ecef;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-weight: 500;
            color: #2c3e50;
        }

        .filter-group input,
        .filter-group select {
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .filter-actions {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }

        .apply-filters {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .apply-filters:hover {
            background: #2980b9;
        }

        .reset-filters {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .reset-filters:hover {
            background: #7f8c8d;
        }

        .orders-table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .orders-table th:hover {
            background: #e9ecef;
        }

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .orders-table tbody tr {
            transition: all 0.3s ease;
        }

        .orders-table tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fde8e8;
            color: #e74c3c;
        }

        .status-processing {
            background: #e8f0fe;
            color: #3498db;
        }

        .status-shipped {
            background: #e8f8f5;
            color: #27ae60;
        }

        .status-delivered {
            background: #f0f0f0;
            color: #7f8c8d;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-view {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            background: #2980b9;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
            border: none;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-edit:hover {
            background: #e67e22;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #333;
        }

        .order-details-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #3498db;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: #2c3e50;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            background: #f8f9fa;
            padding: 0.8rem;
            text-align: left;
        }

        .items-table td {
            padding: 0.8rem;
            border-bottom: 1px solid #e9ecef;
        }

        .items-table tfoot {
            background: #f8f9fa;
            font-weight: bold;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination button {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover {
            background: #f8f9fa;
        }

        .pagination button.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        @media (max-width: 768px) {
            .welcome-section {
                flex-direction: column;
                text-align: center;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .orders-table {
                display: block;
                overflow-x: auto;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 1rem;
            }

            .order-info-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animációk a törléshez */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Törlés gomb stílus */
        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
        }

        .btn-delete:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        .btn-delete:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }

        /* Törlés alatt álló sor */
        tr.deleting {
            background-color: #f8d7da !important;
            transition: background-color 0.3s ease;
        }

        /* Értesítések */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            z-index: 9999;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .notification.success {
            background: linear-gradient(135deg, #48bb78, #38a169);
        }

        .notification.error {
            background: linear-gradient(135deg, #f56565, #e53e3e);
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <div class="logo">Kickstar</div>
            <ul>
                <li><a href="index.php">Főoldal</a></li>
                <li><a href="products.php">Termékek</a></li>
                <li><a href="login.php">🛡️Admin felület</a></li>
            </ul>
        </nav>
    </header>
    <div class="admin-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div>
                <h2>Üdv újra, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! 👋</h2>
                <p class="date"><?php echo date('Y. F j., l'); ?></p>
            </div>
            <div class="admin-profile">
                <div class="admin-avatar">👤</div>
                <a href="logout.php" class="logout-btn">Kijelentkezés</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <h3>Összes rendelés</h3>
                    <div class="stat-number"><?php echo number_format($totalOrders); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <h3>Függőben lévő</h3>
                    <div class="stat-number"><?php echo number_format($pendingOrders); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <h3>Teljes bevétel</h3>
                    <div class="stat-number"><?php echo number_format($totalRevenue, 0, ',', ' '); ?> Ft</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👟</div>
                <div class="stat-content">
                    <h3>Termékek száma</h3>
                    <div class="stat-number"><?php echo number_format($totalProducts); ?></div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-title">
                <h3>Rendelések szűrése</h3>
            </div>

            <form method="GET" action="" class="filters-grid">
                <div class="filter-group">
                    <label>Keresés</label>
                    <input type="text" name="search" placeholder="Rendelés ID, név, email..." value="<?php echo htmlspecialchars($searchFilter); ?>">
                </div>

                <div class="filter-group">
                    <label>Státusz</label>
                    <select name="status">
                        <option value="">Összes státusz</option>
                        <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Függőben</option>
                        <option value="processing" <?php echo $statusFilter == 'processing' ? 'selected' : ''; ?>>Feldolgozás alatt</option>
                        <option value="shipped" <?php echo $statusFilter == 'shipped' ? 'selected' : ''; ?>>Szállítás alatt</option>
                        <option value="delivered" <?php echo $statusFilter == 'delivered' ? 'selected' : ''; ?>>Kiszállítva</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Dátum</label>
                    <input type="date" name="date" value="<?php echo $dateFilter; ?>">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="apply-filters">Szűrés</button>
                    <a href="dashboard.php" class="reset-filters">Alaphelyzet</a>
                </div>
            </form>
        </div>

        <!-- Rendelések táblázat -->
        <table class="orders-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vásárló</th>
                    <th>Email</th>
                    <th>Összeg</th>
                    <th>Dátum</th>
                    <th>Tételek</th>
                    <th>Státusz</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> Ft</td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo $order['item_count']; ?> db</td>
                        <td>
                            <?php
                            // Státusz badge-ek - EGYSZERŰ MEGJELENÍTÉS, SELECT MENÜ NÉLKÜL
                            $statusClass = '';
                            $statusText = '';

                            switch ($order['status']) {
                                case 'pending':
                                    $statusClass = 'status-pending';
                                    $statusText = '⏳ Függőben';
                                    break;
                                case 'processing':
                                    $statusClass = 'status-processing';
                                    $statusText = '⚙️ Feldolgozás alatt';
                                    break;
                                case 'shipped':
                                    $statusClass = 'status-shipped';
                                    $statusText = '🚚 Szállítás alatt';
                                    break;
                                case 'delivered':
                                    $statusClass = 'status-delivered';
                                    $statusText = '✅ Kiszállítva';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'status-cancelled';
                                    $statusText = '❌ Lemondva';
                                    break;
                                default:
                                    $statusClass = 'status-unknown';
                                    $statusText = $order['status'];
                            }
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-view" onclick="viewOrder(<?php echo $order['id']; ?>)">👁️ Részletek</button>
                                <button class="btn-edit" onclick="editOrder(<?php echo $order['id']; ?>)">✏️ Szerkeszt</button>
                                <button class="btn-delete" onclick="deleteOrder(<?php echo $order['id']; ?>)">🗑️ Törlés</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if (count($orders) > 10): ?>
            <div class="pagination">
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <button>4</button>
                <button>5</button>
                <span>...</span>
                <button>10</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="orderDetails"></div>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div id="editOrderModal" class="modal">
        <div class="modal-content">
            <span class="close-edit">&times;</span>
            <div id="editOrderForm"></div>
        </div>
    </div>

    <script>
        // Státusz frissítése
        function updateStatus(orderId, newStatus) {
            if (confirm('Biztosan módosítod a rendelés státuszát?')) {
                fetch('update-order-status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Státusz sikeresen frissítve!', 'success');
                        } else {
                            showNotification('Hiba történt a frissítés során!', 'error');
                        }
                    });
            }
        }

        // Rendelés részletek megtekintése
        function viewOrder(orderId) {
            const modal = document.getElementById('orderModal');
            const content = document.getElementById('orderDetails');

            content.innerHTML = '<div style="text-align: center;"><div class="spinner"></div> Betöltés...</div>';
            modal.style.display = 'block';

            fetch('get-order-details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    content.innerHTML = data;
                })
                .catch(error => {
                    content.innerHTML = '<p style="color: red;">Hiba történt a rendelés betöltése közben!</p>';
                });
        }

        // Rendelés szerkesztése
        function editOrder(orderId) {
            const modal = document.getElementById('editOrderModal');
            const content = document.getElementById('editOrderForm');

            content.innerHTML = '<div style="text-align: center;"><div class="spinner"></div> Betöltés...</div>';
            modal.style.display = 'block';

            fetch('edit-order.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    content.innerHTML = data;
                });
        }

        // Rendelés törlése
        function deleteOrder(orderId) {
            if (confirm('Biztosan törölni szeretnéd ezt a rendelést? Ez a művelet nem visszavonható!')) {
                fetch('delete-order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Rendelés sikeresen törölve!', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showNotification('Hiba történt a törlés során!', 'error');
                        }
                    });
            }
        }

        // Értesítés megjelenítése
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: ${type === 'success' ? '#27ae60' : '#e74c3c'};
                color: white;
                padding: 1rem 2rem;
                border-radius: 6px;
                z-index: 2000;
                animation: slideIn 0.3s ease;
                box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Modal bezárás
        document.querySelector('.close').onclick = function() {
            document.getElementById('orderModal').style.display = 'none';
        }

        document.querySelector('.close-edit').onclick = function() {
            document.getElementById('editOrderModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            const editModal = document.getElementById('editOrderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        }

        // Táblázat rendezés
        function sortTable(column) {
            const table = document.querySelector('.orders-table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            const sortedRows = rows.sort((a, b) => {
                const aValue = a.cells[column].textContent;
                const bValue = b.cells[column].textContent;

                if (column === 3) { // Összeg
                    return parseFloat(aValue) - parseFloat(bValue);
                } else if (column === 4) { // Dátum
                    return new Date(aValue) - new Date(bValue);
                } else {
                    return aValue.localeCompare(bValue, 'hu');
                }
            });

            tbody.innerHTML = '';
            sortedRows.forEach(row => tbody.appendChild(row));
        }

        // Export CSV
        function exportToCSV() {
            const rows = [];
            const table = document.querySelector('.orders-table');

            // Fejléc
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                if (!th.textContent.includes('Műveletek')) {
                    headers.push(th.textContent.replace('🔽', '').trim());
                }
            });
            rows.push(headers.join(','));

            // Adatok
            table.querySelectorAll('tbody tr').forEach(tr => {
                const row = [];
                tr.querySelectorAll('td').forEach((td, index) => {
                    if (index < 7) { // Műveletek oszlop kihagyása
                        let value = td.textContent.trim();
                        if (value.includes(',')) {
                            value = `"${value}"`;
                        }
                        row.push(value);
                    }
                });
                rows.push(row.join(','));
            });

            const csv = rows.join('\n');
            const blob = new Blob([csv], {
                type: 'text/csv'
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `rendelesek_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
        }

        // Export PDF (placeholder)
        function exportToPDF() {
            showNotification('PDF export funkció fejlesztés alatt...', 'info');
        }

        // Animációk
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            .spinner {
                display: inline-block;
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 20px auto;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>
