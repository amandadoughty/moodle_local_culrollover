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

namespace local_culrollover\task;

date_default_timezone_set('Europe/London');

// php admin/tool/task/cli/schedule_task.php --execute=\\local_culrollover\\task\\rollover_task

class rollover_task extends \core\task\scheduled_task {

    protected $srccourse;
    protected $dstcourse;
    protected $srccontext;
    protected $dstcontext;

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('rollovertask', 'local_culrollover');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG, $DB;

        mtrace("\n\n CUL Rollover running on " . date('l', time()) . "\n");

        $records = $DB->get_records('cul_rollover', array('status' => 'Pending'));
        
        if($records){
            foreach ($records as $record) {
                try {
                    $rollover = new \local_culrollover\rollover($record);
                    $rollover->copy_courses();
                } catch (\Exception $e) {
                    mtrace(" ... copy_courses failed. \n\n");
                    mtrace(" ... {$e->getMessage()} \n\n");

                    // try{
                        $params = array(
                            'context' => $context = \context_course::instance($record->destid),
                            'objectid' => $record->id,
                            'relateduserid' => $record->userid,
                            'other' => array(
                                'error' => $e->getMessage()
                                )
                        );

                        $event = \local_culrollover\event\rollover_failed::create($params);
                        $snapshot = clone($record);
                        $snapshot->id = $record->id;
                        $event->add_record_snapshot('cul_rollover', $snapshot);
                        $event->trigger();

                        $record->status = 'Failed';
                        $DB->update_record('cul_rollover', $record);
                        // Update the destination course record in case the failure left it renamed.
                        $rollover->update_course_settings();
                    // } catch (\Exception $e) {
                        mtrace(" ... status update failed. \n\n");
                        mtrace(" ... {$e->getMessage()} \n\n");
                    // }
                }
            }
        } else {
            mtrace("  ... nothing to rollover. \n\n");
        }
        
        mtrace("  ... and we are done. \n\n");
    }

}
