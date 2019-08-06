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
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__.'/../locallib.php');

class tool_securityquestions_locallib_testcase extends advanced_testcase {

    public function test_insert_question() {
        $this->resetAfterTest(true);
        global $DB;

        // Try to insert a question into database
        $this->assertEquals(true, tool_securityquestions_insert_question('does this work?'));

        // Get Questions from the database, and check there is only 1 response
        $records = $DB->get_records('tool_securityquestions');
        $this->assertEquals(1, count($records));

        // Try and insert a duplicate record, verify doesn't insert
        $this->assertEquals(false, tool_securityquestions_insert_question('does this work?'));
        $records = $DB->get_records('tool_securityquestions');
        $this->assertEquals(1, count($records));

        // Try and insert an empty question
        $this->assertEquals(false, tool_securityquestions_insert_question(''));
        $records = $DB->get_records('tool_securityquestions');
        $this->assertEquals(1, count($records));

        // TODO TEST FOR EMPTY QUESTIONS SUCH AS '     '
    }

    public function test_get_active_questions() {
        $this->resetAfterTest(true);
        global $DB;

        // Insert some questions to the database
        tool_securityquestions_insert_question('active1');
        tool_securityquestions_insert_question('active2');

        // Test there is the right amount of active questions
        $active = tool_securityquestions_get_active_questions();
        $this->assertEquals(2, count($active));

        // Manually deprecate both questions
        foreach ($active as $question) {
            $DB->set_field('tool_securityquestions', 'deprecated', 1, array('id' => $question->id));
        }

        // Test for no active questions
        $active2 = tool_securityquestions_get_active_questions();
        $this->assertEquals(0, count($active2));

        // Add one more question
        tool_securityquestions_insert_question('active3');

        // Test that the only record returned is 'active3'
        $active3 = tool_securityquestions_get_active_questions();
        $this->assertEquals(1, count($active3));
        $this->assertEquals('active3', reset($active3)->content);
    }

    public function test_can_deprecate_question() {
        $this->resetAfterTest(true);
        global $DB;
        global $CFG;

        // Set minimum number of required questions
        set_config('minquestions', 3 , 'tool_securityquestions');

        // Add a question, test whether it can be deprecated (checking for active < min questions)
        tool_securityquestions_insert_question('question1');
        $records = tool_securityquestions_get_active_questions();
        $this->assertEquals(false, tool_securityquestions_can_deprecate_question(reset($records)->id));

        // Now set minimum to 0, and test that it can be deprecated
        set_config('minquestions', 0 , 'tool_securityquestions');
        $this->assertEquals(true, tool_securityquestions_can_deprecate_question(reset($records)->id));

        // Set min back to 3, and add more questions to be higher than min that isnt 0
        set_config('minquestions', 3 , 'tool_securityquestions');
        tool_securityquestions_insert_question('question2');
        tool_securityquestions_insert_question('question3');
        tool_securityquestions_insert_question('question4');

        // Test that all these questions can be deprecated
        $active = tool_securityquestions_get_active_questions();
        foreach ($active as $question) {
            $this->assertEquals(true, tool_securityquestions_can_deprecate_question($question->id));
        }

        // Manually deprecate the first question, and test that the rest cant be deprecated
        $DB->set_field('tool_securityquestions', 'deprecated', 1, array('id' => reset($records)->id));
        $active2 = tool_securityquestions_get_active_questions();
        foreach ($active2 as $question) {
            $this->assertEquals(false, tool_securityquestions_can_deprecate_question($question->id));
        }

        // Set min to 0, and test that a deprecated question cannot be deprecated
        set_config('minquestions', 0 , 'tool_securityquestions');
        $this->assertEquals(false, tool_securityquestions_can_deprecate_question(reset($records)->id));
    }

    public function test_deprecate_question() {
        // This function will not be tested as heavily as can_deprecate_question, as
        // the functionality is largely based on can_deprecate_question
        $this->resetAfterTest(true);
        global $DB;
        global $CFG;

        // Set minimum questions to 0
        set_config('minquestions', 0 , 'tool_securityquestions');

        tool_securityquestions_insert_question('question1');

        // Check it starts not deprecated
        $active = tool_securityquestions_get_active_questions();
        $this->assertEquals(1, count($active));

        // Deprecate this question, and check that it succeeded, and there is no active questions
        $worked = tool_securityquestions_deprecate_question(reset($active)->id);
        $this->assertEquals(true, $worked);
        $active2 = tool_securityquestions_get_active_questions();
        $this->assertEquals(0, count($active2));

        // Check that an already deprecated question cant be deprecated again
        $worked = tool_securityquestions_deprecate_question(reset($active)->id);
        $this->assertEquals(false, $worked);
        $active3 = tool_securityquestions_get_active_questions();
        $this->assertEquals(0, count($active3));
    }

    public function test_pick_questions() {
        $this->resetAfterTest(true);
        global $DB;
        global $CFG;
        global $USER;

        // Setup some questions, and some responses
        for ($i = 1; $i < 6; $i++) {
            tool_securityquestions_insert_question("question$i");
        }

        $j = 1;
        $active = tool_securityquestions_get_active_questions();
        foreach ($active as $question) {
            $response = hash('sha1', "response$j");
            tool_securityquestions_add_response($response, $question->id);
            $j++;
        }

        // Verify that the table for picked questions is empty
        $table = $DB->get_records('tool_securityquestions_ans');
        $this->assertTrue(empty($table));

        // Pick some questions, then check table
        $questions = tool_securityquestions_pick_questions($USER);
        $table2 = $DB->get_records('tool_securityquestions_ans');
        $this->assertFalse(empty($table2));

        // Now try and get questions again, verify the same
        $this->assertEquals($questions, tool_securityquestions_pick_questions($USER));

        // Set period of questions to 5 seconds.
        set_config('questionduration', 5, 'tool_securityquestions');

        // wait 5 seconds to ensure fresh question choice
        sleep(5);
        $questions2 = tool_securityquestions_pick_questions($USER);
        $questions3 = tool_securityquestions_pick_questions($USER);
        $this->assertEquals($questions2, $questions3);
        /*sleep(5);
        // Potentially buggy, if random questions picked = previous choice, not sure
        $questions4 = tool_securityquestions_pick_questions($USER);
        $this->assertNotEquals($questions4, $questions2);
        $this->assertNotEquals($questions4, $questions3);*/ // FIND WAY TO TEST THIS EFFECTIVELY
    }

    public function test_add_response() {
        $this->resetAfterTest(true);
        global $DB;
        global $CFG;
        global $USER;

        // Add some questions for responses
        tool_securityquestions_insert_question('question1');
        tool_securityquestions_insert_question('question2');
        tool_securityquestions_insert_question('question3');

        // Ensure that there are no responses recorded
        $questions = $DB->get_records('tool_securityquestions_res', array('userid' => $USER->id));
        $count = count($questions);
        $this->assertEquals(0, $count);

        // Add responses, and check it is hashed and added correctly
        $active = tool_securityquestions_get_active_questions();
        $i = 1;
        $this->assertEquals(3, count($active));

        foreach ($active as $question) {
            tool_securityquestions_add_response("response$i", $question->id);
            $i++;
        }
        $count2 = count($DB->get_records('tool_securityquestions_res', array('userid' => $USER->id)));
        $this->assertEquals(3, $count2);

        $j = 1;
        foreach ($active as $question) {
            $this->assertEquals(hash('sha1', "response$j"), $DB->get_field('tool_securityquestions_res', 'response', array('userid' => $USER->id, 'qid' => $question->id)));
            $j++;
        }

        // Update response to a question, then check it didnt add another table, and that the entry was updated
        tool_securityquestions_add_response('response4', reset($active)->id);
        $count3 = count($DB->get_records('tool_securityquestions_res', array('userid' => $USER->id)));
        $this->assertEquals(3, $count3);

        $this->assertEquals(hash('sha1', 'response4'), $DB->get_field('tool_securityquestions_res', 'response', array('userid' => $USER->id, 'qid' => reset($active)->id)));

        // Check that nothing happens for QID that doesnt exist
        $this->assertEquals(false, tool_securityquestions_add_response('response5', 10000));
        $count4 = count($DB->get_records('tool_securityquestions_res', array('userid' => $USER->id)));
        $this->assertEquals(3, $count4);
    }

    public function test_validate_injected_questions() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;
        // Add questions and responses to validate against
        tool_securityquestions_insert_question('question1');
        tool_securityquestions_insert_question('question2');
        tool_securityquestions_insert_question('question3');

        // Setup fake data object to validate questions against with correct responses
        $data = array();

        $active = tool_securityquestions_get_active_questions();
        $i = 0;
        foreach ($active as $question) {
            tool_securityquestions_add_response("response$i", $question->id);
            $data["question$i"] = "response$i";
            $data["hiddenq$i"] = $question->id;
            $i++;
        }

        $errors = array();

        // Test that validation passed, and no errors were returned
        $errors = tool_securityquestions_validate_injected_questions($data, $errors, $USER);
        $this->assertEquals(array(), $errors);

        $data["question0"] = "badresponse1";
        $data["question1"] = "badresponse2";

        $errors2 = array();
        // Test that validation failed, and errors were returned
        $errors2 = tool_securityquestions_validate_injected_questions($data, $errors2, $USER);
        $this->assertNotEquals(array(), $errors2);
    }

    public function test_get_active_user_responses() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;
        // Set minimum number of questions to 1
        set_config('minquestions', 1 , 'tool_securityquestions');

        // Add questions and responses to validate against
        tool_securityquestions_insert_question('question1');
        tool_securityquestions_insert_question('question2');
        tool_securityquestions_insert_question('question3');

        // Get all active questions and set responses
        $active = tool_securityquestions_get_active_questions();
        $i = 0;
        foreach ($active as $question) {
            tool_securityquestions_add_response("response$i", $question->id);
            $i++;
        }

        // Verify that number of responses recorded = number of active
        $this->assertEquals(count(tool_securityquestions_get_active_user_responses()), count($active));

        // Add more questions, dont record responses
        tool_securityquestions_insert_question('question4');
        tool_securityquestions_insert_question('question5');

        // Check that active responses is still the same
        $this->assertEquals(count(tool_securityquestions_get_active_user_responses()), 3);
        $active2 = tool_securityquestions_get_active_questions();
        $this->assertEquals(count($active2), 5);

        // Now deprecate a question with a response and ensure amount drops
        $this->assertEquals(true, tool_securityquestions_deprecate_question(reset($active)->id));
        $this->assertEquals(count(tool_securityquestions_get_active_user_responses()), 2);
    }
}


