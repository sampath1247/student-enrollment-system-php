<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
$username = $_GET["username"];
verify_session($sessionid);

// Check if this is a fresh load or a form submission retry (on update failure)
if (!isset($_POST["update_fail"])) {
    // Fetch the record to be updated from the database
    $sql = "SELECT fname, lname FROM admin WHERE username = '$username'";
    $result_array = execute_sql_in_oracle($sql);
    $result = $result_array["flag"];
    $cursor = $result_array["cursor"];
    
    if ($result == false) {
        display_oracle_error_message($cursor);
        die("Query Failed.");
    }

    $values = oci_fetch_array($cursor);
    oci_free_statement($cursor);

    // Assign the current values
    $fname = $values["FNAME"];
    $lname = $values["LNAME"];
} else {
    // Retrieve form data on update failure
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
}

// Display the update form
echo("
  <form method='post' action='admin_update_action.php?sessionid=$sessionid'>
    username (Read-only): <input type='text' readonly value='$username' name='username'> <br />
    First Name (Required): <input type='text' value='$fname' name='fname' maxlength='30' required> <br />
    Last Name (Required): <input type='text' value='$lname' name='lname' maxlength='30' required> <br />
    <input type='submit' value='Update'>
    <input type='reset' value='Reset to Original Value'>
  </form>

  <form method='post' action='admin.php?sessionid=$sessionid'>
    <input type='submit' value='Go Back'>
  </form>
");
?>
