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
 * Ajax for select2.
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once ('../../config.php');
require_once($CFG->dirroot . '/local/culrollover/classes/forms/select_courses_form.php');

global $DB, $CFG;

$regexarray = array(
	'src' => $CFG->src_filter_regex_term,
	'dst' => $CFG->dest_filter_regex_term
);
$query = null;
$data = array();

$type = $_REQUEST['type'];
$regex = $regexarray[$type];

if(isset($_REQUEST['q'])){
	$query = $_REQUEST['q'];
}

$filter = "WHERE shortname REGEXP '$regex'";

if($query){
	$filter .= " AND (shortname LIKE '%$query%' OR fullname LIKE '%$query%')";
}

$courses = select_courses_form::culrollover_get_user_capability_course($filter);
$pending = select_courses_form::culrollover_get_pending_course();

if($courses){
	foreach($courses as $course){
		// Check if pending already and disable if so. 
		if(($type == 'dst') && in_array($course->id, $pending)) {
            $disabled = true;              
		} else {
			$disabled = false;
		}

	    $data[] = array('id' => $course->id,'text' => $course->shortname, 'disabled' => $disabled);
	}
}

echo json_encode(array('items' => $data));
