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

require_once(dirname(__FILE__) . '/../../../config.php');
require_once(__DIR__.'/set_responses_form.php');
require_once(__DIR__.'/locallib.php');
require_once(__DIR__. '/../../../login/lib.php');

global $CFG, $SESSION, $PAGE, $USER;

$courseid = optional_param('courseid', SITEID, PARAM_INT);
$url = new moodle_url('/user/preferences.php');
if ($courseid !== SITEID) {
    $url->param('courseid', $courseid);
}
$PAGE->set_url($url);

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title('Edit Security Question Responses');
$PAGE->set_heading(get_string('setresponsespagestring', 'tool_securityquestions'));

// First, check if the require_recent_login function exists
if (function_exists('require_recent_login')) {
    // Require recent login to edit responses to questions
    require_recent_login();
}

$notifysuccess = false;
$notifycontent = '';

if (!empty($SESSION->wantsurl)) {
    $prevurl = $SESSION->wantsurl;
} else {
    $prevurl = new moodle_url('/my/');
}

$form = new set_responses_form();
if ($form->is_cancelled()) {
    redirect($prevurl);

} else if ($fromform = $form->get_data()) {
    $qid = $fromform->questions;
    $response = $fromform->response;
    tool_securityquestions_add_response($response, $qid);

    // Set flags for display notification
    $notifysuccess = true;
    $notifycontent = $DB->get_record('tool_securityquestions', array('id' => $qid))->content;
}

// Build the page output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('setresponsespagestring', 'tool_securityquestions'));
echo '<br>';
$form->display();

// Display notification if successful response recorded
if ($notifysuccess == true) {
    $notifysuccess == false;
    echo $OUTPUT->notification(get_string('formresponserecorded', 'tool_securityquestions', $notifycontent), 'notifysuccess');
}

generate_count_header();
echo $OUTPUT->footer();


function generate_count_header() {
    global $DB;
    global $USER;

    // Get number of additional responses required
    $answered = $DB->get_records('tool_securityquestions_res', array('userid' => $USER->id));

    // Check all answered questions for how many are currently valid
    $active = 0;
    foreach ($answered as $answer) {
        // Get field and check if deprecated
        $deprecated = $DB->get_field('tool_securityquestions', 'deprecated', array('id' => $answer->qid));
        if ($deprecated == 0) {
            $active++;
        }
    }

    $numrequired = get_config('tool_securityquestions', 'minuserquestions');
    $numremaining = $numrequired - $active;
    if ($numremaining < 0) {
        $numremaining = 0;
    }
    $displaystring = get_string('formresponsesremaining', 'tool_securityquestions', $numremaining);

    // Add display element
    echo("<h4>$displaystring</h4>");
}