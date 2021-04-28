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
 * Privacy Subsystem implementation for local_culrollover.
 *
 * @package    local_culrollover
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_culrollover\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementation for local_culrollover.
 *
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements 
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'cul_rollover',
            [
                'sourceid' => 'privacy:metadata:local_culrollover:sourceid',
                'destid' => 'privacy:metadata:local_culrollover:destid',
                'userid' => 'privacy:metadata:local_culrollover:userid',
                'datesubmitted' => 'privacy:metadata:local_culrollover:datesubmitted',
                'status' => 'privacy:metadata:local_culrollover:status',
                'schedule' => 'privacy:metadata:local_culrollover:schedule',
                'type' => 'privacy:metadata:local_culrollover:type',
                'merge' => 'privacy:metadata:local_culrollover:merge',
                'includegroups' => 'privacy:metadata:local_culrollover:groups',
                'enrolments' => 'privacy:metadata:local_culrollover:enrolments',
                'visible' => 'privacy:metadata:local_culrollover:visible',
                'visibledate' => 'privacy:metadata:local_culrollover:visibledate',
                'completiondate' => 'privacy:metadata:local_culrollover:completiondate',
                'notify' => 'privacy:metadata:local_culrollover:notify',
                'template' => 'privacy:metadata:local_culrollover:template',                
            ],
            'privacy:metadata:cul_rollover'
        );

        $collection->add_database_table(
            'cul_rollover_config',
            [
                'courseid' => 'privacy:metadata:local_culrollover:courseid',
                'name' => 'privacy:metadata:local_culrollover:name',
                'value' => 'privacy:metadata:local_culrollover:value',
                'timemodified' => 'privacy:metadata:local_culrollover:timemodified',
                'userid' => 'privacy:metadata:local_culrollover:userid'
            ],
            'privacy:metadata:cul_rollover_config'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        global $DB;

        $contextlist = new contextlist();
        $hasdata = false;
        // Rollovers and rollover locks are in the system context.
        $hasdata = $DB->record_exists_select('cul_rollover', 'userid = ?', [$userid]);
        $hasdata = $hasdata || $DB->record_exists_select('cul_rollover_config', 'userid = ?', [$userid]);        

        if ($hasdata) {
            $contextlist->add_system_context();
        }        

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        // Add Rollover users.
        $sql = "SELECT userid
                  FROM {cul_rollover}";

        $userlist->add_from_sql('userid', $sql, []);

        // Add course editors.
        $sql = "SELECT userid
                  FROM {cul_rollover_config}";

        $userlist->add_from_sql('userid', $sql, []);
    }    

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        // Remove non-system contexts. If it ends up empty then early return.
        $contexts = array_filter($contextlist->get_contexts(), function($context) {
            return $context->contextlevel == CONTEXT_SYSTEM;
        });

        if (empty($contexts)) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        // Export the local_culrollover.
        self::export_user_data_local_culrollover($userid);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_system) {
            return;
        }

        $DB->delete_records('cul_rollover');
        $DB->delete_records('cul_rollover_config');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        // Remove non-system contexts. If it ends up empty then early return.
        $contexts = array_filter($contextlist->get_contexts(), function($context) {
            return $context->contextlevel == CONTEXT_SYSTEM;
        });

        if (empty($contexts)) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        $DB->delete_records_select('cul_rollover', 'userid = ?', [$userid]);
        $DB->delete_records_select('cul_rollover_config', 'userid = ?', [$userid]);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        
        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);

        $DB->delete_records_select('cul_rollover', "userid {$userinsql}", $userinparams);
        $DB->delete_records_select('cul_rollover_config', "userid {$userinsql}", $userinparams);
    }

    /**
     * Export the rollover data.
     *
     * @param int $userid
     */
    protected static function export_user_data_local_culrollover($userid) {
        global $DB;

        $context = \context_system::instance();

        $rolloverdata = [];
        $rolloverconfigdata = [];     
        $select = 'userid = ?';
        $local_culrollover = $DB->get_recordset_select('cul_rollover', $select, [$userid], 'datesubmitted ASC');

        foreach ($local_culrollover as $rollover) {
            $visibledate = !is_null($rollover->visibledate) ? transform::datetime($rollover->visibledate) : '-';
            $completiondate = !is_null($rollover->completiondate) ? transform::datetime($rollover->completiondate) : '-';

            $data = (object) [
                'sourceid' => $rollover->sourceid,
                'destid' => $rollover->destid,
                'userid' => $rollover->userid,
                'datesubmitted' => transform::datetime($rollover->datesubmitted),
                'status' => $rollover->status,
                'schedule' => transform::datetime($rollover->schedule),
                'type' => $rollover->type,
                'merge' => $rollover->merge,
                'includegroups' => transform::yesno($rollover->includegroups),
                'enrolments' => $rollover->enrolments,
                'visible' => transform::yesno($rollover->visible),
                'visibledate' => $visibledate,
                'completiondate' => $completiondate,
                'notify' => $rollover->notify,
                'template' => $rollover->template                             
            ];

            $rolloverdata[] = $data;
        }
        $local_culrollover->close();

        writer::with_context($context)->export_data([get_string('privacy:metadata:cul_rollover', 'local_culrollover')], (object) $rolloverdata);

        $local_culrollover_config = $DB->get_recordset_select('cul_rollover_config', $select, [$userid], 'timemodified ASC');

        foreach ($local_culrollover_config as $config) {
            $data = (object) [
                'courseid' => $config->courseid,
                'name' => $config->name,
                'value' => $config->value,
                'timemodified' => transform::datetime($config->timemodified),
                'userid' => $config->userid
            ];

            $rolloverconfigdata[] = $data;
        }
        $local_culrollover->close();

        writer::with_context($context)->export_data([get_string('privacy:metadata:cul_rollover_config', 'local_culrollover')], (object) $rolloverconfigdata);
    }
}
