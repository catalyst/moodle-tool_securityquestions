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
 * Scheduled task to cleanup deleted user table information
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_securityquestions\task;

/**
 * Task to clean plugin database tables.
 */
class clean_tables extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskcleantables', 'tool_securityquestions');
    }

    /**
     * Performs the cleaning of events.
     */
    public function execute() {
        global $DB;

        // First, find any users that are deleted in the user table
        $delusers = $DB->get_records('user', array ('deleted' => 1));
        foreach ($delusers as $deluser) {
            // Remove entries from locked table
            $DB->delete_records('tool_securityquestions_loc', array('userid' => $deluser->id));

            // Remove entries from the response table
            $DB->delete_records('tool_securityquestions_res', array('userid' => $deluser->id));

            // Remove entries from the answer table
            $DB->delete_records('tool_securityquestions_ans', array('userid' => $deluser->id));
        }

        // Now purge records of any missing user
        // tool_securityquestions_loc contains unique userid records
        $sql = "SELECT userid FROM {tool_securityquestions_loc} WHERE userid NOT IN (SELECT id FROM {user})";
        $missingusers = $DB->get_records_sql($sql);

        foreach ($missingusers as $missinguser) {
            // Remove entries from locked table
            $DB->delete_records('tool_securityquestions_loc', array('userid' => $missinguser->userid));

            // Remove entries from the response table
            $DB->delete_records('tool_securityquestions_res', array('userid' => $missinguser->userid));

            // Remove entries from the answer table
            $DB->delete_records('tool_securityquestions_ans', array('userid' => $missinguser->userid));
        }
    }
}

