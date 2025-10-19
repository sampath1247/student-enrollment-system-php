<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Suppress warnings
ini_set("display_errors", 0);

// Get input from the update form
$username = $_POST["username"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];

// Build the SQL update statement
$sql = "UPDATE admin 
        SET fname = '$fname', lname = '$lname' 
        WHERE username = '$username'";

$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
    // Display error message and form to retry
    echo "<B>Update Failed.</B><br />";
    display_oracle_error_message($cursor);
    
    die("
        <form method='post' action='admin_update.php?sessionid=$sessionid'>
            <input type='hidden' name='username' value='$username'>
            <input type='hidden' name='fname' value='$fname'>
            <input type='hidden' name='lname' value='$lname'>
            <input type='hidden' name='update_fail' value='1'>
            <input type='submit' value='Go Back'>
        </form>
    ");
}

// Redirect back to the user list after successful update
header("Location: admin.php?sessionid=$sessionid");
?>
