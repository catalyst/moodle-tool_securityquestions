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
 * Local manager class for tables.
 *
 * @package    tool_securityquestions
 * @copyright  2020 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_securityquestions\local;

defined('MOODLE_INTERNAL') || die;

/**
 * A class to generate tables for security questions pages.
 */
class table_manager {
    /**
     * Generates table of locked out users.
     *
     * @return string|bool The html for the table, or false if no locked out users.
     */
    public static function get_lockout_table() {
        global $DB;

        // Get records from database for populating table.
        $sqlfrag = "tier > 0";
        $lockedusers = $DB->get_records_select('tool_securityquestions_loc', $sqlfrag, array(), 'tier DESC');

        // If no users found, return early.
        if (empty($lockedusers)) {
            return false;
        }

        $table = new \html_table();
        $table->head = array(
            get_string('userid', 'grades'),
            get_string('username'),
            get_string('email'),
            get_string('fullname'),
            get_string('formlockouttier', 'tool_securityquestions'),
            get_string('actions'),
        );
        $table->attributes['class'] = 'generaltable table table-bordered';
        $table->align = array('center', 'left', 'left', 'left', 'center', 'left');

        foreach ($lockedusers as $userrecord) {
            $user = $DB->get_record('user', array('id' => $userrecord->userid));

            // Initialise the lock, to check for expired locks.
            $lock = tool_securityquestions_get_lock_state($user);
            // If lock was reset to 0, ignore this user.
            if ($lock->tier == 0) {
                continue;
            }

            // Setup actions cell.
            $reset = new \moodle_url('/admin/tool/securityquestions/reset_lockout.php',
                array('reset' => $user->id, 'sesskey' => sesskey()));
            $clearres = new \moodle_url('/admin/tool/securityquestions/reset_lockout.php',
                array('clear' => $user->id, 'sesskey' => sesskey()));

            $cell = \html_writer::link($reset, get_string('formresetlockout', 'tool_securityquestions')).'<br>'.
                    \html_writer::link($clearres, get_string('formclearresponsestable', 'tool_securityquestions'));

            $table->data[] = array(
                $user->id,
                $user->username,
                $user->email,
                fullname($user),
                $userrecord->tier,
                $cell,
            );
        }

        return \html_writer::table($table);
    }

    /**
     * Gets the table of all current questions
     *
     * @return string|bool The HTML of the table, or false if no questions are found.
     */
    public static function get_questions_table() {
        global $DB;
        // Get records from database for populating table.
        $questions = $DB->get_records('tool_securityquestions', null, 'deprecated ASC, content ASC');

        // If there are no questions, return early.
        if (empty($questions)) {
            return false;
        }

        $table = new \html_table();
        $table->head = array(
            get_string('formtablequestion', 'tool_securityquestions'),
            get_string('formtablecount', 'tool_securityquestions'),
            get_string('formtabledeprecate', 'tool_securityquestions'),
            get_string('action'),
        );
        $table->attributes['class'] = 'generaltable table table-bordered';
        $table->colclasses = array('centeralign', 'centeralign', 'centeralign', 'centeralign');

        foreach ($questions as $question) {
            if ($question->deprecated == 1) {
                $dep = get_string('yes');
            } else {
                $dep = get_string('no');
            }
            $count = $DB->count_records('tool_securityquestions_res', array('qid' => $question->id));

            // Setup action cell.
            if ($count == 0 && $question->deprecated == 1) {
                $url = new \moodle_url('/admin/tool/securityquestions/set_questions.php',
                    array('delete' => $question->id, 'sesskey' => sesskey()));
                $link = \html_writer::link($url, get_string('delete'));
            } else {
                $url = new \moodle_url('/admin/tool/securityquestions/set_questions.php',
                    array('deprecate' => $question->id, 'sesskey' => sesskey()));
                $link = \html_writer::link($url, get_string('formdeprecate', 'tool_securityquestions'));
            }

            $table->data[] = array($question->content, $count, $dep, $link);
        }

        return \html_writer::table($table);
    }
}
