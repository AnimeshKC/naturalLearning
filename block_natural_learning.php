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
        
        global $COURSE;
 
        //link to a URL with that displays the data
        $url = new moodle_url('/blocks/natural_learning/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
        $this->content->footer = html_writer::link($url, get_string('View Page', 'block_simplehtml'));
        
        return $this->content;
    }
    
    public function specialization() {
        
    }

    // allow block only on course pages, not main page or in activities etc.
    function applicable_formats() {
        return array('all' => false, 'course-view' => true);
    }

}