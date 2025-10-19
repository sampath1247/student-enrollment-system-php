<?php
include "utility_functions.php";

$sessionid = $_GET['sessionid'];
verify_session($sessionid);

$sid=$_GET['sid'];
$grades = $_POST["grades"];
$sections=$_POST["sections"];

// Combine the grades array into a single string (flatten the array)

$grade =  implode("", $grades); 

//print_r($grade);

//print_r($grade[0]);

 //echo "$grade";



//print_r($sections[0]);

//  if (isset($_POST['sections'])) {
//      // This will be an array of section IDs
//     foreach ($sections as $section_id) {
//         echo "Section ID: $section_id <br>";
//     }
// }


 //echo "$sid";

if (empty($grades)) {
    echo "<p>No grades were submitted.</p>";
    echo "<form method='post' action='admin.php?sessionid=$sessionid'><input type='submit' value='Go Back'></form>";
    exit();
}



//print_r(str_split($sections));
//echo "$grade[0]";
//$i=0;
//echo "$i";

// Loop through the sections array and update grades for each section

foreach ($sections as $index => $section) {

    $char = $grade[$index];
    //echo "$char";
    //echo "$section";

    //echo "123";
    //echo "$sections[$i]";
    //$i=$i+1;
    //$section_id=strval($sections[]);

        // Prepare SQL query to update the grade in the "enrollments" table for the given student (sid) and section

        $sql_update_grade = "
            UPDATE enrollments 
            SET grade = '$char'
            WHERE sid = '$sid' and section_id= '$section'";
        execute_sql_in_oracle($sql_update_grade);
}


        
    

        //echo $sql_update_grade;
// Automate probation status update
$sql_update_probation = "
    UPDATE student s
    SET probation_status = (
        SELECT CASE 
                   WHEN GPA < 2.0 THEN 'Y'
                   ELSE 'N'
               END
        FROM student_academic_info sai
        WHERE sai.sid = s.sid
    )
    WHERE EXISTS (
        SELECT 1 FROM student_academic_info sai
        WHERE sai.sid = s.sid
    )";
execute_sql_in_oracle($sql_update_probation);

echo "<p>Grades updated and probation statuses adjusted.</p>";
echo "<form method='post' action='admin.php?sessionid=$sessionid&section_id=$section_id'><input type='submit' value='Go Back'></form>";
?>
