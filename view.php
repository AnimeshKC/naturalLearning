<?php
global $DB; 
require_once('../../config.php');
require_once('natural_learning_form.php');




$courseid = required_param('courseid', PARAM_INT);
if (! $course = $DB->get_record('course', array("id" => $courseid))) {
    print_error(get_string('invalidcourse', 'naturalLearning', $courseid));
}

require_login($course);
$natural_learning = new natural_learning_form();

$sql = "select * from {modules} m "
        ."join {course_modules} cm on m.id = cm.module "
        ."where cm.course = ".$courseid;

$recordset = $DB->get_records_sql($sql);
$urlArray = array();
$checkString = "complete.html";
$string = '';
foreach($recordset as $id => $record){
    $sql_d = "select * from {".$record->name."} r where r.course = ".$courseid
            ." and r.id = ".$record->instance;
    $detail = $DB->get_record_sql($sql_d);
    $string = $string." ".$detail->intro;
    if ($record->name == "url"){
        $urlString = "".$detail->externalurl;
        if (strpos($urlString,$checkString) !== false) {
            array_push($urlArray,$urlString);
        }
    }  
}

$context = context_course::instance($courseid);

$contextid_value = $context->id;

function extractData($array, $contextid){
    
    $dataArray = array();
    foreach ($array as $urlValue){
        
        $fs = get_file_storage();
        $parts = explode("/",$urlValue);
        $component = "course"; 
        $filearea = "legacy";
        $itemid = 0; 
        $a=0;
        $filepath = '';
        for ($a=6;$a<(sizeof($parts)-1);$a++)
            $filepath .= "/".$parts[$a]; 
        $filepath .= "/"; // add trailing delimiter
        $filename = $parts[$a];  
        $file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
        
        if ($file){
            $contents = $file->get_content();
            array_push($dataArray, $contents);
        } else{
            echo 'There is no file';
            echo "<br>";
        }
        
    }
    
    $fp = fopen('dataArray.json', 'w');
    fwrite($fp, json_encode($dataArray));
    fclose($fp);
    
    //print_r($dataArray);

    //$output = exec('C:\Users\Animesh KC\AppData\Local\Programs\Python\Python36-32\pythonw.exe simpleJSON.py
      
    $output = exec('pythonw.exe parseData.py');
    $result = json_decode($output);
    
    //print_r($result);
    
    //Array with the words and their frequencies
    $wordArray = $result[0];
    //Array with the connections between the words and their distances
    $connectionsArray = $result[1];
    //Word to start off the visualizaion; most frequent word
    $initPrimaryWord = $wordArray[0][1];
    
    $minDist = $connectionsArray[0][2];
    //test minDist: 1
    //echo $minDist;
    
    $endArray = end($connectionsArray);
    $maxDist = $endArray[2];
    //test maxDist: 43
    //echo $maxDist;
    
    print_r($wordArray);
    echo "<br>";
    echo "<br>";
    print_r($connectionsArray);
    echo "<br>";
    echo "<br>";
    //echo $initPrimaryWord;
    
    
    $htmlhead = '<!DOCTYPE html> 
    <html> <head> <meta charset="utf-8">
    <title> Node Visualization</title>;
    </head>
    <body>
    <h1>Node Visualization</h1>
    <canvas id="network" width = "500" height = "500"></canvas>
    <script src="https://d3js.org/d3.v5.js </script>
    <script src="javascript/main.js"></script>';

    echo $htmlhead;
    
    $htmlfoot = '</body></html>';

    $out = array(
    'words'          => $wordArray,
    'connections'         => $connectionsArray,
    'primaryWord'          => $initPrimaryWord,
    'minimumDist'    => $minDist,
    'maximumDist' => $maxDist,
);

    echo '<script> var incoming = '.json_encode($out).';</script>';
    echo $htmlfoot;
    //print_r($wordArray);
    //$printArray = traverseArray($result);
    
    //echo gettype($outputArray);
    //print_r($result);
    

    /*$dataArray = Array('A', 'B', 'C');
    //print_r ($testArray);
    
    $result = shell_exec('C:\\Users\\Animesh KC\\AppData\\Local\\Programs\\Python\\Python36-32\\python.exe C:\\wamp64\\www\\moodle\\blocks\\natural_learning\\parseData.py '. escapeshellarg(json_encode($testArray)).' 2>&1');
    $resultData = json_decode($result);
    //print_r($resultData); 
    var_dump($resultData);*/
    
}

//http://sigmajs.org/
//http://js.cytoscape.org/




    

extractData($urlArray, $contextid_value);

    
