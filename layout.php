<?php
// includes/layout.php – header() and footer() template helpers

function pageHeader(string $title, string $active = ''): void {
    $flash = getFlash();
    $nav = [
        'index'       => ['href' => '../index.php',            'label' => '🏠 Dashboard'],
        'roles'       => ['href' => '../pages/roles.php',      'label' => '🎭 Roles'],
        'users'       => ['href' => '../pages/users.php',      'label' => '👤 Users'],
        'equipment'   => ['href' => '../pages/equipment.php',  'label' => '🚜 Equipment'],
        'sites'       => ['href' => '../pages/sites.php',      'label' => '📍 Sites'],
        'assignments' => ['href' => '../pages/assignments.php','label' => '📋 Assignments'],
        'checkouts'   => ['href' => '../pages/checkouts.php',  'label' => '🔑 Key Checkouts'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> – Equipment Tracker</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="sidebar">
    <div class="sidebar-brand">⚙️ EquipTrack</div>
    <ul>
    <?php foreach ($nav as $key => $item): ?>
        <li>
            <a href="<?= $item['href'] ?>"
               class="<?= ($active === $key) ? 'active' : '' ?>">
               <?= $item['label'] ?>
            </a>
        </li>
    <?php endforeach; ?>
    </ul>
</nav>
<main class="content">
    <h1><?= htmlspecialchars($title) ?></h1>
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>
    <?php
}

function pageFooter(): void {
    ?>
</main>
</body>
</html>
    <?php
}
