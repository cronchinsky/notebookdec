<?php

require_once($CFG->libdir . "/formslib.php");

class notebookdec_edit_session_form extends moodleform {
 
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore!
       // $session = $this->_customdata['session'];

        $mform->addElement('text', 'name', get_string('session_name', 'notebookdec'));
        $mform->addRule('name', 'This field is required', 'required');
        $mform->addElement('textarea', 'directions', get_string('directions', 'notebookdec'),'wrap="virtual" rows="3" cols="65"');
        $mform->addElement('textarea', 'prompts', get_string('session_prompts', 'notebookdec'),'wrap="virtual" rows="3" cols="65"');
		$mform->setDefault('prompts', 'Before I thought..., now I think... | I am wondering... | I was surprised... | I felt affirmed by...');
          
        $this->add_action_buttons();
    }                           
}                               