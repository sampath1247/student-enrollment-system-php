<?php
include "utility_functions.php";

// Verify session
$sessionid = $_GET['sessionid'];
verify_session($sessionid);

echo "<h2>Grade Entry Page</h2>";

// Retrieve the section_id and course_id from the URL
$sid = isset($_GET['sid']) ? $_GET['sid'] : '';


$sql="SELECT e.section_id, sec.course_id, sec.semester, c.course_title, e.grade FROM 
enrollments e
JOIN 
    sections sec ON e.section_id = sec.section_id
JOIN 
    courses c ON sec.course_id = c.course_id
WHERE 
    e.sid = '$sid' 
ORDER BY 
    sec.semester, sec.section_id";

$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array['cursor'];
$row = oci_fetch_array($cursor);

//echo "{$row['GRADE']}";


//echo "123";





if ($row['SECTION_ID'] && $row['COURSE_ID']) {
    // Query to fetch students enrolled in the section
  

        $sql="SELECT e.sid,s.fname,s.lname,e.section_id, sec.course_id, sec.semester, c.course_title, e.grade,s.probation_status FROM 
enrollments e
JOIN
     student s on e.sid=s.sid
JOIN 
    sections sec ON e.section_id = sec.section_id
JOIN 
    courses c ON sec.course_id = c.course_id
WHERE 
    e.sid = '$sid'";


//echo $sql;




    
    $result_array = execute_sql_in_oracle($sql);
    $cursor = $result_array['cursor'];

    echo "<form method='post' action='grade_entry_action.php?sessionid=$sessionid&sid=$sid'>";  // Pass section_id to action page
    echo "<table border='1'>
            <tr>
                <th>Semester</th>
                <th>Section ID</th>
                <th>Course ID</th>
                <th>Course title</th>
                <th>Current Grade</th>
                <th>New Grade</th>
            </tr>";

            $sql_sid = "SELECT s.sid, s.fname, s.lname, s.probation_status 
            FROM student s 
            WHERE s.sid = '$sid'";

$result_array_sid = execute_sql_in_oracle($sql_sid);
$cursor_sid = $result_array_sid['cursor'];
$row_sid = oci_fetch_array($cursor_sid);


$name = "{$row_sid['FNAME']} {$row_sid['LNAME']}";
$probation_status = $row_sid['PROBATION_STATUS'];
echo "<h1>Student ID: $sid</h1>";
echo "<h1>Name: $name</h1>";
echo "<h1>Probation Status: $probation_status</h1>";


    // Create an array to store section IDs


$sections=array();

    // Display students and current grades
    while ($row = oci_fetch_array($cursor)) {
        $current_grade = $row['GRADE'];
        $section_id=$row['SECTION_ID'];
        $course_id=$row['COURSE_ID'];
        //echo "$current_grade";
        $semester = $row['SEMESTER'];
        $course_title = $row['COURSE_TITLE'];
        $grades=array();
        $sections[] = $row['SECTION_ID'];  // Add section ID to the array

                // Display the course details along with a dropdown to select a new grade

        echo "<tr>
                <td>$semester</td>
                <td>$section_id</td>
                <td>$course_id</td>
                <td>$course_title</td>
                <td>$current_grade</td>
                <td>
                    <select name='grades[$section_id]'>
                        <option value=''>--Select--</option>
                        <option value='A' " . ($current_grade == 'A' ? 'selected' : '') . ">A</option>
                        <option value='B' " . ($current_grade == 'B' ? 'selected' : '') . ">B</option>
                        <option value='C' " . ($current_grade == 'C' ? 'selected' : '') . ">C</option>
                        <option value='D' " . ($current_grade == 'D' ? 'selected' : '') . ">D</option>
                        <option value='F' " . ($current_grade == 'F' ? 'selected' : '') . ">F</option>
                    </select>
                </td>
                
              </tr>";
    }

        // Add hidden input fields for each section ID to pass them to the action page


    foreach ($sections as $section_id) {
        echo "<input type='hidden' name='sections[]' value='$section_id'>";
    }
    

    echo "</table>";
    echo "<input type='submit' value='Submit Grades'>";
    echo "</form>";
} else {
    echo "<p>Error: Section ID or Course ID is missing.</p>";
}

echo("<form method='post' action='admin.php?sessionid=$sessionid'>
  <input type='submit' value='Go Back'>
</form>");
?>


