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

// =============================================DEPRECATION FUNCTIONS============================================

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

function tool_securityquestions_get_active_questions() {
    global $DB;
    $active = $DB->get_records('tool_securityquestions', array('deprecated' => 0), 'id ASC');
    return $active;
}

function tool_securityquestions_get_active_user_responses() {
    global $DB;
    global $USER;
    $active = tool_securityquestions_get_active_questions();
    $questions = array();

    foreach ($active as $question) {
        $record = $DB->get_record('tool_securityquestions_res', array('userid' => $USER->id, 'qid' => $question->id));
        if (!empty($record)) {
            array_push($questions, $record);
        }
    }

    return $questions;
}

function tool_securityquestions_deprecate_question($qid) {
    global $DB;

    if (tool_securityquestions_can_deprecate_question($qid)) {
        $DB->set_field('tool_securityquestions', 'deprecated', 1, array('id' => $qid));
        return true;
    } else {
        return false;
    }
}

// ================================================FORM INJECTION FUNCTIONS============================================

function tool_securityquestions_inject_security_questions($mform, $user) {

    // Check that enough questions have been answered by the user to enable securityquestions
    if (count(tool_securityquestions_get_active_questions()) >= get_config('tool_securityquestions', 'minuserquestions')) {
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
            $mform->addElement('hidden', "hiddenq$i", $qid);
        }
    }
}

function tool_securityquestions_validate_injected_questions($data, $errors, $user) {

    // Check that enough questions have been answered by the user to enable securityquestions
    if (count(tool_securityquestions_get_active_questions()) >= get_config('tool_securityquestions', 'minuserquestions')) {
        global $DB;
        global $USER;

        $numquestions = get_config('tool_securityquestions', 'answerquestions');

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
            $response = hash('sha1', $response);
            if ($response != $setresponse) {
                $errors[$name] = get_string('formanswerfailed', 'tool_securityquestions');
            }
        }
        return $errors;
    } else {
        return $errors;
    }
}

// =============================================QUESTION SETUP=========================================================

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

function tool_securityquestions_inject_navigation_node($navigation, $user, $usercontext, $course, $coursecontext) {
    global $PAGE;

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

function require_question_responses() {
    global $USER;
    global $DB;

    // Check whether enough questions are set to make the plugin active
    $setquestions = $DB->get_records('tool_securityquestions', array('deprecated' => 0));
    $requiredset = get_config('tool_securityquestions', 'minquestions');

    if (count($setquestions) >= $requiredset) {

        // Check whether user has answered enough questions
        $requiredquestions = get_config('tool_securityquestions', 'minuserquestions');
        $answeredquestions = $DB->get_records('tool_securityquestions_res', array('userid' => $USER->id));
        $url = '/admin/tool/securityquestions/set_responses.php';
        if (count($answeredquestions) < $requiredquestions) {
            redirect($url);
        }
    }
}

// =============================================SET QUESTIONS AND RESPONSES============================================

function tool_securityquestions_insert_question($question) {
    global $DB;
    if ($question != '') {
        $sqlquestion = $DB->sql_compare_text($question, strlen($question));

        if (!($DB->record_exists_sql('SELECT * FROM {tool_securityquestions} WHERE content = ?', array($sqlquestion)))) {
            return $DB->insert_record('tool_securityquestions', array('content' => $question, 'deprecated' => 0));
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function tool_securityquestions_add_response($response, $qid) {
    global $USER;
    global $DB;

    // First check if question actually exists to set a response for
    if ($DB->record_exists('tool_securityquestions', array('id' => $qid))) {
        // Hash response
        $response = hash('sha1', $response);
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

// ======================================LOCKOUT INTERACTION FUNCTIONS=============================================

function tool_securityquestions_increment_lockout_counter($user) {
    global $DB;

    // First ensure the user is initialised in the table
    tool_securityquestions_initialise_lockout_counter($user);

    //If already initialised, increment counter
    $count = $DB->get_field('tool_securityquestions_loc', 'counter', array('userid' => $user->id));
    $DB->set_field('tool_securityquestions_loc', 'counter',($count + 1), array('userid' => 0));
    return true;
}

function tool_securityquestions_initialise_lockout_counter($user) {
    global $DB;

    //Check if user exists in the lockout table
    if (!$DB->record_exists('tool_securityquestions_loc', array('userid' => $user->id))) {
        // If not, create entry for user, not locked out, counter 0
        $DB->insert_record('tool_securityquestions_loc', array('userid' => $user->id, 'locked' => 0, 'counter' => 0));
        return true;
    } else {
        return false;
    }
}

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