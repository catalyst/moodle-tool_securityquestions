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

// Deprecation Functions.
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
    // Do not use get_active_questions, need different sorting order.
    $active = $DB->get_records('tool_securityquestions', array('deprecated' => 0), 'content ASC');
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
    global $DB, $USER;

    if (tool_securityquestions_can_deprecate_question($qid)) {
        $DB->set_field('tool_securityquestions', 'deprecated', 1, array('id' => $qid));
        $question = $DB->get_record('tool_securityquestions', (array('id' => $qid)));

        // Fire event for question deprecation.
        $event = \tool_securityquestions\event\question_deprecated::question_deprecated_event($USER, $question->content);
        $event->trigger();

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
    // This function has no side effects unlike deprecate, which must check minimum questions.
    global $DB, $USER;

    // If record doesnt exist, return false.
    if ($DB->record_exists('tool_securityquestions', array('id' => $qid))) {
        $DB->set_field('tool_securityquestions', 'deprecated', 0, array('id' => $qid));

        $question = $DB->get_record('tool_securityquestions', array('id' => $qid));
        // Fire event for question addition back to the pool.
        $event = \tool_securityquestions\event\question_added::question_added_event($USER, $question->content);
        $event->trigger();

        return true;
    } else {
        return false;
    }
}

/**
 * Delete a question.
 * @param int $qid
 * @return bool
 */
function tool_securityquestions_delete_question($qid) {
    global $DB, $USER;
    // This function does not have to check for minimum questions, as question must be deprecated before use.
    // Check question exists.
    if ($DB->record_exists('tool_securityquestions', array('id' => $qid))) {
        $question = $DB->get_record('tool_securityquestions', array('id' => $qid));
        // Ensure question is deprecated before delete.
        if ($question->deprecated == 1) {
            // Finally ensure no-one is using question.
            if ($DB->count_records('tool_securityquestions_res', array('qid' => $qid)) == 0) {
                $DB->delete_records('tool_securityquestions', array('id' => $qid));

                // Fire event for question deletion.
                $event = \tool_securityquestions\event\question_deleted::question_deleted_event($USER, $question->content);
                $event->trigger();

                return true;
            }
        }
    }
    // Wasnt able to delete.
    return false;
}

// Form Injection Functions.

/**
 * Injects security question elements into a form
 *
 * @param mform $mform the form to inject elements into
 * @param stdClass $user the user to pick questions for
 */
function tool_securityquestions_inject_security_questions($mform, $user) {

    // First check if user has the capability to interact with questions.
    $usercontext = context_user::instance($user->id);
    if (!has_capability('tool/securityquestions:questionsaccess', $usercontext, $user)) {
        return;
    }

    // Check that enough questions have been answered by the user to enable securityquestions.
    $minquestions = get_config('tool_securityquestions', 'minuserquestions');
    if (count(tool_securityquestions_get_active_user_responses($user)) >= $minquestions) {
        global $DB;

        $numquestions = get_config('tool_securityquestions', 'answerquestions');

        $nonce = optional_param('nonce', 0, PARAM_INT);

        // If there is submitted form data, and we pick new questions, and they dont line up with
        // The submitted data, validation will fail. In this case, we need to write the prev
        // values into the form for validation to check against the correct questions.
        $submitted = ($nonce > 0);

        if ($submitted) {
            $prevvalues = tool_securityquestions_pick_questions($user, true);
        }
        $inputarr = tool_securityquestions_pick_questions($user);

        if ($submitted && $prevvalues !== $inputarr) {
            // There has been a rollover. Write the previous values into a
            // user preference to use in validation. This avoids submission replay attacks.
            set_user_preference('tool_securityquestions_submitted_vals', serialize($prevvalues));
        }

        // Add a nonce for tracking if form was submitted, so validation is correct.
        $mform->addElement('hidden', 'nonce', time());
        $mform->setType('nonce', PARAM_INT);

        for ($i = 0; $i < $numquestions; $i++) {
            // Get qid from inputarr.
            $qid = $inputarr[$i];
            $qcontent = $DB->get_field('tool_securityquestions', 'content', array('id' => $qid));

            // Format and display to the user.
            $questionnum = $i + 1;
            $contentarray = array('num' => $questionnum, 'content' => $qcontent);
            $heading = \html_writer::tag('h4', get_string('injectedquestiontitle', 'tool_securityquestions', $contentarray));
            $mform->addElement('html', $heading);

            $mform->addElement('text', "question$i", get_string('formanswerquestion', 'tool_securityquestions', $questionnum));
            $mform->setType("question$i", PARAM_TEXT);
            $mform->addRule("question$i", get_string('required'), 'required', null, 'client');

            $mform->addElement('hidden', "hiddenq$i", $qid);
            $mform->setType("hiddenq$i", PARAM_TEXT);
        }
    }
}

/**
 * Validates injected form elements for security questions to check for correct responses
 *
 * @param array $data The form data submitted by the user
 * @param stdClass $user the user to validate responses against
 * @return array $errors The array of error messages with any additional messages added
 */
function tool_securityquestions_validate_injected_questions($data, $user) {
    $errors = array();

    // First check if user has the capability to interact with questions.
    $usercontext = context_user::instance($user->id);
    if (!has_capability('tool/securityquestions:questionsaccess', $usercontext, $user)) {
        return $errors;
    }

    // Check that enough questions have been answered by the user to enable securityquestions.
    $minquestions = get_config('tool_securityquestions', 'minuserquestions');
    if (count(tool_securityquestions_get_active_user_responses($user)) >= $minquestions) {

        global $DB;
        $numquestions = get_config('tool_securityquestions', 'answerquestions');
        $errmsg = '';
        // Check if there were questions already set in user preferences to validate against.
        // due to a rollover of the selected questions.
        $submittedser = get_user_preferences('tool_securityquestions_submitted_vals', null);
        if (!empty($submittedser)) {
            $submitted = unserialize($submittedser);
        }
        // For each question field, check response against database.
        for ($i = 0; $i < $numquestions; $i++) {
            // Get question response for database.
            $name = 'question'.$i;
            $hiddenname = 'hiddenq'.$i;
            $response = $data["$name"];
            $qid = !empty($submittedser) ? $qid = $submitted[$i] : $data["$hiddenname"];

            // Check reponse for errors.
            if (!tool_securityquestions_verify_response($response, $user, $qid)) {
                $errmsg = get_string('formanswerfailed', 'tool_securityquestions');
            }
        }

        $tieroneduration = get_config('tool_securityquestions', 'tieroneduration');
        $tiertwoduration = get_config('tool_securityquestions', 'tiertwoduration');

        // If locked out, respond with a contextual lockout message.
        if (tool_securityquestions_is_locked_out($user)) {
            // Find the lockout tier, and display error message based on tier.
            $record = $DB->get_record('tool_securityquestions_loc', array('userid' => $user->id));
            switch ($record->tier) {
                case 1:
                    $timestring = format_time($record->timefailed + $tieroneduration - time());
                    $errmsg = get_string('formlockedouttimer', 'tool_securityquestions', $timestring);
                    break;

                case 2:
                    $timestring = format_time($record->timefailed + $tiertwoduration - time());;
                    $errmsg = get_string('formlockedouttimer', 'tool_securityquestions', $timestring);
                    break;

                default:
                    $errmsg = get_string('formlockedout', 'tool_securityquestions');
                    break;
            }
        }

        // Now we have decided on message, set for all questions.
        if (!empty($errmsg)) {
            for ($i = 0; $i < $numquestions; $i++) {
                $name = 'question'.$i;
                $errors[$name] = $errmsg;
            }

            if (!tool_securityquestions_is_locked_out($user)) {
                // If an error was found, increment lockout counter.
                tool_securityquestions_increment_lockout_counter($user);

                $lockoutamount = get_config('tool_securityquestions', 'lockoutnum');
                // If counter is now over the specified count, lock account.
                if (tool_securityquestions_get_lockout_counter($user) >= $lockoutamount && $lockoutamount > 0) {
                    tool_securityquestions_lock_user($user);

                    $newtier = $DB->get_field('tool_securityquestions_loc', 'tier', array('userid' => $user->id));

                    switch ($newtier) {
                        case 1:
                            $time = format_time($tieroneduration);
                            $errorstring = get_string('formlockedouttimer', 'tool_securityquestions', $time);
                            break;

                        case 2:
                            $time = format_time($tiertwoduration);
                            $errorstring = get_string('formlockedouttimer', 'tool_securityquestions', $time);
                            break;

                        default:
                            $errorstring = get_string('formlockedout', 'tool_securityquestions');
                            break;
                    }

                    // Output a notification to display to the user, based on the tier they just entered.
                    \core\notification::error($errorstring);
                } else {
                    // Show a regular error banner.
                    \core\notification::error(get_string('formanswerfailedbanner', 'tool_securityquestions'));
                }
            }
        }
    }

    // Clear any set submission question values.
    unset_user_preference('tool_securityquestions_submitted_vals');

    return $errors;
}

/**
 * This function checks the response for validity, and updates the hash if required.
 *
 * @param string $response the response to verify.
 * @param stdClass $user the user to verify for.
 * @param int $qid the qid to verify for
 * @return bool true if verified, else false
 */
function tool_securityquestions_verify_response($response, $user, $qid) {
    global $DB;

    // Execute DB query with data.
    $setresponse = $DB->get_field('tool_securityquestions_res', 'response', array('userid' => $user->id, 'qid' => $qid));
    // Hash response and compare to the database.
    $responsehash = tool_securityquestions_hash_response($response, $user);
    $sanitisedresponse = tool_securityquestions_sanitise_response($response);

    if (!password_verify($sanitisedresponse, $setresponse)) {
        // Hash may be legacy.
        $legacyresponsehash = tool_securityquestions_hash_response($response, $user, true);
        if ($setresponse === $legacyresponsehash) {
            // Here we need to update the hash in the DB.
            $DB->set_field('tool_securityquestions_res', 'response', $responsehash, ['userid' => $user->id, 'qid' => $qid]);
        } else {
            return false;
        }
    } else {
        // Response is verified. Store latest hash just to ensure the hash is on latest algo.
        $DB->set_field('tool_securityquestions_res', 'response', $responsehash, ['userid' => $user->id, 'qid' => $qid]);
    }

    return true;
}

// Question Setup.
/**
 * Picks questions for a user to respond to
 *
 * @param stdClass $user the user to pick questions for
 * @param bool $noreplace If questions are timed out, dont replace them. Used in validating of expired questions.
 * @return array $inputarr an array of question ID's to inject into a form
 */
function tool_securityquestions_pick_questions($user, $noreplace = false) {
    global $DB;
    global $CFG;

    $numquestions = get_config('tool_securityquestions', 'answerquestions');

    // First, check if user has had questions selected in the last 5 mins.
    $currentquestions = $DB->get_records('tool_securityquestions_ans', array('userid' => $user->id), 'id ASC');

    // Get array of questions less than 5 mins old.
    $temparray = array();
    foreach ($currentquestions as $question) {
        // Check if timecreated is <5 mins ago.
        $period = get_config('tool_securityquestions', 'questionduration');
        $currenttime = time();
        if ($noreplace) {
            array_push($temparray, $question);
        } else if ($question->timecreated >= ($currenttime - $period)) {
            array_push($temparray, $question);
        }
    }

    // If found, perform data manipulation, if not, pick new questions and store them.
    if (count($temparray) >= $numquestions) {
        // If questions found, Make sure the length of current array is what you are expecting, if not, get first n of array.
        if (count($temparray) > $numquestions) {
            $temparray = array_slice($temparray, 0, $numquestions);
        }

        $inputarr = array();
        $i = 0;
        // Change array to be the format required for form injection.
        foreach ($temparray as $question) {
            $inputarr[$i] = $question->qid;
            $i++;
        }

        return $inputarr;

    } else {

        // Get all questions with responses.
        $answeredquestions = $DB->get_records('tool_securityquestions_res', array('userid' => $user->id), 'qid ASC');

        // Filter for questions that are currently active.
        $answeredactive = array();

        $j = 0;
        foreach ($answeredquestions as $question) {
            $deprecated = $DB->get_field('tool_securityquestions', 'deprecated', array('id' => $question->qid));
            if ($deprecated == 0) {
                $answeredactive[$j] = $question;
                $j++;
            }
        }

        // Randomly pick n questions to be answered.
        $pickedkeys = array_rand($answeredactive, $numquestions);

        // Create array to pass questions ids to the form.
        $inputarr = array();
        $i = 0;
        foreach ($pickedkeys as $key) {
            $inputarr[$i] = $answeredactive[$key]->qid;
            $i++;
        }

        // Now questions have been picked, delete all records from table for user.
        $DB->delete_records('tool_securityquestions_ans', array('userid' => $user->id));

        // Now add current records for picked questions.
        $j = 0;
        foreach ($inputarr as $question) {
            $time = time();
            $DB->insert_record('tool_securityquestions_ans',
                array('userid' => $user->id, 'qid' => $inputarr[$j], 'timecreated' => $time));
            $j++;
        }

        return $inputarr;
    }
}

// Navigation Injection.
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

    // First check if user has the capability to interact with questions.
    if (!has_capability('tool/securityquestions:questionsaccess', $usercontext, $user)) {
        return;
    }

    // If users auth type is external, and they dont have a password, dont inject.
    if (!tool_securityquestions_check_external_auth()) {
        return;
    }

    // Only inject if user is on the preferences page.
    $onpreferencepage = $PAGE->url->compare(new moodle_url('/user/preferences.php'), URL_MATCH_BASE);
    if (!$onpreferencepage) {
        return null;
    }

    // Only inject if the plugin is enabled.
    if (!get_config('tool_securityquestions', 'enable_plugin')) {
        return null;
    }

    // Dont inject if not enough questions are set.
    if (count(tool_securityquestions_get_active_questions()) < get_config('tool_securityquestions', 'minquestions')) {
        return null;
    }

    $url = new moodle_url('/admin/tool/securityquestions/set_responses.php');
    $node = navigation_node::create(get_string('setresponsessettingsmenu', 'tool_securityquestions'), $url,
            navigation_node::TYPE_SETTING);
    $usernode = $navigation->find('useraccount', navigation_node::TYPE_CONTAINER);
    $usernode->add_node($node);
}

/**
 * Forces redirect to the set_responses page if users havent answered enough questions
 */
function tool_securityquestions_require_question_responses() {
    global $USER, $DB, $SESSION, $PAGE, $CFG;

    // First check if cached flag for satisfaction is set.
    if (property_exists($SESSION, 'tool_securityquestions_satisfied') && (!empty($SESSION->tool_securityquestions_satisfied))) {
        return;
    }

    // First check if user has the capability to interact with questions.
    $usercontext = context_user::instance($USER->id);
    if (!has_capability('tool/securityquestions:questionsaccess', $usercontext, $USER)) {
        return;
    }

    // If users auth type is external, and they dont have a password, dont redirect.
    if (!tool_securityquestions_check_external_auth()) {
        return;
    }

    $config = get_config('tool_securityquestions');
    // If questions already presented.
    if (property_exists($SESSION, 'tool_securityquestions_presentedresponse')) {
        if (!$config->mandatory_questions) {
            // Do not redirect if not mandatory.
            return;
        } else if ($config->graceperiod != 0) {
            $logintime = get_user_preferences('tool_securityquestions_logintime', null);
            // Check user time logged in since questions active.
            if ($logintime + $config->graceperiod >= time()) {
                // If still in grace period, return.
                return;
            }
        }
    }

    // Do not redirect if already on final page url, prevents redir loops from require_login.
    if ($PAGE->has_set_url() && $PAGE->url == $CFG->wwwroot.'/admin/tool/securityquestions/set_responses.php') {
        return;
    }

    // Check whether enough questions are set to make the plugin active.
    $setquestions = $DB->get_records('tool_securityquestions', array('deprecated' => 0));
    $requiredset = $config->minquestions;

    if (count($setquestions) >= $requiredset) {

        // Set user preference for first time being presented questions.
        if (get_user_preferences('tool_securityquestions_logintime', null) == null) {
            set_user_preference('tool_securityquestions_logintime', time());
        }

        // Check whether user has answered enough questions.
        $requiredquestions = $config->minuserquestions;
        $answeredquestions = tool_securityquestions_get_active_user_responses($USER);
        $url = new moodle_url('/admin/tool/securityquestions/set_responses.php');
        if (count($answeredquestions) < $requiredquestions) {
            // Dont redirect if not in browser session.
            if (!CLI_SCRIPT && !AJAX_SCRIPT) {
                // If page has URL set, set it to wantsurl for cancel. Avoids issues with dashboard not having PAGE->url set.
                if ($PAGE->has_set_url()) {
                    $SESSION->wantsurl = $PAGE->url;
                } else {
                    // Attempt to determine if on dashboard without triggering warnings.
                    // HACK.
                    if (preg_match('/my/', $_SERVER['REQUEST_URI'])) {
                        $SESSION->wantsurl = new moodle_url('/my/');
                    }
                }
                redirect($url);
            }
        } else {
            // Cache not required in SESSION.
            // This means that any actions that would prompt for additional responses,
            // will only do so in a new session.
            $SESSION->tool_securityquestions_satisfied = 1;
        }
    } else {
        // We should also cache here, and avoid overhead if plugin is not completely setup.
        $SESSION->tool_securityquestions_satisfied = 1;
    }
}

/**
 * Checks whether a users auth type should interact with Security Questions
 *
 * @return bool true if a user can interact with Security Questions, false if not
 */
function tool_securityquestions_check_external_auth() {
    global $USER;

    $auth = get_auth_plugin($USER->auth);
    $hascap = has_capability('moodle/user:changeownpassword', context_system::instance(), $USER->id);
    // If user cannot reset, or change password internally, or they dont have the capability, dont redir.
    if (!$auth->can_reset_password() || !$auth->can_change_password() ||  !empty($auth->change_password_url()) || !$hascap) {
        return false;
    } else {
        return true;
    }
}

// Set Questions and Responses.
/**
 * Inserts a question into the database
 *
 * @param string $question The question content to be inserted
 * @return bool returns true if a question was successfully inserted or undeprecated, false for failure
 */
function tool_securityquestions_insert_question($question) {
    global $DB, $USER;
    // Trim question first.
    $question = trim($question);
    if ($question != '') {
        // Construct query and determine if question already exists.
        $sqlquestion = $DB->sql_compare_text($question, strlen($question));
        $record = $DB->get_record_sql('SELECT * FROM {tool_securityquestions} WHERE content = ?', array($sqlquestion));

        if (empty($record)) {
            // Fire event for question addition.
            $event = \tool_securityquestions\event\question_added::question_added_event($USER, $question);
            $event->trigger();

            return $DB->insert_record('tool_securityquestions', array('content' => $question, 'deprecated' => 0));
        } else if (!empty($record) && $record->deprecated != 0) {
            // If Deprecated record found, undeprecate.
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

    // First check if question actually exists to set a response for.
    if ($DB->record_exists('tool_securityquestions', array('id' => $qid))) {
        // Hash response.
        $response = tool_securityquestions_hash_response($response, $USER);
        // Check if response to question already exists, if so update, else, create record.
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
 * Deletes a response for a user
 *
 * @param int $qid the question id to delete a response for
 * @return bool success if response deleted
 */
function tool_securityquestions_delete_response($qid) {
    global $DB, $USER;

    // First check if question actually exists to set a response for.
    if ($DB->record_exists('tool_securityquestions_res', array('qid' => $qid, 'userid' => $USER->id))) {
        $DB->delete_records('tool_securityquestions_res', array('qid' => $qid, 'userid' => $USER->id));
        return true;
    } else {
        return false;
    }
}

/**
 * Hashes and normalises user responses. If legacy mode is true
 * a SHA1 hash will be returned. This should only be used for comparison
 * to legacy hashes, which will be upgraded immediately after.
 *
 * @param string $response the string to be hashed and normalised
 * @param stdClass $user the user object we are hashing for
 * @param bool $legacy whether the returned hash should be a legacy hash.
 * @return string the normalised and hashed string
 */
function tool_securityquestions_hash_response($response, $user, $legacy = false) {
    $string = tool_securityquestions_sanitise_response($response);

    if (!$legacy) {
        // Hashing can be offloaded to the core method for hashing passwords.
        return hash_internal_user_password($string);
    } else {
        // Return the legacy SHA1 hash.
        $salt = hash('sha1', $user->username);
        return hash('sha1', $string.$salt);
    }
}

/**
 * Small helper function to sanitise strings before hashing/comparison
 *
 * @param string $response
 * @return string the sanitised response
 */
function tool_securityquestions_sanitise_response($response) {
    // Save the previous encoding state, then set to what is needed.
    $currencoding = mb_internal_encoding();
    mb_internal_encoding('UTF-8');
    $formatted = mb_strtolower(trim($response));
    // Now set encoding back to whatever was in place.
    mb_internal_encoding($currencoding);
    return $formatted;
}

// Lockout Interaction Functions.
/**
 * Increments the lockout counter for a user
 *
 * @param stdClass $user the user to increment the counter of
 */
function tool_securityquestions_increment_lockout_counter($user) {
    global $DB;

    // First ensure the user is initialised in the table.
    $lock = tool_securityquestions_get_lock_state($user);

    // Increment counter, and update the field.
    $count = $lock->counter;
    $DB->set_field('tool_securityquestions_loc', 'counter', ($count + 1), array('userid' => $user->id));
}

/**
 * This function ensures the lock record is fresh.
 * It instantiates the lock record if the user does not have a record,
 * And resets any expired lock records.
 *
 * @param stdClass $user the user to increment the counter of
 * @return stdClass returns the lock record of the user.
 */
function tool_securityquestions_get_lock_state($user) {
    global $DB;
    // Params for a clean lock reset.
    $resetarr = ['tier' => 0, 'counter' => 0, 'timefailed' => 0, 'userid' => $user->id];

    // Check if user exists in the lockout table.
    if (!$DB->record_exists('tool_securityquestions_loc', array('userid' => $user->id))) {
        // If not, create entry for user, not locked out, counter 0.
        $id = $DB->insert_record('tool_securityquestions_loc', $resetarr, true);
    } else {
        // Now check for if user has a lockout that is expired, and reset it.
        $record = $DB->get_record('tool_securityquestions_loc', array('userid' => $user->id));
        $id = $record->id;
        $resetduration = get_config('tool_securityquestions', 'lockoutexpiryduration');
        // Check if locks should expire, and check if this lock is valid for expiry.
        if (($record->timefailed < time() - $resetduration) && $record->timefailed != 0 && $resetduration != 0) {
            // Update the record to a fresh record.
            $resetarr['id'] = $id;
            $DB->update_record('tool_securityquestions_loc', $resetarr);

            // Log a new lockout expired event.
            $event = \tool_securityquestions\event\lockout_expired::lockout_expired_event($user);
            $event->trigger();
        }
    }

    // Return the up to date lock record.
    return $DB->get_record('tool_securityquestions_loc', ['id' => $id]);
}

/**
 * Checks whether a user is currently locked from resetting password.
 * This function checks time period of lockout tier, against current time,
 * to see if more attempts can be made.
 *
 * @param stdClass $user the user to increment the counter of
 * @return bool returns true if user is locked out, false if not locked out
 */
function tool_securityquestions_is_locked_out($user) {
    global $DB;
    // First ensure that the user is initialised in the table.
    $lock = tool_securityquestions_get_lock_state($user);

    $tierone = get_config('tool_securityquestions', 'tieroneduration');
    $tiertwo = get_config('tool_securityquestions', 'tiertwoduration');

    // Now check the users tier, and compare times.
    switch ($lock->tier) {
        case 0:
            return false;

        case 1:
            // If still within lockout duration, true.
            return (time() - $lock->timefailed < $tierone);

        case 2:
            // If still within lockout duration, true.
            return (time() - $lock->timefailed < $tiertwo);

        default:
            return true;
    }
}

/**
 * Locks a user, initialises then locks a user if not found in table
 *
 * @param stdClass $user the user to increment the counter of
 */
function tool_securityquestions_lock_user($user) {
    global $DB;
    // First ensure that the user is initialised in the table (should never be uninitialised here).
    $lock = tool_securityquestions_get_lock_state($user);

    // Set the new tier of lockout, never exceeding 3, skipping 0 time tiers.
    $tierfound = false;
    $origtier = $lock->tier;
    $newtier = $origtier < 3 ? $origtier + 1 : $origtier;

    while (!$tierfound) {
        switch ($newtier) {
            case 1:
                // If duration for tier is 0, jump to next and try again.
                if (get_config('tool_securityquestions', 'tieroneduration') == 0) {
                    $newtier++;
                } else {
                    $tierfound = true;
                }
                break;

            case 2:
                // If duration for tier is 0, jump to next and try again.
                if (get_config('tool_securityquestions', 'tiertwoduration') == 0) {
                    $newtier++;
                } else {
                    $tierfound = true;
                }
                break;

            default:
                // Full lockout reached.
                $tierfound = true;
                break;
        }
    }

    // Set the new tier, and reset the counter for that tier, then update timefailed.
    $newfields = ['tier' => $newtier, 'counter' => 0, 'timefailed' => time(), 'id' => $lock->id];
    $DB->update_record('tool_securityquestions_loc', $newfields);

    // Calculate locked until.
    switch ($newtier) {
        case 1:
            $duration = get_config('tool_securityquestions', 'tieroneduration');
            break;

        case 2:
            $duration = get_config('tool_securityquestions', 'tiertwoduration');
            break;

        default:
            $duration = get_config('tool_securityquestions', 'lockoutexpiryduration');
            break;
    }
    $lockeduntil = time() + $duration;

    // Trigger event with new tier set.
    $event = \tool_securityquestions\event\locked_out::locked_out_event($user, $lockeduntil);
    $event->trigger();
}

/**
 * Unlocks a user, and resets the lockout counter
 *
 * @param stdClass $unlockuser the user to unlock.
 */
function tool_securityquestions_unlock_user($unlockuser) {
    global $DB, $USER;
    // First ensure that the user is initialised in the table (should never be uninitialised here).
    $lock = tool_securityquestions_get_lock_state($unlockuser);

    // Set lockout to false, and reset counter to 0.
    $newfields = ['tier' => 0, 'counter' => 0, 'timefailed' => 0, 'id' => $lock->id];
    $DB->update_record('tool_securityquestions_loc', $newfields);

    // Fire an unlocked event for the user.
    $event = \tool_securityquestions\event\user_unlocked::user_unlocked_event($USER, $unlockuser);
    $event->trigger();
}

/**
 * Returns the current count of the attempt counter
 *
 * @param stdClass $user the user to increment the counter of
 * @return int the current attempt counter
 */
function tool_securityquestions_get_lockout_counter($user) {
    // Get lock state and return the counter.
    $lock = tool_securityquestions_get_lock_state($user);
    return $lock->counter;
}

/**
 * Resets the lockout attempt counter to 0
 *
 * @param stdClass $user the user to increment the counter of
 */
function tool_securityquestions_reset_lockout_counter($user) {
    global $DB;
    // First ensure that the user is initialised in the table (should never be uninitialised here).
    $lock = tool_securityquestions_get_lock_state($user);
    $DB->set_field('tool_securityquestions_loc', 'counter', 0, array('id' => $lock->id));
}

/**
 * Clears all responses for a user
 *
 * @param stdClass $user the user to clear responses for
 */
function tool_securityquestions_clear_user_responses($user) {
    global $DB;

    $DB->delete_records('tool_securityquestions_res', array('userid' => $user->id));
    set_user_preference('tool_securityquestions_logintime', time());
}

/**
 * Generates the select array for use in setting resposes.
 */
function tool_securityquestions_generate_select_array() {
    global $DB;

    // Generate array for questions.
    $questions = tool_securityquestions_get_active_questions();
    $qarray = array();
    foreach ($questions as $question) {
        $qarray[$question->id] = $question->content;
    }

    return $qarray;
}
