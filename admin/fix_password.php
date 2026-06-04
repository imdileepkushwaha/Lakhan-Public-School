<?php
/**
 * Emergency admin password reset on LIVE server.
 * Usage: fix_password.php?key=lps_setup_2026
 * DELETE this file immediately after use.
 */
$RESET_KEY = 'lps_setup_2026'; // Same as server_setup.php — change & delete after use

if (($_GET['key'] ?? '') !== $RESET_KEY) {
    http_response_code(403);
    die('Forbidden. Use: admin/fix_password.php?key=YOUR_SECRET');
}

require_once '../config/db.php';

$message = '';
$msg_type = '';
$diagnostics = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? 'admin');
    $new_password = $_POST['new_password'] ?? '';

    if ($username === '' || $new_password === '') {
        $message = 'Username and password are required.';
        $msg_type = 'error';
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('SELECT id, password FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $update = $pdo->prepare('UPDATE admin_users SET password = ? WHERE username = ?');
            $update->execute([$hash, $username]);
            $message = 'Password updated for user "' . htmlspecialchars($username) . '". Try login now.';
            $msg_type = 'success';
        } else {
            $insert = $pdo->prepare('INSERT INTO admin_users (username, password) VALUES (?, ?)');
            $insert->execute([$username, $hash]);
            $message = 'Admin user "' . htmlspecialchars($username) . '" created. Try login now.';
            $msg_type = 'success';
        }
    }
}

try {
    $stmt = $pdo->query('SELECT id, username, password, LENGTH(password) as plen FROM admin_users');
    $users = $stmt->fetchAll();
    foreach ($users as $u) {
        $looks_hashed = str_starts_with($u['password'], '$2y$') || str_starts_with($u['password'], '$2a$');
        $test_ok = $looks_hashed ? password_verify('Admin@6170', $u['password']) : false;
        $diagnostics[] = [
            'username' => $u['username'],
            'id' => $u['id'],
            'hash_ok' => $looks_hashed,
            'len' => $u['plen'],
            'matches_Admin@6170' => $test_ok,
            'preview' => substr($u['password'], 0, 20) . '...',
        ];
    }
} catch (Throwable $e) {
    $diagnostics[] = ['error' => $e->getMessage()];
}

$dbname_display = $GLOBALS['dbname'] ?? 'unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Password</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        body.admin-login-page { padding: 30px 15px; }
        .fix-box { max-width: 520px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,.2); }
        .fix-box h2 { margin: 0 0 10px; color: #00122e; }
        .hint { color: #64748b; font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5; }
        .diag { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 0.85rem; }
        .diag table { width: 100%; border-collapse: collapse; }
        .diag td, .diag th { padding: 6px 8px; text-align: left; border-bottom: 1px solid #eee; }
        .ok { color: #198754; } .bad { color: #dc3545; }
        .form-group-full { margin-bottom: 16px; }
        .form-group-full label { display: block; margin-bottom: 6px; font-weight: 600; }
        .form-group-full input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
        .alert-ok { background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .alert-err { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body class="admin-login-page">
    <div class="fix-box">
        <h2>Fix Admin Login</h2>
        <p class="hint">Database: <code><?= htmlspecialchars($dbname_display) ?></code><br>
        Login only works if password in DB is a <strong>bcrypt hash</strong>, not plain text.</p>

        <?php if ($message): ?>
            <div class="<?= $msg_type === 'success' ? 'alert-ok' : 'alert-err' ?>"><?= $message ?></div>
        <?php endif; ?>

        <?php if (!empty($diagnostics) && !isset($diagnostics[0]['error'])): ?>
        <div class="diag">
            <strong>Current admin users in this database:</strong>
            <table>
                <tr><th>User</th><th>Hash OK?</th><th>Matches Admin@6170?</th></tr>
                <?php foreach ($diagnostics as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['username']) ?></td>
                    <td class="<?= $d['hash_ok'] ? 'ok' : 'bad' ?>"><?= $d['hash_ok'] ? 'Yes' : 'No (plain text?)' ?></td>
                    <td class="<?= $d['matches_Admin@6170'] ? 'ok' : 'bad' ?>"><?= $d['matches_Admin@6170'] ? 'Yes' : 'No' ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="key" value="<?= htmlspecialchars($_GET['key'] ?? '') ?>">
            <div class="form-group-full">
                <label>Username</label>
                <input type="text" name="username" value="admin" required>
            </div>
            <div class="form-group-full">
                <label>New Password</label>
                <input type="text" name="new_password" value="Admin@6170" required autocomplete="off">
            </div>
            <button type="submit" class="settings-btn settings-btn-inline" style="width:100%;border:none;cursor:pointer;">
                Save Password (bcrypt hash)
            </button>
        </form>

        <p class="hint" style="margin-top:20px;">
            <strong>Login tips:</strong> Username = <code>admin</code> (lowercase). Password exact: <code>Admin@6170</code> (case sensitive).<br>
            After login works, <strong>delete fix_password.php</strong>.
        </p>
        <a href="login.php">Go to Login</a>
    </div>
</body>
</html>
