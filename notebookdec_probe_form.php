<?php


require_once($CFG->libdir . "/formslib.php");

class notebookdec_probe_form extends moodleform {
 
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore! 

        //$mform->addElement('select', 'type', 'Type', array('M'=>'Multiple Choice', 'O' => 'Open Ended'));
        //$mform->addRule('type', 'This field is required', 'required');
        
        $mform->addElement('text', 'probe', 'Prompt', 'size="30"');
        $mform->addRule('probe', 'This field is required', 'required');
        
        //$mform->addElement('textarea', 'options', 'Answer Choices', 'wrap="virtual" rows="5" cols="100"');
        
        $range = range(-50,50);
        $options = array_combine($range,$range);
        
        $mform->addElement('select','weight','Weight',$options);
        $mform->setDefault('weight', 0);        //Default value
        
        $this->add_action_buttons(false);
    }                           
}                               
