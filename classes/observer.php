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
 * Event observer for deleted users
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * User Deleted event observer class
 *
 * @package    tool_securityquestions
 * @copyright  Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_securityquestions_observer {
    /**
     * Event processor - user deleted
     *
     * @param \core\event\user_deleted $event
     * @return bool
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        global $DB;

        // Purge records from all security questions tables based on userid.
        $DB->delete_records('tool_securityquestions_loc', ['userid' => $event->objectid]);
        $DB->delete_records('tool_securityquestions_ans', ['userid' => $event->objectid]);
        $DB->delete_records('tool_securityquestions_res', ['userid' => $event->objectid]);

        return true;
    }
}
