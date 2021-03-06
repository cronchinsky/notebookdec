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
 * Prints a particular instance of notebookdec
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage notebookdec
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace notebookdec with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // notebookdec instance ID - it should be named as the first character of the module
$sid = optional_param('sid', 0, PARAM_INT); // the session where the notebookdec is being printed from
if ($id) {
    $cm         = get_coursemodule_from_id('notebookdec', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $notebookdec  = $DB->get_record('notebookdec', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $notebookdec  = $DB->get_record('notebookdec', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $notebookdec->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('notebookdec', $notebookdec->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}



require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);


add_to_log($course->id, 'notebookdec', 'view', "view.php?id={$cm->id}", $notebookdec->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/notebookdec/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($notebookdec->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->css('/mod/notebookdec/css/notebookdec.css');

notebookdec_set_display_type($notebookdec);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('notebookdec-'.$somevar);

// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading('Ideas to Take Away notebookdec');
echo "<div id='back-link'><p><a href='" . $CFG->wwwroot . '/mod/notebookdec/session.php?id=' . $sid ."'>Back to notebook</a></p></div>"; 
echo "<p><strong>$notebookdec->name</strong></p>";

$nid = $notebookdec->id;

$sessions = $DB->get_records('notebookdec_sessions', array('nid' => $nid));

if ($sessions) {
  //echo $OUTPUT->heading($notebookdec->name);
  notebookdec_print($notebookdec, $sessions, $USER);
}
else {
  echo $OUTPUT->heading('There are no sessions in this notebook.');
}

// Finish the page
echo $OUTPUT->footer();
