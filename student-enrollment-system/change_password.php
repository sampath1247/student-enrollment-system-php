<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Fetch the logged-in user's information (username)
$sql = "SELECT username FROM usersession WHERE sessionid='$sessionid'";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
$row = oci_fetch_array($cursor);
$username = $row["username"];

oci_free_statement($cursor);

// Check if the form is submitted
if ($_POST) {
    // Retrieve form data
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Validate the current password by checking it against the database
    $sql = "SELECT password FROM users WHERE username='$username'";
    $result_array = execute_sql_in_oracle($sql);
    $cursor = $result_array["cursor"];
    $row = oci_fetch_array($cursor);
    $stored_password = $row["PASSWORD"];
    
    oci_free_statement($cursor);
    
    // Verify if the current password matches
    if ($stored_password !== $current_password) {
        echo "<p style='color:red;'>Current password is incorrect. Please try again.</p>";
    } elseif ($new_password !== $confirm_password) {
        echo "<p style='color:red;'>New password and confirmation do not match. Please try again.</p>";
    } else {
        // Update the user's password
        $sql_update = "UPDATE users SET password='$new_password' WHERE username='$username'";
        $result_array = execute_sql_in_oracle($sql_update);
        
        echo "<p style='color:green;'>Password successfully updated!</p>";
        header("Refresh: 2; url=welcomepage.php?sessionid=$sessionid");
    }
} else {
    // Display the change password form
    echo "<h2>Change Password</h2>";
    echo "<form method='POST'>
            <label for='current_password'>Current Password:</label>
            <input type='password' name='current_password' id='current_password' required><br/><br/>
            
            <label for='new_password'>New Password:</label>
            <input type='password' name='new_password' id='new_password' required><br/><br/>
            
            <label for='confirm_password'>Confirm New Password:</label>
            <input type='password' name='confirm_password' id='confirm_password' required><br/><br/>
            
            <input type='submit' value='Change Password'>
          </form>";
}
?>
