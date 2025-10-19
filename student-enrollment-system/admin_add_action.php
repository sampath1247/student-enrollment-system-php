<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Suppress PHP auto warnings
ini_set("display_errors", 0);

// Get the values from the form
$aid = trim($_POST["aid"]);
if ($aid == "") $aid = 'NULL'; 
 // Optional aid


$fname = $_POST["fname"];
$lname = $_POST["lname"];
$start_date = trim($_POST["start_date"]);
$username = $_POST["username"];
$rnumber = $_POST["rnumber"];  // Role from the dropdown


// Role number for Admin (Assuming rnumber = 1 for Admin)


if ($username == "" || $fname == "" || $lname == "" || $rnumber == "") {
  echo "<B>All fields are required.</B><br />";
  die("<i>
      <form method=\"post\" action=\"admin_add.php?sessionid=$sessionid\">
      <input type=\"hidden\" value=\"$username\" name=\"username\">
      <input type=\"hidden\" value=\"$fname\" name=\"fname\">
      <input type=\"hidden\" value=\"$lname\" name=\"lname\">
      <input type=\"hidden\" value=\"$start_date\" name=\"start_date\">
      <input type=\"hidden\" value=\"$rnumber\" name=\"rnumber\">
      <input type=\"submit\" value=\"Go Back\">
      </form>
      </i>");
}

// Validate the date format before inserting
if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $start_date)) {
    echo "<B>Invalid date format.</B> Please use MM/DD/YYYY format.<br />";
    die("<i> 
        <form method=\"post\" action=\"admin_add.php?sessionid=$sessionid\">
        <input type=\"hidden\" value=\"$aid\" name=\"aid\">
        <input type=\"hidden\" value=\"$fname\" name=\"fname\">
        <input type=\"hidden\" value=\"$lname\" name=\"lname\">
        <input type=\"hidden\" value=\"$start_date\" name=\"start_date\">
        <input type=\"hidden\" value=\"$username\" name=\"username\">
        <input type=\"submit\" value=\"Go Back\">
        </form>
        </i>");
}

// Step 1: Insert into users table (if the user doesn't already exist)
$sql_check_user = "SELECT * FROM users WHERE username = '$username'";
$result_check_user = execute_sql_in_oracle($sql_check_user);
if (oci_fetch_array($result_check_user['cursor']) == false) {
    // Insert the user into the users table
    $sql_insert_user = "INSERT INTO users (username, password, rnumber) VALUES ('$username', 'default123', $rnumber)";
    $result_insert_user = execute_sql_in_oracle($sql_insert_user);

    if ($result_insert_user['flag'] == false) {
        echo "<B>Failed to add user.</B><br />";
        display_oracle_error_message($result_insert_user['cursor']);
        die("<i> 
            <form method=\"post\" action=\"admin_add.php?sessionid=$sessionid\">
            <input type=\"submit\" value=\"Go Back\">
            </form>
            </i>");
    }
}

// Step 2: Insert into admin table

if ($rnumber == 1 || $rnumber == 3) {
$sql_insert_admin = "INSERT INTO admin (aid, fname, lname, start_date, rnumber, username) 
                    VALUES ($aid, '$fname', '$lname', TO_DATE('$start_date', 'MM/DD/YYYY'), $rnumber, '$username')";
$result_insert_admin = execute_sql_in_oracle($sql_insert_admin);

if ($result_insert_admin['flag'] == false) {
    // Error handling
    echo "<B>Insertion Failed.</B><br />";
    display_oracle_error_message($result_insert_admin['cursor']);
    die("<i>
        <form method=\"post\" action=\"admin_add.php?sessionid=$sessionid\">
        <input type=\"hidden\" value=\"$aid\" name=\"aid\">
        <input type=\"hidden\" value=\"$fname\" name=\"fname\">
        <input type=\"hidden\" value=\"$lname\" name=\"lname\">
        <input type=\"hidden\" value=\"$start_date\" name=\"start_date\">
        <input type=\"hidden\" value=\"$username\" name=\"username\">
        Read the error message and then try again:
        <input type=\"submit\" value=\"Go Back\">
        </form>
        </i>");
}
}

if ($rnumber == 2 || $rnumber == 3) {
  // Insert into student table
  $sql_insert_student = "INSERT INTO student (sid, fname, lname, admission_date, rnumber, username) 
                         VALUES (student_seq.NEXTVAL, '$fname', '$lname', TO_DATE('$start_date', 'MM/DD/YYYY'), $rnumber, '$username')";
  $result_insert_student = execute_sql_in_oracle($sql_insert_student);

  if ($result_insert_student['flag'] == false) {
      echo "<B>Insertion into Student Failed.</B><br />";
      display_oracle_error_message($result_insert_student['cursor']);
  }
  
}

// Record inserted, go back to admin page
Header("Location:admin.php?sessionid=$sessionid");
?>
