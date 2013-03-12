<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of a problem in the sort module.
 *
 * @package    mod
 * @subpackage notebookdec
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Add in the classify form.
require_once(dirname(__FILE__) . '/notebookdec_edit_form.php');

// Grab the sid from the url
$id = optional_param('id', 0, PARAM_INT); // session ID
$newSave = optional_param('newSave', 0, PARAM_INT);
$ready = optional_param('ready', 0, PARAM_INT);
$message = '';


// Load the session from the url ID
$session = $DB->get_record('notebookdec_sessions', array('id' => $id));

// If the session is not found, throw an error
if (!$session) {
  error('That session does not exist!');
}

if ($newSave) {
	$message = '<h3>Your notebook session has been saved.</h3>';
}

if ($ready) {
	$message .= '<h3>Your notebook session has been submitted to facilitators.</h3>';
}

// Load the notebookdec activity, course, and cm context from the problem, and up the chain.
$notebookdec = $DB->get_record('notebookdec', array('id' => $session->nid));
$sessions = $DB->get_records('notebookdec_sessions', array('nid' => $notebookdec->id));

$entry = $DB->get_record("notebookdec_entries", array("uid" => $USER->id, "notebookdec" => $notebookdec->id));

$course = $DB->get_record('course', array('id' => $notebookdec->course));
if ($course->id) {
  $cm = get_coursemodule_from_instance('notebookdec', $notebookdec->id, $course->id, false, MUST_EXIST);
}
else {
  error('Could not find the course!');
}

// This is some moodle stuff that seems to be necessary :)
require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

// Log this page view.
add_to_log($course->id, 'notebookdec', 'view', "session.php?id={$cm->id}", $session->name, $cm->id);

/// Print the page header

  $PAGE->set_url('/mod/notebookdec/session.php', array('id' => $session->id));
  $PAGE->set_title(format_string($session->name));
  $PAGE->set_heading(format_string($course->fullname));
  $PAGE->set_context($context);
  $PAGE->add_body_class('notebookdec-session-view');
  $PAGE->set_pagelayout('standard');
  notebookdec_set_display_type($notebookdec);


// Add the necssary CSS and javascript

  $PAGE->requires->css('/mod/notebookdec/css/notebookdec.css');
  $PAGE->requires->js('/mod/notebookdec/scripts/notebookdec.js');


	$probes = $DB->get_records('notebookdec_probes', array('sid' => $session->id));
	$pids = array_keys($probes);
	
	$activities = $DB->get_records('notebookdec_activities', array('sid' => $session->id));
	$aids = array_keys($activities);
	
	
	$prev_probe_responses = array();
	$prev_activity_responses = array();
	$prev_text_responses = array();
	
	if ($pids) {
		$prev_probe_responses = $DB->get_records_select('notebookdec_probe_responses', "uid = $USER->id AND pid IN (" . implode(",",$pids) . ") ");
	} 
	
	if ($aids) {
		$prev_activity_responses = $DB->get_records_select('notebookdec_activity_responses', "uid = $USER->id AND aid IN (" . implode(",",$aids) . ") ");
	} 
		
	$prev_text_responses = $DB->get_records_select('notebookdec_text_responses', "uid = $USER->id AND sid = $session->id");
	
 $mform = new notebookdec_edit_form("/mod/notebookdec/session.php?id={$session->id}", array('probes' => $probes, 'activities' => $activities, 'session' => $session));
 
 if ($responses = $mform->get_data()) {
 
 	notebookdec_debug($prev_text_responses);
 	$timenow = time();
    $newentry->modified = $timenow;
 
 	if ($entry) {
        $newentry->id = $entry->id;
        if (!$DB->update_record("notebookdec_entries", $newentry)) {
            print_error("Could not update your notebook");
        }
        $logaction = "update entry";
        
    } else {
        $newentry->uid = $USER->id;
        $newentry->notebookdec = $notebookdec->id;
        if (!$newentry->id = $DB->insert_record("notebookdec_entries", $newentry)) {
            print_error("Could not insert a new notebookdec entry");
        }
        $logaction = "add entry";
    } 
 
  
  if ($pids) {
  	$DB->delete_records_select('notebookdec_probe_responses',"pid IN (" . implode(",",$pids) . ") AND uid = $USER->id");
  }
  
  if ($aids) {
  	$DB->delete_records_select('notebookdec_activity_responses',"aid IN (" . implode(",",$aids) . ") AND uid = $USER->id");
  }
  
  $DB->delete_records_select('notebookdec_text_responses',"sid = $session->id AND uid = $USER->id");
 
  $form_items = array();
  $form_text = array();
  
  foreach ($responses as $key => $response) {
    
    $exploded_key = explode("-",$key);
    
    $keysize = sizeof($exploded_key);    
    
    if ($keysize == 3) {
  
    	list($table, $field, $item_id) = $exploded_key; 		
    	$form_items[$table][$item_id][$field] = $response;
    	
    }  else if ($keysize == 2) {
    		list($table, $field) = $exploded_key;
    		$form_text[$field] = $response;
    }

   }
   
   foreach  ($form_items as $table => $item_ids) {
   		foreach ($item_ids as $item_id => $fields) {
		      $new_response = new stdClass();

		      if ($table == 'probe') { 
		      	$new_response->pid = $item_id;
		      } else {
		      	$new_response->aid = $item_id;
		      }
		      if (array_key_exists("useradio",$fields))  $new_response->useradio = $fields['useradio'];
			  $new_response->plans = $fields['plans'];
      		  $new_response->uid = $USER->id;

		      $DB->insert_record('notebookdec_' . $table .'_responses',$new_response);
    	}
    
    }
    

      $new_response = new stdClass();
      $new_response->uid = $USER->id;
      $new_response->sid = $id;
      $new_response->math = $form_text['math'];
      $new_response->students = $form_text['students'];
      $new_response->thoughts = $form_text['thoughts'];
      
      if (array_key_exists("submit_session",$form_text)) {
      	$new_response->submit_session = $form_text['submit_session'];      	

		// check if they had previously submitted the session to facilitors
		$prev_text_responses_id = array_shift(array_keys($prev_text_responses));
		$ready_response = $prev_text_responses[$prev_text_responses_id]->submit_session;

      	if (!$ready_response) {
      	  // send a message on reload
      	  $ready = '&ready=1';
      	}
      }
      
      $DB->insert_record('notebookdec_text_responses',$new_response);
  
  echo 'Got data';
  redirect("session.php?id=$id&newSave=1$ready");
}

// set existing data.
$form_data = array();


foreach ($prev_text_responses as $response) {
  
	$form_data['text-math'] = $response->math;  
	$form_data['text-students'] = $response->students; 
	$form_data['text-thoughts'] = $response->thoughts;  
	$form_data['text-submit_session'] = $response->submit_session;  


}

foreach ($prev_probe_responses as $response) {
  
	$form_data['probe-plans-' . $response->pid] = $response->plans; 
	$form_data['probe-useradio-' . $response->pid] = $response->useradio;  

}

foreach ($prev_activity_responses as $response) {
  
	$form_data['activity-plans-' . $response->aid] = $response->plans; 
	$form_data['activity-useradio-' . $response->aid] = $response->useradio;  

}

$mform->set_data($form_data);

 
  // Output starts here
  
echo $OUTPUT->header();
echo $OUTPUT->heading($notebookdec->name);
echo "<div id='directions'><h4>$session->directions</h4></div>";

if ($message) {
	echo "<div class='message'>$message</div>";
}

$mform->display();


echo "<div class='print-notebookdec'>";
echo '<a id="print" href="' . $CFG->wwwroot . '/mod/notebookdec/print.php?n=' . $notebookdec->id . '&amp;sid=' . $session->id .'" onclick="display_confirm(\'' . $CFG->wwwroot . '/mod/notebookdec/print.php?n=' . $notebookdec->id . '&amp;sid=' . $session->id . '\',\'print\'); return false;">Print my notebook</a>';
echo "</div>";

$i = 0;
$num_sessions = count($sessions);
echo "<div class='notebookdec-session-list'>";
echo "<p><strong>My notebook sessions:</strong></p>";
echo "<ul id='session-links'>";
foreach ($sessions as $session) {
	$class = 'session';
	if ($i == 0) {
        $class = $class . ' first';
    } else if ($i == $num_sessions - 1) {
        $class = 'last';
    }
    $i++;
  echo '<li class="'.$class. '"><a href="#" onclick=display_confirm(\''. $CFG->wwwroot . '/mod/notebookdec/session.php?id=' . $session->id . '\');>' . $session->name . '</a>';
  echo '</li>';
}
echo "</ul>";
echo "</div>";

   
// Finish the page
echo $OUTPUT->footer();
