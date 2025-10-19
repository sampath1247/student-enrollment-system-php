<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Suppress warnings
ini_set("display_errors", 0);

// Get values from the form
$username = $_POST["username"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$admission_date = $_POST["admission_date"];
$start_date = $_POST["start_date"];

// Update `student` table
$sql_update_student = "UPDATE student 
                       SET fname = '$fname', lname = '$lname', 
                           admission_date = TO_DATE('$admission_date', 'MM/DD/YYYY')
                       WHERE username = '$username'";
$result_update_student = execute_sql_in_oracle($sql_update_student);

if ($result_update_student["flag"] == false) {
    echo "<B>Update Failed in Student Table.</B><br />";
    display_oracle_error_message($result_update_student["cursor"]);
    die("
      <i>
      <form method='post' action='student_admin_update.php?sessionid=$sessionid&username=$username'>
      Read the error message, and then try again:
      <input type='submit' value='Go Back'>
      </form>
      </i>
    ");
}

// Update `admin` table
$sql_update_admin = "UPDATE admin 
                     SET fname = '$fname', lname = '$lname', 
                         start_date = TO_DATE('$start_date', 'MM/DD/YYYY')
                     WHERE username = '$username'";
$result_update_admin = execute_sql_in_oracle($sql_update_admin);

if ($result_update_admin["flag"] == false) {
    echo "<B>Update Failed in Admin Table.</B><br />";
    display_oracle_error_message($result_update_admin["cursor"]);
    die("
      <i>
      <form method='post' action='student_admin_update.php?sessionid=$sessionid&username=$username'>
      Read the error message, and then try again:
      <input type='submit' value='Go Back'>
      </form>
      </i>
    ");
}

// Redirect back to the main page on successful update
Header("Location:admin.php?sessionid=$sessionid");
?>
