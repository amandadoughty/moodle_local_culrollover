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
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/culrollover/renderer.php');

global $USER, $DB, $COURSE, $CFG;

$context = context_system::instance();
require_login();
require_capability('local/culrollover:view', $context, $USER);

$PAGE->set_context($context);
$title = get_string('title', 'local_culrollover');
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/culrollover/index.php');
$PAGE->set_pagetype('mod-local_culrollover');
$PAGE->navbar->add(get_string('breadcrumb', 'local_culrollover'), new moodle_url('/local/culrollover/'));
// I have not been able to convert x-editable into an amd module.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jqueryui-editable/js/jqueryui-editable.min.js'), true);
$PAGE->requires->css(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jqueryui-editable/css/jqueryui-editable.css'));
$PAGE->requires->css(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet'));

$PAGE->requires->js('/local/culrollover/js/cul_rollover.js', true);
$PAGE->requires->js('/local/culrollover/js/common.js', false);
$PAGE->requires->css('/local/culrollover/css/style.css');

$step = optional_param('step', 0, PARAM_INT);
$cancel = optional_param('cancel', 0, PARAM_INT);
$delentry = optional_param('delentry', 0, PARAM_INT);
$repentry = optional_param('repentry', 0, PARAM_INT);

if($cancel) {
    redirect($CFG->wwwroot . '/local/culrollover/');
}

$output = '';
$deleted = '';
$repeated = '';
$table = '';
$form = '';
$message = '';

if($delentry) {
    if(has_capability('local/culrollover:view', context_user::instance($USER->id))) {
        $record = $DB->get_record('cul_rollover', array('id' => $delentry));
        // Must be owner of record or sys admin to delete.
        if ($record) {
            if (has_capability('moodle/site:config', context_system::instance()) || ($USER->id == $record->userid)) {
                $DB->delete_records('cul_rollover', array('id' => $delentry));
                $cuser = $DB->get_record('user', array('id' => $record->userid));
                $cfullname = fullname($cuser);
                $date = date('d/M/Y', $record->datesubmitted);
                $sourcecourse = $DB->get_record('course', array('id' => $record->sourceid));
                $sourcecoursename = $sourcecourse->shortname;
                $destcourse = $DB->get_record('course', array('id' => $record->destid));
                $destcoursename = $destcourse->shortname;
                $dfullname = fullname($USER);
                $logstr = "Deleted rollover (id= $delentry) for $cfullname (submitted: $date $sourcecoursename -> $destcoursename). Record deleted by $dfullname.";

                $params = array(
                    'context' => $PAGE->context,
                    'objectid' => $record->id,
                    'relateduserid' => $record->userid,
                    'other' => array(
                        'logstr' => $logstr
                        )
                );

                $event = \local_culrollover\event\rollover_deleted::create($params);
                $record->id = $record->id;
                $event->add_record_snapshot('cul_rollover', $record);
                $event->trigger();

                $deleted .= html_writer::tag('p', $logstr);
            } else {
                $deleted .= html_writer::tag('p', 'Sorry, you do not have permission to do that!');
            }
        } else {
            $deleted .= html_writer::tag('p', 'Sorry, record does not exist!');
        }
    }
}

if($repentry) {
    if(has_capability('local/culrollover:view', context_user::instance($USER->id))) {
        $record = $DB->get_record('cul_rollover', array('id' => $repentry));
        // Must be owner of record or sys admin to repeat.
        if ($record) {
            if (has_capability('moodle/site:config', context_system::instance()) || ($USER->id == $record->userid)) {                
                unset($record->id);
                $record->status = 'Pending';
                $record->datesubmitted = time();
                $record->schedule = time();
                $record->completiondate = '';
                $DB->insert_record('cul_rollover', $record);

                $cuser = $DB->get_record('user', array('id' => $record->userid));
                $cfullname = fullname($cuser);
                $date = date('d/M/Y', $record->datesubmitted);
                $sourcecourse = $DB->get_record('course', array('id' => $record->sourceid));
                $sourcecoursename = $sourcecourse->shortname;
                $destcourse = $DB->get_record('course', array('id' => $record->destid));
                $destcoursename = $destcourse->shortname;
                $dfullname = fullname($USER);
                $logstr = "Repeated rollover (id= $repentry) for $cfullname (submitted: $date $sourcecoursename -> $destcoursename). Rollover repeated by $dfullname.";
                $repeated .= html_writer::tag('p', $logstr);
            } else {
                $repeated .= html_writer::tag('p', 'Sorry, you do not have permission to do that!');
            }
        } else {
            $repeated .= html_writer::tag('p', 'Sorry, record does not exist!');
        }
    }
}

$renderer = $PAGE->get_renderer('local_culrollover');

if(!$step) {    
    $table = $renderer->get_rollover_summaries();
    $form = $renderer->get_default_options_form();    
} elseif($step == MIGRATION_INITIAL_CHOICES) {

    list($message, $form) = $renderer->get_select_courses_form();
} elseif($step == MIGRATION_COURSE_CHOICES) {
    // $PAGE->requires->js_call_amd('local_culrollover/reviewsettings', 'initialise');

    if (isset($_POST['rolloveraddfields'])) {
        list($message, $form) = $renderer->add_courses();
    } else { 
        list($message, $form) = $renderer->get_review_options_form();
    }
} elseif($step == MIGRATION_REVIEW_CHOICES) {
    $result = $renderer->process_review_options_form();
    
    if ($result) {
        redirect('index.php');
    }
}

$output .= html_writer::tag('h3', get_string('existingrecords', 'local_culrollover'));
$output .= html_writer::start_tag('div', array('id' => 'existing-records'));
$output .= $deleted;
$output .= $repeated;
$output .= $table;
$output .= html_writer::tag('h3', get_string('addformigration', 'local_culrollover'));
$output .= html_writer::start_tag('div', array('id' => 'bottom-stuff'));
$output .= html_writer::start_tag('div');
$output .= $message;
$output .= $form;
$output .= html_writer::end_tag('div');
$output .= html_writer::end_tag('div');

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
?>