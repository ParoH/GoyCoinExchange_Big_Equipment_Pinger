<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/layout.php';

// ── CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $eq   = (int)($_POST['equipment_id']  ?? 0);
    $site = (int)($_POST['site_id']       ?? 0);
    $disp = (int)($_POST['dispatched_by'] ?? 0);
    $sd   = $_POST['start_date']          ?? '';
    $ed   = ($_POST['end_date'] ?? '') ?: null;
    if (!$eq || !$site || !$disp || !$sd) {
        setFlash('All required fields must be filled.', 'error');
    } else {
        dbExec(
            'INSERT INTO assignments (equipment_id,site_id,dispatched_by,start_date,end_date)
             VALUES (?,?,?,?,?)',
            'iiiss', $eq, $site, $disp, $sd, $ed
        );
        setFlash('Assignment created.');
    }
    redirect('assignments.php');
}

// ── UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id   = (int)$_POST['assignment_id'];
    $eq   = (int)$_POST['equipment_id'];
    $site = (int)$_POST['site_id'];
    $disp = (int)$_POST['dispatched_by'];
    $sd   = $_POST['start_date'];
    $ed   = ($_POST['end_date'] ?? '') ?: null;
    dbExec(
        'UPDATE assignments SET equipment_id=?,site_id=?,dispatched_by=?,start_date=?,end_date=?
         WHERE assignment_id=?',
        'iiissi', $eq, $site, $disp, $sd, $ed, $id
    );
    setFlash('Assignment updated.');
    redirect('assignments.php');
}

// ── DELETE
if (isset($_GET['delete'])) {
    dbExec('DELETE FROM assignments WHERE assignment_id=?', 'i', (int)$_GET['delete']);
    setFlash('Assignment deleted.');
    redirect('assignments.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $rows = dbFetchAll('SELECT * FROM assignments WHERE assignment_id=?', 'i', (int)$_GET['edit']);
    $editRow = $rows[0] ?? null;
}

$assignments = dbFetchAll(
    "SELECT a.*, e.asset_tag, s.site_name,
            CONCAT(u.first_name,' ',u.last_name) AS dispatcher_name
     FROM   assignments a
     JOIN   equipment e ON e.equipment_id = a.equipment_id
     JOIN   sites     s ON s.site_id      = a.site_id
     JOIN   users     u ON u.user_id      = a.dispatched_by
     ORDER  BY a.assignment_id DESC"
);
$equipmentList = dbFetchAll("SELECT equipment_id, asset_tag FROM equipment ORDER BY asset_tag");
$siteList      = dbFetchAll("SELECT site_id, site_name FROM sites ORDER BY site_name");
$userList      = dbFetchAll("SELECT user_id, CONCAT(first_name,' ',last_name) AS full_name FROM users ORDER BY first_name");

pageHeader('Assignments', 'assignments');
?>

<div class="card">
    <h2 style="font-size:1rem;margin-bottom:1rem;color:#475569;">
        <?= $editRow ? 'Edit Assignment' : 'New Assignment' ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="action" value="<?= $editRow ? 'update' : 'create' ?>">
        <?php if ($editRow): ?>
        <input type="hidden" name="assignment_id" value="<?= $editRow['assignment_id'] ?>">
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
                <label>Site</label>
                <select name="site_id" required>
                    <option value="">– select –</option>
                    <?php foreach ($siteList as $s): ?>
                    <option value="<?= $s['site_id'] ?>"
                        <?= (($editRow['site_id'] ?? '') == $s['site_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['site_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Dispatched By</label>
                <select name="dispatched_by" required>
                    <option value="">– select –</option>
                    <?php foreach ($userList as $u): ?>
                    <option value="<?= $u['user_id'] ?>"
                        <?= (($editRow['dispatched_by'] ?? '') == $u['user_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['full_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" required
                       value="<?= htmlspecialchars($editRow['start_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>End Date <small>(optional)</small></label>
                <input type="date" name="end_date"
                       value="<?= htmlspecialchars($editRow['end_date'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary"><?= $editRow ? 'Update' : 'Save' ?></button>
            <?php if ($editRow): ?><a href="assignments.php" class="btn">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr><th>ID</th><th>Equipment</th><th>Site</th><th>Dispatched By</th>
                <th>Start</th><th>End</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($assignments as $a): ?>
        <tr>
            <td><?= $a['assignment_id'] ?></td>
            <td><?= htmlspecialchars($a['asset_tag']) ?></td>
            <td><?= htmlspecialchars($a['site_name']) ?></td>
            <td><?= htmlspecialchars($a['dispatcher_name']) ?></td>
            <td><?= $a['start_date'] ?></td>
            <td><?= $a['end_date'] ?? '<em style="color:#94a3b8">Ongoing</em>' ?></td>
            <td>
                <a href="?edit=<?= $a['assignment_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="?delete=<?= $a['assignment_id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php pageFooter(); ?>
