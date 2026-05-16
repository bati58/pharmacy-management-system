<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class BackupController
{
    public function __construct()
    {
        AuthMiddleware::check();
        AuthMiddleware::requireRole(['manager']);
    }

    public function download()
    {
        global $pdo;
        $dbName = DB_NAME;
        $date = date('Y-m-d_H-i-s');
        $filename = "pharmaflow_backup_{$date}.sql";

        // Set headers for file download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $tables = [];
        $result = $pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $output = "-- PharmaFlow System Backup\n";
        $output .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Create table structure
            $stmt = $pdo->query("SHOW CREATE TABLE $table");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $output .= "\n\n" . $row[1] . ";\n\n";

            // Export data
            $stmt = $pdo->query("SELECT * FROM $table");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $output .= "INSERT INTO $table (" . implode(", ", array_keys($row)) . ") VALUES (";
                $values = [];
                foreach ($row as $val) {
                    if ($val === null) $values[] = "NULL";
                    else $values[] = $pdo->quote($val);
                }
                $output .= implode(", ", $values) . ");\n";
            }
        }

        $output .= "\n\nSET FOREIGN_KEY_CHECKS=1;";
        echo $output;
        exit;
    }
}
