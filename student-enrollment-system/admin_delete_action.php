<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Suppress warnings
ini_set("display_errors", 0);

// Get the username from the form
$username = $_POST["username"];

// Delete the admin record from the `adminuser` table based on `username`
$sql_delete_admin = "DELETE FROM admin WHERE username = '$username'";
$result_array = execute_sql_in_oracle($sql_delete_admin);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
    // Error occurred, display the error message
    echo "<B>Deletion Failed.</B><br />";
    display_oracle_error_message($cursor);

    die("
      <i>
      <form method='post' action='admin.php?sessionid=$sessionid'>
      Read the error message, and then try again:
      <input type='submit' value='Go Back'>
      </form>
      </i>
    ");
}

// Optionally: Delete the user from the `users` table
// $sql_delete_user = "DELETE FROM users WHERE username = '$username'";
// execute_sql_in_oracle($sql_delete_user);

// Redirect back to the user list
Header("Location:admin.php?sessionid=$sessionid");
?>
