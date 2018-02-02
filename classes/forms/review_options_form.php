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
 * Step 3: review_options_form
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/culrollover/constants.php');

class review_options_form extends moodleform {
    
    public function definition() {
        global $CFG, $DB, $PAGE;

        $mform =& $this->_form;
        $mform->addElement('header','reviewoptions', get_string('reviewoptions_header','local_culrollover'));
        $mform->setExpanded('reviewoptions', true);
        $number = $this->_customdata['rolloverrepeats'];
        $count = 0;
        $table = new html_table();
        $table->id = 'datatable';
        $migrate = '';
        $row = array();
        $table->head = array(
            get_string('source_header', 'local_culrollover'), 
            get_string('dest_header', 'local_culrollover'), 
            get_string('migration_date', 'local_culrollover'),
            get_string('type', 'local_culrollover'),
            get_string('method', 'local_culrollover'),
            get_string('group', 'local_culrollover'),
            get_string('roles', 'local_culrollover'),
            get_string('visiblity', 'local_culrollover'),
            get_string('visiblefromdate', 'local_culrollover'),
            get_string('deleterow', 'local_culrollover')
            );

        $cellmigrate = $this->_customdata['defaultmigrateondate'];
        $cellwhat = $this->get_copy_content_options($this->_customdata['defaultwhat']);
        $cellmerge = $this->get_merge_options($this->_customdata['defaultmerge']);
        $cellgroups = $this->get_group_options($this->_customdata['defaultgroups']);
        $cellvisible = $this->get_visibility_options($this->_customdata['defaultvisible']);
        $celldelete = html_writer::link('#', 'Remove', array('class' => 'DeleteRow'));
        $defaultroles = explode(',', $this->_customdata['defaultroles']);
        $allroles = $DB->get_records('role');
        $displayrolenames = '';
        $jsrolesvalue = '[]';
        $jsrolessource = '[]';

        // Get the selected role names to display.
        // Get the js value and source strings to pass to createEditableFields().
        if($CFG->allowedroles != '') {
            $rolesallowed = explode(',', $CFG->allowedroles);
            $jsrolessourcearray = array();

            foreach($rolesallowed as $roleid) {
                $jsrolessourcearray[] = "{ value: $roleid, text :'{$allroles[$roleid]->shortname}'}";
            }

            $jsrolessource = '[' . implode(',', $jsrolessourcearray) . ']';

            if(!empty($defaultroles) && is_array($defaultroles)) {
                foreach($defaultroles as $roleid) {
                    if($roleid) {
                        $displayrolenames .= $allroles[$roleid]->shortname . ' ';
                    }
                }

                $jsrolesvalue = '[' . implode(',', $defaultroles) . ']';
            }
        }

        $hasrollovers = false;
        
        while($count < $number) {

            if(empty($this->_customdata['source'][$count]) || 
                empty($this->_customdata['dest'][$count])) { 
                $count++;
                continue;
            }

            $srcname = $this->get_course_name($this->_customdata['source'][$count]);
            $destname = $this->get_course_name($this->_customdata['dest'][$count]);
            $roles = isset($this->_customdata['roles'][$count])? $this->_customdata['roles'][$count] : $this->_customdata['defaultroles'];
            $visible = isset($this->_customdata['visible'][$count])? $this->_customdata['visible'][$count] : $this->_customdata['defaultvisible'];
            $visibleondate = isset($this->_customdata['visibleondate'][$count])? $this->_customdata['visibleondate'][$count] : $this->_customdata['defaultvisibleondate'];          

            if($visible == COURSE_HIDDEN) {
                $visibleondate = '';
            }

            if($visible == COURSE_VISIBLE_NOW) {
                $visibleondate = '';
            }

            if($visible == COURSE_VISIBLE_LATER) {
                $visibleondate = date('d/M/Y', $visibleondate);
            }

            $mform->addElement(
                'hidden',
                "source[$count]", 
                $this->_customdata['source'][$count],
                array('id' => "source_$count")
                );
            $mform->setType("source[$count]", PARAM_TEXT);
            $mform->addElement(
                'hidden',
                "dest[$count]",  
                $this->_customdata['dest'][$count],
                array('id' => "dest_$count")
                );
            $mform->setType("dest[$count]", PARAM_TEXT);
            $mform->addElement(
                'hidden',
                "migrateondate[$count]", 
                $this->_customdata['migrateondate'][$count],
                array('id' => "migrateondate_$count") 
                );
            $mform->setType("migrateondate[$count]", PARAM_INT);
            $mform->addElement(
                'hidden',
                "what[$count]", 
                $this->_customdata['what'][$count],
                array('id' => "what_$count")
                );
            $mform->setType("what[$count]", PARAM_INT);
            $mform->addElement(
                'hidden',
                "groups[$count]", 
                $this->_customdata['groups'][$count],
                array('id' => "groups_$count") 
                );
            $mform->setType("groups[$count]", PARAM_INT);
            $mform->addElement(
                'hidden',
                "merge[$count]", 
                $this->_customdata['merge'][$count],
                array('id' => "merge_$count")
                );
            $mform->setType("merge[$count]", PARAM_INT);

            $mform->addElement(
                'hidden',
                "roles[$count]",
                $roles,
                array('id' => "roles_$count")
                );
            $mform->setType("roles[$count]", PARAM_TEXT);         
            
            $mform->addElement(
                'hidden',
                "visible[$count]",
                $this->_customdata['visible'][$count],
                array('id' => "visible_$count")
                );
            $mform->setType("visible[$count]", PARAM_INT);
            $mform->addElement(
                'hidden',
                "visibleondate[$count]",
                $this->_customdata['visibleondate'][$count],
                array('id' => "visibleondate_$count")
                );
            $mform->setType("visibleondate[$count]", PARAM_INT);


            if($this->is_course_populated($this->_customdata['dest'][$count])) {
                $destclass = "destination populated tooltip-content";
                // If populated give a link to dest.
                $desturl = new moodle_url('/course/view.php', array('id' => $this->_customdata['dest'][$count]));
                $celldest = html_writer::link($desturl, $destname, array('target' => '_blank'));
            } else {
                $destclass = "destination";
                $celldest = $destname;                
            }            

            $row[] = array(
                $this->get_cell_data("source[$count]", $srcname, 'source'),
                $this->get_cell_data("dest[$count]", $celldest, $destclass),
                $this->get_cell_data("migrateondate[$count]", date('d/M/Y', $cellmigrate), 'migrateondate'),
                $this->get_cell_data("what[$count]", $cellwhat, 'what'),
                $this->get_cell_data("merge[$count]", $cellmerge, 'merge'),
                $this->get_cell_data("groups[$count]", $cellgroups, 'groups'),
                $this->get_cell_data("roles[$count]", $displayrolenames, 'roles'),
                $this->get_cell_data("visible[$count]", $cellvisible, 'visible'),
                $this->get_cell_data("visibleondate[$count]", $visibleondate, 'visibleondate'),
                $this->get_cell_data("delete[$count]", $celldelete)
                );

            $hasrollovers = true;
            $count ++;
        }

        // If the user submitted no sets of valid source and destination courses.
        if(!$hasrollovers) {
            throw new moodle_exception('emptycourseselection', 'local_culrollover', '/local/culrollover', '');
        }

        $table->data = $row;
        $mform->addElement('html', html_writer::table($table));

        // TODO better way
        $params = array();        

        $groups = array(
            'value' => $this->_customdata['defaultgroups'],
            'source' => array(
                    array(
                        'value' => DO_NOT_COPY_GROUPS,
                        'text' => get_string('nogroups', 'local_culrollover'),
                        ),
                    array(
                        'value' => COPY_GROUPS,
                        'text' => get_string('groups', 'local_culrollover'),
                        )
                )
            );

        $params['groups'] = $groups;

        $merge = array(
            'value' => $this->_customdata['defaultmerge'],
            'source' => array(
                    array(
                        'value' => MERGE_EXISTING_CONTENT,
                        'text' => get_string('merge', 'local_culrollover'),
                        ),
                    array(
                        'value' => DELETE_EXISTING_CONTENT,
                        'text' => get_string('delete', 'local_culrollover'),
                        )
                )
            );

        $params['merge'] = $merge;

        $roles = array(
            'value' => $jsrolesvalue,
            'source' => $jsrolessource
            );

        $params['roles'] = $roles;

        $visible = array(
            'value' => $this->_customdata['defaultvisible'],
            'source' => array(
                    array(
                        'value' => COURSE_HIDDEN,
                        'text' => $this->get_visibility_options(COURSE_HIDDEN),
                        ),
                    array(
                        'value' => COURSE_VISIBLE_NOW,
                        'text' => $this->get_visibility_options(COURSE_VISIBLE_NOW),
                        ),
                    array(
                        'value' => COURSE_VISIBLE_LATER,
                        'text' => $this->get_visibility_options(COURSE_VISIBLE_LATER),
                        )
                )
            );

        $params['visible'] = $visible;
        $params = json_encode($params);

        $src_script = "<script>createEditableFields($params)</script>"; // TODO - amd?
        $mform->addElement('html', $src_script);

        // $PAGE->requires->js_call_amd('local_culrollover/x-editable', 'initialise', $params);
        // // Add the JS to show/hide options when a selection changes.
        $PAGE->requires->js_call_amd('local_culrollover/reviewsettings', 'initialise');
        
        $allroles = $DB->get_records('role');

        if($CFG->allowedroles != '') {
            $roles = explode(',', $CFG->allowedroles);

            foreach($roles as $role) {
                $rolearray[$role] = $allroles[$role]->shortname;
            }

            $select = $mform->addElement('select', 'rolestoinform', get_string('rolestoinform', 'local_culrollover'), $rolearray);
            $mform->addHelpButton('rolestoinform','rolestoinform_help', 'local_culrollover');
            $select->setMultiple(true);
        }

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
        $mform->addElement('hidden', 'step', MIGRATION_REVIEW_CHOICES);
        $mform->setType('step', PARAM_INT);
        $mform->addElement('hidden', 'rolloverrepeats', $number);
        $mform->setType('rolloverrepeats', PARAM_INT);
        $this->add_action_buttons(true, get_string('finish', 'local_culrollover'));
    }

    /**
     * Adding a jQuery tooltip to a table cell
     * @param $id
     * @param $text
     * @param string $class
     * @return html_table_cell
     */
    public function get_cell_data($id, $text, $class = '') {
        $cell = new html_table_cell();
        $cell->id = $id;
        $cell->text = $text;

        if(strpos($class, 'populated')) {
            $cell->attributes = array('class' => $class, 'title' => get_string('tooltipwarning', 'local_culrollover'));
        } elseif ($class == 'what') {
            $cell->attributes = array('class' => $class, 'title' => 'click to edit except if template has been chosen');
        } elseif ($class == 'source' || $class == 'destination') {
            $cell->attributes = array('class' => $class, 'title' => 'can\'t edit these now');
        } else {
            $cell->attributes = array('class' => $class, 'title' => 'click to edit');
        }

        return $cell;
    }

    /**
     * Helper function - gets the preset copy options
     * @param string $id
     * @return array
     */
    public function get_copy_content_options($id = '') {
        switch($id) {
            case COPY_CONTENT_ONLY:
                return get_string('copy_content', 'local_culrollover');
                break;
            case COPY_EVERYTHING:
                return get_string('copy_everything', 'local_culrollover');
                break;
            default:
                return array(
                    COPY_CONTENT_ONLY => get_string('copy_content', 'local_culrollover'),
                    COPY_EVERYTHING => get_string('copy_everything', 'local_culrollover'),
                    );
        }

    }

    /**
     * Helper function for displaying all the process options
     * @param string $id
     * @return array
     */
    public function get_merge_options($id = '') {
        
        switch($id) {
            case MERGE_EXISTING_CONTENT:
                return get_string('merge', 'local_culrollover');
                break;
            case DELETE_EXISTING_CONTENT:
                return get_string('delete', 'local_culrollover');
                break;
            default:
                return array(
                    MERGE_EXISTING_CONTENT => get_string('merge', 'local_culrollover'),
                    DELETE_EXISTING_CONTENT => get_string('delete', 'local_culrollover')
                    );
        }
    }

    /**
     * * Helper function for displaying all the group copy options
     * @param string $id
     * @return array
     */
    public function get_group_options($id = '') {
        
        switch($id) {
            case DO_NOT_COPY_GROUPS:
                return get_string('nogroups', 'local_culrollover');
                break;
            case COPY_GROUPS:
                return get_string('groups', 'local_culrollover');
                break;
            default:
                return array(
                    DO_NOT_COPY_GROUPS => get_string('nogroups', 'local_culrollover'),
                    COPY_GROUPS => get_string('groups', 'local_culrollover')
                    );
        }
    }

    /**
     * * Helper function for displaying all the visibility options
     * @param string $id
     * @return array
     */
    public function get_visibility_options($id =' ') {
        
        switch($id) {
            case COURSE_HIDDEN:
                return get_string('hidecourse', 'local_culrollover');
                break;
            case COURSE_VISIBLE_NOW:
                return get_string('visiblecourse', 'local_culrollover');
                break;
            case COURSE_VISIBLE_LATER:
                return get_string('coursevisibleon', 'local_culrollover');
                break;
            default:
                return array(
                    COURSE_HIDDEN => get_string('hidecourse', 'local_culrollover'),
                    COURSE_VISIBLE_NOW => get_string('visiblecourse', 'local_culrollover'),
                    COURSE_VISIBLE_LATER => get_string('coursevisibleon', 'local_culrollover')
                    );
        }
    }

    /**
     * Helper function to check if a given course is empty
     * @param $courseid
     * @return bool
     */
    public function is_course_populated($courseid) {
        global $DB;

        $query = $DB->count_records('course_modules', array('course' => $courseid));
        
        if($query > 1) {
            return true;
        } else {
            return false;
        }    
    }

    /**
     * Simple helper function to get the course shortname (Changing the return to full name will populate the course selection dropdown
     * with course full names.
     * @param $courseid
     * @return mixed
     */
    public function get_course_name($courseid) {
        global $DB;

        if ($course = $DB->get_record('course', array('id' => $courseid))){
            return $course->shortname;
        }

        return null;
    }
}

