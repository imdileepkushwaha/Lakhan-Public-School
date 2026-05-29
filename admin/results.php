<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$upload_dir = '../images/results/';

// Handle Add
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $student_name = trim($_POST['student_name']);
    $class_name = trim($_POST['class_name']);
    $percentage = trim($_POST['percentage']);
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    $image_filename = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_filename = uniqid('result_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
    }

    $stmt = $pdo->prepare("INSERT INTO student_results (student_name, class_name, percentage, image_path, is_visible) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student_name, $class_name, $percentage, $image_filename, $is_visible]);
    header("Location: results.php?success=1");
    exit;
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = $_POST['id'];
    $student_name = trim($_POST['student_name']);
    $class_name = trim($_POST['class_name']);
    $percentage = trim($_POST['percentage']);
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE student_results SET student_name=?, class_name=?, percentage=?, is_visible=? WHERE id=?");
    $stmt->execute([$student_name, $class_name, $percentage, $is_visible, $id]);

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // delete old
        $stmt = $pdo->prepare("SELECT image_path FROM student_results WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if ($old && $old['image_path']) {
            @unlink($upload_dir . $old['image_path']);
        }
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_filename = uniqid('result_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
        
        $stmt = $pdo->prepare("UPDATE student_results SET image_path=? WHERE id=?");
        $stmt->execute([$image_filename, $id]);
    }
    header("Location: results.php?updated=1");
    exit;
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM student_results WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $res = $stmt->fetch();
    if ($res && $res['image_path']) {
        @unlink($upload_dir . $res['image_path']);
    }
    $stmt = $pdo->prepare("DELETE FROM student_results WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: results.php?deleted=1");
    exit;
}

// Handle Toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE student_results SET is_visible = NOT is_visible WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    header("Location: results.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM student_results ORDER BY created_at DESC");
$results = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Results - Lakhan Public School</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>

    <div class="admin-wrapper">
        <div class="mobile-overlay" onclick="toggleSidebar()"></div>
        
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <div class="dashboard-body">
                
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert-success">
                        <i class="fa-solid fa-check-circle"></i> Result added successfully!
                    </div>
                <?php endif; ?>
                <?php if(isset($_GET['updated'])): ?>
                    <div class="alert-info">
                        <i class="fa-solid fa-check-circle"></i> Result updated successfully!
                    </div>
                <?php endif; ?>

                <div class="upload-form">
                    <h3><i class="fa-solid fa-user-graduate"></i> Add New Result</h3>
                    <form action="results.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Student Name</label>
                                <input type="text" name="student_name" required placeholder="E.g. Aman Verma">
                            </div>
                            <div class="form-group">
                                <label>Class</label>
                                <input type="text" name="class_name" required placeholder="E.g. Class - XII">
                            </div>
                            <div class="form-group">
                                <label>Percentage / Score</label>
                                <input type="text" name="percentage" required placeholder="E.g. 98.4%">
                            </div>
                        </div>
                        <div class="form-row form-row-custom">
                            <div class="form-group">
                                <label>Student Photo (Optional)</label>
                                <input type="file" name="image" accept="image/*">
                                <small class="form-hint">If left empty, a default avatar will be shown.</small>
                            </div>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" name="is_visible" id="is_visible" checked class="checkbox-input">
                                <label for="is_visible" class="m-0">Show on Website</label>
                            </div>
                            <button type="submit" class="btn-primary btn-submit">
                                <i class="fa-solid fa-plus"></i> Add Result
                            </button>
                        </div>
                    </form>
                </div>

                <div class="results-grid">
                    <?php foreach($results as $res): ?>
                        <div class="result-card">
                            <?php if($res['is_visible']): ?>
                                <div class="status-badge status-active"><i class="fa-solid fa-eye"></i> Active</div>
                            <?php else: ?>
                                <div class="status-badge status-hidden"><i class="fa-solid fa-eye-slash"></i> Hidden</div>
                            <?php endif; ?>
                            
                            <div class="result-img-wrapper">
                                <?php if($res['image_path'] && file_exists('../images/results/' . $res['image_path'])): ?>
                                    <img src="../images/results/<?= $res['image_path'] ?>" alt="Photo">
                                <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($res['student_name']) ?>&background=D81B60&color=fff&size=150" alt="Avatar">
                                <?php endif; ?>
                            </div>
                            <h4><?= htmlspecialchars($res['student_name']) ?></h4>
                            <p><?= htmlspecialchars($res['class_name']) ?></p>
                            <span class="score"><?= htmlspecialchars($res['percentage']) ?></span>
                            
                            <div class="card-actions">
                                <a href="results.php?toggle=<?= $res['id'] ?>" class="btn-toggle <?= $res['is_visible'] ? 'active' : '' ?>" title="Toggle Visibility">
                                    <i class="fa-solid <?= $res['is_visible'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                </a>
                                <button class="btn-edit" onclick='openEditModal(<?= json_encode($res) ?>)' title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="results.php?delete=<?= $res['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this result?')" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($results)): ?>
                        <div class="empty-results">
                            <i class="fa-solid fa-trophy"></i>
                            <h3>No Results Added Yet</h3>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <i class="fa-solid fa-xmark modal-close" onclick="closeEditModal()"></i>
            <h3 class="modal-title"><i class="fa-solid fa-pen"></i> Edit Result</h3>
            <form action="results.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group mb-15">
                    <label>Student Name</label>
                    <input type="text" name="student_name" id="edit_name" required>
                </div>
                <div class="form-group mb-15">
                    <label>Class</label>
                    <input type="text" name="class_name" id="edit_class" required>
                </div>
                <div class="form-group mb-15">
                    <label>Percentage / Score</label>
                    <input type="text" name="percentage" id="edit_score" required>
                </div>
                <div class="form-group mb-15">
                    <label>Update Photo (Leave blank to keep current)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group checkbox-group mb-20">
                    <input type="checkbox" name="is_visible" id="edit_visible" class="checkbox-input">
                    <label for="edit_visible" class="m-0">Show on Website</label>
                </div>
                
                <button type="submit" class="btn-primary btn-submit">
                    Update Result
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.mobile-overlay').classList.toggle('active');
        }

        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.student_name;
            document.getElementById('edit_class').value = data.class_name;
            document.getElementById('edit_score').value = data.percentage;
            document.getElementById('edit_visible').checked = data.is_visible == 1;
            
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
    </script>
</body>
</html>



