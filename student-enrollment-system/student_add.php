<?php
include "utility_functions.php";

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Retrieve input values or set defaults
$fname = isset($_POST["fname"]) ? $_POST["fname"] : '';
$lname = isset($_POST["lname"]) ? $_POST["lname"] : '';
$admission_date = isset($_POST["admission_date"]) ? $_POST["admission_date"] : '';
$username = isset($_POST["username"]) ? $_POST["username"] : '';
$rnumber = isset($_POST["rnumber"]) ? $_POST["rnumber"] : '';
$age = isset($_POST["age"]) ? $_POST["age"] : '';
$address = isset($_POST["address"]) ? $_POST["address"] : '';
$student_type = isset($_POST["student_type"]) ? $_POST["student_type"] : '';
$concentration = isset($_POST["concentration"]) ? $_POST["concentration"] : '';
$standing = isset($_POST["standing"]) ? $_POST["standing"] : '';
$password = isset($_POST["password"]) ? $_POST["password"] : '';

// Display the form
echo("
<script>
  function updateFields() {
    const studentType = document.getElementById('student_type').value;
    const concentrationField = document.getElementById('concentration_field');
    const standingField = document.getElementById('standing_field');

    // Show or hide fields based on Student Type
    if (studentType === 'Graduate') {
      concentrationField.style.display = 'block';
      standingField.style.display = 'none';
    } else if (studentType === 'Undergraduate') {
      concentrationField.style.display = 'none';
      standingField.style.display = 'block';
    } else {
      concentrationField.style.display = 'none';
      standingField.style.display = 'none';
    }
  }
</script>

<form method=\"post\" action=\"student_add_action.php?sessionid=$sessionid\">
  Username (Required): 
  <input type=\"text\" value=\"$username\" size=\"10\" maxlength=\"8\" name=\"username\"> <br />
  
  First Name (Required): 
  <input type=\"text\" value=\"$fname\" size=\"30\" maxlength=\"30\" name=\"fname\"> <br />
  
  Last Name (Required): 
  <input type=\"text\" value=\"$lname\" size=\"30\" maxlength=\"30\" name=\"lname\"> <br />
  
  Admission Date (MM/DD/YYYY): 
  <input type=\"text\" value=\"$admission_date\" size=\"10\" maxlength=\"10\" name=\"admission_date\"> <br />
  
  Age (Required): 
  <input type=\"number\" value=\"$age\" name=\"age\" min=\"16\" max=\"100\"> <br/><br />
  
  Address (Required): 
  <textarea name=\"address\" rows=\"4\" cols=\"50\">$address</textarea> <br/><br />
  
  Role:
  <select name=\"rnumber\">
    <option value=\"\">Select Role</option>
    <option value=\"1\" " . ($rnumber == "1" ? "selected" : "") . ">Admin</option>
    <option value=\"2\" " . ($rnumber == "2" ? "selected" : "") . ">Student</option>
    <option value=\"3\" " . ($rnumber == "3" ? "selected" : "") . ">Student-Admin</option>
  </select><br/>
  
  Student Type (Required):
  <select id=\"student_type\" name=\"student_type\" onchange=\"updateFields()\">
    <option value=\"\">Select Type</option>
    <option value=\"Undergraduate\" " . ($student_type == "Undergraduate" ? "selected" : "") . ">Undergraduate</option>
    <option value=\"Graduate\" " . ($student_type == "Graduate" ? "selected" : "") . ">Graduate</option>
  </select> <br/><br/>
  
  <div id=\"concentration_field\" style=\"display: " . ($student_type == "Graduate" ? "block" : "none") . ";\">
    Concentration (Required): 
    <input type=\"text\" name=\"concentration\" size=\"30\" maxlength=\"30\" value=\"$concentration\"> <br/><br/>
  </div>
  
  <div id=\"standing_field\" style=\"display: " . ($student_type == "Undergraduate" ? "block" : "none") . ";\">
    Standing (Required):
    <select name=\"standing\">
      <option value=\"\">Select Standing</option>
      <option value=\"Senior\" " . ($standing == "Senior" ? "selected" : "") . ">Senior</option>
      <option value=\"Junior\" " . ($standing == "Junior" ? "selected" : "") . ">Junior</option>
    </select> <br/><br/>
  </div>
  
  Password (Required): 
  <input type=\"password\" value=\"$password\" size=\"30\" maxlength=\"30\" name=\"password\"> <br/><br />
  
  <input type=\"submit\" value=\"Add User\">
  <input type=\"reset\" value=\"Reset to Original Value\">
</form>

<form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
</form>
");
?>
