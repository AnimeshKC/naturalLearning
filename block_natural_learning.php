<?php

defined('MOODLE_INTERNAL') || die();



class block_natural_learning extends block_base {
    public function init() {
        $this->title = get_string('natural_learning', 'block_natural_learning');
    }
    public function get_content(){
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content     = new stdClass;
        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }
        else{
            $this->content->text = 'Extracts DB content';
        }
        
        $this->content2     = new stdClass;
        global $COURSE, $DB, $CONTEXT_COURSE;
 
        //link to a URL with that displays the data
        $updateState = 0;
        $url = new moodle_url('/blocks/natural_learning/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id, 'updateState' => $updateState));
        $this->content->footer = html_writer::link($url, get_string('generate', 'natural_learning'));
        
        $naturalLearningTable = 'block_natural_learning';
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if(has_capability('moodle/site:config', $coursecontext)){
            if ($DB->record_exists($naturalLearningTable, array('courseid' => $COURSE->id))){
        $updateState = 1;
        $url2 = new moodle_url('/blocks/natural_learning/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id, 'updateState' => $updateState));
        $this->content->text = html_writer::link($url2, get_string('update', 'natural_learning'));
        }
        }
        
       
        return $this->content;
    }

    public function specialization() {
        
    }

    // allow block only on course pages, not main page or in activities etc.
    function applicable_formats() {
        return array('all' => false, 'course-view' => true);
    }

}