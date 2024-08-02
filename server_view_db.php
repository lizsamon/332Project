
<?php
$host = 'localhost'; 
$dbname = 'project_database';
$user = 'denise1'; 
$pass = 'abc123'; 

// Connect to database
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getInput($key) {
    if (php_sapi_name() == 'cli') {
        global $argv;
        $value = null;
        foreach ($argv as $arg) {
            if (strpos($arg, "$key=") === 0) {
                $value = substr($arg, strlen("$key="));
                break;
            }
        }
        return $value;
    } else {
        return $_POST[$key] ?? null;
    }
}

// Queries
$tableQueries = [
    'College_degrees' => "SELECT * FROM College_degrees",
    'Course' => "SELECT * FROM Course",
    'Department' => "SELECT * FROM Department",
    'Enrollment_Record' => "SELECT * FROM Enrollment_Record",
    'Minors' => "SELECT * FROM Minors",
    'Professor' => "SELECT * FROM Professor",
    'Section' => "SELECT * FROM Section",
    'Student' => "SELECT * FROM Student"
];

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_server_db.css">
</head>
<body>';

// Get selected tables 
$selectedTables = isset($_POST['tables']) ? $_POST['tables'] : [];

// Display tables based on selection
foreach ($selectedTables as $table) {
    if (array_key_exists($table, $tableQueries)) {
        $query = $tableQueries[$table];
        $result = $conn->query($query);

        echo "<h2>Table: " . htmlspecialchars($table) . "</h2>";
        echo "<table border='1'><tr>";

        // Print column 
        while ($field = $result->fetch_field()) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";

        // Print rows
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }

        echo "</table><br>";
    }
}

$conn->close();
?>
