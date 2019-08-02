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
 * Page for users to answer the security questions
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__.'../locallib.php');

class tool_securityquestions_locallib_testcase extends advanced_testcase {

    public function test_insert_question() {
        $this->resetAfterTest(true);
        global $DB;

        // Try to insert a question into database
        $this->assertEquals(true, insert_question('does this work?'));

        // Get Questions from the database, and check there is only 1 response
        $records = $DB->get_records('tool_securityquestions');
        $this->assertEquals(1, count($records));

        // Try and insert a duplicate record, verify doesn't insert
        $this->assertEquals(false, insert_question('does this work?'));
        $records = $DB->get_records('tool_securityquestions');
        $this->assertEquals(1, count($records));

        // Try and insert an empty question
        $this->assertEquals(false, insert_question(''));
        $records = $DB->get_records('tool_securityquestions');
        $this->assertEquals(1, count($records));
    }

    public function test_get_active_questions() {
        $this->resetAfterTest(true);
        global $DB;

        // Insert some questions to the database
        insert_question('active1');
        insert_question('active2');

        // Test there is the right amount of active questions
        $active = get_active_questions();
        $this->assertEquals(2, count($active));

        // Manually deprecate both questions
        foreach ($active as $question) {
            $DB->set_field('tool_securityquestions', 'deprecated', 1, array('id' => $question->id));
        }

        // Test for no active questions
        $active2 = get_active_questions();
        $this->assertEquals(0, count($active2));

        // Add one more question
        insert_question('active3');

        // Test that the only record returned is 'active3'
        $active3 = get_active_questions();
        $this->assertEquals(1, count($active3));
        $this->assertEquals('active3', reset($active3)->content);
    }
}


