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
 * Step 1: default_options_form
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/culrollover/constants.php');

class default_options_form extends moodleform {
    
    public function definition() {
        global $CFG, $DB, $PAGE;

        $mform =& $this->_form;
        $mform->addElement('header', 'defaultoptions', get_string('defaultoptions_header', 'local_culrollover'));
        $mform->addHelpButton('defaultoptions', 'defaultoptions_helptext', 'local_culrollover');
        $mform->setExpanded('defaultoptions', true);

        // When should the rollover happen?
        $radioarray = array();
        $radioarray[] =& $mform->createElement('radio', 'defaultwhen', '', get_string('nextdate', 'local_culrollover'), SCHEDULE_NOW);
        $radioarray[] =& $mform->createElement('radio', 'defaultwhen', '', get_string('specificdate', 'local_culrollover'), SCHEDULE_LATER);
        $mform->addGroup($radioarray, 'whengroup', get_string('whentorollover', 'local_culrollover'), array(' '), false);
        $mform->addElement('date_selector', 'defaultmigrateondate', get_string('migratedate', 'local_culrollover'));
        $mform->addHelpButton('whengroup','whengroup_helptext', 'local_culrollover');

        // What to Rollover?
        $whatgroup = $mform->addElement(
            'text',
            'whatgroup',
            get_string('whattoroolover', 'local_culrollover'),
            array('value' => get_string('copy_content', 'local_culrollover'))
            );

        $whatgroup->freeze();
        $mform->setType('whatgroup', PARAM_TEXT);
        $mform->addHelpButton('whatgroup', 'whatgroup_helptext', 'local_culrollover');
        // Make this a hidden value as user is no longer allowed to select any other option.
        $mform->addElement('hidden', 'defaultwhat', COPY_CONTENT_ONLY);
        $mform->setType('defaultwhat', PARAM_INT);

        // Do we merge or delete?
        $radioarray = array();
        $radioarray[] =& $mform->createElement('radio', 'defaultmerge', '', get_string('merge','local_culrollover'), MERGE_EXISTING_CONTENT);
        $radioarray[] =& $mform->createElement('radio', 'defaultmerge', '', get_string('delete','local_culrollover'), DELETE_EXISTING_CONTENT);
        $mform->addGroup($radioarray, 'mergegroup', get_string('howtorollover','local_culrollover'), array(' '), false);
        $mform->addHelpButton('mergegroup', 'mergegroup_helptext', 'local_culrollover');

        // Copy across groups
        $radioarray = array();
        $radioarray[] =& $mform->createElement('radio', 'defaultgroups', '', get_string('nogroups', 'local_culrollover'), DO_NOT_COPY_GROUPS);
        $radioarray[] =& $mform->createElement('radio', 'defaultgroups', '', get_string('groups', 'local_culrollover'), COPY_GROUPS);
        $mform->addGroup($radioarray, 'groupsgroup', get_string('shouldgroupsbecopied', 'local_culrollover'), array(' '), false);
        $mform->addHelpButton('groupsgroup','groupsgroup_helptext', 'local_culrollover');

        // Which roles to copy across?
        $roles = explode(',', $CFG->allowedroles);
        $rolearray = array();
        $allroles = $DB->get_records('role');

        if(!empty($roles[0])) {
            foreach($roles as $role) {
                $rolearray[$role] = $allroles[$role]->shortname;
            }

            $select = $mform->addElement('select', 'defaultroles', get_string('rolestocopy', 'local_culrollover'), $rolearray);
            $select->setMultiple(true);
            $mform->addHelpButton('defaultroles', 'roles_helptext', 'local_culrollover');
        } else {
            $select = $mform->addElement('static', 'norolestocopy', get_string('norolestocopy', 'local_culrollover'));
            $mform->addHelpButton('norolestocopy', 'noroles_helptext', 'local_culrollover');
        }

        // Course visibility Settings.
        $radioarray=array();
        $radioarray[] =& $mform->createElement('radio', 'defaultvisible', '', get_string('visible', 'local_culrollover'), COURSE_VISIBLE_NOW);
        $radioarray[] =& $mform->createElement('radio', 'defaultvisible', '', get_string('notvisible', 'local_culrollover'), COURSE_HIDDEN);
        $radioarray[] =& $mform->createElement('radio', 'defaultvisible', '', get_string('visiblefrom', 'local_culrollover'), COURSE_VISIBLE_LATER);
        $mform->addGroup($radioarray, 'visiblegroup', get_string('shouldcoursebevisible', 'local_culrollover'), array(' '), false);
        $mform->addHelpButton('visiblegroup','visiblegroup_helptext', 'local_culrollover');
        $mform->addElement('date_selector', 'defaultvisibleondate', get_string('visibledate', 'local_culrollover'));

        // Lets add a hidden element to show us which step we are on.
        $mform->addElement('hidden', 'step', MIGRATION_INITIAL_CHOICES);
        $mform->setType('step', PARAM_INT);

        // All done - add our buttons.
        $this->add_action_buttons(true, get_string('choosecourses', 'local_culrollover'));
        // Add the JS to show/hide options when a selection changes.
        $PAGE->requires->js_call_amd('local_culrollover/defaultoptions', 'initialise');
        $PAGE->requires->css(new moodle_url('/local/culrollover/css/jquery.dataTables.min.css'));
    }
}
