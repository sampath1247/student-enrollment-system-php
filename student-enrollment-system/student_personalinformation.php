<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
include "utility_functions.php";



$sessionid = $_GET["sessionid"];
$sid = $_GET["sid"];
verify_session($sessionid);

// Fetch the list of all students
$sql = "SELECT s.sid, s.fname, s.lname, s.admission_date, s.rnumber, u.username , s.age,s.address,s.student_type,s.probation_status
        FROM student s 
        JOIN users u ON s.username = u.username
        where s.sid='$sid'";

$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];

if ($row = oci_fetch_array($cursor)) {
    echo "<h2>Student Personal Information</h2>";
    echo "<p><strong>Student ID:</strong> {$row['SID']}</p>";
    echo "<p><strong>Full Name:</strong> {$row['FNAME']} {$row['LNAME']}</p>";
    echo "<p><strong>Age:</strong> {$row['AGE']}</p>";
    echo "<p><strong>Address:</strong> {$row['ADDRESS']}</p>";
    echo "<p><strong>Student Type:</strong> {$row['STUDENT_TYPE']}</p>";
    echo "<p><strong>Probation Status:</strong> {$row['PROBATION_STATUS']}</p>";
} else {
    echo "<p>No information found for the student.</p>";
}





// Free resources
oci_free_statement($cursor);



echo("
<form method=\"post\" action=\"student.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
");
?>
