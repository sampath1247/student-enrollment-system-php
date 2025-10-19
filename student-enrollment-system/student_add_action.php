<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Suppress PHP auto warnings
ini_set("display_errors", 1);
error_reporting(E_ALL);

$db_conn = oci_connect('gq073', 'klpnul', 'gqiannew3:1521/orc.uco.local');
if (!$db_conn) {
    $e = oci_error();
    die("<b>Database connection failed:</b> " . $e['message']);
}


// Retrieve form data
$fname = trim($_POST["fname"]);
$lname = trim($_POST["lname"]);
$admission_date = trim($_POST["admission_date"]);
$username = trim($_POST["username"]);
$rnumber = trim($_POST["rnumber"]);
$age = trim($_POST["age"]);
$address = trim($_POST["address"]);
$student_type = trim($_POST["student_type"]);
$concentration = isset($_POST["concentration"]) ? trim($_POST["concentration"]) : '';
$standing = isset($_POST["standing"]) ? trim($_POST["standing"]) : '';
$password = trim($_POST["password"]);

// Debugging: Log values
error_log("Adding student: fname=$fname, lname=$lname, username=$username, rnumber=$rnumber");

// Validate required fields
if ($username == "" || $fname == "" || $lname == "" || $rnumber == "" || $student_type == "") {
    echo "<b>All fields are required.</b><br />";
    die("<i>
        <form method='post' action='student_add.php?sessionid=$sessionid'>
            <input type='hidden' value='$fname' name='fname'>
            <input type='hidden' value='$lname' name='lname'>
            <input type='hidden' value='$admission_date' name='admission_date'>
            <input type='hidden' value='$username' name='username'>
            <input type='hidden' value='$age' name='age'>
            <input type='hidden' value='$address' name='address'>
            <input type='hidden' value='$student_type' name='student_type'>
            <input type='hidden' value='$password' name='password'>
            <input type='submit' value='Go Back'>
        </form>
    </i>");
}

// Validate the date format
if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $admission_date)) {
    echo "<b>Invalid date format.</b> Please use MM/DD/YYYY.<br />";
    die("<i>
        <form method='post' action='student_add.php?sessionid=$sessionid'>
            <input type='hidden' value='$fname' name='fname'>
            <input type='hidden' value='$lname' name='lname'>
            <input type='hidden' value='$admission_date' name='admission_date'>
            <input type='hidden' value='$username' name='username'>
            <input type='hidden' value='$age' name='age'>
            <input type='hidden' value='$address' name='address'>
            <input type='hidden' value='$student_type' name='student_type'>
            <input type='hidden' value='$password' name='password'>
            <input type='submit' value='Go Back'>
        </form>
    </i>");
}

// Check if the user exists in the `users` table
$sql_check_user = "SELECT * FROM users WHERE username = :username";
$statement_check_user = oci_parse($db_conn, $sql_check_user);
oci_bind_by_name($statement_check_user, ':username', $username);
oci_execute($statement_check_user);


if (oci_fetch($statement_check_user)) {
    // Username already exists
    echo "<b>Username already exists. Please choose another one.</b>";
    die("<i>
        <form method='post' action='student_add.php?sessionid=$sessionid'>
            <input type='hidden' value='$username' name='username'>
            <input type='submit' value='Go Back'>
        </form>
    </i>");
}



if (!oci_fetch_array($statement_check_user)) {
    // Insert new user
    $sql_insert_user = "INSERT INTO users (username, password, rnumber) VALUES (:username, :password, :rnumber)";
    $statement_insert_user = oci_parse($db_conn, $sql_insert_user);
    oci_bind_by_name($statement_insert_user, ':username', $username);
    oci_bind_by_name($statement_insert_user, ':password', $password);
    oci_bind_by_name($statement_insert_user, ':rnumber', $rnumber);

    if (!oci_execute($statement_insert_user)) {
        $error = oci_error($statement_insert_user);
        error_log("Insert User Error: " . $error['message']);
        die("<b>Failed to add user.</b> " . $error['message']);
    }
}

// Call the stored procedure to insert the student
$sql_call_procedure = "BEGIN 
    insert_student(:fname, :lname, TO_DATE(:admission_date, 'MM/DD/YYYY'), :rnumber, :username, :age, :address, :student_type, :standing, :concentration, :probation_status);
END;";
$statement_call_procedure = oci_parse($db_conn, $sql_call_procedure);
$probation_status = 'N'; // Default probation status

// Bind parameters
oci_bind_by_name($statement_call_procedure, ':fname', $fname);
oci_bind_by_name($statement_call_procedure, ':lname', $lname);
oci_bind_by_name($statement_call_procedure, ':admission_date', $admission_date);
oci_bind_by_name($statement_call_procedure, ':rnumber', $rnumber);
oci_bind_by_name($statement_call_procedure, ':username', $username);
oci_bind_by_name($statement_call_procedure, ':age', $age);
oci_bind_by_name($statement_call_procedure, ':address', $address);
oci_bind_by_name($statement_call_procedure, ':student_type', $student_type);
oci_bind_by_name($statement_call_procedure, ':standing', $standing);
oci_bind_by_name($statement_call_procedure, ':concentration', $concentration);
oci_bind_by_name($statement_call_procedure, ':probation_status', $probation_status);

// Execute stored procedure
if (!oci_execute($statement_call_procedure)) {
    $error = oci_error($statement_call_procedure);
    error_log("Stored Procedure Error: " . $error['message']);
    die("<b>Failed to insert student using stored procedure.</b> " . $error['message']);
}

// Redirect to admin page on success
header("Location: admin.php?sessionid=$sessionid");
?>
