<?php


ini_set('display_errors', 1);
error_reporting(E_ALL);

include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Generate the search query form
echo("
  <form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
  First Name: <input type=\"text\" size=\"20\" maxlength=\"30\" name=\"q_fname\">
  Last Name: <input type=\"text\" size=\"20\" maxlength=\"30\" name=\"q_lname\">
      Student ID: <input type=\"text\" size=\"20\" maxlength=\"10\" name=\"q_student_id\"><br>
    Course Number: <input type=\"text\" size=\"20\" maxlength=\"10\" name=\"q_course_number\"><br>
Student Type:
    <select name=\"q_student_type\">
      <option value=\"\">--Select--</option>
      <option value=\"Undergraduate\">Undergraduate</option>
      <option value=\"Graduate\">Graduate</option>
    </select><br>
    Probation Status:
    <select name=\"q_probation_status\">
      <option value=\"\">--Select--</option>
      <option value=\"Y\">Yes</option>
      <option value=\"N\">No</option>
    </select><br>
   <BR />
  Start Date: 
  Month (mm) <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"q_start_month\"> 
  Day (dd) <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"q_start_day\"> 
  Year (yyyy) <input type=\"text\" size=\"4\" maxlength=\"4\" name=\"q_start_year\"> 
  <input type=\"submit\" value=\"Search\">
  </form>

  <form method=\"post\" action=\"welcomepage.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
");


$q_fname = isset($_POST["q_fname"]) ? trim($_POST["q_fname"]) : "";
$q_lname = isset($_POST["q_lname"]) ? trim($_POST["q_lname"]) : "";
$q_start_month = isset($_POST["q_start_month"]) ? trim($_POST["q_start_month"]) : "";
$q_start_day = isset($_POST["q_start_day"]) ? trim($_POST["q_start_day"]) : "";
$q_start_year = isset($_POST["q_start_year"]) ? trim($_POST["q_start_year"]) : "";
$q_student_id = isset($_POST["q_student_id"]) ? trim($_POST["q_student_id"]) : "";
$q_course_number = isset($_POST["q_course_number"]) ? trim($_POST["q_course_number"]) : "";
$q_student_type = isset($_POST["q_student_type"]) ? trim($_POST["q_student_type"]) : "";
$q_concentration = isset($_POST["q_concentration"]) ? trim($_POST["q_concentration"]) : "";
$q_standing = isset($_POST["q_standing"]) ? trim($_POST["q_standing"]) : "";
$q_probation_status = isset($_POST["q_probation_status"]) ? trim($_POST["q_probation_status"]) : "";
// Admins Query
$whereClause = "1=1";
if ($q_fname != "") {
    $whereClause .= " AND a.fname LIKE '%$q_fname%'";  // Only for admins (a.fname)
}
if ($q_lname != "") {
    $whereClause .= " AND a.lname LIKE '%$q_lname%'";
}
if ($q_start_month != "" || $q_start_day != "" || $q_start_year != "") {
    $date_condition = [];
    if ($q_start_month != "") {
        $date_condition[] = "TO_CHAR(a.start_date, 'MM') = '$q_start_month'";
    }
    if ($q_start_day != "") {
        $date_condition[] = "TO_CHAR(a.start_date, 'DD') = '$q_start_day'";
    }
    if ($q_start_year != "") {
        $date_condition[] = "TO_CHAR(a.start_date, 'YYYY') = '$q_start_year'";
    }
    $whereClause .= " AND (" . implode(" AND ", $date_condition) . ")";
}





$sql_admins = "SELECT u.username, a.fname, a.lname, TO_CHAR(a.start_date, 'YYYY') AS start_date
               FROM users u
               JOIN admin a ON u.username = a.username
               WHERE $whereClause AND u.rnumber = 1
               ORDER BY u.username";

$result_admins = execute_sql_in_oracle($sql_admins);
$cursor_admins = $result_admins["cursor"];

// Students Query (Separate for students)
$whereClause = "1=1";
if ($q_fname != "") {
    $whereClause .= " AND s.fname LIKE '%$q_fname%'";  // Only for students (s.fname)
}
if ($q_lname != "") {
    $whereClause .= " AND s.lname LIKE '%$q_lname%'";
}
if ($q_start_month != "" || $q_start_day != "" || $q_start_year != "") {
    $date_condition = [];
    if ($q_start_month != "") {
        $date_condition[] = "TO_CHAR(s.admission_date, 'MM') = '$q_start_month'";
    }
    if ($q_start_day != "") {
        $date_condition[] = "TO_CHAR(s.admission_date, 'DD') = '$q_start_day'";
    }
    if ($q_start_year != "") {
        $date_condition[] = "TO_CHAR(s.admission_date, 'YYYY') = '$q_start_year'";
    }
    $whereClause .= " AND (" . implode(" AND ", $date_condition) . ")";
}

if ($q_student_id != "") {
    $whereClause .= " AND s.sid LIKE '%$q_student_id%'";  // Student ID filter
}

// Add conditions for Course Number
if ($q_course_number != "") {
    $whereClause .= " AND c.course_id LIKE '%$q_course_number%'";  // Course number filter
}

// Add conditions for Student Type
if ($q_student_type != "") {
    $whereClause .= " AND s.student_type = '$q_student_type'";  // Student type filter
}

if ($q_standing != "") {
    $whereClause .= " AND s.standing = '$q_standing'";  // Student type filter
}

if ($q_concentration != "") {
    $whereClause .= " AND s.concentration = '$q_concentration'";  // Student type filter
}

// Add conditions for Probation Status
if ($q_probation_status != "") {
    $whereClause .= " AND s.probation_status = '$q_probation_status'";  // Probation status filter
}



$sql_students = "
    SELECT 
        u.username, 
        s.fname AS student_fname, 
        s.lname AS student_lname, 
        TO_CHAR(s.admission_date, 'YYYY') AS admission_date, 
        s.sid, 
        r.rname AS role_name
    FROM 
        users u
    LEFT JOIN 
        student s ON u.username = s.username
    JOIN 
        role r ON u.rnumber = r.rnumber
    WHERE $whereClause and
        u.rnumber = 2
    ORDER BY 
        u.username";





        
$result_students = execute_sql_in_oracle($sql_students);
$cursor_students = $result_students["cursor"];

// Student-Admins Query (for both student and admin roles)
$whereClause = "1=1";
if ($q_fname != "") {
    $whereClause .= " AND (s.fname LIKE '%$q_fname%' OR a.fname LIKE '%$q_fname%')";  // For both roles
}
if ($q_lname != "") {
    $whereClause .= " AND (s.lname LIKE '%$q_lname%' OR a.lname LIKE '%$q_lname%')";
}
if ($q_start_month != "" || $q_start_day != "" || $q_start_year != "") {
    $date_condition = [];
    if ($q_start_month != "") {
        $date_condition[] = "TO_CHAR(s.admission_date, 'MM') = '$q_start_month'";
        $date_condition[] = "TO_CHAR(a.start_date, 'MM') = '$q_start_month'";
    }
    if ($q_start_day != "") {
        $date_condition[] = "TO_CHAR(s.admission_date, 'DD') = '$q_start_day'";
        $date_condition[] = "TO_CHAR(a.start_date, 'DD') = '$q_start_day'";
    }
    if ($q_start_year != "") {
        $date_condition[] = "TO_CHAR(s.admission_date, 'YYYY') = '$q_start_year'";
        $date_condition[] = "TO_CHAR(a.start_date, 'YYYY') = '$q_start_year'";
    }
    $whereClause .= " AND (" . implode(" AND ", $date_condition) . ")";
}

if ($q_student_id != "") {
    $whereClause .= " AND s.sid LIKE '%$q_student_id%'";  // Student ID filter
}

// Add conditions for Course Number
if ($q_course_number != "") {
    $whereClause .= " AND c.course_id LIKE '%$q_course_number%'";  // Course number filter
}

// Add conditions for Student Type
if ($q_student_type != "") {
    $whereClause .= " AND s.student_type = '$q_student_type'";  // Student type filter
}

if ($q_standing != "") {
    $whereClause .= " AND s.standing = '$q_standing'";  // Student type filter
}

if ($q_concentration != "") {
    $whereClause .= " AND s.concentration = '$q_concentration'";  // Student type filter
}

// Add conditions for Probation Status
if ($q_probation_status != "") {
    $whereClause .= " AND s.probation_status = '$q_probation_status'";  // Probation status filter
}

$sql_student_admins = "SELECT u.username, s.fname AS student_fname, s.lname AS student_lname,TO_CHAR(s.admission_date, 'YYYY') AS admission_date,TO_CHAR(a.start_date, 'YYYY') AS start_date,s.sid, r.rname AS role_name FROM users u LEFT JOIN student s ON u.username = s.username LEFT JOIN admin a ON u.username = a.username JOIN role r ON u.rnumber = r.rnumber WHERE $whereClause and u.rnumber = 3 ORDER BY u.username
";

$result_student_admins = execute_sql_in_oracle($sql_student_admins);
$cursor_student_admins = $result_student_admins["cursor"];


$sidList = array(); // Initialize an empty array to store all student SIDs





//$sidList = []; // Initialize an empty array to store all student SIDs

$sql_username = "SELECT s.sid FROM student s JOIN enrollments e ON s.sid = e.sid";
$result_username_array = execute_sql_in_oracle($sql_username);
$cursor_username = $result_username_array["cursor"];

// Fetch all SIDs and store them in the $sidList array
while ($row = oci_fetch_array($cursor_username, OCI_ASSOC)) {
    $sidList[] = $row['SID'];
}
oci_free_statement($cursor_username);

//echo $sidList[1];


$sql_sid = "SELECT sid FROM student";
$result_sid = execute_sql_in_oracle($sql_sid);
$cursor_sid = $result_sid["cursor"];



$row_sid = oci_fetch_array($cursor_sid, OCI_ASSOC);
if ($row_sid) {
    $sidstudent = $row_sid['SID'];
} else {
    die("
        <B>Error:</B> Student ID ($sidstudent) does not exist.<br />
        <form method='post' action='admin.php?sessionid=$sessionid'>
        <input type='submit' value='Go Back'>
        </form>
    ");
}
$sidListString = "'" . implode("','", $sidList) . "'"; 



$sql = "SELECT e.section_id, sec.semester, c.course_id, c.course_title
    FROM enrollments e
    JOIN sections sec ON e.section_id = sec.section_id
    JOIN courses c ON sec.course_id = c.course_id
    WHERE e.sid IN ($sidListString)";

//echo $sql;

    
$result_array = execute_sql_in_oracle($sql);

//echo "123";
$cursor = $result_array['cursor'];

// Display the section/course info
$row = oci_fetch_array($cursor,OCI_ASSOC);

if($row){
    $course_id=$row['COURSE_ID'];
    $section_id=$row['SECTION_ID'];
}

// Display Admins, Students, and Student-Admins as before
// (Display code remains unchanged)

// Display Admins with start date
echo "<h3>Admins</h3>";
echo "<table border=1>";
echo "<tr> <th>username</th> <th>First Name</th> <th>Last Name</th> <th>Start Date</th> <th>Update</th> <th>Delete</th></tr>";

while ($values = oci_fetch_array($cursor_admins)) {
  $username = $values[0];
  $fname = $values[1];
  $lname = $values[2];
  $start_date = $values[3]; // This will now have the full year
  echo("<tr>
        <td>$username</td> <td>$fname</td> <td>$lname</td> <td>$start_date</td>
        <td><a href=\"admin_update.php?sessionid=$sessionid&username=$username\">Update</a></td>
        <td><a href=\"admin_delete.php?sessionid=$sessionid&username=$username\">Delete</a></td>
        </tr>");
}
echo("<form method=\"post\" action=\"admin_add.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Add A New User\">
  </form>");
oci_free_statement($cursor_admins);
echo "</table>";

// Display Students with admission date
echo "<h3>Students</h3>";
echo "<table border=1>";
echo "<tr> <th>username</th> <th>First Name</th> <th>Last Name</th> <th>Admission Date</th> <th>Student id</th> <th>Role</th> <th>Update</th> <th>Delete</th> <th>grade</th></tr>";

while ($values = oci_fetch_array($cursor_students)) {
  $username = $values[0];
  $fname = $values[1];
  $lname = $values[2];
  $admission_date = $values[3]; // This will now have the full year
  $sid=$values[4];
  $rname=$values[5];
  echo("<tr>
        <td>$username</td> <td>$fname</td> <td>$lname</td> <td>$admission_date</td><td>$sid</td> <td>$rname</td>
        <td><a href=\"student_update.php?sessionid=$sessionid&username=$username&sid=$sid\">Update</a></td>
        <td><a href=\"student_delete.php?sessionid=$sessionid&username=$username\">Delete</a></td>");
        if (in_array($sid, $sidList)) {
                echo "<td><a href='grade_entry.php?sessionid=$sessionid&sid=$sid'>Enter Grade</a></td>";
        } else {
            echo "<td>No Grade</td>";
        }
    }
echo("<form method=\"post\" action=\"student_add.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Add A New User\">
  </form>");
oci_free_statement($cursor_students);
echo "</table>";

// Display Student-Admins with both start date and admission date
echo "<h3>Student-Admins</h3>";
echo "<table border=1>";
echo "<tr> <th>username</th> <th>First Name</th> <th>Last Name</th> <th>Admission Date</th> <th>Start Date</th> <th>Student ID</th> <th>Role</th> <th>Update</th> <th>Delete</th> <th>grade</th></tr>";

// Fetch and display the results for Student-Admins
while ($values = oci_fetch_array($cursor_student_admins)) {
  $username = $values[0];
  $fname = $values[1];  // Since fname is the same for both student and admin
  $lname = $values[2];  // Since lname is the same for both student and admin
  $admission_date = $values[3]; // This will now have the full year
  $start_date = $values[4]; // This will now have the full year
  $sid=$values[5];
  $rname=$values[6];
  echo("<tr>
        <td>$username</td> <td>$fname</td> <td>$lname</td> <td>$admission_date</td> <td>$start_date</td><td>$sid</td> <td>$rname</td>
        <td><a href=\"student_update.php?sessionid=$sessionid&username=$username\">Update</a></td>
        <td><a href=\"student_delete.php?sessionid=$sessionid&username=$username\">Delete</a></td>");
        if (in_array($sid, $sidList)) {
            echo "<td><a href='grade_entry.php?sessionid=$sessionid&sid=$sid'>Enter Grade</a></td>";
    } else {
        echo "<td>No Grade</td>";
    }
        echo "</tr>";

}

oci_free_statement($cursor_student_admins);
echo "</table>";
?>
