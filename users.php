<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/layout.php';

// ── CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $role  = (int)($_POST['role_id']    ?? 0);
    $first = trim($_POST['first_name']  ?? '');
    $last  = trim($_POST['last_name']   ?? '');
    $pin   = trim($_POST['pin_code']    ?? '');
    if (!$role || !$first || !$last || !preg_match('/^\d{4,6}$/', $pin)) {
        setFlash('All fields are required and PIN must be 4-6 digits.', 'error');
    } else {
        dbExec('INSERT INTO users (role_id,first_name,last_name,pin_code) VALUES (?,?,?,?)',
               'isss', $role, $first, $last, $pin);
        setFlash('User created.');
    }
    redirect('users.php');
}

// ── UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id    = (int)($_POST['user_id']    ?? 0);
    $role  = (int)($_POST['role_id']    ?? 0);
    $first = trim($_POST['first_name']  ?? '');
    $last  = trim($_POST['last_name']   ?? '');
    $pin   = trim($_POST['pin_code']    ?? '');
    if ($id && $role && $first && $last && preg_match('/^\d{4,6}$/', $pin)) {
        dbExec('UPDATE users SET role_id=?,first_name=?,last_name=?,pin_code=? WHERE user_id=?',
               'isssi', $role, $first, $last, $pin, $id);
        setFlash('User updated.');
    }
    redirect('users.php');
}

// ── DELETE
if (isset($_GET['delete'])) {
    dbExec('DELETE FROM users WHERE user_id=?', 'i', (int)$_GET['delete']);
    setFlash('User deleted.');
    redirect('users.php');
}

// ── EDIT FETCH
$editRow = null;
if (isset($_GET['edit'])) {
    $rows = dbFetchAll('SELECT * FROM users WHERE user_id=?', 'i', (int)$_GET['edit']);
    $editRow = $rows[0] ?? null;
}

$users = dbFetchAll(
    "SELECT u.*, r.role_name FROM users u JOIN roles r ON r.role_id=u.role_id ORDER BY u.user_id"
);
$roles = dbFetchAll('SELECT * FROM roles ORDER BY role_name');

pageHeader('Users', 'users');
?>

<div class="card">
    <h2 style="font-size:1rem;margin-bottom:1rem;color:#475569;">
        <?= $editRow ? 'Edit User' : 'Add New User' ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="action"  value="<?= $editRow ? 'update' : 'create' ?>">
        <?php if ($editRow): ?>
        <input type="hidden" name="user_id" value="<?= $editRow['user_id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" required maxlength="50"
                       value="<?= htmlspecialchars($editRow['first_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" required maxlength="50"
                       value="<?= htmlspecialchars($editRow['last_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role_id" required>
                    <option value="">– select –</option>
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['role_id'] ?>"
                        <?= (($editRow['role_id'] ?? '') == $r['role_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['role_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>PIN Code (4-6 digits)</label>
                <input type="text" name="pin_code" required maxlength="6" pattern="\d{4,6}"
                       value="<?= htmlspecialchars($editRow['pin_code'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary"><?= $editRow ? 'Update' : 'Save' ?></button>
            <?php if ($editRow): ?><a href="users.php" class="btn">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Role</th><th>PIN</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['user_id'] ?></td>
            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
            <td><?= htmlspecialchars($u['role_name']) ?></td>
            <td><?= str_repeat('●', strlen($u['pin_code'])) ?></td>
            <td>
                <a href="?edit=<?= $u['user_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="?delete=<?= $u['user_id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this user?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php pageFooter(); ?>
