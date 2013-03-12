<?php

// This script uses installed report plugins to print notebookdec reports

    require_once('../../config.php');
    require_once($CFG->dirroot.'/mod/notebookdec/locallib.php');
    //require_once($CFG->dirroot.'/mod/notebookdec/report/reportlib.php');

    $id = optional_param('id',0,PARAM_INT);    // Course Module ID, or
    $n = optional_param('n',0,PARAM_INT);     // notebookdec ID

    $mode = optional_param('mode', '', PARAM_ALPHA);        // Report mode

    if ($id) {
        if (! $cm = get_coursemodule_from_id('notebookdec', $id)) {
            print_error('invalidcoursemodule');
        }

        if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
            print_error('coursemisconf');
        }

        if (! $notebookdec = $DB->get_record('notebookdec', array('id' => $cm->instance))) {
            print_error('invalidcoursemodule');
        }

    } else {
        if (! $notebookdec = $DB->get_record('notebookdec', array('id' => $n))) {
            print_error('invalidnotebookdecid', 'notebookdec');
        }
        if (! $course = $DB->get_record('course', array('id' => $notebookdec->course))) {
            print_error('invalidcourseid');
        }
        if (! $cm = get_coursemodule_from_instance("notebookdec", $notebookdec->id, $course->id)) {
            print_error('invalidcoursemodule');
        }
    }
	
	$url = new moodle_url('/mod/notebookdec/report.php', array('id' => $cm->id));
    if ($mode !== '') {
        $url->param('mode', $mode);
    }
    $PAGE->set_url($url);

    require_login($course, false, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    $PAGE->set_pagelayout('report');
    
    add_to_log($course->id, "notebookdec", "report", "report.php?id=$cm->id", "$notebookdec->id", "$cm->id");

	echo $OUTPUT->header();
	echo $OUTPUT->heading('notebookdec grade report');	
	
	// make some easy ways to access the entries.
	if ( $notebookdec_entries = $DB->get_records("notebookdec_entries", array("notebookdec" => $notebookdec->id))) {
	    foreach ($notebookdec_entries as $entry) {
	        $entrybyuser[$entry->uid] = $entry;
	        $entrybyentry[$entry->id]  = $entry;
	    }
	
	} else {
	    $entrybyuser  = array () ;
	    $entrybyentry = array () ;
	}
	
	// Group mode
	$groupmode = groups_get_activity_groupmode($cm);
	$currentgroup = groups_get_activity_group($cm, true);
	

	add_to_log($course->id, "notebookdec", "view responses", "report.php?id=$cm->id", "$notebookdec->id", $cm->id);
	
	/// Print out the notebookdec entries
	

	if ($currentgroup) {
	    $groups = $currentgroup;
	} else {
	    $groups = '';
	}

	$users = get_users_by_capability($context, 'mod/notebookdec:addentries', '', '', '', '', $groups);
	
	if (!$users) {
		echo $OUTPUT->heading(get_string("nousersyet"));
	
	} else {
	    
	    groups_print_activity_menu($cm, $CFG->wwwroot . "/mod/notebookdec/report.php?id=$cm->id");
	
	    $grades = make_grades_menu($notebookdec->grade);
	    if (!$teachers = get_users_by_capability($context, 'mod/notebookdec:edit')) {
	        print_error('noentriesmanagers', 'journal');
	    }
	
	    $allowedtograde = (groups_get_activity_groupmode($cm) != VISIBLEGROUPS OR groups_is_member($currentgroup));
	
	    /*
if ($allowedtograde) {
	        echo '<form action="report.php" method="post">';
	    }
*/
	
	    if ($usersdone = notebookdec_get_users_done($notebookdec, $currentgroup)) {
	        foreach ($usersdone as $user) {
	            notebookdec_print_user_entry($course, $user, $entrybyuser[$user->id], $teachers, $grades);
	            unset($users[$user->id]);
	        }
	    }
	
	    foreach ($users as $user) {       // Remaining users
	        notebookdec_print_user_entry($course, $user, NULL, $teachers, $grades);
	    }
	
	    /*
if ($allowedtograde) {
	        echo "<center>";
	        echo "<input type=\"hidden\" name=\"id\" value=\"$cm->id\" />";
	        echo "<input type=\"submit\" value=\"".get_string("saveallfeedback", "notebookdec")."\" />";
	        echo "</center>";
	        echo "</form>";
	    }
*/
	}
		

/// Print footer

    echo $OUTPUT->footer();


