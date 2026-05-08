<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/layout.php';

// ── CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['role_name'] ?? '');
    if ($name === '') {
        setFlash('Role name is required.', 'error');
    } else {
        dbExec('INSERT INTO roles (role_name) VALUES (?)', 's', $name);
        setFlash("Role '$name' created.");
    }
    redirect('roles.php');
}

// ── UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id   = (int)($_POST['role_id']   ?? 0);
    $name = trim($_POST['role_name']  ?? '');
    if ($id && $name) {
        dbExec('UPDATE roles SET role_name=? WHERE role_id=?', 'si', $name, $id);
        setFlash("Role updated.");
    }
    redirect('roles.php');
}

// ── DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    dbExec('DELETE FROM roles WHERE role_id=?', 'i', $id);
    setFlash('Role deleted.');
    redirect('roles.php');
}

// ── FETCH EDIT ROW
$editRow = null;
if (isset($_GET['edit'])) {
    $rows = dbFetchAll('SELECT * FROM roles WHERE role_id=?', 'i', (int)$_GET['edit']);
    $editRow = $rows[0] ?? null;
}

$roles = dbFetchAll('SELECT r.*, COUNT(u.user_id) AS user_count
                     FROM   roles r
                     LEFT JOIN users u ON u.role_id = r.role_id
                     GROUP  BY r.role_id
                     ORDER  BY r.role_id');

pageHeader('Roles', 'roles');
?>

<!-- Add / Edit Form -->
<div class="card">
    <h2 style="font-size:1rem;margin-bottom:1rem;color:#475569;">
        <?= $editRow ? 'Edit Role' : 'Add New Role' ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="action"  value="<?= $editRow ? 'update' : 'create' ?>">
        <?php if ($editRow): ?>
        <input type="hidden" name="role_id" value="<?= $editRow['role_id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="role_name">Role Name</label>
                <input type="text" id="role_name" name="role_name" required maxlength="50"
                       value="<?= htmlspecialchars($editRow['role_name'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary"><?= $editRow ? 'Update' : 'Save' ?></button>
            <?php if ($editRow): ?>
            <a href="roles.php" class="btn">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Role Name</th>
                <th>Users</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($roles as $r): ?>
            <tr>
                <td><?= $r['role_id'] ?></td>
                <td><?= htmlspecialchars($r['role_name']) ?></td>
                <td><?= $r['user_count'] ?></td>
                <td>
                    <a href="?edit=<?= $r['role_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <?php if ($r['user_count'] == 0): ?>
                    <a href="?delete=<?= $r['role_id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this role?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php pageFooter(); ?>
