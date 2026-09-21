<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Database\Migrator;

try {
    $db = Database::getConnection();
    $migrator = new Migrator($db);
    $migrator->migrate();
    echo "✓ Database migration completed successfully!\n";
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}