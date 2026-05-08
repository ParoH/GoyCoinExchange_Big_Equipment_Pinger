<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/layout.php';

$statuses = ['available', 'in_use', 'maintenance', 'retired'];

// ── CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $tag      = trim($_POST['asset_tag']    ?? '');
    $cat      = trim($_POST['category']     ?? '');
    $status   = $_POST['status']            ?? 'available';
    $lat      = $_POST['current_lat']       !== '' ? (float)$_POST['current_lat']  : null;
    $lng      = $_POST['current_long']      !== '' ? (float)$_POST['current_long'] : null;
    $ping     = $_POST['last_ping_time']    !== '' ? $_POST['last_ping_time']       : null;

    if (!$tag || !$cat) {
        setFlash('Asset tag and category are required.', 'error');
    } else {
        dbExec(
            'INSERT INTO equipment (asset_tag,category,status,current_lat,current_long,last_ping_time)
             VALUES (?,?,?,?,?,?)',
            'sssdd' . ($ping ? 's' : 's'),
            $tag, $cat, $status, $lat, $lng, $ping
        );
        setFlash('Equipment added.');
    }
    redirect('equipment.php');
}

// ── UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id     = (int)$_POST['equipment_id'];
    $tag    = trim($_POST['asset_tag']    ?? '');
    $cat    = trim($_POST['category']     ?? '');
    $status = $_POST['status']            ?? 'available';
    $lat    = $_POST['current_lat']  !== '' ? (float)$_POST['current_lat']  : null;
    $lng    = $_POST['current_long'] !== '' ? (float)$_POST['current_long'] : null;
    $ping   = $_POST['last_ping_time'] !== '' ? $_POST['last_ping_time']    : null;
    dbExec(
        'UPDATE equipment SET asset_tag=?,category=?,status=?,current_lat=?,current_long=?,last_ping_time=?
         WHERE equipment_id=?',
        'sssdds' . 'i',
        $tag, $cat, $status, $lat, $lng, $ping, $id
    );
    setFlash('Equipment updated.');
    redirect('equipment.php');
}

// ── DELETE
if (isset($_GET['delete'])) {
    dbExec('DELETE FROM equipment WHERE equipment_id=?', 'i', (int)$_GET['delete']);
    setFlash('Equipment deleted.');
    redirect('equipment.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $rows = dbFetchAll('SELECT * FROM equipment WHERE equipment_id=?', 'i', (int)$_GET['edit']);
    $editRow = $rows[0] ?? null;
}

$equipment = dbFetchAll('SELECT * FROM equipment ORDER BY equipment_id');

pageHeader('Equipment', 'equipment');
?>

<div class="card">
    <h2 style="font-size:1rem;margin-bottom:1rem;color:#475569;">
        <?= $editRow ? 'Edit Equipment' : 'Add Equipment' ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="action" value="<?= $editRow ? 'update' : 'create' ?>">
        <?php if ($editRow): ?>
        <input type="hidden" name="equipment_id" value="<?= $editRow['equipment_id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Asset Tag</label>
                <input type="text" name="asset_tag" required maxlength="50"
                       value="<?= htmlspecialchars($editRow['asset_tag'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" required maxlength="50"
                       value="<?= htmlspecialchars($editRow['category'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>"
                        <?= (($editRow['status'] ?? 'available') === $s) ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace('_', ' ', $s)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Latitude</label>
                <input type="number" name="current_lat" step="0.0000001"
                       value="<?= htmlspecialchars($editRow['current_lat'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Longitude</label>
                <input type="number" name="current_long" step="0.0000001"
                       value="<?= htmlspecialchars($editRow['current_long'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Last Ping Time</label>
                <input type="datetime-local" name="last_ping_time"
                       value="<?= htmlspecialchars(
                           isset($editRow['last_ping_time'])
                               ? str_replace(' ', 'T', $editRow['last_ping_time'])
                               : ''
                       ) ?>">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary"><?= $editRow ? 'Update' : 'Save' ?></button>
            <?php if ($editRow): ?><a href="equipment.php" class="btn">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr><th>ID</th><th>Asset Tag</th><th>Category</th><th>Status</th>
                <th>Lat</th><th>Long</th><th>Last Ping</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($equipment as $e): ?>
        <tr>
            <td><?= $e['equipment_id'] ?></td>
            <td><?= htmlspecialchars($e['asset_tag']) ?></td>
            <td><?= htmlspecialchars($e['category']) ?></td>
            <td><span class="badge badge-<?= $e['status'] ?>"><?= str_replace('_',' ',$e['status']) ?></span></td>
            <td><?= $e['current_lat']  ?? '–' ?></td>
            <td><?= $e['current_long'] ?? '–' ?></td>
            <td><?= $e['last_ping_time'] ?? '–' ?></td>
            <td>
                <a href="?edit=<?= $e['equipment_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="?delete=<?= $e['equipment_id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php pageFooter(); ?>
