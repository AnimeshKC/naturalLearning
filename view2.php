<?php
global $DB; 
require_once('../../config.php');
$courseid = required_param('courseid', PARAM_INT);
if (! $course = $DB->get_record('course', array("id" => $courseid))) {
    print_error(get_string('invalidcourse', 'naturalLearning', $courseid));
}
$sql = "select * from {modules} m "
        ."join {course_modules} cm on m.id = cm.module "
        ."where cm.course = ".$courseid;





print_r($course);
echo "<br>\n";
echo "<br>\n";


$recordset = $DB->get_records_sql($sql);

foreach($recordset as $id => $record){
    print_r($record);
    print("Activity type: ".$record->name."'<br>\n'");
    echo "**** <br>\n";
    $sql_d = "select * from {".$record->name."} r where r.course = ".$courseid
            ." and r.id = ".$record->instance;
    $detail = $DB->get_record_sql($sql_d);
    if (!($record->name == "url" or $record->name == "assign"
            or $record->name == "label" ))
            print_r($detail);
    echo "<br>\n";

    if ($record->name == "url")  echo "URL: ".$detail->externalurl."<br>";
    echo "Resource name: ".$detail->name;
    echo "<br>nIntro: <table border=2><tr><td>"
        .$detail->intro."</td><tr></table>\n";
    echo "<br>/n";  
}

