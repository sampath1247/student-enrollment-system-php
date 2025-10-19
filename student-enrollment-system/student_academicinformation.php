<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "utility_functions.php";

$sessionid = $_GET['sessionid'];
verify_session($sessionid);

$sid = $_GET['sid']; // Student ID from the URL

//echo "$sid";

$sql = "
    SELECT sid, fname, lname , courses_completed, total_credits, gpa ,probation_status
    FROM student_academic_info 
    WHERE sid = '$sid'
";


$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array['cursor'];


echo "<h2>Student Academic Information</h2>";
if ($row = oci_fetch_array($cursor)) {
    echo "<p><strong>Full Name:</strong> {$row['FNAME']} {$row['LNAME']}</p>";
    echo "<p><strong>Courses Completed:</strong> {$row['COURSES_COMPLETED']}</p>";
    echo "<p><strong>Total Credits:</strong> {$row['TOTAL_CREDITS']}</p>";
    echo "<p><strong>GPA:</strong> {$row['GPA']}</p>";
    echo "<p><strong>Probation Status:</strong> " . ($row['PROBATION_STATUS'] === 'Y' ? 'On Probation' : 'In Good Standing') . "</p>";

} else {
    echo "<p>No academic information available for this student.</p>";
}


$sql_sections = "
    SELECT sec.section_id, c.course_title, sec.semester, e.grade 
    FROM enrollments e
    JOIN sections sec ON e.section_id = sec.section_id
    JOIN courses c ON sec.course_id = c.course_id
    WHERE e.sid = '$sid' AND sec.semester = 'Spring 2024'
    ORDER BY sec.semester
";
$result_sections = execute_sql_in_oracle($sql_sections);
$cursor = $result_sections["cursor"];



echo "<h3>Sections Taken</h3>";
echo "<table border='1'>    
        <tr>
            <th>Section ID</th>
            <th>Course Title</th>
            <th>Semester</th>
            <th>Grade</th>
        </tr>";
while ($section_row = oci_fetch_array($cursor)) {
    echo "<tr>
            <td>{$section_row['SECTION_ID']}</td>
            <td>{$section_row['COURSE_TITLE']}</td>
            <td>{$section_row['SEMESTER']}</td>
            <td>{$section_row['GRADE']}</td>
          </tr>";
}
echo "</table>";


$sql_in_progress = "
    SELECT sec.section_id, c.course_title, sec.semester
    FROM enrollments e
    JOIN sections sec ON e.section_id = sec.section_id
    JOIN courses c ON sec.course_id = c.course_id
    WHERE e.sid = '$sid' AND sec.semester = 'Fall 2024'
";
$result_in_progress = execute_sql_in_oracle($sql_in_progress);
$cursor = $result_in_progress['cursor'];

echo "<h3>In-Progress Courses</h3>";
echo "<table border='1'>
        <tr>
            <th>Section ID</th>
            <th>Course Title</th>
            <th>Semester</th>
        </tr>";
while ($in_progress_row = oci_fetch_array($cursor)) {
    echo "<tr>
            <td>{$in_progress_row['SECTION_ID']}</td>
            <td>{$in_progress_row['COURSE_TITLE']}</td>
            <td>{$in_progress_row['SEMESTER']}</td>
          </tr>";
}
echo "</table>";

echo("
<form method=\"post\" action=\"student.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
");

?>
