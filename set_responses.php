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
 * Security questions response form for users
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
//defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__.'/set_responses_form.php');
global $CFG;

$form = new set_responses_form();
if ($form->is_cancelled()) {

} else if ($fromform = $form->get_data()) {
    $qid = $fromform->questions;
    global $USER;
    $response = $fromform->response;
    //Hash response
    $response = hash('sha1', $response);
    //Validation stops response from being empty
    //Check if response to question already exists, if so update, else, create record
    if ($DB->record_exists('tool_securityquestions_res', array('qid'=>$qid, 'userid' => $USER->id))) {
        //CHECK FOR SQL INJECTION
        $DB->set_field('tool_securityquestions_res', 'response', $response, array('qid'=>$qid, 'userid' => $USER->id));
    } else {
        $DB->insert_record('tool_securityquestions_res', array('qid' => $qid, 'userid' => $USER->id, 'response' => $response));
    }
}

// Build the page output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('setresponsespagestring', 'tool_securityquestions'));
//echo $OUTPUT->heading(get_string('setsecurityquestionspagestring', 'tool_securityquestions')); FIX
$form->display();

echo $OUTPUT->footer();