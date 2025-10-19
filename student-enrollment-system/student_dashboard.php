<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Display student functionalities
echo "<h2>Student Dashboard</h2>";
echo "<ul>
    <li><a href='student.php?sessionid=$sessionid'>View student list</a></li>
        <li><a href='change_password.php?sessionid=$sessionid'>Change Password</a></li>

    <li><a href='logout_action.php?sessionid=$sessionid'>Logout</a></li>

</ul>";

echo("
<form method=\"post\" action=\"welcomepage.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
");
?>
