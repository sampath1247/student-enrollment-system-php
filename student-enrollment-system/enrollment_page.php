<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "utility_functions.php";

$sessionid = $_GET['sessionid'];

 





verify_session($sessionid);

$sid = $_GET['sid'];

//echo"$sid";

//$sid = $_GET['sid']; 

echo "<h2>Student Enrollment Page</h2>";


echo "<h2>Search Courses to Enroll</h2>";

echo "
  <form method=\"post\" action=\"enrollment_page.php?sessionid=$sessionid&sid=$sid\">
    <label for='course_number'>Course ID:</label>
    <input type=\"text\" name=\"course_id\" size=\"20\" maxlength=\"10\"><br>
    
    
    
    <label for='semester'>Semester:</label>
    <select name=\"semester\">
          <option value=\"\">All</option>

      <option value=\"Spring 2025\">Spring 2025</option>
      <option value=\"Fall 2024\">Fall 2024</option>
      <option value=\"Spring 2024\">Spring 2024</option>
    </select><br>
    
    <input type=\"submit\" value=\"Search Sections\">
  </form>";

  
$course_id = isset($_POST["course_id"]) ? trim($_POST["course_id"]) : "";
$semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';

// Create the WHERE clause of the SQL query, which will be built dynamically based on user input

$whereClause = "1=1";  // Base condition to ensure the query runs

// Add conditions based on user input
if ($course_id != "") {
    $whereClause .= " AND c.course_id LIKE '%$course_id%'";  // Course ID filter
}

if ($semester != "") {
    $whereClause .= " AND sec.semester = '$semester'";  // Semester filter
}


      echo "<h3>Successfully Enrolled in Course</h3>";
        echo "<table border='1'>
                <tr>
                    <th>Section ID</th>
                    <th>Course ID</th>
                    <th>Course Title</th>
                    <th>Credits</th>
                    <th>action</th>
                </tr>";



    


        
    
        
        $sql_enrolled_courses = "
    SELECT 
        sec.section_id,
        c.course_id,
        c.course_title,
        c.credits
    FROM (
        SELECT e.sid, sec.section_id, c.course_id, 
               MIN(e.enrollment_id) AS enrollment_id,
               MIN(c.course_title) AS course_title,
               MIN(c.credits) AS credits
        FROM enrollments e
        JOIN sections sec ON e.section_id = sec.section_id
        JOIN courses c ON sec.course_id = c.course_id
        WHERE e.sid = '$sid' AND sec.semester = 'Spring 2025'
        GROUP BY e.sid, sec.section_id, c.course_id
    ) grouped_courses
    JOIN enrollments e ON e.enrollment_id = grouped_courses.enrollment_id
    JOIN sections sec ON sec.section_id = grouped_courses.section_id
    JOIN courses c ON c.course_id = grouped_courses.course_id
    ORDER BY c.course_title, sec.section_id";





$enrollments= array();


    
$result_enrolled = execute_sql_in_oracle($sql_enrolled_courses);
$cursor_enrolled = $result_enrolled['cursor'];

while ($row_enrolled = oci_fetch_array($cursor_enrolled)) {
    echo "<tr>
            <td>{$row_enrolled['SECTION_ID']}</td>
            <td>{$row_enrolled['COURSE_ID']}</td>
            <td>{$row_enrolled['COURSE_TITLE']}</td>
            <td>{$row_enrolled['CREDITS']}</td>
            <td><a href='enroll_action.php?sid=$sid&sessionid=$sessionid&section_id={$row_enrolled['SECTION_ID']}&course_id={$row_enrolled['COURSE_ID']}&action=1'>drop</a></td>     
          </tr>";
}
echo "</table>";
    

   
$sql = "
  SELECT 
    MAX(e.enrollment_id) AS enrollment_id, 
    sec.section_id,
    c.course_id,
    c.course_title,
    c.credits,
    sec.semester,
    sec.enrollment_deadline,
    sec.capacity,
    (sec.capacity - COUNT(e.sid)) AS available_seats, 
    LISTAGG(p.prerequisite_course_id, ', ') WITHIN GROUP (ORDER BY p.prerequisite_course_id) AS prerequisites  
  FROM sections sec
  JOIN courses c ON sec.course_id = c.course_id
  LEFT JOIN enrollments e ON sec.section_id = e.section_id
  LEFT JOIN prerequisites p ON c.course_id = p.course_id
  WHERE $whereClause  
  GROUP BY sec.section_id, c.course_id, c.course_title, c.credits, sec.semester, sec.enrollment_deadline, sec.capacity
  ORDER BY c.course_title, sec.section_id";


          
$result_sections = execute_sql_in_oracle($sql);
$cursor = $result_sections['cursor'];



echo "<form method='post' action='enroll_action.php?sessionid=$sessionid&sid=$sid'>";  // Pass section_id to action page


echo "<h3>Courses</h3>";


echo "<table border='1'>
        <tr>
            <th>Section ID</th>
            <th>Course ID</th>
            <th>Course Title</th>
            <th>Credits</th>
            <th>Semester</th>
            <th>Enrollment Deadline</th>
            <th>Capacity</th>
            <th>Available Seats</th>
            <th>Prerequisites</th>
            <th>Action</th>
        </tr>";

        $row = oci_fetch_array($cursor);

        

while ($row = oci_fetch_array($cursor)) {
    $deadline_passed = strtotime($row['ENROLLMENT_DEADLINE']) < time();
    $checkbox_value = json_encode([
      'section_id' => $row['SECTION_ID'],
      'course_id' => $row['COURSE_ID'],
      'enrollment_id' => $row['ENROLLMENT_ID'],
      'prerequisites' => $row['PREREQUISITES']
  ]);

      // Display the course details and a checkbox to enroll in the course

    echo "<tr>
    <td>
    
          <input type='checkbox' name='enrollments[]' value='" . htmlspecialchars($checkbox_value, ENT_QUOTES, 'UTF-8') . "'>
                  </td>
            <td>{$row['SECTION_ID']}</td>
            <td>{$row['COURSE_ID']}</td>
            <td>{$row['COURSE_TITLE']}</td>
            <td>{$row['CREDITS']}</td>
            <td>{$row['SEMESTER']}</td>
            <td>{$row['ENROLLMENT_DEADLINE']}</td>
            <td>{$row['CAPACITY']}</td>
            <td>{$row['AVAILABLE_SEATS']}</td>
            <td>" . ($row['PREREQUISITES'] ?: 'None') . "</td>

          </tr>";
}
echo "</table>";

echo "<input type='submit' value='Enroll'>";
echo "</form>";


echo("
<form method=\"post\" action=\"student.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
");





?>