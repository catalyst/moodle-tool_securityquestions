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

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__.'/answer_questions_form.php');
require_once(__DIR__.'/locallib.php');

defined('MOODLE_INTERNAL') || die();
global $DB;
global $USER;
global $CFG;
// TODO add page setup here to make it clean

$prevurl = $CFG->wwwroot;


// ===========================SETUP FORM DATA===================================

$inputarr = pick_questions();

// ========================================FORM BEHAVIOUR==================================
$form = new answer_questions_form(null, $inputarr);
if ($form->is_cancelled()) {

    redirect($prevurl);

} else if ($fromform = $form->get_submitted_data()) {
    $passed = validate_responses($fromform);
    if ($passed) {
        echo 'PASSED VALIDATION';
        die;
    } else if (!$passed) {
        echo 'FAILED VALIDATION';
        die;
    }
} else {
    // Build the page output.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('answerquestionspagestring', 'tool_securityquestions'));
    display_questions($inputarr);
    $form->display();

    echo $OUTPUT->footer();
}

// ===============================================FORM SETUP FUNCTIONS=======================================

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

function display_questions($inputarr) {
    global $DB;
    $i = 1;
    foreach ($inputarr as $question) {
        $content = $DB->get_field('tool_securityquestions', 'content', array('id' => $question));
        echo "<h3>Question $i: $content</h3>";
        $i++;
    }
}

function validate_responses($data) {
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
            return false;
        }
    }
    return true;
}

