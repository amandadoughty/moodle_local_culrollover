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
 * The local_culrollover folder updated event.
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_culrollover\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_culrollover folder updated event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string cfullname: the name of the user who created the rollover.
 *      - string dfullname: the name of the user who deleted the rollover.
 *      - string sourcecoursename: the name of the source course.
 *      - string destcoursename: the name of the destination course.
 *      - int delentry: the id of the rollover. 
 *      - string date: the date the rollover was sbmitted. 
 *      - string error: the error message.
 * }
 *
 * @package    local_culrollover
 * @since      Moodle 2.8
 * @copyright  2015 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rollover_completed extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'cul_rollover';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventrollover_completed', 'local_culrollover');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "Rollover completed successfully";
    }

    /**
     * Get url related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/culrollover/index.php', array('id' => $this->contextinstanceid));
    }   
}
