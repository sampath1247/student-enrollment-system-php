<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Suppress warnings
ini_set("display_errors", 0);
error_reporting(E_ALL);

// Get the student ID (sid) from the form
$sid = $_POST["sid"];
if (empty($sid)) {
    die("
        <B>Error:</B> Student ID is required.<br />
        <form method='post' action='admin.php?sessionid=$sessionid'>
        <input type='submit' value='Go Back'>
        </form>
    ");
}

// Step 1: Retrieve the username associated with the sid
$sql_username = "SELECT username FROM student WHERE sid='$sid'";
$result_username_array = execute_sql_in_oracle($sql_username);
$cursor_username = $result_username_array["cursor"];

$row = oci_fetch_array($cursor_username, OCI_ASSOC);
if ($row) {
    $username = $row['USERNAME'];
} else {
    die("
        <B>Error:</B> Student ID ($sid) does not exist.<br />
        <form method='post' action='admin.php?sessionid=$sessionid'>
        <input type='submit' value='Go Back'>
        </form>
    ");
}

// Step 2: Delete enrollments related to the sid
$sql_delete_enrollments = "DELETE FROM enrollments WHERE sid = '$sid'";
$result_array_enrollments = execute_sql_in_oracle($sql_delete_enrollments);
if ($result_array_enrollments["flag"] == false) {
    echo "<B>Failed to delete enrollments.</B><br />";
    display_oracle_error_message($result_array_enrollments["cursor"]);
    die("
        <i>
        <form method='post' action='admin.php?sessionid=$sessionid'>
        Read the error message, and then try again:
        <input type='submit' value='Go Back'>
        </form>
        </i>
    ");
}

// Step 3: Delete the student record
$sql_delete_student = "DELETE FROM student WHERE sid = '$sid'";
$result_array_student = execute_sql_in_oracle($sql_delete_student);
if ($result_array_student["flag"] == false) {
    echo "<B>Failed to delete student record.</B><br />";
    display_oracle_error_message($result_array_student["cursor"]);
    die("
        <i>
        <form method='post' action='admin.php?sessionid=$sessionid'>
        Read the error message, and then try again:
        <input type='submit' value='Go Back'>
        </form>
        </i>
    ");
}

// Step 4: Delete the user record from the `users` table
$sql_delete_users = "DELETE FROM users WHERE username = '$username'";
$result_array_users = execute_sql_in_oracle($sql_delete_users);
if ($result_array_users["flag"] == false) {
    echo "<B>Failed to delete user record.</B><br />";
    display_oracle_error_message($result_array_users["cursor"]);
    die("
        <i>
        <form method='post' action='admin.php?sessionid=$sessionid'>
        Read the error message, and then try again:
        <input type='submit' value='Go Back'>
        </form>
        </i>
    ");
}

// Step 5: Redirect back to the admin page on success
header("Location: admin.php?sessionid=$sessionid");
?>
