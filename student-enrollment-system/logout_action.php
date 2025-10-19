<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
$sql = "DELETE FROM usersession WHERE sessionid='$sessionid'";
execute_sql_in_oracle($sql);
header("Location:login.html");
?>
