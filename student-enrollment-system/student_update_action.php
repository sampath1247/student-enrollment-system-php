<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Suppress warnings
ini_set("display_errors", 0);

// Get input from the update form
$username = $_POST["username"];
$sid = $_POST["sid"];
$fname = trim($_POST["fname"]);
$lname = trim($_POST["lname"]);
$age = trim($_POST["age"]);
$address = trim($_POST["address"]);
$admission_date = trim($_POST["admission_date"]);
$student_type = trim($_POST["student_type"]);
$concentration = isset($_POST["concentration"]) ? trim($_POST["concentration"]) : null;
$standing = isset($_POST["standing"]) ? trim($_POST["standing"]) : null;

// Validate required fields
if ($fname == "" || $lname == "" || $age == "" || $address == "" || $admission_date == "" || $student_type == "") {
    echo "<b>All required fields must be filled.</b><br />";
    die("
        <form method='post' action='student_update.php?sessionid=$sessionid'>
            <input type='hidden' name='username' value='$username'>
            <input type='hidden' name='sid' value='$sid'>
            <input type='hidden' name='fname' value='$fname'>
            <input type='hidden' name='lname' value='$lname'>
            <input type='hidden' name='age' value='$age'>
            <input type='hidden' name='address' value='$address'>
            <input type='hidden' name='admission_date' value='$admission_date'>
            <input type='hidden' name='student_type' value='$student_type'>
            <input type='hidden' name='concentration' value='$concentration'>
            <input type='hidden' name='standing' value='$standing'>
            <input type='hidden' name='update_fail' value='1'>
            <input type='submit' value='Go Back'>
        </form>
    ");
}

// Validate date format
if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $admission_date)) {
    echo "<b>Invalid date format. Please use MM/DD/YYYY.</b><br />";
    die("
        <form method='post' action='student_update.php?sessionid=$sessionid'>
            <input type='hidden' name='username' value='$username'>
            <input type='hidden' name='sid' value='$sid'>
            <input type='hidden' name='fname' value='$fname'>
            <input type='hidden' name='lname' value='$lname'>
            <input type='hidden' name='age' value='$age'>
            <input type='hidden' name='address' value='$address'>
            <input type='hidden' name='admission_date' value='$admission_date'>
            <input type='hidden' name='student_type' value='$student_type'>
            <input type='hidden' name='concentration' value='$concentration'>
            <input type='hidden' name='standing' value='$standing'>
            <input type='hidden' name='update_fail' value='1'>
            <input type='submit' value='Go Back'>
        </form>
    ");
}

// Build the SQL update statement
$sql = "UPDATE student
        SET fname = '$fname', lname = '$lname', age = '$age', address = '$address', 
            admission_date = TO_DATE('$admission_date', 'MM/DD/YYYY'), student_type = '$student_type', 
            concentration = '$concentration', standing = '$standing'
        WHERE username = '$username' AND sid = '$sid'";

$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
    // Display error message and form to retry
    echo "<B>Update Failed.</B><br />";
    display_oracle_error_message($cursor);
    
    die("
        <form method='post' action='student_update.php?sessionid=$sessionid'>
            <input type='hidden' name='username' value='$username'>
            <input type='hidden' name='sid' value='$sid'>
            <input type='hidden' name='fname' value='$fname'>
            <input type='hidden' name='lname' value='$lname'>
            <input type='hidden' name='age' value='$age'>
            <input type='hidden' name='address' value='$address'>
            <input type='hidden' name='admission_date' value='$admission_date'>
            <input type='hidden' name='student_type' value='$student_type'>
            <input type='hidden' name='concentration' value='$concentration'>
            <input type='hidden' name='standing' value='$standing'>
            <input type='hidden' name='update_fail' value='1'>
            <input type='submit' value='Go Back'>
        </form>
    ");
}

// Redirect back to the user list after successful update
header("Location: admin.php?sessionid=$sessionid");
?>
