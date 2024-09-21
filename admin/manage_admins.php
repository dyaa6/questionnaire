<?php
// /admin/manage_admins.php

define('ALLOW_ACCESS', true);
include '../includes/header.php';
include '../includes/db_connect.php';
include '../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

// Fetch all admins
$stmt = $pdo->query('SELECT * FROM admins');
$admins = $stmt->fetchAll();

// Handle adding a new admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Check if username or email exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, email) VALUES (?, ?, ?)');
        $stmt->execute([$username, $password_hash, $email]);
        $success_message = "New admin '$username' has been created.";
    } else {
        $error_message = "Username or email already exists.";
    }
}

// Handle deleting an admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_admin'])) {
    $admin_id = (int)$_POST['admin_id'];

    // Prevent self-deletion
    if ($admin_id != $_SESSION['admin_id']) {
        $stmt = $pdo->prepare('DELETE FROM admins WHERE admin_id = ?');
        $stmt->execute([$admin_id]);
        $success_message = "Admin has been deleted.";
    } else {
        $error_message = "You cannot delete your own account while logged in.";
    }
}
?>

<div class="mb-4">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
</div>

<h2>Manage Admins</h2>

<h3>Current Admins</h3>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($admins as $admin): ?>
        <tr>
            <td><?php echo htmlspecialchars($admin['username']); ?></td>
            <td><?php echo htmlspecialchars($admin['email']); ?></td>
            <td>
                <?php if ($admin['admin_id'] != $_SESSION['admin_id']): ?>
                    <form action="manage_admins.php" method="post" style="display: inline;">
                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                        <button type="submit" name="delete_admin" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this admin?');">Delete</button>
                    </form>
                <?php else: ?>
                    <span class="text-muted">(You)</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Add New Admin</h3>

<form action="manage_admins.php" method="post">
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" name="add_admin" class="btn btn-primary">
    <i class="fas fa-user-plus"></i> Add Admin
</button>
</form>

<a href="index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>

<?php
include '../includes/footer.php';
?>