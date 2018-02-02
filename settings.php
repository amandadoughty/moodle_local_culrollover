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
 * Admin settings
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	global $DB;

	$settings = new admin_settingpage('local_culrollover', 'CUL Rollover');
	$ADMIN->add('localplugins', $settings);

	$settings->add(new admin_setting_configtext(
		'src_filter_regex_term', 
		get_string('src_regex_identifier', 'local_culrollover'),    
		get_string('src_regex_identifier_helper', 'local_culrollover'), 
		'.*',    
		PARAM_RAW_TRIMMED
		)
	);
	$settings->add(new admin_setting_configtext(
		'dest_filter_regex_term', 
		get_string('dest_regex_identifier', 'local_culrollover'),    
		get_string('dest_regex_identifier_helper', 'local_culrollover'), 
		'.*',    
		PARAM_RAW_TRIMMED
		)
	);
	
	$sql = "SELECT r.id, r.name, r.shortname
                FROM {role} r
                JOIN {role_context_levels} rcl ON (rcl.contextlevel = :context AND r.id = rcl.roleid)
                ORDER BY r.sortorder ASC";
    
    $roles = $DB->get_records_sql($sql, array('context' => CONTEXT_COURSE));

	foreach($roles as $role) {
	    $rolearray[$role->id] = $role->shortname;
	}

	$settings->add(new admin_setting_configmultiselect(
		'allowedroles',
		get_string('allowedroles', 'local_culrollover'),
		get_string('allowedroles_desc', 'local_culrollover'), 
		array(), 
		$rolearray
		)
	);
	$settings->add(new admin_setting_configcheckbox(
		'rollover_debug',
		get_string('rollover_debugging', 'local_culrollover'),
		get_string('rollover_debugging_desc', 'local_culrollover'),
		0
		)
	);
	$settings->add(new admin_setting_configtext(
		'rollover_delay',
		get_string('rollover_delay', 'local_culrollover'),
		get_string('rollover_delay_desc', 'local_culrollover'),
		250
		)
	);
	
	$settings->add(new admin_setting_configcheckbox(
		'backupdestination',
		get_string('alwaysbackupdestination', 'local_culrollover'),
		get_string('backupdestination_desc', 'local_culrollover'),
		0
		)
	);
}

	