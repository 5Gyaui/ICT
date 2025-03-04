<?php
require_once 'database/Database.php';

$db = new Database();
backup_tables($db, 'ict_inv');

/* Function to Backup Database */
function backup_tables($db, $name, $tables = '*')
{
    $return = ""; // Initialize return variable

    // Get all tables
    if ($tables == '*') {
        $tables = [];
        $result = $db->getRows("SHOW TABLES");
        foreach ($result as $row) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }

    // Loop through each table
    foreach ($tables as $table) {
        $result = $db->getRows("SELECT * FROM $table");
        $num_fields = count($result) > 0 ? count($result[0]) : 0;

        $row2 = $db->getRow("SHOW CREATE TABLE $table");
        $return .= "\n\n" . $row2['Create Table'] . ";\n\n";

        // Insert table data
        foreach ($result as $row) {
            $return .= "INSERT INTO $table VALUES(";
            $values = [];
            foreach ($row as $value) {
                $values[] = isset($value) ? "'" . addslashes($value) . "'" : "NULL";
            }
            $return .= implode(',', $values);
            $return .= ");\n";
        }
        $return .= "\n\n\n";
    }

    // Save the SQL backup to a file
    $backup_file = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
    file_put_contents($backup_file, $return);

    echo "Backup saved as: " . $backup_file;
}
?>
