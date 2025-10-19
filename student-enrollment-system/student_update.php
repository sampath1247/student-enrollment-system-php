<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
$username = $_GET["username"];
$sid=$_GET["sid"];
verify_session($sessionid);

// Check if this is a fresh load or a form submission retry (on update failure)
if (!isset($_POST["update_fail"])) {
    // Fetch the record to be updated from the database
    $sql = "SELECT fname, lname,age,address,admission_date,student_type,concentration,standing FROM student WHERE username = '$username' and sid = '$sid' ";
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
    $age=$values["AGE"];
    $address = $values["ADDRESS"];
    $admission_date = $values["ADMISSION_DATE"];
    $student_type = $values["STUDENT_TYPE"];
    $concentration = $values["CONCENTRATION"];
    $standing = $values["STANDING"];
} else {
    // Retrieve form data on update failure
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    $age = $_POST["age"];
    $address = $_POST["address"];
    $admission_date = $_POST["admission_date"];
    $student_type = $_POST["student_type"];
    $concentration = $_POST["concentration"];
    $standing = $_POST["standing"];  
}

// Display the update form
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

<form method='post' action='student_update_action.php?sessionid=$sessionid'>
  <p>UserName (Read-only): <input type='text' readonly value='$username' name='username'></p>
  <p>StudentID (Read-only): <input type='text' readonly value='$sid' name='sid'></p>
  
  <p>First Name (Required): <input type='text' value='$fname' name='fname' maxlength='30' required></p>
  <p>Last Name (Required): <input type='text' value='$lname' name='lname' maxlength='30' required></p>
  <p>Age (Required): <input type='number' value='$age' name='age' min='16' max='100' required></p>
  <p>Address (Required): <textarea name='address' rows='4' cols='50'>$address</textarea></p>
  <p>Admission Date (mm/dd/yyyy):  <input type=\"text\" value=\"$admission_date\" size=\"10\" maxlength=\"10\" name=\"admission_date\"></p>

  <p>Student Type (Required):
    <select id='student_type' name='student_type' onchange='updateFields()'>
      <option value=''>Select Type</option>
      <option value='Undergraduate' " . ($student_type == "Undergraduate" ? "selected" : "") . ">Undergraduate</option>
      <option value='Graduate' " . ($student_type == "Graduate" ? "selected" : "") . ">Graduate</option>
    </select>
  </p>
  
  <div id='concentration_field' style='display: " . ($student_type == "Graduate" ? "block" : "none") . ";'>
    <p>Concentration: <input type='text' value='$concentration' name='concentration' maxlength='30'></p>
  </div>
  
  <div id='standing_field' style='display: " . ($student_type == "Undergraduate" ? "block" : "none") . ";'>
    <p>Standing:
      <select name='standing'>
        <option value=''>Select Standing</option>
        <option value='Senior' " . ($standing == "Senior" ? "selected" : "") . ">Senior</option>
        <option value='Junior' " . ($standing == "Junior" ? "selected" : "") . ">Junior</option>
      </select>
    </p>
  </div>
  
  <p><input type='submit' value='Update'><input type='reset' value='Reset to Original Value'></p>
</form>

<form method='post' action='admin.php?sessionid=$sessionid'>
  <input type='submit' value='Go Back'>
</form>
");
?>
