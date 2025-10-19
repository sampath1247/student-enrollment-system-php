<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
$username = $_GET["username"];
verify_session($sessionid);

// Fetch the admin details to confirm deletion
$sql = "SELECT a.aid, a.fname, a.lname FROM admin a WHERE a.username = '$username'";
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


$aid = $values['AID'];
$fname = $values['FNAME'];
$lname = $values['LNAME'];

// Display admin details and ask for confirmation
echo("
  <form method='post' action='admin_delete_action.php?sessionid=$sessionid'>
  Admin ID: <input type='text' readonly value='$aid' name='username'><br />
  First Name: <input type='text' disabled value='$fname' name='fname'><br />
  Last Name: <input type='text' disabled value='$lname' name='lname'><br />
    <input type='hidden' name='username' value='$username'>
  <input type='submit' value='Delete'>
  </form>

  <form method='post' action='admin.php?sessionid=$sessionid'>
  <input type='submit' value='Go Back'>
  </form>
");
?>
