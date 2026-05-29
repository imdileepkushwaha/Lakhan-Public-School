<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle marking as read if requested
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE enquiries SET status = 'read' WHERE id = ?");
    $stmt->execute([$_GET['mark_read']]);
    header("Location: dashboard.php");
    exit;
}

// Fetch all enquiries
$stmt = $pdo->query("SELECT * FROM enquiries ORDER BY created_at DESC");
$enquiries = $stmt->fetchAll();

// Get unread count
$stmt_unread = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'unread'");
$unread_count = $stmt_unread->fetchColumn();

// Get total count
$stmt_total = $pdo->query("SELECT COUNT(*) FROM enquiries");
$total_count = $stmt_total->fetchColumn();

// Get today's count
$today = date('Y-m-d');
$stmt_today = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE DATE(created_at) = ?");
$stmt_today->execute([$today]);
$today_count = $stmt_today->fetchColumn();

// Get this month's count
$month = date('Y-m');
$stmt_month = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt_month->execute([$month]);
$month_count = $stmt_month->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lakhan Public School</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <?php include 'includes/header.php'; ?>

            <!-- Dashboard Body -->
            <div class="dashboard-body">
                <!-- Stats Section -->
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
                        <div class="stat-details">
                            <h3><?= $today_count ?></h3>
                            <p>Today's Enquiries</p>
                        </div>
                    </div>
                    <div class="stat-card pink">
                        <div class="stat-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
                        <div class="stat-details">
                            <h3><?= $unread_count ?></h3>
                            <p>Unread Enquiries</p>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-icon"><i class="fa-solid fa-calendar-week"></i></div>
                        <div class="stat-details">
                            <h3><?= $month_count ?></h3>
                            <p>This Month</p>
                        </div>
                    </div>
                    <div class="stat-card purple">
                        <div class="stat-icon"><i class="fa-solid fa-database"></i></div>
                        <div class="stat-details">
                            <h3><?= $total_count ?></h3>
                            <p>Total Enquiries</p>
                        </div>
                    </div>
                </div>

                <div class="data-card">
                    <div class="data-card-header">
                        Recent Enquiries
                        <span class="stat-total">Total: <?= count($enquiries) ?></span>
                    </div>
                    <div class="table-responsive">
                        <?php if(empty($enquiries)): ?>
                            <div class="empty-state">
                                <i class="fa-regular fa-folder-open"></i>
                                <h3>No Enquiries Found</h3>
                                <p>When someone submits a contact form, it will show up here.</p>
                            </div>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($enquiries as $enq): ?>
                                    <tr class="<?= $enq['status'] == 'unread' ? 'tr-unread' : '' ?>">
                                        <td>#<?= $enq['id'] ?></td>
                                        <td><?= date('d M Y, h:i A', strtotime($enq['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($enq['name']) ?></td>
                                        <td><a href="mailto:<?= htmlspecialchars($enq['email']) ?>"><?= htmlspecialchars($enq['email']) ?></a></td>
                                        <td><?= !empty($enq['phone']) ? '<a href="tel:'.htmlspecialchars($enq['phone']).'">'.htmlspecialchars($enq['phone']).'</a>' : 'N/A' ?></td>
                                        <td><?= nl2br(htmlspecialchars($enq['message'] ?? '')) ?></td>
                                        <td>
                                            <?php if($enq['status'] == 'unread'): ?>
                                                <span class="status-badge status-unread">Unread</span>
                                            <?php else: ?>
                                                <span class="status-badge status-read">Read</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($enq['status'] == 'unread'): ?>
                                                <a href="dashboard.php?mark_read=<?= $enq['id'] ?>" class="btn-sm">Mark as Read</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
</body>
</html>


