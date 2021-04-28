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
 * Event observers used in Rollover.
 *
 * @package    local_culrollover
 * @copyright 2018 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_culrollover.
 */
class local_culrollover_observer {
    /**
     * Triggered via course changing events.
     *
     * @param \core\event\base $event
     */
    public static function course_edited(\core\event\base $event) {
        global $DB;

        $eventdata = $event->get_data();
        // If there is no courseid then the value of $event->data['courseid'] is 0.
        $courseid = $eventdata['courseid'];

        // Check that the event has not fired on course creation.
        $course = $DB->get_record('course', ['id' => $courseid]);

        if ($course->timecreated == $course->timemodified) {
            return;
        }

        $userid = $eventdata['userid'];
        $configname = 'rolloverlocked';
        $table = 'cul_rollover_config';
        $record = $DB->get_record($table, ['courseid' => $courseid, 'name' => $configname]);

        $data = new stdClass();
        $data->courseid = $courseid;        
        $data->name = 'rolloverlocked';
        $data->value = 1;
        $data->timemodified = time();
        $data->userid = $userid;
        
        if (!$record) {
            $DB->insert_record($table, $data);    
        } else if ($record->value != 1) {
            $DB->update_record($table, $data);
        }

        $params = [
            'context' => $event->get_context(),
            'objectid' => $event->objectid,
            'userid' => $userid,
            'courseid' => $courseid
        ];

        $event = local_culrollover\event\course_locked::create($params);
        $event->trigger();
    }
}