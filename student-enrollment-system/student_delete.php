<?php
include "utility_functions.php";

ini_set("display_errors", 1);
error_reporting(E_ALL);


$sessionid = $_GET["sessionid"];
$username = $_GET["username"];
verify_session($sessionid);

// Fetch the admin details to confirm deletion
$sql = "SELECT * FROM student WHERE username = '$username'";
$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];


if ($result == false) {
    display_oracle_error_message($cursor);
    die("Client Query Failed.");
}

if (!($values = oci_fetch_array($cursor))) {
    // If the admin record is already deleted, redirect back to the user list
    Header("Location:admin.php?sessionid=$sessionid");
}
oci_free_statement($cursor);


echo("
  <form method='post' action='student_delete_action.php?sessionid=$sessionid&sid={$values['SID']}&username={$values['USERNAME']}'>
    <h3>Confirm Deletion</h3>
    <p>UserName (Read-only): <input type='text' value='{$values['USERNAME']}' readonly></p>
    <p>StudentID (Read-only): <input type='text' value='{$values['SID']}' readonly></p>
    <p>Student Type (Read-only): <input type='text' value='{$values['STUDENT_TYPE']}' readonly></p>
    <p>First Name (Read-only): <input type='text' value='{$values['FNAME']}' readonly></p>
    <p>Last Name (Read-only): <input type='text' value='{$values['LNAME']}' readonly></p>
    <p>Age (Read-only): <input type='text' value='{$values['AGE']}' readonly></p>
    <p>Address (Read-only): <input type='text' value='{$values['ADDRESS']}' readonly></p>
    <p>Admission Date (Read-only): <input type='text' value='{$values['ADMISSION_DATE']}' readonly></p>
    <p>Standing: <input type='text' value='{$values['STANDING']}' readonly></p>
    <p>Concentration: <input type='text' value='{$values['CONCENTRATION']}' readonly></p>
    
    <input type='hidden' name='username' value='{$values['USERNAME']}'>
    <input type='hidden' name='sid' value='{$values['SID']}'>
    <input type='submit' value='Delete'>
  </form>

  <form method='post' action='admin.php?sessionid=$sessionid'>
    <input type='submit' value='Go Back'>
  </form>
");
?>
