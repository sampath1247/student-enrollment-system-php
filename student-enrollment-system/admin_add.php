<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Obtain the inputs from admin_add_action.php if any
$aid = $_POST["aid"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$start_date = $_POST["start_date"];
$username = $_POST["username"];
$rnumber = $_POST["rnumber"]; // To capture the role

// Display the insertion form with the dropdown for role selection
echo("
  <form method=\"post\" action=\"admin_add_action.php?sessionid=$sessionid\">
    Admin ID (Required): <input type=\"text\" value=\"$aid\" size=\"10\" maxlength=\"8\" name=\"aid\"> <br />

  username (Required): <input type=\"text\" value=\"$username\" size=\"10\" maxlength=\"8\" name=\"username\"> <br />
  First Name (Required): <input type=\"text\" value=\"$fname\" size=\"30\" maxlength=\"30\" name=\"fname\"> <br />
  Last Name (Required): <input type=\"text\" value=\"$lname\" size=\"30\" maxlength=\"30\" name=\"lname\"> <br />
  Start Date (MM/DD/YYYY): <input type=\"text\" value=\"$start_date\" size=\"10\" maxlength=\"10\" name=\"start_date\"> <br />
  
  <!-- Dropdown for Role -->
  Role:
  <select name=\"rnumber\">
    <option value=\"\">Select Role</option>
    <option value=\"1\">Admin</option>
    <option value=\"2\">Student</option>
    <option value=\"3\">Student-Admin</option>
  </select><br/>

  <input type=\"submit\" value=\"Add User\">
  <input type=\"reset\" value=\"Reset to Original Value\">
  </form>

  <form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
");
?>
