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
 * Renderer
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/culrollover/classes/forms/default_options_form.php');
require_once($CFG->dirroot . '/local/culrollover/classes/forms/select_courses_form.php');
require_once($CFG->dirroot . '/local/culrollover/classes/forms/review_options_form.php');

class local_culrollover_renderer extends plugin_renderer_base {

    public function get_rollover_summaries() {
        global $DB, $USER;

        $userid = $USER->id;
        $table = '';
        $users = array();

        if(has_capability('moodle/site:config', context_system::instance())) {
             // Get date one year ago.
            $timestamp = strtotime('-1 months', time());
            $where = "datesubmitted >= $timestamp";
        } else {
            $where = "userid = $userid";
        }

        // Get record set.
        $requests = $DB->get_recordset_sql("SELECT 
                                                r.*, 
                                                c1.shortname as srcshortname, 
                                                c1.fullname as srcfullname, 
                                                c1.shortname as dstshortname, 
                                                c1.fullname as dstfullname,
                                                u.firstname,
                                                u.lastname,
                                                u.alternatename
                                            FROM {cul_rollover} r 
                                            JOIN {course} c1 ON c1.id = r.sourceid
                                            JOIN {course} c2 ON c2.id = r.destid
                                            JOIN {user} u ON u.id = r.userid
                                            WHERE $where");

        if (!empty($requests)) {
            $table .= html_writer::start_tag('table', array('id' => 'previous', 'class' => 'dataTable'));
            $table .= html_writer::start_tag('thead');
            $headers = array(
                    'id',
                    't',
                    'firstname',
                    'lastname',
                    'alternatename',
                    get_string('date', 'local_culrollover'),
                    get_string('source_header', 'local_culrollover'),
                    get_string('dest_header', 'local_culrollover'),
                    get_string('type_header', 'local_culrollover'),
                    get_string('status', 'local_culrollover'),
                    get_string('user','local_culrollover'),
                    '',
                    ''
                );

            foreach ($headers as $header) {
                $table .= html_writer::tag('th', $header, array('class' => $header));
            }

            $table .= html_writer::end_tag('thead');
            // If JS is enabled then body is rendered using ajax.
            $table .= html_writer::start_tag('tbody');
            $rows = array();

            foreach($requests as $request) {
                $cells = array();
                $cells[] = $request->id;
                $cells[] = $request->schedule;
                $cells[] = $request->firstname;
                $cells[] = $request->lastname;
                $cells[] = $request->alternatename;
                $cells[] = date('d/M/Y', $request->schedule);
                $cells[] = self::format_srccoursename($request->srcshortname, $request);
                $cells[] = self::format_dstcoursename($request->dstshortname, $request);
                $cells[] = self::format_type($request->type, $request);
                $cells[] = self::format_status($request->status, $request);
                $cells[] = self::format_user($request->userid, $users);
                $cells[] = self::format_delete($request->id, $request);
                $cells[] = self::format_repeat($request->id, $request);                
                $rows[] = $cells;
            } 

            foreach ($rows as $cells) {
                $table .= html_writer::start_tag('tr');

                foreach ($cells as $header => $cell) {
                    $table .= html_writer::tag('td', $cell);
                }
                $table .= html_writer::end_tag('tr');
            }

            $table .= html_writer::end_tag('tbody');
            $table .= html_writer::end_tag('table');
        } else {
            $table .= get_string('norequests','local_culrollover');
        }

        $isEmptyTable = get_string('norequests','local_culrollover');

        // Add the JS to make the table sortable.
        $this->page->requires->js_call_amd('local_culrollover/rollovertable', 'initialise', array($isEmptyTable));
        return $table;
    }

    // public static function format_coursename($d, $row) {
    //     global $DB;
        
    //     $url = new moodle_url('/course/view.php', array('id' => $d));
    //     $course = $DB->get_record('course', array('id' => $d));
    //     return html_writer::link($url, $course->shortname);
    // }

    public static function format_srccoursename($d, $row) {
        global $DB;
        
        $url = new moodle_url('/course/view.php', array('id' => $row->sourceid));
        return html_writer::link($url, $d);
    }

    public static function format_dstcoursename($d, $row) {
        global $DB;
        
        $url = new moodle_url('/course/view.php', array('id' => $row->destid));
        return html_writer::link($url, $d);
    }

    public static function format_type($d, $row) {
        global $DB;
        // Construct tooltip text.
        switch ($row->merge) {
            case 0:
                $merge = 'merge | ';
                break;
            case 1:
                $merge = 'delete first | ';
                break;
        }

        switch ($row->merge) {
            case 0:
                $groups = 'no groups | ';
                break;
            case 1:
                $groups = 'groups | ';
                break;
        }

        $allroles = $DB->get_records('role');
        $rolestocopy = 'roles to copy: ';
        $roles = array();            
        $enrolments = $row->enrolments;

        if(!$enrolments) {
            $rolestocopy .= 'none | ';
        } else {
            // Loop through and get roles.
            $enrolments = explode(',', $enrolments);

            foreach ($enrolments as $role) {
                $roles[] = $allroles[$role]->shortname;
            }

            $rolestocopy .= implode(', ', $roles) . ' | ';
        }

        switch ($row->visible) {
            case 0:
                $visible = 'hide | ';
                break;
            case 1:
                $visible = 'make visible | ';
                break;
            case 2:
                $visible = 'visible on ' . date('d/M/Y', $row->visibledate) . ' | ';
                break;
        }

        $rolestonotify = 'roles to notify: ';
        $roles = array();
        $notifies = $row->notify;

        if (!$notifies) {
            $rolestonotify .= 'none';
        } else {
            // Loop through and get roles.
            $notifies  = explode (',', $notifies);

            foreach ($notifies as $role) {
                $roles[] = $allroles[$role]->shortname;
            }

            $rolestonotify .= implode(', ', $roles);
        }

        $tooltip = 'options {' . $merge . $groups . $rolestocopy . $visible . $rolestonotify . '}';
        // end tooltip

        switch ($d) {
            case 0:
                $type = get_string('copy_everything', 'local_culrollover');
                break;
            case 1:
                $type = get_string('copy_content', 'local_culrollover');
                break;
            default:
                $type = get_string('copy_unknown', 'local_culrollover');
        }

        return "<span title=\"$tooltip\">$type</span>";
    }

    public static function format_status($d, $row) {
        if($d == 'Complete') {
            $compDate = date('d/M/Y H:i', $row->completiondate);
        } else {
            $compDate = date('d/M/Y', $row->schedule);
        }

        return "{$d} ($compDate)";
    }

    public static function format_user($d, &$users = array()) {
        global $DB;

        if(!isset($users[$d])) {
            $user = $DB->get_record('user', array('id' => $d));
            $users[$d] = $user;
        } else {
            $user = $users[$d];
        }

        $url = new moodle_url('/user/profile.php', array('id' => $d));
        
        return html_writer::link($url, fullname($user));
    }

    public static function format_delete($d, $row) {

        if( has_capability('moodle/site:config', context_system::instance()) || ($row->status == 'Pending') ) {
            $url = new moodle_url('', array('delentry' => $d));
            $delete = html_writer::link(
                $url,
                '<i class="icon-large icon-remove"></i>',
                array(
                    'class' => 'DeleteRow',
                    'title' => 'Remove from Queue',
                    'onclick' => "return confirm('Are you sure you want to delete this entry?')"
                    )
                );
        } else {
            $delete = "&nbsp;";
        }

        return $delete;
    }

    public static function format_repeat($d, $row) {

        if( has_capability('moodle/site:config', context_system::instance()) || ($row->status == 'Failure') ) {
            $url = new moodle_url('', array('repentry' => $d));
            $repeat = html_writer::link(
                $url,
                '<i class="icon-large icon-repeat"></i>',
                array(
                    'class' => 'RepeatRow',
                    'title' => 'Reschedule rollover',
                    'onclick' => "return confirm('Are you sure you want to run this rollover again?')" // TODO lang string
                    )
                );
        } else {
            $repeat = "&nbsp;";
        }

        return $repeat;
    }    

    public function get_default_options_form() {
        $defaultoptionsform = new default_options_form();
        return $defaultoptionsform->render();
    }

    public function get_select_courses_form() {
        $defaultoptionsform = new default_options_form(null, $_POST, 'post', '', array('id' => 'default_options_form'));

        if($defaultoptionsform->is_submitted()) {
            $choices = $defaultoptionsform->get_data();
            // TODO if not valid
        } else {

        }

        if(isset($choices->defaultroles)) {
            $choices->defaultroles = join(',', $choices->defaultroles);
        } else {
            $choices->defaultroles = NULL;
        }
        
        $choices = (array)$choices;
        $message = get_string('selectcoursesmessage', 'local_culrollover');
        $selectcoursesform = new select_courses_form(null, $choices, 'post', '', array('id' => 'select_courses_form'));
        
        $form = $selectcoursesform->render();

        return array($message, $form);
    }

    public function add_courses() {
        $message = get_string('selectcoursesmessage', 'local_culrollover');        
        $selectcoursesform = new select_courses_form(null, $_POST, 'post', '', array('id' => 'select_courses_form'));
        $form = $selectcoursesform->render();

        return array($message, $form);
    }

    public function get_review_options_form() {
        $selectcoursesform = new select_courses_form(null, $_POST, 'post', '', array('id' => 'select_courses_form'));

        if($selectcoursesform->is_submitted()) {
            $choices = $selectcoursesform->get_data();
            $choices = (array)$choices;
        } else {
            // TODO
        }

        $message = get_string('reviewmessage', 'local_culrollover');       
        $reviewoptionsform = new review_options_form(null, $choices, 'post', '', array('id' => 'review_options_form'));
        $form = $reviewoptionsform->render();

        return array($message, $form);
    }

    public function process_review_options_form() {
        global $USER, $DB;

        $reviewoptionsform = new review_options_form(null, $_POST, 'post', '', array('id' => 'review_options_form'));

        if($reviewoptionsform->is_submitted()) {
            $choices = $reviewoptionsform->get_data();
        } else {
            // TODO
        }

        $i = $choices->rolloverrepeats - 1;

        while($i >= 0) {
            if(isset($choices->source[$i]) && !empty($choices->source[$i])) {
                $rollover = new stdClass();
                $rollover->sourceid = $choices->source[$i];
                $rollover->destid = str_replace('\\', '', $choices->dest[$i]);
                $rollover->userid = $USER->id;
                $rollover->datesubmitted = time();
                $rollover->status = 'Pending';
                $scheduledatelength = strlen((string)$choices->migrateondate[$i]);

                if($scheduledatelength > 10) {
                    $rollover->schedule = $choices->migrateondate[$i] / 1000;
                } else{
                    $rollover->schedule = $choices->migrateondate[$i];
                }

                if(isset($choices->rolestoinform)) {
                    $notifies = implode(',', $choices->rolestoinform);
                } else {
                    $notifies = null;
                }

                $rollover->type = $choices->what[$i];
                $rollover->merge = $choices->merge[$i];
                $rollover->groups = $choices->groups[$i];
                $rollover->enrolments = $choices->roles[$i];
                $rollover->notify = $notifies;
                $rollover->visible = $choices->visible[$i];
                $visibleondatelength = strlen((string)$choices->visibleondate[$i]);

                if($visibleondatelength > 10){
                    $rollover->visibledate = $choices->visibleondate[$i] / 1000;
                } else {
                    $rollover->visibledate = $choices->visibleondate[$i];
                }

                $rollover->completiondate = '';
                $DB->insert_record('cul_rollover', $rollover);
            }

            $i--;
        }

        return true;
    }
}