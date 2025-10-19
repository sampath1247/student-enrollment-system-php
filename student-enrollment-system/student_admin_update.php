<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
$username = $_GET["username"];
verify_session($sessionid);

// Retrieve current details from both `student` and `admin` tables
$sql = "SELECT s.fname, s.lname, s.admission_date, a.start_date 
        FROM student s
        JOIN admin a ON s.username = a.username
        WHERE s.username = '$username'";
        
$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
    display_oracle_error_message($cursor);
    die("Client Query Failed.");
}

if (!($values = oci_fetch_array($cursor))) {
    // If the record is missing, redirect back to admin.php
    Header("Location:admin.php?sessionid=$sessionid");
}
oci_free_statement($cursor);

$fname = $values[0];
$lname = $values[1];
$admission_date = $values[2];
$start_date = $values[3];

// Display the form with current details
echo("
  <form method='post' action='student_admin_update_action.php?sessionid=$sessionid'>
    username: <input type='text' readonly value='$username' name='username'><br />
    First Name: <input type='text' value='$fname' name='fname'><br />
    Last Name: <input type='text' value='$lname' name='lname'><br />
    Admission Date (MM/DD/YYYY): <input type='text' value='$admission_date' name='admission_date'><br />
    Start Date (MM/DD/YYYY): <input type='text' value='$start_date' name='start_date'><br />
    <input type='submit' value='Update'>
  </form>

  <form method='post' action='admin.php?sessionid=$sessionid'>
    <input type='submit' value='Go Back'>
  </form>
");
?>
