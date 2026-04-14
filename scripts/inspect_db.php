<?php
// Quick DB inspector for sqlite used by the app
$db = __DIR__ . '/../database/database.sqlite';
if (!file_exists($db)) { echo "DB not found: $db\n"; exit(1); }
try {
    $pdo = new PDO('sqlite:' . $db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to: $db\n\n";

    // list tables
    echo "Tables:\n";
    $res = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $res->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) echo " - $t\n";
    echo "\n";

    // check users columns
    echo "users table columns:\n";
    $cols = $pdo->query("PRAGMA table_info('users')")->fetchAll(PDO::FETCH_ASSOC);
    if (!$cols) { echo " (no users table)\n\n"; }
    else {
        foreach ($cols as $c) {
            echo sprintf(" %s %s %s %s\n", $c['cid'], $c['name'], $c['type'], $c['notnull'] ? 'NOTNULL' : 'NULL');
        }
        echo "\n";
    }

    // show last 20 migrations
    echo "migrations (last 20):\n";
    $stmt = $pdo->query("SELECT id, migration, batch FROM migrations ORDER BY id DESC LIMIT 20");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) echo " (migrations table empty)\n";
    else {
        foreach ($rows as $r) echo " - {$r['migration']} (batch {$r['batch']})\n";
    }
    echo "\n";

    // show last 10 verification_tokens rows
    echo "verification_tokens (last 10):\n";
    $stmt = $pdo->query("SELECT id, user_id, token, method, created_at FROM verification_tokens ORDER BY id DESC LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) echo " (none)\n";
    else {
        foreach ($rows as $r) echo " - id={$r['id']} user_id={$r['user_id']} token={$r['token']} method={$r['method']} created_at={$r['created_at']}\n";
    }

    // show last 10 users
    echo "\nusers (last 10):\n";
    $stmt = $pdo->query("SELECT id, name, email, phone, created_at FROM users ORDER BY id DESC LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) echo " (none)\n";
    else {
        foreach ($rows as $r) echo " - id={$r['id']} name={$r['name']} email={$r['email']} phone={$r['phone']} created_at={$r['created_at']}\n";
    }

} catch (PDOException $e) {
    echo "PDO error: " . $e->getMessage() . "\n";
    exit(1);
}
