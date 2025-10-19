<?php
include "utility_functions.php";

$username = $_POST["username"];
$password = $_POST["password"];

$sql = "SELECT username, rnumber FROM users WHERE username='$username' AND password='$password'";
$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false){
    display_oracle_error_message($cursor);
    die("Client Query Failed.");
}

if ($row = oci_fetch_array($cursor)) {

    oci_free_statement($cursor);

    // found the client
    $username = $row[0];
    // Generate a session ID and store it in the usersession table
    $sessionid = md5(uniqid(rand()));
    $sql = "INSERT INTO usersession (sessionid, username, sessiondate) VALUES ('$sessionid', '$username', SYSDATE)";


    $result_array= execute_sql_in_oracle($sql);
    $result = $result_array["flag"];
    $cursor = $result_array["cursor"];

    if ($result == false){
        display_oracle_error_message($cursor);
        die("Failed to create a new session");
    }

    else{
    // Redirect to the welcome page
    header("Location:welcomepage.php?sessionid=$sessionid");
    }
} else {
    // Login failed
    echo "Login failed. Please <a href='login.html'>try again</a>.";
}
oci_free_statement($cursor);
?>
