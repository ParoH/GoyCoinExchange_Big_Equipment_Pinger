<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/layout.php';

// ── CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $eq       = (int)($_POST['equipment_id']   ?? 0);
    $op       = (int)($_POST['operator_id']    ?? 0);
    $cout     = $_POST['checkout_time']        ?? date('Y-m-d H:i:s');
    $cin      = ($_POST['checkin_time']  ?? '') ?: null;
    $pre      = trim($_POST['pre_inspect_log']  ?? '') ?: null;
    $post     = trim($_POST['post_inspect_log'] ?? '') ?: null;
    if (!$eq || !$op) {
        setFlash('Equipment and operator are required.', 'error');
    } else {
        dbExec(
            'INSERT INTO key_checkouts
             (equipment_id,operator_id,checkout_time,checkin_time,pre_inspect_log,post_inspect_log)
             VALUES (?,?,?,?,?,?)',
            'iissss', $eq, $op, $cout, $cin, $pre, $post
        );
        setFlash('Checkout recorded.');
    }
    redirect('checkouts.php');
}

// ── UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id   = (int)$_POST['checkout_id'];
    $eq   = (int)$_POST['equipment_id'];
    $op   = (int)$_POST['operator_id'];
    $cout = $_POST['checkout_time'];
    $cin  = ($_POST['checkin_time']  ?? '') ?: null;
    $pre  = trim($_POST['pre_inspect_log']  ?? '') ?: null;
    $post = trim($_POST['post_inspect_log'] ?? '') ?: null;
    dbExec(
        'UPDATE key_checkouts
         SET equipment_id=?,operator_id=?,checkout_time=?,checkin_time=?,
             pre_inspect_log=?,post_inspect_log=?
         WHERE checkout_id=?',
        'iissssi', $eq, $op, $cout, $cin, $pre, $post, $id
    );
    setFlash('Checkout updated.');
    redirect('checkouts.php');
}

// ── CHECKIN shortcut
if (isset($_GET['checkin'])) {
    $id = (int)$_GET['checkin'];
    dbExec('UPDATE key_checkouts SET checkin_time=NOW() WHERE checkout_id=? AND checkin_time IS NULL',
           'i', $id);
    setFlash('Equipment checked in.');
    redirect('checkouts.php');
}

// ── DELETE
if (isset($_GET['delete'])) {
    dbExec('DELETE FROM key_checkouts WHERE checkout_id=?', 'i', (int)$_GET['delete']);
    setFlash('Record deleted.');
    redirect('checkouts.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $rows = dbFetchAll('SELECT * FROM key_checkouts WHERE checkout_id=?', 'i', (int)$_GET['edit']);
    $editRow = $rows[0] ?? null;
}

$checkouts = dbFetchAll(
    "SELECT kc.*, e.asset_tag, CONCAT(u.first_name,' ',u.last_name) AS operator_name
     FROM   key_checkouts kc
     JOIN   equipment e ON e.equipment_id = kc.equipment_id
     JOIN   users     u ON u.user_id      = kc.operator_id
     ORDER  BY kc.checkout_id DESC"
);
$equipmentList = dbFetchAll("SELECT equipment_id, asset_tag FROM equipment ORDER BY asset_tag");
$userList      = dbFetchAll("SELECT user_id, CONCAT(first_name,' ',last_name) AS full_name FROM users ORDER BY first_name");

pageHeader('Key Checkouts', 'checkouts');
?>

<div class="card">
    <h2 style="font-size:1rem;margin-bottom:1rem;color:#475569;">
        <?= $editRow ? 'Edit Checkout' : 'New Checkout' ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="action" value="<?= $editRow ? 'update' : 'create' ?>">
        <?php if ($editRow): ?>
        <input type="hidden" name="checkout_id" value="<?= $editRow['checkout_id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Equipment</label>
                <select name="equipment_id" required>
                    <option value="">– select –</option>
                    <?php foreach ($equipmentList as $e): ?>
                    <option value="<?= $e['equipment_id'] ?>"
                        <?= (($editRow['equipment_id'] ?? '') == $e['equipment_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['asset_tag']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Operator</label>
                <select name="operator_id" required>
                    <option value="">– select –</option>
                    <?php foreach ($userList as $u): ?>
                    <option value="<?= $u['user_id'] ?>"
                        <?= (($editRow['operator_id'] ?? '') == $u['user_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['full_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Checkout Time</label>
                <input type="datetime-local" name="checkout_time"
                       value="<?= htmlspecialchars(
                           isset($editRow['checkout_time'])
                               ? str_replace(' ', 'T', $editRow['checkout_time'])
                               : date('Y-m-d\TH:i')
                       ) ?>">
            </div>
            <div class="form-group">
                <label>Check-in Time <small>(leave blank if still out)</small></label>
                <input type="datetime-local" name="checkin_time"
                       value="<?= htmlspecialchars(
                           isset($editRow['checkin_time'])
                               ? str_replace(' ', 'T', $editRow['checkin_time'])
                               : ''
                       ) ?>">
            </div>
            <div class="form-group" style="grid-column: span 2">
                <label>Pre-Inspection Log</label>
                <textarea name="pre_inspect_log" rows="3"><?= htmlspecialchars($editRow['pre_inspect_log'] ?? '') ?></textarea>
            </div>
            <div class="form-group" style="grid-column: span 2">
                <label>Post-Inspection Log</label>
                <textarea name="post_inspect_log" rows="3"><?= htmlspecialchars($editRow['post_inspect_log'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary"><?= $editRow ? 'Update' : 'Save' ?></button>
            <?php if ($editRow): ?><a href="checkouts.php" class="btn">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr><th>ID</th><th>Asset Tag</th><th>Operator</th><th>Checked Out</th>
                <th>Checked In</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($checkouts as $c): ?>
        <tr>
            <td><?= $c['checkout_id'] ?></td>
            <td><?= htmlspecialchars($c['asset_tag']) ?></td>
            <td><?= htmlspecialchars($c['operator_name']) ?></td>
            <td><?= $c['checkout_time'] ?></td>
            <td>
                <?php if ($c['checkin_time']): ?>
                    <?= $c['checkin_time'] ?>
                <?php else: ?>
                    <a href="?checkin=<?= $c['checkout_id'] ?>" class="btn btn-sm btn-success"
                       onclick="return confirm('Mark as checked in now?')">Check In</a>
                <?php endif; ?>
            </td>
            <td>
                <a href="?edit=<?= $c['checkout_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="?delete=<?= $c['checkout_id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php pageFooter(); ?>
