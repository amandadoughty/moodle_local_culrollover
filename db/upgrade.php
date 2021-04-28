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
 * Upgrade code for the plugin
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function xmldb_local_culrollover_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2016063001) {
        // Define table cul_rollover to be edited.
        $table = new xmldb_table('cul_rollover');
        $field = new xmldb_field('enrolments', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'groups');
        $dbman->change_field_notnull($table, $field);
        $field = new xmldb_field('completiondate', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'visibledate');
        $dbman->change_field_notnull($table, $field);
        $field = new xmldb_field('visibledate', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'visible');
        $dbman->change_field_notnull($table, $field);
        // CUL Rollover savepoint reached.
        upgrade_plugin_savepoint(true, 2016063001, 'local', 'culrollover');
    }

    if ($oldversion < 2016080505) {
        // Define table cul_rollover_config.
        $table = new xmldb_table('cul_rollover_config');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('idx_courseid', XMLDB_INDEX_UNIQUE, ['courseid', 'name']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // CUL Rollover savepoint reached.
        upgrade_plugin_savepoint(true, 2016080505, 'local', 'culrollover');
    }

    if ($oldversion < 2019031203) {
        global $DB;

        $table = new xmldb_table('cul_rollover');
        $field = new xmldb_field('groups', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null, null, '0', 'merge');

        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'includegroups');
        }

        // CUL Rollover savepoint reached.
        upgrade_plugin_savepoint(true, 2019031203, 'local', 'culrollover');
    }

    return true;
}
