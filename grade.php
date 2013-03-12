<?php

require_once("../../config.php");

$id   = required_param('id', PARAM_INT);          // Course module ID

$PAGE->set_url('/mod/notebookdec/grade.php', array('id'=>$id));
if (! $cm = get_coursemodule_from_id('notebookdec', $id)) {
    print_error('invalidcoursemodule');
}

if (! $notebookdec = $DB->get_record("notebookdec", array("id"=>$cm->instance))) {
    print_error('invalidid', 'notebookdec');
}

if (! $course = $DB->get_record("course", array("id"=>$notebookdec->course))) {
    print_error('coursemisconf', 'notebookdec');
}

require_login($course, false, $cm);

if (has_capability('mod/notebookdec:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
    redirect('report.php?id='.$cm->id);
} else {
    redirect('view.php?id='.$cm->id);
}