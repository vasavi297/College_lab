<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'college_lab';

// Connect to database
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Function to detect circular dependencies
function topologicalSort($tables, $dependencies) {
    $sorted = [];
    $visited = [];
    $temp = [];
    
    foreach ($tables as $table) {
        if (!isset($visited[$table])) {
            visit($table, $tables, $dependencies, $visited, $temp, $sorted);
        }
    }
    
    return $sorted;
}

function visit($table, $tables, $dependencies, &$visited, &$temp, &$sorted) {
    if (isset($temp[$table])) {
        die("Circular dependency detected involving table: $table");
    }
    
    if (!isset($visited[$table])) {
        $temp[$table] = true;
        
        if (isset($dependencies[$table])) {
            foreach ($dependencies[$table] as $dep) {
                visit($dep, $tables, $dependencies, $visited, $temp, $sorted);
            }
        }
        
        unset($temp[$table]);
        $visited[$table] = true;
        array_unshift($sorted, $table); // Add to beginning for correct order
    }
}

// Get all tables and their dependencies
$tables = [];
$dependencies = []; // table => [parent_tables]

$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $table = $row[0];
    $tables[] = $table;
    
    // Get foreign key dependencies for this table
    $fk_query = "SELECT 
                    REFERENCED_TABLE_NAME as parent_table
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = '$dbname' 
                  AND TABLE_NAME = '$table' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                  AND REFERENCED_TABLE_NAME != ''";
    
    $fk_result = $conn->query($fk_query);
    while ($fk_row = $fk_result->fetch_assoc()) {
        $parent = $fk_row['parent_table'];
        if (!isset($dependencies[$table])) {
            $dependencies[$table] = [];
        }
        if (!in_array($parent, $dependencies[$table])) {
            $dependencies[$table][] = $parent;
        }
    }
}

// Sort tables by dependencies (parent tables first)
$sortedTables = topologicalSort($tables, $dependencies);

echo "Table creation order:\n";
foreach ($sortedTables as $i => $table) {
    echo ($i + 1) . ". $table\n";
    if (isset($dependencies[$table]) && !empty($dependencies[$table])) {
        echo "   → Depends on: " . implode(', ', $dependencies[$table]) . "\n";
    }
}

// Start output with proper settings
$output = "/*\n";
$output .= "  Database Export: $dbname\n";
$output .= "  Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= "  Tables in correct foreign key order\n";
$output .= "*/\n\n";

$output .= "-- Disable foreign key checks during import\n";
$output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$output .= "SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;\n";
$output .= "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;\n";
$output .= "SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
$output .= "SET NAMES utf8mb4;\n";
$output .= "SET CHARACTER SET utf8mb4;\n\n";

// Create database
$output .= "DROP DATABASE IF EXISTS `$dbname`;\n";
$output .= "CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
$output .= "USE `$dbname`;\n\n";

// Step 1: Create all tables without foreign keys first
$output .= "-- Step 1: Create all tables without foreign keys\n";
foreach ($sortedTables as $table) {
    $create = $conn->query("SHOW CREATE TABLE `$table`");
    $createRow = $create->fetch_assoc();
    
    // Remove foreign key constraints temporarily
    $createSql = $createRow['Create Table'];
    
    // Remove FOREIGN KEY constraints (we'll add them later)
    $createSql = preg_replace('/,\s*CONSTRAINT `[^`]+` FOREIGN KEY \(`[^`]+`\) REFERENCES `[^`]+` \(`[^`]+`\).*?(?=,|$)/', '', $createSql);
    $createSql = preg_replace('/\s*FOREIGN KEY \(`[^`]+`\) REFERENCES `[^`]+` \(`[^`]+`\).*?(?=,|$)/', '', $createSql);
    
    $output .= "-- Table: $table\n";
    $output .= $createSql . ";\n\n";
}

// Step 2: Add foreign key constraints
$output .= "\n-- Step 2: Add foreign key constraints\n";
foreach ($sortedTables as $table) {
    // Get all foreign keys for this table
    $fk_query = "SELECT 
                    CONSTRAINT_NAME,
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME,
                    (SELECT UPDATE_RULE FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                     WHERE CONSTRAINT_SCHEMA = '$dbname' 
                     AND CONSTRAINT_NAME = kcu.CONSTRAINT_NAME) as UPDATE_RULE,
                    (SELECT DELETE_RULE FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                     WHERE CONSTRAINT_SCHEMA = '$dbname' 
                     AND CONSTRAINT_NAME = kcu.CONSTRAINT_NAME) as DELETE_RULE
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                WHERE kcu.TABLE_SCHEMA = '$dbname' 
                  AND kcu.TABLE_NAME = '$table' 
                  AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                  AND kcu.REFERENCED_TABLE_NAME != ''";
    
    $fk_result = $conn->query($fk_query);
    $hasForeignKeys = false;
    
    while ($fk_row = $fk_result->fetch_assoc()) {
        if (!$hasForeignKeys) {
            $output .= "-- Foreign keys for table: $table\n";
            $hasForeignKeys = true;
        }
        
        $constraintName = $fk_row['CONSTRAINT_NAME'];
        $column = $fk_row['COLUMN_NAME'];
        $refTable = $fk_row['REFERENCED_TABLE_NAME'];
        $refColumn = $fk_row['REFERENCED_COLUMN_NAME'];
        $updateRule = $fk_row['UPDATE_RULE'] ?: 'RESTRICT';
        $deleteRule = $fk_row['DELETE_RULE'] ?: 'RESTRICT';
        
        $output .= "ALTER TABLE `$table` ADD CONSTRAINT `$constraintName` ";
        $output .= "FOREIGN KEY (`$column`) REFERENCES `$refTable` (`$refColumn`) ";
        $output .= "ON DELETE $deleteRule ON UPDATE $updateRule;\n";
    }
    
    if ($hasForeignKeys) {
        $output .= "\n";
    }
}

// Step 3: Insert data (in reverse order - child tables first is sometimes better)
$output .= "\n-- Step 3: Insert data (starting with tables that have no foreign keys)\n";

// Create a list of tables with no foreign key dependencies
$independentTables = [];
foreach ($sortedTables as $table) {
    if (!isset($dependencies[$table]) || empty($dependencies[$table])) {
        $independentTables[] = $table;
    }
}

// Insert data for independent tables first
foreach ($independentTables as $table) {
    $data = $conn->query("SELECT * FROM `$table`");
    if ($data->num_rows > 0) {
        $output .= "-- Data for table: $table\n";
        
        while ($row = $data->fetch_assoc()) {
            $values = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . $conn->real_escape_string($value) . "'";
                }
            }
            $output .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
        }
        $output .= "\n";
    }
}

// Then insert data for dependent tables
foreach ($sortedTables as $table) {
    if (!in_array($table, $independentTables)) {
        $data = $conn->query("SELECT * FROM `$table`");
        if ($data->num_rows > 0) {
            $output .= "-- Data for table: $table\n";
            
            while ($row = $data->fetch_assoc()) {
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = "NULL";
                    } else {
                        $values[] = "'" . $conn->real_escape_string($value) . "'";
                    }
                }
                $output .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
            }
            $output .= "\n";
        }
    }
}

// Step 4: Restore settings
$output .= "\n-- Step 4: Restore database settings\n";
$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
$output .= "SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;\n";
$output .= "SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;\n";
$output .= "SET SQL_MODE = @OLD_SQL_MODE;\n";

// Save to file
$filename = 'database_proper_order.sql';
file_put_contents($filename, $output);

echo "\n✅ File saved: $filename\n";
echo "📊 Total tables: " . count($sortedTables) . "\n";
echo "📋 Independent tables (no foreign keys): " . count($independentTables) . "\n";

$conn->close();

// Create simple import script
$importScript = "#!/bin/bash\n";
$importScript .= "# Import script for database\n";
$importScript .= "echo 'Importing database...'\n";
$importScript .= "mysql -u root -p < $filename\n";
$importScript .= "echo '✅ Database imported successfully!'\n";
$importScript .= "echo 'Verifying foreign keys...'\n";
$importScript .= "mysql -u root -p -e \"USE $dbname; SELECT TABLE_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' AND TABLE_SCHEMA = '$dbname';\"\n";

file_put_contents('import.sh', $importScript);
chmod('import.sh', 0755);

echo "🚀 Import script: import.sh\n";
echo "   Run: ./import.sh\n";
?>