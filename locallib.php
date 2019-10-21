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
 * Local library for question functions
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once(__DIR__.'/classes/event/user_locked.php');

// =============================================DEPRECATION FUNCTIONS============================================

/**
 * Returns whether a question can be deprecated
 *
 * @param int $qid The ID of the question to test
 * @return bool whether the question can be deprecated
 */
function tool_securityquestions_can_deprecate_question($qid) {
    global $CFG;
    global $DB;

    $active = tool_securityquestions_get_active_questions();

    if (count($active) > get_config('tool_securityquestions', 'minquestions')) {
        $question = $DB->get_record('tool_securityquestions', array('id' => $qid));
        if (!empty($question)) {
            if ($question->deprecated) {
                return false;
            } else {
                return true;
            }
        }
    } else {
        return false;
    }
}

/**
 * Returns an array of all active question records
 *
 * @return array an array of all active question records
 */
function tool_securityquestions_get_active_questions() {
    global $DB;
    $active = $DB->get_records('tool_securityquestions', array('deprecated' => 0), 'id ASC');
    return $active;
}

/**
 * Returns an array of all responses to active questions from a user
 *
 * @param stdClass $user The User to check responses for
 * @return array an array of all response records to active questions for a given user
 */
function tool_securityquestions_get_active_user_responses($user) {
    global $DB;
    $active = tool_securityquestions_get_active_questions();
    $questions = array();

    foreach ($active as $question) {
        $record = $DB->get_record('tool_securityquestions_res', array('userid' => $user->id, 'qid' => $question->id));
        if (!empty($record)) {
            array_push($questions, $record);
        }
    }

    return $questions;
}

/**
 * Deprecates a question if it can be deprecated
 *
 * @param int $qid The question ID to deprecate
 * @return bool returns true if the question was deprecated, false if not permitted
 */
function tool_securityquestions_deprecate_question($qid) {
    global $DB;

    if (tool_securityquestions_can_deprecate_question($qid)) {
        $DB->set_field('tool_securityquestions', 'deprecated', 1, array('id' => $qid));
        return true;
    } else {
        return false;
    }
}

/**
 * Undeprecates a question
 *
 * @param int $qid The question ID to undeprecate
 * @return bool returns true if the question was undeprecated, false if question couldn't be found
 */
function tool_securityquestions_undeprecate_question($qid) {
    // This function has no side effects unlike deprecate, which must check minimum questions
    global $DB;
    // If record doesnt exist, return false
    if ($DB->record_exists('tool_securityquestions', array('id' => $qid))) {
        $DB->set_field('tool_securityquestions', 'deprecated', 0, array('id' => $qid));
        return true;
    } else {
        return false;
    }
}

// ================================================FORM INJECTION FUNCTIONS============================================

/**
 * Injects security question elements into a form
 *
 * @param mform $mform the form to inject elements into
 * @param stdClass $user the user to pick questions for
 */
function tool_securityquestions_inject_security_questions($mform, $user) {

    // First check if user has the capability to interact with questions
    $usercontext = context_user::instance($user->id);
    if (!has_capability('tool/securityquestions:questionsaccess', $usercontext, $user)) {
        return;
    }

    // Check that enough questions have been answered by the user to enable securityquestions
    if (count(tool_securityquestions_get_active_user_responses($user)) >= get_config('tool_securityquestions', 'minuserquestions')) {
        global $DB;
        global $USER;

        $numquestions = get_config('tool_securityquestions', 'answerquestions');

        $inputarr = tool_securityquestions_pick_questions($user);

        for ($i = 0; $i < $numquestions; $i++) {
            // get qid from inputarr
            $qid = $inputarr[$i];
            $qcontent = $DB->get_field('tool_securityquestions', 'content', array('id' => $qid));

            // Format and display to the user
            $questionnum = $i + 1;
            $mform->addElement('html', "<h4>Security Question $questionnum: $qcontent</h4>");
            $mform->addElement('text', "question$i", get_string('formanswerquestion', 'tool_securityquestions', $questionnum));
            $mform->setType("question$i", PARAM_TEXT);
            $mform->addElement('hidden', "hiddenq$i", $qid);
            $mform->setType("hiddenq$i", PARAM_TEXT);
        }
    }
}

/**
 * Validates injected form elements for security questions to check for correct responses
 *
 * @param array $data The form data submitted by the user
 * @param array $errors The array of error messages for form elements
 * @param stdClass $user the user to validate responses against
 * @return array $errors The array of error messages with any additional messages added
 */
function tool_securityquestions_validate_injected_questions($data, $user) {
    $errors = array();

    // First check if user has the capability to interact with questions
    $usercontext = context_user::instance($user->id);
    if (!has_capability('tool/securityquestions:questionsaccess', $usercontext, $user)) {
        return $errors;
    }

    // Check that enough questions have been answered by the user to enable securityquestions
    if (count(tool_securityquestions_get_active_user_responses($user)) >= get_config('tool_securityquestions', 'minuserquestions')) {
        global $DB;

        $numquestions = get_config('tool_securityquestions', 'answerquestions');
        $errorfound = false;
        // For each question field, check response against database
        for ($i = 0; $i < $numquestions; $i++) {
            // Get question response for database
            $name = 'question'.$i;
            $hiddenname = 'hiddenq'.$i;
            $response = $data["$name"];
            $qid = $data["$hiddenname"];

            $qcontent = $DB->get_record('tool_securityquestions', array('id' => $qid));
            // Execute DB query with data
            $setresponse = $DB->get_field('tool_securityquestions_res', 'response', array('userid' => $user->id, 'qid' => $qid));
            // Hash response and compare to the database
            $response = tool_securityquestions_hash_response($response);
            if ($response != $setresponse) {
                $errors[$name] = get_string('formanswerfailed', 'tool_securityquestions');
                $errorfound = true;
            }

            // If locked out, always respond with the lockout message
            if (tool_securityquestions_is_locked_out($user)) {
                $errors[$name] = get_string('formlockedout', 'tool_securityquestions');
            }
        }

        if ($errorfound && !tool_securityquestions_is_locked_out($user)) {
            // If an error was found, increment lockout counter
            tool_securityquestions_increment_lockout_counter($user);

            $lockoutamount = get_config('tool_securityquestions', 'lockoutnum');
            // If counter is now over the specified count, lock account
            if (tool_securityquestions_get_lockout_counter($user) >= $lockoutamount && $lockoutamount > 0) {
                tool_securityquestions_lock_user($user);
            }
        }

        // Lastly, return the errors array
        return $errors;
    } else {
        return $errors;
    }
}

// =============================================QUESTION SETUP=========================================================
/**
 * Picks questions for a user to respond to
 *
 * @param stdClass $user the user to pick questions for
 * @return array $inputarr an array of question ID's to inject into a form
 */
function tool_securityquestions_pick_questions($user) {
    global $DB;
    global $CFG;

    $numquestions = get_config('tool_securityquestions', 'answerquestions');

    // First, check if user has had questions selected in the last 5 mins
    $currentquestions = $DB->get_records('tool_securityquestions_ans', array('userid' => $user->id), 'id ASC');

    // Get array of questions less than 5 mins old
    $temparray = array();
    foreach ($currentquestions as $question) {
        // Check if timecreated is <5 mins ago
        $period = get_config('tool_securityquestions', 'questionduration');
        $currenttime = time();
        if ($question->timecreated >= ($currenttime - $period)) {
            array_push($temparray, $question);
        }
    }

    // If found, perform data manipulation, if not, pick new questions and store them
    if (count($temparray) >= $numquestions) {
        // if questions found, Make sure the length of current array is what you are expecting, if not, get first n of array
        if (count($temparray) > $numquestions) {
            $temparray = array_slice($temparray, 0, $numquestions);
        }

        $inputarr = array();
        $i = 0;
        // Change array to be the format required for form injection
        foreach ($temparray as $question) {
            $inputarr[$i] = $question->qid;
            $i++;
        }

        return $inputarr;

    } else {

        // Get all questions with responses
        $answeredquestions = $DB->get_records('tool_securityquestions_res', array('userid' => $user->id), 'qid ASC');

        // Filter for questions that are currently active
        $answeredactive = array();

        $j = 0;
        foreach ($answeredquestions as $question) {
            $deprecated = $DB->get_field('tool_securityquestions', 'deprecated', array('id' => $question->qid));
            if ($deprecated == 0) {
                $answeredactive[$j] = $question;
                $j++;
            }
        }

        // Randomly pick n questions to be answered
        $pickedkeys = array_rand($answeredactive, $numquestions);

        // Create array to pass questions ids to the form
        $inputarr = array();
        $i = 0;
        foreach ($pickedkeys as $key) {
            $inputarr[$i] = $answeredactive[$key]->qid;
            $i++;
        }

        // Now questions have been picked, delete all records from table for user
        $DB->delete_records('tool_securityquestions_ans', array('userid' => $user->id));

        // Now add current records for picked questions
        $j = 0;
        foreach ($inputarr as $question) {
            $time = time();
            $DB->insert_record('tool_securityquestions_ans', array('userid' => $user->id, 'qid' => $inputarr[$j], 'timecreated' => $time));
            $j++;
        }

        return $inputarr;
    }
}

// ==========================================NAVIGATION INJECTION====================================================
/**
 * Injects a navigation node onto a user's preferences page to edit/set responses to security questions
 *
 * @param navigation $navigation the navigation node to inject into
 * @param stdClass $user the user to pick questions for
 * @param context $usercontext the user context to operate under
 * @param course $course the current course
 * @param context $coursecontext the context of the current course
 * @return null Returns null if not on the correct pages to inject
 */
function tool_securityquestions_inject_navigation_node($navigation, $user, $usercontext, $course, $coursecontext) {
    global $PAGE;

    // First check if user has the capability to interact with questions
    if (!has_capability('tool/securityquestions:questionsaccess', $usercontext, $user)) {
        return;
    }

    // Only inject if user is on the preferences page
    $onpreferencepage = $PAGE->url->compare(new moodle_url('/user/preferences.php'), URL_MATCH_BASE);
    if (!$onpreferencepage) {
        return null;
    }

    // Only inject if the plugin is enabled
    if (!get_config('tool_securityquestions', 'enable_plugin')) {
        return null;
    }

    // Dont inject if not enough questions are set
    if (count(tool_securityquestions_get_active_questions()) < get_config('tool_securityquestions', 'minquestions')) {
        return null;
    }

    $url = new moodle_url('/admin/tool/securityquestions/set_responses.php');
    $node = navigation_node::create(get_string('setresponsessettingsmenu', 'tool_securityquestions'), $url,
            navigation_node::TYPE_SETTING);
    $navigation->add_node($node);
}

/**
 * Forces redirect to the set_responses page if users havent answered enough questions
 */
function require_question_responses() {
    global $USER, $DB, $SESSION, $PAGE, $CFG;

    // First check if user has the capability to interact with questions
    $usercontext = context_user::instance($USER->id);
    if (!has_capability('tool/securityquestions:questionsaccess', $usercontext, $USER)) {
        return;
    }

    $config = get_config('tool_securityquestions');
    // If questions already presented
    if (property_exists($SESSION, 'presentedresponse') && !$config->mandatory_questions) {
        if (!$config->mandatory_questions) {
            // Do not redirect if not mandatory
            return;
        } else if ($config->graceperiod != 0) {
            $logintime = get_user_preferences('tool_securityquestions_logintime', null);
            // Check user time logged in since questions active
            if ($logintime == null) {
                // If user does not have a login time recorded, record here + return
                set_user_preference('tool_securityquestions_logintime', time());
                return;
            } else if ($logintime + $config->graceperiod >= time()){
                // If still in grace period, return
                return;
            }
        }
    }

    // Do not redirect if already on final page url, prevents redir loops from require_login
    if ($PAGE->has_set_url() && $PAGE->url == $CFG->wwwroot.'/user/preferences.php') {
        return;
    }

    // Check whether enough questions are set to make the plugin active
    $setquestions = $DB->get_records('tool_securityquestions', array('deprecated' => 0));
    $requiredset = $config->minquestions;

    if (count($setquestions) >= $requiredset) {

        // Check whether user has answered enough questions
        $requiredquestions = $config->minuserquestions;
        $answeredquestions = $DB->get_records('tool_securityquestions_res', array('userid' => $USER->id));
        $url = '/admin/tool/securityquestions/set_responses.php';
        if (count($answeredquestions) < $requiredquestions) {
            // Dont redirect if not in browser session
            if (!CLI_SCRIPT && !AJAX_SCRIPT) {
                // If page has URL set, set it to wantsurl for cancel. Avoids issues with dashboard not having PAGE->url set
                if ($PAGE->has_set_url()) {
                    $SESSION->wantsurl = $PAGE->url;
                }
                // Set flag for responses being presented
                $SESSION->presentedresponse = true;
                redirect($url);
            }
        }
    }
}

// =============================================SET QUESTIONS AND RESPONSES============================================
/**
 * Inserts a question into the database
 *
 * @param string $question The question content to be inserted
 * @return bool returns true if a question was successfully inserted or undeprecated, false for failure
 */
function tool_securityquestions_insert_question($question) {
    global $DB;
    // Trim question first
    $question = trim($question);
    if ($question != '') {
        // Construct query and determine if question already exists
        $sqlquestion = $DB->sql_compare_text($question, strlen($question));
        $record = $DB->get_record_sql('SELECT * FROM {tool_securityquestions} WHERE content = ?', array($sqlquestion));

        if (empty($record)) {
            return $DB->insert_record('tool_securityquestions', array('content' => $question, 'deprecated' => 0));
        } else if (!empty($record) && $record->deprecated != 0) {
            // If Deprecated record found, undeprecate
            return tool_securityquestions_undeprecate_question($record->id);
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Inserts a response to a question into the database
 *
 * @param string $response The question response to be inserted
 * @param int $qid the question to respond to
 * @return bool returns true if a response was successfully inserted, false for failure
 */
function tool_securityquestions_add_response($response, $qid) {
    global $USER;
    global $DB;

    // First check if question actually exists to set a response for
    if ($DB->record_exists('tool_securityquestions', array('id' => $qid))) {
        // Hash response
        $response = tool_securityquestions_hash_response($response);
        // Check if response to question already exists, if so update, else, create record
        if ($DB->record_exists('tool_securityquestions_res', array('qid' => $qid, 'userid' => $USER->id))) {
            $DB->set_field('tool_securityquestions_res', 'response', $response, array('qid' => $qid, 'userid' => $USER->id));
            return true;
        } else {
            $DB->insert_record('tool_securityquestions_res', array('qid' => $qid, 'userid' => $USER->id, 'response' => $response));
            return true;
        }
    } else {
        return false;
    }
}

/**
 * Hashes and normalises user responses
 *
 * @param string $response the string to be hashed and normalised
 * @return string the normalised and hashed string
 */
function tool_securityquestions_hash_response($response) {
    $temp = strtolower(trim($response));
    return hash('sha1', $temp);
}

// ======================================LOCKOUT INTERACTION FUNCTIONS=============================================
/**
 * Increments the lockout counter for a user
 *
 * @param stdClass $user the user to increment the counter of
 */
function tool_securityquestions_increment_lockout_counter($user) {
    global $DB;

    // First ensure the user is initialised in the table
    tool_securityquestions_initialise_lockout_counter($user);

    // If already initialised, increment counter
    $count = $DB->get_field('tool_securityquestions_loc', 'counter', array('userid' => $user->id));
    $DB->set_field('tool_securityquestions_loc', 'counter', ($count + 1), array('userid' => $user->id));
}

/**
 * Initialises a user in the lockout table
 *
 * @param stdClass $user the user to increment the counter of
 * @return bool returns true if user initialised, false if already present
 */
function tool_securityquestions_initialise_lockout_counter($user) {
    global $DB;

    // Check if user exists in the lockout table
    if (!$DB->record_exists('tool_securityquestions_loc', array('userid' => $user->id))) {
        // If not, create entry for user, not locked out, counter 0
        $DB->insert_record('tool_securityquestions_loc', array('userid' => $user->id, 'locked' => 0, 'counter' => 0));
        return true;
    } else {
        return false;
    }
}

/**
 * Checks whether a user is currently locked from resetting password
 *
 * @param stdClass $user the user to increment the counter of
 * @return bool returns true if user is locked out, false if not locked out
 */
function tool_securityquestions_is_locked_out($user) {
    global $DB;
    // First ensure that the user is initialised in the table
    tool_securityquestions_initialise_lockout_counter($user);

    $lock = $DB->get_field('tool_securityquestions_loc', 'locked', array('userid' => $user->id));
    if ($lock) {
        return true;
    } else {
        return false;
    }
}

/**
 * Locks a user, initialises then locks a user if not found in table
 *
 * @param stdClass $user the user to increment the counter of
 */
function tool_securityquestions_lock_user($user) {
    global $DB;
    // First ensure that the user is initialised in the table (should never be uninitialised here)
    tool_securityquestions_initialise_lockout_counter($user);
    $DB->set_field('tool_securityquestions_loc', 'locked', 1, array('userid' => $user->id));

    // Add event for logging locked user
    $event = \tool_securityquestions\event\locked_out::locked_out_event($user);
    $event->trigger();
}

/**
 * Unlocks a user, and resets the lockout counter
 *
 * @param stdClass $user the user to increment the counter of
 */
function tool_securityquestions_unlock_user($user) {
    global $DB;
    // First ensure that the user is initialised in the table (should never be uninitialised here)
    tool_securityquestions_initialise_lockout_counter($user);
    // Set lockout to false, and reset counter to 0
    $DB->set_field('tool_securityquestions_loc', 'locked', 0, array('userid' => $user->id));
    $DB->set_field('tool_securityquestions_loc', 'counter', 0, array('userid' => $user->id));
}

/**
 * Returns the current count of the attempt counter
 *
 * @param stdClass $user the user to increment the counter of
 * @return int the current attempt counter
 */
function tool_securityquestions_get_lockout_counter($user) {
    global $DB;
    // First ensure that the user is initialised in the table (should never be uninitialised here)
    tool_securityquestions_initialise_lockout_counter($user);
    return $DB->get_field('tool_securityquestions_loc', 'counter', array('userid' => $user->id));
}

/**
 * Resets the lockout attempt counter to 0
 *
 * @param stdClass $user the user to increment the counter of
 */
function tool_securityquestions_reset_lockout_counter($user) {
    global $DB;
    // First ensure that the user is initialised in the table (should never be uninitialised here)
    tool_securityquestions_initialise_lockout_counter($user);
    $DB->set_field('tool_securityquestions_loc', 'counter', 0, array('userid' => $user->id));
}

// ===========================================TEMPLATE FILE FUNCTIONS======================================
/**
 * Reads a question template file, and inserts all questions
 *
 * @param string $filepath the path to the question template file
 */
function tool_securityquestions_read_questions_file($filepath) {

    if (file_exists($filepath)) {
        try {
            $questions = fopen($filepath, 'r');
        } catch (Exception $e) {
            return false;
        }
    }

    if (!empty($questions)) {
        while (!feof($questions)) {
            $question = trim(fgets($questions));
            tool_securityquestions_insert_question($question);
        }
    }

    return true;
}

/**
 * Forces use of a template file if the admin config specifies one
 */
function tool_securityquestions_use_template_file() {
    $file = get_config('tool_securityquestions', 'questionfile');
    if ($file !== '') {
        // If a filepath is set, use that config file
        $path = __DIR__.'/question/'.$file;
        tool_securityquestions_read_questions_file($path);
    }
}