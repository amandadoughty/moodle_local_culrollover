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
 * Step 2: select_courses_form
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/culrollover/constants.php');

class select_courses_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE;

        // Some differences in SQL syntax.
        if ($DB->sql_regex_supported()) {
            $REGEXP    = $DB->sql_regex(true);
            $NOTREGEXP = $DB->sql_regex(false);
        }

        $mform =& $this->_form;
        $mform->addElement('header', 'selectcourses', get_string('selectcourses_header', 'local_culrollover'));
        $mform->setExpanded('selectcourses', true);
        $courses = 1;
        $srcregex = $CFG->src_filter_regex_term;
        $dstregex = $CFG->dest_filter_regex_term;       
        $srccourses = array();
        $dstcourses = array();
        $srcfilter = "WHERE shortname $REGEXP '$srcregex'";
        $srccourses = self::culrollover_get_user_capability_course($srcfilter);
        $dstfilter = "WHERE shortname $REGEXP '$dstregex'";
        $dstcourses = self::culrollover_get_user_capability_course($dstfilter);
        $pending = self::culrollover_get_pending_course();
        $emptyoption = array('' => '');
        $srcoptions = array();
        $dstoptions = array();

        if(!empty($srccourses) && !empty($dstcourses)) {
            foreach($srccourses as $srccourse) {
                $srcoptions[$srccourse->id] = addslashes($srccourse->shortname);
            }

            $srcoptions = $emptyoption + $srcoptions;

            foreach($dstcourses as $dstcourse) {
                $dstoptions[$dstcourse->id]['text'] = addslashes($dstcourse->shortname);
                $dstoptions[$dstcourse->id]['value'] = $dstcourse->id;

                // Check if pending already and disable if so.
                if (in_array($dstcourse->id, $pending)) {
                    $dstoptions[$dstcourse->id]['attributes'] = 'disabled';
                } else {
                    $dstoptions[$dstcourse->id]['attributes'] = '';
                }
            }
        } else {
            throw new moodle_exception('nocoursestomigrate', 'local_culrollover', '/local/culrollover');
        }

        $repeatarray = array();

        $attributes = array(
            'placeholder' => get_string('source_courses','local_culrollover'),
            'class' => 'source_select'          
            );

        $label = get_string('selectsourcecourses', 'local_culrollover');
        $srcselect = $mform->createElement('select', 'source', $label, $srcoptions, $attributes);
        $repeatarray[] = $srcselect;

        $attributes = array(
            'placeholder' => get_string('destination_courses','local_culrollover'),
            'class' => 'dest_select'
            );

        $label = get_string('selectdestinationcourses', 'local_culrollover');
        $dstselect = $mform->createElement('select', 'dest', $label, array(), $attributes);
        $dstselect->addOption('', '');

        foreach($dstoptions as $option){
            // Need to use addOption to be able to include option attributes.
            $dstselect->addOption($option['text'], $option['value'], $option['attributes']);
        }

        $repeatarray[] = $dstselect;

        $element = $mform->createElement('hidden', 'when', $this->_customdata['defaultwhen']);
        $mform->setType('when', PARAM_INT);
        $repeatarray[] = $element;

        $element = $mform->createElement('hidden', 'migrateondate', $this->_customdata['defaultmigrateondate']);
        $mform->setType('migrateondate', PARAM_INT);
        $repeatarray[] = $element;            

        $element = $mform->createElement('hidden', 'what', $this->_customdata['defaultwhat']);
        $mform->setType('what', PARAM_INT);
        $repeatarray[] = $element;

        $element = $mform->createElement('hidden', 'merge', $this->_customdata['defaultmerge']);
        $mform->setType('merge', PARAM_INT);
        $repeatarray[] = $element;

        $element = $mform->createElement('hidden', 'groups', $this->_customdata['defaultgroups']);
        $mform->setType('groups', PARAM_INT);
        $repeatarray[] = $element;

        $element = $mform->createElement('hidden', 'roles', $this->_customdata['defaultroles']);
        $mform->setType('roles', PARAM_TEXT);
        $repeatarray[] = $element;

        $element = $mform->createElement('hidden', 'visible', $this->_customdata['defaultvisible']);
        $mform->setType('visible', PARAM_INT);
        $repeatarray[] = $element;

        $element = $mform->createElement('hidden', 'visibleondate', $this->_customdata['defaultvisibleondate']);
        $mform->setType('visibleondate', PARAM_INT);
        $repeatarray[] = $element;

        $repeateloptions = array();

        $this->repeat_elements(
            $repeatarray, 
            1,
            $repeateloptions, 
            'rolloverrepeats', 
            'rolloveraddfields', 
            1, 
            get_string('addmore', 'local_culrollover'), 
            true
            );

        // Make the first rollover required. Error comes up as soon as select has focus.
        // if ($mform->elementExists('source[0]')) {
        //     $mform->addRule('source[0]', get_string('atleastonesource', 'local_culrollover'), 'required', null, 'client');
        // }

        // if ($mform->elementExists('dest[0]')) {
        //     $mform->addRule('dest[0]', get_string('atleastonedest', 'local_culrollover'), 'required', null, 'client');
        // }       

        // Add the JS to make the select inputs dynamic.
        $i = 0;

        if(isset($this->_customdata['rolloverrepeats'])) {
            $i = $this->_customdata['rolloverrepeats'];
        }

        // Add the JS to make the select inputs dynamic.
        $PAGE->requires->js_call_amd('local_culrollover/selectcourses', 'initialise', [$CFG->rollover_delay, $CFG->rollover_chars]);
        
        // Include all the default values in case the form is being submitted to add more courses.
        $mform->addElement('hidden', 'defaultwhen', $this->_customdata['defaultwhen']);
        $mform->setType('defaultwhen', PARAM_INT);
        $mform->addElement('hidden', 'defaultmigrateondate', $this->_customdata['defaultmigrateondate']);
        $mform->setType('defaultmigrateondate', PARAM_INT);
        $mform->addElement('hidden', 'defaultwhat', $this->_customdata['defaultwhat']);
        $mform->setType('defaultwhat', PARAM_INT);
        $mform->addElement('hidden', 'defaultmerge', $this->_customdata['defaultmerge']);
        $mform->setType('defaultmerge', PARAM_INT);
        $mform->addElement('hidden', 'defaultgroups', $this->_customdata['defaultgroups']);
        $mform->setType('defaultgroups', PARAM_INT);
        $mform->addElement('hidden', 'defaultroles', $this->_customdata['defaultroles']);
        $mform->setType('defaultroles', PARAM_TEXT);
        $mform->addElement('hidden', 'defaultvisible', $this->_customdata['defaultvisible']);
        $mform->setType('defaultvisible', PARAM_INT);
        $mform->addElement('hidden', 'defaultvisibleondate', $this->_customdata['defaultvisibleondate']);
        $mform->setType('defaultvisibleondate', PARAM_INT);

        $mform->addElement('hidden','courses', $courses);
        $mform->setType('courses', PARAM_INT);

        // Add Step check.
        $mform->addElement('hidden','step', MIGRATION_COURSE_CHOICES);
        $mform->setType('step', PARAM_INT);

        // Buttons for activity.
        $this->add_action_buttons(true, get_string('review','local_culrollover'));
    }

    /**
     * An approximate helper - get the course id from the course short name (course short names might not be unique)
     * @param $shortname
     * @param $location
     * @return mixed
     * @throws moodle_exception
     */
    public function get_course_id_from_shortname($shortname, $location) {
        global $DB, $CFG, $ROLLOVERID;

        $db_check = $DB->record_exists('course', array('shortname' => trim($shortname)));

        if($db_check) {
            $data =  $DB->get_record('course', array('shortname' => trim($shortname)), 'id', MUST_EXIST);
            return $data->id;
        } else {
            throw new moodle_exception('invalidcourseshortname', 'local_culrollover', '/local/culrollover', '', $shortname);
        }
    }

    public static function check_rollover_pending($dstid) {
        global $DB;

        $db_check = $DB->record_exists('cul_rollover', array('status' => 'Pending', 'destid' => $dstid));
        return $db_check; 
    }

    /**
     * This function gets the list of courses that this user has a particular capability in.
     * It is still not very efficient.
     *
     * @param string $filter If set, use in where clause.
     *   table with sql modifiers (DESC) if needed
     * @return array|bool Array of courses, if none found false is returned.
     */
    public static function culrollover_get_user_capability_course($filter = '') {
        global $DB;

        // Obtain a list of everything relevant about all courses including context.
        // Note the result can be used directly as a context (we are going to), the course
        // fields are just appended.

        $contextpreload = context_helper::get_preload_record_columns_sql('x');

        $courses = array();
        $rs = $DB->get_recordset_sql("SELECT c.id, c.shortname, c.fullname, $contextpreload
                                        FROM {course} c
                                        JOIN {context} x ON (c.id=x.instanceid AND x.contextlevel=".CONTEXT_COURSE.")
                                        $filter
                                    ORDER BY c.shortname, c.fullname");

        // Check capability for each course in turn
        foreach ($rs as $course) {
            context_helper::preload_from_record($course);
            $context = context_course::instance($course->id);
            if (is_siteadmin() || has_capability('moodle/course:update', $context, null, true)) {
                // We've got the capability. Make the record look like a course record
                // and store it
                $courses[] = $course;
            }
        }
        $rs->close();
        return empty($courses) ? false : $courses;
    }

    public static function culrollover_get_pending_course() {
        global $DB;

        $pending = array();

        $records = $DB->get_records('cul_rollover', array('status' => 'Pending'));

        foreach($records as $record) {
            $pending[] = $record->destid;
        }

        return array_unique($pending);
    }
}
