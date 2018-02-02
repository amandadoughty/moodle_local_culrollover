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
 * Ajax for datatable.
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/culrollover/renderer.php');

global $DB, $USER;

$userid = $USER->id;

// Get record set.
if(has_capability('moodle/site:config', context_system::instance())) {
    $where = null;
} else {
    $where = "userid = $userid";
}
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array(
        'db' => 'id',
        'al' => 'cr.id',
        'dt' => 0
        ),
    array(
        'db' => 'datesubmitted',
        'al' => 'cr.datesubmitted', 
        'dt' => 1
        ),
    array(
        'db' => 'firstname',
        'al' => 'u.firstname',  
        'dt' => 2
    ),
    array(
        'db' => 'lastname',
        'al' => 'u.lastname',  
        'dt' => 3
    ),
    array(
        'db' => 'alternatename',
        'al' => 'u.alternatename',  
        'dt' => 4
    ),
    array( 
        'db' => 'datesubmitted',
        'al' => 'cr.datesubmitted',    
        'dt' => 5,
        'formatter' => function($d, $row) {
            return date('d/M/Y', $d);
        }
    ),    
    array(
        'db' => 'srcshortname',
        'al' => 'sc.shortname', 
        'dt' => 6,
        'formatter' => function($d, $row) {
            return local_culrollover_renderer::format_srccoursename($d, $row);
        }
    ),
    array(
        'db' => 'dstshortname',
        'al' => 'dc.shortname',
        'dt' => 7,
        'formatter' => function($d, $row) {
            return local_culrollover_renderer::format_dstcoursename($d, $row);
        }
    ),
    array(
        'db' => 'type',
        'al' => 'cr.type', 
        'dt' => 8,
        'formatter' => function($d, $row) {
            return local_culrollover_renderer::format_type($d, $row);
        }
    ),
    array(
        'db' => 'status',
        'al' => 'cr.status',  
        'dt' => 9,
        'formatter' => function($d, $row) {
            return local_culrollover_renderer::format_status($d, $row);
        }
    ),
    array(
        'db' => 'userid',
        'al' => 'cr.userid',  
        'dt' => 10,
        'formatter' => function($d, $row) {
            return local_culrollover_renderer::format_user($d);
        }
    ),
    array(
        'db' => 'id',
        'al' => 'cr.id',  
        'dt' => 11,
        'formatter' => function($d, $row) {
            return local_culrollover_renderer::format_delete($d, $row);
        }
    ), 
    array(
        'db' => 'id',
        'al' => 'cr.id',  
        'dt' => 12,
        'formatter' => function($d, $row) {
            return local_culrollover_renderer::format_repeat($d, $row);
        }
    ),      
    array(
        'db' => 'schedule',
        'al' => 'cr.schedule',  
        'dt' => 13
        ),    
    array(
        'db' => 'merge',
        'al' => 'cr.merge',  
        'dt' => 14
        ),
    array(
        'db' => 'groups',
        'al' => 'cr.groups',  
        'dt' => 15
        ),
    array(
        'db' => 'enrolments',
        'al' => 'cr.enrolments', 
        'dt' => 16
        ),
    array(
        'db' => 'visible',
        'al' => 'cr.visible',  
        'dt' => 17
        ),
    array(
        'db' => 'visibledate',
        'al' => 'cr.visibledate',  
        'dt' => 18
        ),
    array(
        'db' => 'completiondate',
        'al' => 'cr.completiondate',  
        'dt' => 19
        ),
    array(
        'db' => 'notify',
        'al' => 'cr.notify',  
        'dt' => 20
        ),
    // array(
    //     'db' => 'srcshortname',
    //     'al' => 'srcshortname',  
    //     'dt' => 17
    //     ),
    array(
        'db' => 'sourceid',
        'al' => 'cr.sourceid',  
        'dt' => 21
        ),
    array(
        'db' => 'destid',
        'al' => 'cr.destid',  
        'dt' => 22
        ),
    array(
        'db' => 'srcfullname',
        'al' => 'srcfullname',  
        'dt' => 23
        ),
    // array(
    //     'db' => 'dstshortname',
    //     'al' => 'dstshortname',  
    //     'dt' => 19
    //     ),
    array(
        'db' => 'dstfullname',
        'al' => 'dstfullname',  
        'dt' => 24
        )
);
 
require_once($CFG->dirroot . '/local/culrollover/classes/cul_ssp.php');
 
echo json_encode(
    cul_ssp::complex($_GET, $columns, null, $where)
);

