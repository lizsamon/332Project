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


echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_server_query.css">
</head>
<body>';

// Get user input
$query_select = $_POST['query_select'] ?? null;
$professor_ssn = $_POST['professor_ssn'] ?? null;
$enrollment_course = $_POST['enrollment_course'] ?? null;
$enrollment_section = $_POST['enrollment_section'] ?? null;
$section_course = $_POST['section_course'] ?? null;
$student_cwid = $_POST['student_cwid'] ?? null;

// Queries 
$queries = [
    'Query 1' => [
        'sql' => "
            SELECT Course.C_title, Section.classroom, Section.meeting_days, Section.start_time, Section.end_time
            FROM Professor, Section, Course
            WHERE Professor.ssn = ? AND Professor.ssn = Section.ssn AND Section.C_no = Course.C_no
        ",
        'params' => [$professor_ssn],
        'types' => 's'
    ],
    'Query 2' => [
        'sql' => "
        SELECT Enrollment_Record.grade, COUNT(Enrollment_Record.grade), Enrollment_Record.C_no, Enrollment_Record.S_no
        FROM Enrollment_Record
        WHERE Enrollment_Record.C_no = ? AND Enrollment_Record.S_no = ?
        GROUP BY Enrollment_Record.grade;
        ",
        'params' => [$enrollment_course, $enrollment_section],
        'types' => 'si'
    ],
    'Query 3' => [
        'sql' => "
            SELECT Section.S_no, Section.classroom, Section.meeting_days, Section.start_time, Section.end_time, COUNT(*)
            FROM Enrollment_Record, Section
            WHERE  Enrollment_Record.C_no = ? AND Enrollment_Record.C_no = Section.C_no AND Enrollment_Record.S_no = Section.S_no 
            GROUP BY Section.S_no
        ",
        'params' => [$section_course],
        'types' => 's'
    ],
    'Query 4' => [
        'sql' => "
            SELECT Enrollment_Record.C_no, Enrollment_Record.grade
            FROM Student, Enrollment_Record
            WHERE Student.cwid = ? AND Student.cwid = Enrollment_Record.cwid
        ",
        'params' => [$student_cwid],
        'types' => 's'
    ]
];

if ($query_select && array_key_exists($query_select, $queries)) {
    $queryData = $queries[$query_select];

    if (!in_array(null, $queryData['params'], true)) {
        // Prepare the query
        $stmt = $conn->prepare($queryData['sql']);

        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        // Bind parameters dynamically
        $stmt->bind_param($queryData['types'], ...$queryData['params']);

        // Execute the query
        if (!$stmt->execute()) {
            die("Execute failed: " . htmlspecialchars($stmt->error));
        }

        $result = $stmt->get_result();

        if ($result === false) {
            die("Getting result failed: " . htmlspecialchars($stmt->error));
        }

        // Display results
        echo "<h2>Results for " . htmlspecialchars($query_select) . "</h2>";
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

        
        $stmt->close();
    } else {
        echo "<p>Please provide all required inputs for the selected query.</p>";
    }
} else {
    echo "<p>No query selected, please select a query to run.</p>";
}

$conn->close();
?>
