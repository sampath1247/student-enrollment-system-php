<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Fetch the user's role
$sql = "SELECT rnumber FROM users WHERE username = (SELECT username FROM usersession WHERE sessionid='$sessionid')";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
$rnumber = oci_fetch_array($cursor)[0];

echo "<h2>Welcome to the User Management System</h2>";
if ($rnumber == 1) {
    echo "<p>You are an Admin. <a href='admin_dashboard.php?sessionid=$sessionid'>Go to Admin Dashboard</a></p>";
} elseif ($rnumber == 2) {
    echo "<p>You are a Student. <a href='student_dashboard.php?sessionid=$sessionid'>Go to Student Dashboard</a></p>";
} elseif ($rnumber == 3) {
    echo "<p>You are a Student Admin. You can access both dashboards:</p>";
    echo "<a href='admin_dashboard.php?sessionid=$sessionid'>Admin Dashboard</a><br/>";
    echo "<a href='student_dashboard.php?sessionid=$sessionid'>Student Dashboard</a>";
}

oci_free_statement($cursor);
?>
