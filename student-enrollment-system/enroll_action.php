<?php
include "utility_functions.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

$sessionid = $_GET['sessionid'] ?? null;
verify_session($sessionid);

$sid=$_GET['sid'] ?? null;

$action=$_GET['action'] ?? null;
$enrollments = $_POST['enrollments'] ?? [];

$enrollments = json_encode($enrollments);



// $enrollments = "'" . implode("','", $enrollments) . "'";
//$enrollments = "[$enrollments]";


//echo "$enrollments";


if (empty($enrollments)) {
    echo "<p>No courses were selected for enrollment.</p>";
    echo "<form method='post' action='enrollment_page.php?sessionid=$sessionid&sid=$sid'><input type='submit' value='Go Back'></form>";
    exit();
}




//echo"$action";





// If the action is '1', attempt to drop a course from enrollment



if ($action === '1') {
    //echo"sampath";
    $course_id = $_GET['course_id'];
    $section_id = $_GET['section_id'];
    $sid = $_GET['sid'];

    // Check if all parameters are provided
    if (!empty($course_id) && !empty($section_id) && !empty($sid)) {
        // Delete the enrollment for the specific course and section
        $sql_delete = "
        DELETE FROM enrollments
        WHERE sid = '$sid'
          AND section_id = '$section_id'
          AND EXISTS (
              SELECT 1 
              FROM sections sec
              JOIN courses c ON sec.course_id = c.course_id
              WHERE sec.section_id = '$section_id'
                AND c.course_id = '$course_id'
          )";

        $result_delete = execute_sql_in_oracle($sql_delete);
        

        // Provide feedback to the user
        if ($result_delete) {
            echo "<p>Course successfully dropped!</p>";
            echo("<form method='post' action='enrollment_page.php?sessionid=$sessionid&sid=$sid&enrolled=true'><input type='submit' value='Go Back'></form>");


        } else {
            echo "<p>Error: Could not drop the course. Please try again.</p>";
        }
    } else {
        echo "<p>Error: Missing required parameters to drop the course.</p>";
    }
    exit();
}

$enrollments_arr=array();


// Decode the JSON-encoded enrollments into an array

$enrollment_array = json_decode($enrollments, true);

// Iterate over each enrollment entry in the decoded data

foreach ($enrollment_array as $enrollment_json) {
    $enrollments_arr[] = json_decode($enrollment_json, true);
}



//var_dump($enrollment_arr);


if (!is_array($enrollments_arr)) {
    echo "<p>Error: Invalid enrollment data structure.</p>";
    exit();
}

//print_r($enrollments_arr);


foreach ($enrollments_arr as $enrollment_data ) {

    $section_id = strval($enrollment_data['section_id']);
    $course_id = strval($enrollment_data['course_id']);
    $enrollment_id = strval($enrollment_data['enrollment_id']);
    $prerequisites = strval($enrollment_data['prerequisites']);

    //echo"$section_id";




if (!empty($prerequisites) && strtolower($prerequisites) !== 'none') {
    echo "<p>Enrollment failed. The course has prerequisites: $prerequisites.</p>";
    echo("<form method='post' action='enrollment_page.php?sessionid=$sessionid&sid=$sid&enrolled=true'><input type='submit' value='Go Back'></form>");
    exit();
}

if (!$section_id) {
    echo "<p>Section ID is missing.</p>";
    exit();
}

$sql_deadline = "SELECT enrollment_deadline FROM sections WHERE section_id = '$section_id'";
$deadline_result = execute_sql_in_oracle($sql_deadline);

$cursor = $deadline_result['cursor'];
$deadline_row = oci_fetch_array($cursor);
if (!$deadline_row || strtotime($deadline_row['ENROLLMENT_DEADLINE']) < time()) {
    echo "<p>Enrollment deadline has passed.</p>";
    echo("<form method='post' action='enrollment_page.php?sessionid=$sessionid&sid=$sid&enrolled=true'><input type='submit' value='Go Back'></form>");
    exit();
}

$sql_seats = "
    SELECT capacity - COUNT(e.section_id) AS available_seats
    FROM sections s
    LEFT JOIN enrollments e ON s.section_id = e.section_id
    WHERE s.section_id = '$section_id'
    GROUP BY s.capacity";
$result_seats = execute_sql_in_oracle($sql_seats);

$seats_row = oci_fetch_array($result_seats['cursor']);
if ($seats_row['AVAILABLE_SEATS'] <= 0) {
    echo "<p>No seats available.</p>";
    echo("<form method='post' action='enrollment_page.php?sessionid=$sessionid&sid=$sid&enrolled=true'><input type='submit' value='Go Back'></form>");
    exit();
}

$sql_course_completed="
    SELECT sec.section_id, c.course_title, sec.semester, e.grade 
    FROM enrollments e
    JOIN sections sec ON e.section_id = sec.section_id
    JOIN courses c ON sec.course_id = c.course_id
    WHERE c.course_id='$course_id' and e.sid='$sid'
";
$result_course = execute_sql_in_oracle($sql_course_completed);
$cursor = $result_course["cursor"];
$course_completed = oci_fetch_array($cursor);
if($course_completed){
    echo"<p>you already completed or enrolled in this course </p>";
    echo("<form method='post' action='enrollment_page.php?sessionid=$sessionid&sid=$sid&enrolled=true'><input type='submit' value='Go Back'></form>");
    exit();
}





// Proceed with enrollment using Oracle's sequence
$sql_insert = "
    INSERT INTO enrollments (sid, section_id, grade)
    VALUES ('$sid', '$section_id', NULL)";
$result_insert = execute_sql_in_oracle($sql_insert);
if (!$result_insert['flag']) {
    echo "<p>Enrollment failed. Please try again.</p>";
    exit();
}


echo "<p>Successfully enrolled in Section ID: $section_id and course ID: $course_id!</p>";
}

echo("<form method='post' action='enrollment_page.php?sessionid=$sessionid&sid=$sid&enrolled=true'><input type='submit' value='Go Back'></form>");
?>
