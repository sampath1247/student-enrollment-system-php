<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
include "utility_functions.php";



$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Fetch the list of all students
$sql = "SELECT s.sid, s.fname, s.lname
        FROM student s 
        JOIN users u ON s.username = u.username";

$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];

echo "<h2>Student List</h2>";
echo "<table border='1'>
        <tr>
            <th>Student ID (SID)</th>
            <th>First Name</th>
            <th>Last Name</th>
           <th>view personal information</th>
           <th>view academic information</th>
           <th>course enrollment</th>

        </tr>";

// Iterate through the results and display each student
while ($row = oci_fetch_array($cursor)) {
    echo "<tr>
            <td>{$row['SID']}</td>
            <td>{$row['FNAME']}</td>
            <td>{$row['LNAME']}</td>
            <td><a href='student_personalinformation.php?sid={$row['SID']}&sessionid=$sessionid'>View Personal Information</a></td>  
            <td><a href='student_academicinformation.php?sid={$row['SID']}&sessionid=$sessionid'>view academic Information</a></td>           
            <td><a href='enrollment_page.php?sid={$row['SID']}&sessionid=$sessionid'>course enrollment</a></td>                    
          </tr>";
}
echo "</table>";

// Free resources
oci_free_statement($cursor);


echo("
<form method=\"post\" action=\"student_dashboard.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
");
?>
