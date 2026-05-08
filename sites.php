<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/layout.php';

// ── CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name   = trim($_POST['site_name']       ?? '');
    $clat   = (float)($_POST['center_lat']   ?? 0);
    $clng   = (float)($_POST['center_long']  ?? 0);
    $radius = (float)($_POST['geofence_radius'] ?? 0);
    if (!$name || !$radius) {
        setFlash('Site name and geofence radius are required.', 'error');
    } else {
        dbExec('INSERT INTO sites (site_name,center_lat,center_long,geofence_radius) VALUES (?,?,?,?)',
               'sddd', $name, $clat, $clng, $radius);
        setFlash('Site created.');
    }
    redirect('sites.php');
}

// ── UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id     = (int)$_POST['site_id'];
    $name   = trim($_POST['site_name']          ?? '');
    $clat   = (float)$_POST['center_lat'];
    $clng   = (float)$_POST['center_long'];
    $radius = (float)$_POST['geofence_radius'];
    dbExec('UPDATE sites SET site_name=?,center_lat=?,center_long=?,geofence_radius=? WHERE site_id=?',
           'sdddi', $name, $clat, $clng, $radius, $id);
    setFlash('Site updated.');
    redirect('sites.php');
}

// ── DELETE
if (isset($_GET['delete'])) {
    dbExec('DELETE FROM sites WHERE site_id=?', 'i', (int)$_GET['delete']);
    setFlash('Site deleted.');
    redirect('sites.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $rows = dbFetchAll('SELECT * FROM sites WHERE site_id=?', 'i', (int)$_GET['edit']);
    $editRow = $rows[0] ?? null;
}

$sites = dbFetchAll('SELECT * FROM sites ORDER BY site_id');

pageHeader('Sites', 'sites');
?>

<div class="card">
    <h2 style="font-size:1rem;margin-bottom:1rem;color:#475569;">
        <?= $editRow ? 'Edit Site' : 'Add Site' ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="action" value="<?= $editRow ? 'update' : 'create' ?>">
        <?php if ($editRow): ?>
        <input type="hidden" name="site_id" value="<?= $editRow['site_id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group" style="grid-column: span 2">
                <label>Site Name</label>
                <input type="text" name="site_name" required maxlength="100"
                       value="<?= htmlspecialchars($editRow['site_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Center Latitude</label>
                <input type="number" name="center_lat" step="0.0000001" required
                       value="<?= htmlspecialchars($editRow['center_lat'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Center Longitude</label>
                <input type="number" name="center_long" step="0.0000001" required
                       value="<?= htmlspecialchars($editRow['center_long'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Geofence Radius (m)</label>
                <input type="number" name="geofence_radius" step="0.01" required min="1"
                       value="<?= htmlspecialchars($editRow['geofence_radius'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary"><?= $editRow ? 'Update' : 'Save' ?></button>
            <?php if ($editRow): ?><a href="sites.php" class="btn">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr><th>ID</th><th>Site Name</th><th>Center Lat</th><th>Center Long</th>
                <th>Radius (m)</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($sites as $s): ?>
        <tr>
            <td><?= $s['site_id'] ?></td>
            <td><?= htmlspecialchars($s['site_name']) ?></td>
            <td><?= $s['center_lat'] ?></td>
            <td><?= $s['center_long'] ?></td>
            <td><?= number_format($s['geofence_radius'], 2) ?></td>
            <td>
                <a href="?edit=<?= $s['site_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="?delete=<?= $s['site_id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this site?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php pageFooter(); ?>
