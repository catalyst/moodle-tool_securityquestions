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
 * Testing file for functions inside locallib.php
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

        // Log in a generated user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

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

        // Log in a generated user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

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

        // Log in a generated user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

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

        // Log in a generated user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

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

        // Log in a generated user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

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
    }

    public function test_add_response() {
        $this->resetAfterTest(true);
        global $DB;
        global $CFG;
        global $USER;

        // Log in a generated user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

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
            $this->assertEquals(hash('sha1', "response$j". hash('sha1', $user->username)),
                $DB->get_field('tool_securityquestions_res', 'response', array('userid' => $USER->id, 'qid' => $question->id)));

            $j++;
        }

        // Update response to a question, then check it didnt add another table, and that the entry was updated
        tool_securityquestions_add_response('response4', reset($active)->id);
        $count3 = count($DB->get_records('tool_securityquestions_res', array('userid' => $USER->id)));
        $this->assertEquals(3, $count3);

        $this->assertEquals(hash('sha1', 'response4'. hash('sha1', $user->username)),
            $DB->get_field('tool_securityquestions_res', 'response', array('userid' => $USER->id, 'qid' => reset($active)->id)));

        // Check that nothing happens for QID that doesnt exist
        $this->assertEquals(false, tool_securityquestions_add_response('response5', 10000));
        $count4 = count($DB->get_records('tool_securityquestions_res', array('userid' => $USER->id)));
        $this->assertEquals(3, $count4);
    }

    public function test_validate_injected_questions() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;

        // Create a user and login as user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

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
        $errors = tool_securityquestions_validate_injected_questions($data, $USER);
        $this->assertEquals(array(), $errors);

        $data["question0"] = "badresponse1";
        $data["question1"] = "badresponse2";

        $errors2 = array();
        // Test that validation failed, and errors were returned
        $errors2 = tool_securityquestions_validate_injected_questions($data, $USER);
        $this->assertNotEquals(array(), $errors2);
    }

    public function test_get_active_user_responses() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;

        // Create a user and login as user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

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
        $this->assertEquals(count(tool_securityquestions_get_active_user_responses($USER)), count($active));

        // Add more questions, dont record responses
        tool_securityquestions_insert_question('question4');
        tool_securityquestions_insert_question('question5');

        // Check that active responses is still the same
        $this->assertEquals(count(tool_securityquestions_get_active_user_responses($USER)), 3);
        $active2 = tool_securityquestions_get_active_questions();
        $this->assertEquals(count($active2), 5);

        // Now deprecate a question with a response and ensure amount drops
        $this->assertEquals(true, tool_securityquestions_deprecate_question(reset($active)->id));
        $this->assertEquals(count(tool_securityquestions_get_active_user_responses($USER)), 2);
    }

    public function test_initialise_lockout_counter() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;

        // Check that user doesnt exist inside of the lockout table to start
        $this->assertEquals(false, $DB->record_exists('tool_securityquestions_loc', array('userid' => $USER->id)));

        // Now initialise the user, and ensure that it adds the user record
        tool_securityquestions_initialise_lockout_counter($USER);
        $this->assertEquals(true, $DB->record_exists('tool_securityquestions_loc', array('userid' => $USER->id)));

        // Check that counter starts at 0, and not locked out
        $record = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));

        $this->assertEquals(0, $record->counter);
        $this->assertEquals(0, $record->locked);

        // Now attempt to initialise again, and ensure no duplicate records
        tool_securityquestions_initialise_lockout_counter($USER);
        $records = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, count($records));

        // Now manually change the status of locked and counter, and ensure not reset by initialise
        $DB->set_field('tool_securityquestions_loc', 'counter', 2, array('userid' => $USER->id));
        $DB->set_field('tool_securityquestions_loc', 'locked', 1, array('userid' => $USER->id));

        tool_securityquestions_initialise_lockout_counter($USER);
        $record2 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));

        $this->assertEquals(2, $record2->counter);
        $this->assertEquals(1, $record2->locked);
    }

    public function test_increment_lockout_counter() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;

        // Test that the function initialises Users first
        $empty = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(0, count($empty));

        tool_securityquestions_increment_lockout_counter($USER);

        $records = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, count($records));

        // Now test that after initialisation, counter is incremented
        $record = reset($records);
        $this->assertEquals(1, $record->counter);

        // Increment again and check it works correctly for already initialised users
        tool_securityquestions_increment_lockout_counter($USER);
        $record2 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(2, $record2->counter);
    }

    public function test_reset_lockout_counter() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;

        // Test that the function initialises Users first
        $empty = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(0, count($empty));

        tool_securityquestions_reset_lockout_counter($USER);

        $records = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, count($records));

        // Check the value of the counter is 0
        $record = reset($records);
        $this->assertEquals(0, $record->counter);

        // Increment the counter
        tool_securityquestions_increment_lockout_counter($USER);
        $record2 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, $record2->counter);

        // Now test that the reset sets back to 0
        tool_securityquestions_reset_lockout_counter($USER);
        $record3 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(0, $record3->counter);

        // Check that it doesnt affect whether accounts are locked or not
        $this->assertEquals(0, $record3->locked);
        $DB->set_field('tool_securityquestions_loc', 'locked', 1, array('userid' => $USER->id));
        tool_securityquestions_reset_lockout_counter($USER);

        $record4 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, $record4->locked);
    }

    public function test_get_lockout_counter() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;

        // Test that the function initialises Users first
        $empty = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(0, count($empty));

        tool_securityquestions_reset_lockout_counter($USER);

        $records = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, count($records));

        // Manually get counter, and compare to results from function
        $record = reset($records);
        $count = tool_securityquestions_get_lockout_counter($USER);
        $this->assertEquals($record->counter, $count);

        // Incremement counter and check correctness
        tool_securityquestions_increment_lockout_counter($USER);
        tool_securityquestions_increment_lockout_counter($USER);

        $record2 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $count2 = tool_securityquestions_get_lockout_counter($USER);
        $this->assertEquals($record2->counter, $count2);

        // Now reset counter, and check correctness
        tool_securityquestions_reset_lockout_counter($USER);
        $record2 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals($record2->counter, 0);
        $count2 = tool_securityquestions_get_lockout_counter($USER);
        $this->assertEquals($record2->counter, $count2);
    }

    public function test_lock_user() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;

        // Create a user and login as user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Test that the function initialises Users first
        $empty = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(0, count($empty));

        tool_securityquestions_lock_user($USER);

        $records = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, count($records));

        // Test that the user is locked
        $record = reset($records);
        $this->assertEquals(1, $record->locked);

        // Test nothing happens if account is locked a second time
        tool_securityquestions_lock_user($USER);
        $record2 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, $record2->locked);
    }

    public function test_unlock_user() {
        $this->resetAfterTest(true);
        global $USER;
        global $DB;

        // Create a user and login as user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Test that the function initialises Users first
        $empty = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(0, count($empty));

        tool_securityquestions_unlock_user($USER);

        $records = $DB->get_records('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, count($records));

        // Test that the user is unlocked
        $record = reset($records);
        $this->assertEquals(0, $record->locked);

        // Lock user then unlock and check functionality
        tool_securityquestions_lock_user($USER);
        $record2 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(1, $record2->locked);

        tool_securityquestions_unlock_user($USER);
        $record3 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(0, $record3->locked);

        // Test that nothing happens attempting to unlock an already unlocked account
        tool_securityquestions_unlock_user($USER);
        $record4 = $DB->get_record('tool_securityquestions_loc', array('userid' => $USER->id));
        $this->assertEquals(0, $record4->locked);
    }

    public static function hash_response_provider() {
        return [
            // Data array [hash, string]
            ['cfba18edc59451992303231fe0704009952f2c0b', 'string'],
            ['cfba18edc59451992303231fe0704009952f2c0b', 'STRING'],
            ['cfba18edc59451992303231fe0704009952f2c0b', ' STRING '],
            ['cfba18edc59451992303231fe0704009952f2c0b', ' string '],
            ['6b8b9353fc19cdfa39ac5bc504b40087e2a00054', '      '],
            ['6872ba2dac81c77d993c8169364d6dab7b3880fb', 'str ing'],
            ['d355c2b36ddefea2c8adecf61e232eda3869bb5b', 'awdawd'],
            ['6b8b9353fc19cdfa39ac5bc504b40087e2a00054', ''],
            ['e90a368f2a0d9e18a1f4416183e708aabb6e81ed', '測試'],
        ];
    }

    /**
     * @dataProvider hash_response_provider
     */
    public function test_hash_response($hash, $string) {
        $this->resetAfterTest(true);
        // Create a user
        $user = $this->getDataGenerator()->create_user(array('username' => 'testuser'));

        $normhash = tool_securityquestions_hash_response($string, $user);
        $this->assertEquals($normhash, $hash);
    }
}


