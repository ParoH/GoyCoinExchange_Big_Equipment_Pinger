<?php
// ============================================================
//  includes/config.php  –  Database connection (MySQLi)
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // default XAMPP user
define('DB_PASS', '');              // default XAMPP password (empty)
define('DB_NAME', 'equipment_tracker');

function getDB(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<p style="color:red;font-family:sans-serif;">
                 <b>Database connection failed:</b> ' .
                 htmlspecialchars($conn->connect_error) . '</p>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Helper: run a SELECT query and return all rows as assoc array
function dbFetchAll(string $sql, string $types = '', ...$params): array {
    $db   = getDB();
    $stmt = $db->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Helper: run INSERT / UPDATE / DELETE and return affected rows
function dbExec(string $sql, string $types = '', ...$params): int {
    $db   = getDB();
    $stmt = $db->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->affected_rows;
}

// Helper: return last insert ID
function dbLastId(): int {
    return (int) getDB()->insert_id;
}

// Redirect helper
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

// Flash message (stored in session)
session_start();
function setFlash(string $msg, string $type = 'success'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function getFlash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
