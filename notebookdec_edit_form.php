
<?php

require_once($CFG->libdir . "/formslib.php");

class notebookdec_edit_form extends moodleform {
 
    function definition() {
         global $CFG;
        
        $mform =& $this->_form; // Don't forget the underscore! 
        $probes = $this->_customdata['probes'];
        $activities = $this->_customdata['activities'];

        $session = $this->_customdata['session'];
                    
       
          $mform->addElement('header',"session-notebookdec",$session->name);
          $mform->addElement('html', "<div id='notebookdec-img'></div>");
          $mform->addElement('html', '<ol class="notebookdec">');
          $mform->addElement('html',"<li class='ideas-math'><p>Ideas about the <strong>MATH CONTENT</strong> that I want to remember from this session:</h3>");
  		  $mform->addElement('textarea', "text-math", '', 'wrap="virtual" rows="3" cols="100"', array('class'=> 'plans'));
          $mform->addElement('html','</li>');
          
          $mform->addElement('html',"<li class='ideas-students'><p>Reflect on your own experience and learning in one session activity, such as solving math problems,<br /> examining student work, or watching video. What stood out for you? .</p>");
  		  $mform->addElement('textarea', "text-students", '', 'wrap="virtual" rows="3" cols="100"', array('class'=> 'plans'));
          $mform->addElement('html','</li>');
          if (!empty($probes)) {
			  $mform->addElement('html', '<li><p>Fill out the table about the session\'s <strong>formative assessment probes</strong>.</p>');
			  $mform->addElement('html','<table class="probes">');
	  		  $mform->addElement('html',"<tr><th>Probes</th>");
	  		  $mform->addElement('html',"<th>Would you use this probe?</th>");
	  		  $mform->addElement('html',"<th>Optional: Write Plans for Using the Probe</th>");
	  		  $mform->addElement('html','</tr>');
	  		    
	       
	          $index = 1;
	          foreach ($probes as $probe) {
	            notebookdec_add_to_form($probe,$mform, $index, 'probe');
	            $index++;
	          }
	          
	          $mform->addElement('html','</table>');
	          $mform->addElement('html', '</li>');
	      }
          
          if (!empty($activities)) {

	          $mform->addElement('html', '<li><p>Check the <strong>activities/instructional practices</strong> that you would like to try with students.</p>');
	          $mform->addElement('html','<table class="activities">');
	  		  $mform->addElement('html',"<tr><th> Activities</th>");
	  		  $mform->addElement('html',"<th>Would you use this activity?</th>");
	  		  $mform->addElement('html',"<th>Optional: Write Plans for Using the Activity</th>");
	  		  $mform->addElement('html','</tr>');
	          
	           $index = 1;
	           foreach ($activities as $activity) {
	            notebookdec_add_to_form($activity, $mform, $index, 'activity');
	            $index++;
	          }
	                    
	          $mform->addElement('html','</table>');          
	          $mform->addElement('html', '</li>');
	      }
     
          $mform->addElement('html', '<li>');
          $mform->addElement('html',"<div class='closing'><p>Closing thoughts on this session: Choose <u>one</u> prompt and write a few sentences or bullet points.</p>");
		  $mform->addElement('html','<ul>');
		  $session_prompts = array();
	          $session_prompts = explode("|",$session->prompts);

		  foreach ($session_prompts as $session_prompt) {
		  	$mform->addElement('html',"<li class='session-prompts'>$session_prompt</li>");  
		  }	
		  	
		  $mform->addElement('html','</ul>'); 
		   
  		  $mform->addElement('textarea', "text-thoughts", '', 'wrap="virtual" rows="3" cols="100"', array('class'=> 'plans'));
           
          $mform->addElement('html', '</li>');
          
          $mform->addElement('html', '</ol>');
          
          $mform->addElement('checkbox', 'text-submit_session','Ready for facilitators');
         
          $this->add_action_buttons(false, "Save");
 		  /* $buttonarray=array();
		  $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		  $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
		  $buttonarray[] = &$mform->createElement('cancel');
		  $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		  $mform->closeHeaderBefore('buttonar');
		  */

    }                          
}                               
