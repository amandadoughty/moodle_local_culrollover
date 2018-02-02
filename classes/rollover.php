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
 * A scheduled task for rollover cron.
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_culrollover;

require_once($CFG->dirroot . '/local/culrollover/constants.php');

date_default_timezone_set('Europe/London');

class rollover {

    protected $srccourse;
    protected $dstcourse;
    protected $srccontext;
    protected $dstcontext;
    protected $record;

    public function __construct($record) {
        $this->record = $record;

        try{
            $this->srccourse = get_course($this->record->sourceid);
            $this->srccontext = \context_course::instance($this->record->sourceid);
        } catch (\Exception $e) {
            throw new \moodle_exception('invalidsrccourseid', 'local_culrollover', '', $this->record->sourceid);
        }

        try{
            $this->dstcourse = get_course($this->record->destid);
            $this->dstcontext = \context_course::instance($this->record->destid);
        } catch (\Exception $e) {
            throw new \moodle_exception('invaliddstcourseid', 'local_culrollover', '', $this->record->destid);
        }
    }

    /**
     * Core logic - This is where the user options are processed
     */
    public function copy_courses() {
        global $DB;

        $opt = array();

        $this->debug(
            "\n  starting rollover from: {$this->srccourse->shortname}({$this->srccourse->id}) to {$this->dstcourse->shortname}({$this->dstcourse->id}) at " . 
            date('jS F Y H:i:s') . 
            "\n"
            );

        $this->debug("   scheduled date for processing is set: " . date('jS F Y', $this->record->schedule)) . "\n" ;
        
        if(time() < $this->record->schedule) {
            $this->debug("   rollover will not be processed today because of the preset schedule date - moving on \n");
            return;
        }    

        $this->record->status = 'Processing';

        $opt[] = array('name' => 'groups', 'value' => (int)$this->record->groups);

        switch($this->record->type) {
            case COPY_EVERYTHING:
                $opt[] = array('name' => 'activities', 'value' => 1);
                $opt[] = array('name' => 'blocks', 'value' => 1);
                $opt[] = array('name' => 'filters', 'value' => 1);
                break;
            case COPY_CONTENT_ONLY:
                $opt[] = array('name' => 'activities', 'value' => 1);
                $opt[] = array('name' => 'blocks', 'value' => 0);
                $opt[] = array('name' => 'filters', 'value' => 0);
                break;
        }

        $this->debug("   rollover type {$this->record->type} \n");

        if($this->record->merge == MERGE_EXISTING_CONTENT) {
            $mode = TARGET_COURSE_MERGE;
            $this->debug("   rollover is in 'merge' mode \n");
            // Get the modinfo before rollover
            $modinfobeforerollover = get_fast_modinfo($this->record->destid);
        } else {
            $mode = TARGET_COURSE_DELETE;
            $this->debug("   rollover is in 'delete first' mode \n");
            $modinfobeforerollover = false;
        }

        $coursevisibility = COURSE_HIDDEN;

        switch($this->record->visible) {
            case COURSE_HIDDEN:
                $coursevisibility = COURSE_HIDDEN;
                break;
            case COURSE_VISIBLE_NOW:
                $coursevisibility = COURSE_VISIBLE_NOW;
                break;
            case COURSE_VISIBLE_LATER:
                $coursevisibility = COURSE_HIDDEN;
                break;
        }

        $this->debug("   visibility = " . $coursevisibility . ", startdate = " . $this->record->visibledate . "\n");
        $this->debug("   doing content import({$this->srccourse->shortname}) \n");

        $this->copy_course($coursevisibility, $opt, $mode);

        // Update course visibility.
        $this->debug( "   checking for course start date \n");

        if($this->record->visibledate && $this->record->visibledate >= time()) {
            $this->debug("   setting course start date to " . date('d/M/Y', $this->record->visibledate) . "\n");
            $this->change_course_startdate($this->record->visibledate);
        }

        $this->dstcourse->visible = $coursevisibility;

        // Copy enrolments.
        if(!empty($this->record->enrolments)) {
            $this->debug( "   roles to copy are ({$this->record->enrolments}) \n");
            $rolestocopy = explode(',', $this->record->enrolments);
            $usersbyrole = array();            

            foreach($rolestocopy as $roleid) {
                $users = get_role_users($roleid, $this->srccontext);
                $usersbyrole[$roleid] = $users;                
            }

            $this->enrol_users($usersbyrole);
        } else {
            $this->debug( "   no roles to copy \n");
        }        

        // Update the destination course record with all the changes.
        $this->update_course_settings();

        // Remove assignments and forums with turnitin plagerism on.
        $this->debug("   attempting to remove assignments and forums with turnitin plagarism on now \n");
        $this->delete_turnitin_activities($modinfobeforerollover);
        $this->delete_forum_activities($modinfobeforerollover);
        
        // Update the rollover record.
        $this->debug("   setting rollover status as complete \n");        
        $this->record->status = 'Complete';
        $this->record->completiondate = time();
        $DB->update_record('cul_rollover', $this->record);

        // Log the rollover.
        $this->debug( "   adding rollover entry to Moodle log \n");
        
        $params = array(
            'context' => $this->dstcontext,
            'objectid' => $this->record->id,
            'relateduserid' => $this->record->userid,
        );

        $event = \local_culrollover\event\rollover_completed::create($params);
        $this->record->id = $this->record->id;
        $event->add_record_snapshot('cul_rollover', $this->record);
        $event->trigger();

        // Notify users.
        if(!empty($this->record->notify)) {
            $this->debug( "   send out email notifications\n");
            $rolestoinform = explode(',', $this->record->notify);
            $userstonotify = array();

            foreach($rolestoinform as $roleid) {
                // Check if we already have a list in $usersbyrole.
                if(!empty($usersbyrole) && isset($usersbyrole[$roleid])) {
                    $userstonotify = array_merge($userstonotify, $usersbyrole[$roleid]);                
                } else {
                    $users = get_role_users($roleid, $this->dstcontext);
                    $userstonotify = array_merge($userstonotify, $users);                     
                }                
            }

            $this->notify_users($userstonotify);
        } else {
            $this->debug("   no users to be informed ... moving on \n");
        }

        // Notify the requestor.
        $requestor = $DB->get_record('user', array('id' => $this->record->userid));
        $this->notify_requestor($requestor);

        // Finished.
        $this->debug("   finishing rollover at " . date('jS F Y H:i:s') . "\n");
    }

    /**
     * @param $visible -- destination course visibility
     * @param $options -- Options for activities filters and blocks
     * @param $mode - Merge or delete the destination content
     * @return array -- return destination course id and shortname
     * @throws moodle_exception
     */
    private function copy_course($visible, $options, $mode) {
        global $CFG, $USER, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $backupsettings = array();

        // Check for backup and restore options.
        if (!empty($options)) {
            foreach ($options as $option) {
                // Strict check for a correct value (always 1 or 0, true or false).
                if ($option['value'] !== 0 and $option['value'] !== 1) {
                    throw new \moodle_exception('invalidextparam', 'local_culrollover', '', $option['name'] . ': ' . $option['value']);
                }

                switch ($option['name']) {
                    case 'activities':
                    case 'blocks':
                    case 'users':
                    case 'filters': 
                    case 'groups':
                    case 'overwrite_conf':
                        $backupsettings[$option['name']] = $option['value'];
                        break;
                    default: 
                        throw new \moodle_exception('invalidextparam', 'local_culrollover', '', $option['name']);
                }
            }
        }

        // Backup the destination course.
        if($CFG->backupdestination) {

            $bc = new \backup_controller(
                \backup::TYPE_1COURSE, 
                $this->dstcourse->id, 
                \backup::FORMAT_MOODLE, 
                \backup::INTERACTIVE_NO, 
                \backup::MODE_GENERAL, 
                $USER->id
                );

            // Set the default filename - does not work.
            $format = $bc->get_format();
            $type = $bc->get_type();
            $id = $bc->get_id();
            $users = $bc->get_plan()->get_setting('users')->get_value();
            $anonymised = $bc->get_plan()->get_setting('anonymize')->get_value(); 

            $bc->get_plan()->get_setting('filename')->set_value(\backup_plan_dbops::get_default_backup_filename($format, $type,
                    $id, $users, $anonymised));

            $bc->execute_plan();

            $results = $bc->get_results();
            $file = $results['backup_destination'];

            $bc->destroy();
        }

        // Backup the source course.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE, 
            $this->srccourse->id, 
            \backup::FORMAT_MOODLE, 
            \backup::INTERACTIVE_NO, 
            \backup::MODE_IMPORT, 
            $USER->id
            );

        foreach ($backupsettings as $name => $value) {
            $bc->get_plan()->get_setting($name)->set_value($value);
        }

        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();
        $bc->execute_plan();
        $bc->destroy();

        // Restore the backup immediately.
        if($mode == TARGET_COURSE_DELETE) {
            $rc = new \restore_controller(
                $backupid, 
                $this->dstcourse->id,
                \backup::INTERACTIVE_NO, 
                \backup::MODE_GENERAL,
                $USER->id, 
                \backup::TARGET_EXISTING_DELETING
                );

            // Move the back up files a tmp location so that they are not deleted.
            $oldcomponent = 'backup';
            $newcomponent = 'tmp_backup';
            $oldfilearea = 'course';
            $newfilearea = 'tmp_filearea';
            $this->move_area_files_to_new_filearea($this->dstcontext->id, $oldcomponent, $newcomponent, $oldfilearea, $newfilearea);

            $options = array();
            $options['keep_groups_and_groupings'] = 1; // In destination course we want to keep them.
            $options['keep_roles_and_enrolments'] = 1; // In destination course we want to keep them.
            \restore_dbops::delete_course_content($this->dstcourse->id, $options);
            $backupsettings['overwrite_conf'] = 1;

            // Restore the backup files to their orignal location.
            $this->move_area_files_to_new_filearea($this->dstcontext->id, $newcomponent, $oldcomponent, $newfilearea, $oldfilearea);            
        }

        if($mode == TARGET_COURSE_MERGE) {
            $rc = new \restore_controller(
                $backupid, 
                $this->dstcourse->id,
                \backup::INTERACTIVE_NO, 
                \backup::MODE_IMPORT, 
                $USER->id, 
                \backup::TARGET_EXISTING_ADDING
                );
        }

        foreach ($backupsettings as $name => $value) {
            $rc->get_plan()->get_setting($name)->set_value($value);
        }

        foreach ($rc->get_plan()->get_tasks() as $taskindex => $task) {
            $settings = $task->get_settings();

            foreach ($settings as $settingindex => $setting) {
                // Set included false for activity, since we controlled it
                // more accurately (i.e. true only for glossary) in backup.
                if (preg_match('/^turnitin.*_([0-9])*_included$/', $setting->get_name())) {
                    $setting->set_value(false);
                }
            }
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();

            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }

                $errorinfo = '';

                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= $error;
                }

                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= $warning;
                    }
                }

                throw new \moodle_exception(get_string('backupprecheckerrors', 'local_culrollover', $errorinfo));
            }
        }

        try {
            $rc->execute_plan();
        } catch(\Exception $e) {            
            \restore_controller_dbops::drop_restore_temp_tables($rc->get_restoreid());
            throw $e;
        }

        $rc->destroy();

        if($mode == TARGET_COURSE_MERGE) {
            $this->delete_extra_news_forums();
        }

        $this->dstcourse->visible = $visible;

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }
    }

    /**
     * Move all the files in a file area from one filearea to another.
     *
     * @param string $contextid the contextid of the file area.
     * @param int $oldfilearea the filearea the files are being moved from.
     * @param int $newfilearea the filearea the files are being moved to.
     * @param string $oldcomponent the plugin that these files belong to.
     * @param string $newcomponent the plugin that these files belong to.
     * @param int $itemid file item ID
     * @return int the number of files moved, for information.
     */
    public function move_area_files_to_new_filearea($contextid, $oldcomponent, $newcomponent, $oldfilearea, $newfilearea) {
        $count = 0;
        $fs = get_file_storage();

        $oldfiles = $fs->get_area_files($contextid, $oldcomponent, $oldfilearea);

        foreach ($oldfiles as $oldfile) {
            $filerecord = new \stdClass();
            $filerecord->component = $newcomponent;
            $filerecord->filearea = $newfilearea;
            $fs->create_file_from_storedfile($filerecord, $oldfile);
            $count += 1;
        }

        if ($count) {
            $fs->delete_area_files($contextid, $oldcomponent, $oldfilearea);
        }
    }

    /**
     * Enrol users. Backup and restore only gives options to enrol all or none. 
     *
     * @param array $usersbyrole  
     * @return mixed
     * @throws coding_exception
     */
    private function enrol_users($usersbyrole) {
        global $DB;

        $instance = $DB->get_record('enrol', array('courseid' => $this->dstcourse->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $timestart = make_timestamp(date('Y'), date('m'), date('d'), 0, 0, 0);

        if(!$enrolmanual = enrol_get_plugin('manual')) { 
            throw new \coding_exception('cannot instantiate enrolmanual'); 
        }

        foreach ($usersbyrole as $roleid => $users) {
            foreach($users as $user) {
                $enrolmanual->enrol_user($instance, $user->id, $roleid, $timestart, 0);
                $this->debug("   enrolling user: $user->id ($user->username) \n");
            }
        } 
    }

    /**
     * Helper function to set the start date of a course
     * @param $this->dstcourse
     * @param $startdate
     */
    private function change_course_startdate($startdate) {
        global $DB;

        $this->dstcourse->startdate = $startdate;

        if($startdate > time()) {
            $this->debug("   setting visiblity to hide \n");
            $this->dstcourse->visible = COURSE_HIDDEN;
        } else {
            $this->dstcourse->visible = COURSE_VISIBLE_NOW;
            $this->debug("   setting visiblity to visible \n");
        }
    }

    /**
     * Remove all turnitin activities from a course - Turnitin v2 assignments are also handled!!
     */

    private function delete_turnitin_activities($modinfobeforerollover) {
        global $DB, $CFG ;

        $courseid = $this->dstcourse->id;
        $modinfo = get_fast_modinfo($courseid);
        $plugins = \core_component::get_plugin_list('plagiarism');

        // Delete assignments with turnitin plagerism plugin
        if(array_key_exists('turnitin', $plugins)) {
            if($modinfobeforerollover) {
                $assignmodsbeforerollover = $modinfobeforerollover->get_instances_of('assign');
            } else {
                $assignmodsbeforerollover = array();
            }

            $assignmods = $modinfo->get_instances_of('assign');
            $assignmods = array_diff_key($assignmods, $assignmodsbeforerollover);
            $assignnames = array();
            $cmids = array();

            foreach($assignmods as $assign) {
                $cmids[] = $assign->id;
                $assignnames[$assign->id] = $assign->name;
            }

            $plagiarismsettings = $DB->get_records_list('plagiarism_turnitin_config', 'cm', $cmids);
            $assignswithtii = array();

            foreach($plagiarismsettings as $plagiarismsetting) {
                if(($plagiarismsetting->name == 'use_turnitin') &&  ($plagiarismsetting->value == 1)) {
                    $assignswithtii[$plagiarismsetting->cm] = $assignnames[$plagiarismsetting->cm];
                }
            }

            foreach($assignswithtii as $cm => $assignmod) {
                $this->debug("   deleting assign $assignmod \n");
                course_delete_module($cm);
            }
        }
        // TODO can be forum and other mods too
    }

    /**
     * Remove turnitin enabled forum activities from a course.
     */
    private function delete_forum_activities($modinfobeforerollover) {
        global $DB, $CFG ;

        $courseid = $this->dstcourse->id;
        $modinfo = get_fast_modinfo($courseid);
        $plugins = \core_component::get_plugin_list('plagiarism');

        if(array_key_exists('turnitin', $plugins)) {

            // Delete forums with turnitin plagerism plugin
            if($modinfobeforerollover) {
                $forummodsbeforerollover = $modinfobeforerollover->get_instances_of('forum');
            } else {
                $forummodsbeforerollover = array();
            }

            $forummods = $modinfo->get_instances_of('forum');
            $forummods = array_diff_key($forummods, $forummodsbeforerollover);
            $forumnames = array();
            $cmids = array();

            foreach($forummods as $forum) {
                $cmids[] = $forum->id;
                $forumnames[$forum->id] = $forum->name;
            }

            $plagiarismsettings = $DB->get_records_list('plagiarism_turnitin_config', 'cm', $cmids);
            $forumswithtii = array();

            foreach($plagiarismsettings as $plagiarismsetting) {
                if(($plagiarismsetting->name == 'use_turnitin') &&  ($plagiarismsetting->value == 1)) {
                    $forumswithtii[$plagiarismsetting->cm] = $forumnames[$plagiarismsetting->cm];
                }
            }

            foreach($forumswithtii as $cm => $forummod) {
                $this->debug("   deleting forum $forummod \n");
                course_delete_module($cm);
            }
        }
    }

    /**
     * Remove extra news forums
     */
    private function delete_extra_news_forums() {
        global $DB, $CFG ;

        $courseid = $this->dstcourse->id;
        $modinfo = get_fast_modinfo($courseid);
        $forums = $modinfo->get_instances_of('forum');

        $params = array(
            'course' => $courseid,
            'type' => 'news'
            );

        $newsforums = $DB->get_records('forum', $params, 'id DESC');
        // Keep the original one.
        array_pop($newsforums);

        foreach($forums as $forumid => $forum) {
            if(isset($newsforums[$forumid])) {
                course_delete_module($forum->id);
            }
        }         
    }

    /**
     * Reinstate any settings we want to keep and update any we have changed.
     * 
     */
     public function update_course_settings() {
        global $DB, $CFG;

        $dstcourse = new \stdClass();
        $dstcourse->id = $this->dstcourse->id;
        $dstcourse->timecreated = time();
        $dstcourse->timemodified = time();
        $dstcourse->category = $this->dstcourse->category;
        $dstcourse->fullname = $this->dstcourse->fullname;
        $dstcourse->shortname = $this->dstcourse->shortname;
        $dstcourse->visible = $this->dstcourse->visible;
        $dstcourse->visibleold = $this->dstcourse->visibleold;
        $dstcourse->startdate = $this->dstcourse->startdate;

        if ($this->record->merge == 0) {
            $dstcourse->format = $this->dstcourse->format;
        } else {
            // Apply the default.
            $dstcourse->format = get_config('moodlecourse')->format;
            // Restore the default course blocks.
            blocks_add_default_course_blocks($dstcourse);
        }

        // Copy number of sections. Overwriting config may update
        // options for a different format.
        $this->copy_course_section_options($dstcourse);

        $DB->update_record('course', $dstcourse);
    }

    /**
     * Copies the number of sections from one course to another.
     * 
     * @param $dstcourse
     */
    private function copy_course_section_options($dstcourse) {
        global $DB;

        $this->debug("   getting course sections options from source ...\n");

        $dstoptions = array(
            'courseid' => $dstcourse->id,
            'format' => $dstcourse->format,
            'name' => 'numsections'
            );

        $srcoptions = $DB->get_record('course_format_options', array(
            'courseid' => $this->srccourse->id, 
            'format' => $this->srccourse->format,
            'name' => 'numsections'
            )
        );

        if($srcoptions) {
            // If there is a record for the number of sections for this 
            // course and format then update it. Otherwise insert one.
            if($dstoptionsrec = $DB->get_record('course_format_options', $dstoptions)) {
                // If this is a merge then we want to keep whichever number
                // of sections is greater.
                if ($this->record->merge == 0) {
                    if($dstoptionsrec->value < $srcoptions->value) {
                        $dstoptionsrec->value = $srcoptions->value;
                    }
                } else {
                    $dstoptionsrec->value = $srcoptions->value;
                }

                $DB->update_record('course_format_options', $dstoptionsrec);
            } else {
                $dstoptions['value'] = $srcoptions->value;
                $DB->insert_record('course_format_options', $dstoptions);
            }
        } else {
            $this->debug("   record does not exist in course_format_options ...\n");
        }
    }    

    /**
     * Notify users.
     *
     * @param array $users  
     */
    private function notify_users($users) {
        $a = new \stdClass();
        $a->coursename = $this->dstcourse->shortname;
        $a->courseid = $this->dstcourse->id;
        $subject = get_string('successemail_subject', 'local_culrollover', $a);
        $message = get_string('successemail', 'local_culrollover', $a);
        $userfrom = \core_user::get_noreply_user();

        foreach ($users as $user) {
            email_to_user(
                $user,
                $userfrom,
                $subject,
                '',
                $message
            );

            $this->debug("    notifying user: $user->username ($user->email) \n");
        }
    }

    /**
     * Notify requestor.
     *
     * @param User $user  
     */
    private function notify_requestor($user) {
        $a = new \stdClass();
        $a->coursename = $this->dstcourse->shortname;
        $a->courseid = $this->dstcourse->id;
        $subject = get_string('successemail_subject_requestor', 'local_culrollover', $a);
        $message = get_string('successemail_requestor', 'local_culrollover', $a);
        $userfrom = \core_user::get_noreply_user();

        email_to_user(
            $user,
            $userfrom,
            $subject,
            '',
            $message
            );

        $this->debug("   notifying requestor: $user->username ($user->email) \n");  
    }    

    /**
     * This function displays all the $this->debug data if the $this->debug setting is set in the admin options
     * @param $string
     */
    private function debug($string) {
        global $CFG;

        if($CFG->rollover_debug) {
            mtrace($string);
        }
    }    

}
