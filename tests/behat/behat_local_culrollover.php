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
 * Steps definitions related with the forum activity.
 *
 * @package    local_culrollover
 * @category   test
 * @copyright  2020 Amanda Doughty Steve Waters
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Rollover-related steps definitions.
 *
 * @package    local_culrollover
 * @category   test
 * @copyright  2020 Amanda Doughty Steve Waters
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_culrollover extends behat_base {
    /**
     * Convert rollover page names to URLs for steps like 'When I am on the "[page name]" page'.
     *
     * Recognised page names are:
     * | Rollover tool | Rollover set up page |
     *
     * @param string $name identifies which identifies this page, e.g. 'Rollover tool'.
     * 
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_url(string $name): moodle_url {
        switch ($name) {
            case 'Rollover tool':
                return new moodle_url('/local/culrollover/');

            default:
                throw new Exception('Unrecognised rollover page type "' . $name . '."');
        }
    }
}