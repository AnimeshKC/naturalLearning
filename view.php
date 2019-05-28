<?php

//debugging purposes
/*ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);*/

global $DB; 
require_once('../../config.php');
require_once('natural_learning_form.php');

$updateState = required_param('updateState', PARAM_INT);



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

//echo ($course->fullname);
$courseName = $course->fullname;
//echo($courseName);
$courseContext = context_course::instance($courseid)->id;
//echo("<br>Course Context ID is: <br>");
//echo($courseContext);
$files = $DB->get_records('files', array('component' => 'course', 'contextid'=> $courseContext));
foreach($files as $element){
    $checkFile = $element->filepath.$element->filename;
    //echo($checkFile);
    //echo("<br>");
    if (preg_match("(unit([0-9]{2})\.xhtml)",$checkFile) == 1 && strpos($checkFile,'backup')== false){
        //echo("<br>");
        $endURL = "localhost/moodle/pluginfile.php/id/content/itemid".$checkFile;
        //echo($endURL);
        array_push($urlArray,$endURL);
    }
}





function extractData($array, $contextid, $courseName){
    
    $dataArray = array();
    //var_dump($array);
    if (sizeof($array) > 0){
        foreach ($array as $urlValue){
        
        
        $fs = get_file_storage();
        $parts = explode("/",$urlValue);
        //print_r($parts);
        $component = "course"; 
        $filearea = "legacy";
        $itemid = 0; 
        $a=0;
        $filepath = '';
        //echo(sizeof($parts));
            
        for ($a=(sizeof($parts)-3);$a<(sizeof($parts)-1);$a++)
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
    } else if (strpos(strtolower($courseName),'physics 200')!== false){
        $fileStr = file_get_contents('physics.txt');
        array_push($dataArray, $fileStr);
    }
    return $dataArray;
}
        $dataArray = extractData($urlArray, $courseContext, $courseName);
        $courseFile = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $courseName);
        $courseFile = mb_ereg_replace("([\.]{2,})", '', $courseFile);
        $courseFile = $courseFile.'.json';
        //var_dump($courseFile);
        //if the data is not to be updated
        if ($updateState == 0 and file_exists($courseFile)){
            $jsonString = file_get_contents($courseFile);
            //echo('File does exist');
            $result = json_decode($jsonString);
        }
        else {
            $fp = fopen('dataArray.json', 'w');
        fwrite($fp, json_encode($dataArray));
        fclose($fp);
        $output = exec('python3 parseData.py');
        //var_dump($output);
        $fp2 = fopen($courseFile, 'w');
        fwrite($fp2,$output);
        fclose($fp2);
        $result = json_decode($output);
        }
        $dataString = implode(" ",$dataArray);
        $naturalLearningTable = 'block_natural_learning';
        $dataObj = new stdClass();
        $dataObj->id = $courseid;
        $dataObj->courseid = $courseid;
        $dataObj->data_uuid = uniqid($more_entropy = TRUE);
        //var_dump($dataObj);
        if ($DB->record_exists($naturalLearningTable, array('courseid' => $courseid))){
            $courseDB = $DB->get_record('block_natural_learning', array('courseid' => $courseid));
            $dataObj->id = $courseDB->id;
            if($updateState == 1){
                $DB->update_record($naturalLearningTable,$dataObj);
            }
        } else {
            $DB->insert_record($naturalLearningTable,$dataObj);
        }
        /*$dataObj = new stdClass();
        //$dataObj->id = $courseid;
        $dataObj->data_uuid = uniqid();
        //var_dump($dataObj);
        $naturalLearningTable = 'block_natural_learning';
        $DB->insert_record($naturalLearningTable,$dataObj);*/
        
        $courseDB = $DB->get_record('block_natural_learning', array('id' => $courseid));    
        //var_dump($courseDB);
        
    preg_match_all("/<p>(.*?)<\/p>/", $dataString, $B);
    $paragraphArray = $B[1];
    //echo "<br> <br> <br>";
    //print_r($paragraphArray);
    //$output = exec('C:\Users\Animesh KC\AppData\Local\Programs\Python\Python36-32\pythonw.exe simpleJSON.py
  
preg_match_all("{(<table([\s\S]*?)<\/table>)}", $dataString, $C);
//echo('Print Tables');
$tableArray = $C[1];
//print_r($tableArray);

preg_match_all("{(<ol>([\s\S]*?)</ol>)}", $dataString, $D);
//echo ('<br> Print Math');
$mathArray = $D[1];
//echo('<br> Now check tables and math');
$tableMathArray = array_merge($tableArray,$mathArray);
//print_r($tableMathArray);
    
//var_dump($tableMathArray);
    //print_r($result);
    
    //Array with the words and their frequencies
    $wordArray = $result[0];
    
    
    //print_r($contentArray);
    
    //Array with the connections between the words and their distances
    $connectionsArray = $result[1];
    //Word to start off the visualizaion; most frequent word
    $initPrimaryWord = $wordArray[0][1];
    
    $maxDist = $connectionsArray[0][2];
    //test minDist: 1
    //var_dump($maxDist);
    //echo $minDist;
    
    $endArray = end($connectionsArray);
    $minDist = $endArray[2];
    //var_dump($minDist);

    //test maxDist: 43
    //echo $maxDist;
    
    $contentArray = array(); 
    
        function appendData($paragraphArray, $tableMathArray, $word){
        $array1 = array();
        $array2 = array();
        
        foreach($paragraphArray as $element){
            if (strpos(strtolower($element),$word)!== false){
                array_push($array1, $element);
            }
        }
        
        foreach($tableMathArray as $element){
            if (strpos(strtolower($element),$word)!== false){
                array_push($array2, $element);
            
        }
    }
        
        return $finalArray = array($word,$array1, $array2);
    }
    
    foreach($wordArray as $element){
        //print_r($element[1]);
        $finalArray = appendData($paragraphArray, $tableMathArray, $element[1]);
        array_push($contentArray, $finalArray);

    }
    
    //var_dump($contentArray);
    /*print_r($wordArray);
    echo "<br>";
    echo "<br>";
    print_r($connectionsArray);
    echo "<br>";
    echo "<br>";
    //echo $initPrimaryWord; */
    
    $htmlhead = '<!DOCTYPE html> 
    <html> <head> <meta charset="utf-8">
    <title> Node Visualization</title>
    
    <style  type = "text/css">
.active {
  stroke: #000;
  stroke-width: 2px;
}

.link {
  stroke: #777;
  stroke-opacity: 0.3;
  stroke-width: 1.5px;
}

div {
  border: solid 1px #000;
  background: #eee;
  text-align: center;
  margin: 10px;
  padding: 10px;
}

.node circle {
  fill: #ccc;
  stroke: #000;
  stroke-width: 1.5px;
}


</style>
    </head>
    <body>
    <h1>Node Visualization</h1>
    <canvas id="network" width = "800" height = "500"></canvas>
    <script src="javascript/d3/d3.js"></script>
    <script src="javascript/main.js?newversion"></script>';
    

    
    $htmlfoot = '</body></html>';

    $out = array(
    'words'          => $wordArray,
    'connections'         => $connectionsArray,
    'primaryWord'          => $initPrimaryWord,
    'minimumDist'    => $minDist,
    'maximumDist' => $maxDist,
    'contentArray'=> $contentArray,
);
    //var_dump($out);
    echo '<script> var incoming = '.json_encode($out).';</script>';
    echo $htmlhead;
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
    

//http://sigmajs.org/
//http://js.cytoscape.org/


//https://apsce.for-more.biz/progress

    


    
