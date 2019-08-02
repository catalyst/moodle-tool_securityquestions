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
    $active = $DB->get_records('tool_securityquestions', array('deprecated' => 0));
    return $active;
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

function inject_security_questions($mform) {
    global $DB;
    global $USER;

    $inputarr = pick_questions();
    $numquestions = get_config('tool_securityquestions', 'answerquestions');

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

function validate_injected_questions($data, $errors) {
    global $DB;
    global $USER;
    $numquestions = get_config('tool_securityquestions', 'answerquestions');

    // For each question field, check response against database
    for ($i = 0; $i < $numquestions; $i++) {
        // Get question response for database
        $name = 'question'.$i;
        $hiddenname = 'hiddenq'.$i;
        $response = $data->$name;
        $qid = $data->$hiddenname;

        $qcontent = $DB->get_record('tool_securityquestions', array('id' => $qid));

        // Execute DB query with data
        $setresponse = $DB->get_field('tool_securityquestions_res', 'response', array('userid' => $USER->id, 'qid' => $qid));
        // Hash response and compare to the database
        $response = hash('sha1', $response);
        if ($response != $setresponse) {
            $errors[$name] = 'wawawaw';
        }
    }
    return $errors;
}

// =============================================QUESTION SETUP=========================================================

function pick_questions() {
    global $DB;
    global $USER;

    // Get all questions with responses
    $numquestions = get_config('tool_securityquestions', 'answerquestions');
    $answeredquestions = $DB->get_records('tool_securityquestions_res', array('userid' => $USER->id));

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

    return $inputarr;
}
