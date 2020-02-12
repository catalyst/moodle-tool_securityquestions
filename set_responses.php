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
require_once(__DIR__.'/locallib.php');

// PAGE->seturl must be set before require_login to avoid infinite redir loop
$url = new moodle_url('/admin/tool/securityquestions/set_responses.php');
$PAGE->set_url($url);

// First, check if the require_recent_login function exists
if (function_exists('require_recent_login')) {
    // Require recent login to edit responses to questions
    require_recent_login();
} else {
    require_login();
}

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title('Edit Security Question Responses');
$PAGE->set_heading(get_string('setresponsespagestring', 'tool_securityquestions'));
$PAGE->set_cacheable(false);

// Add navigation menu
if ($node = $PAGE->settingsnav->find('usercurrentsettings', null)) {
    $PAGE->navbar->add($node->get_content(), $node->action());
}
$PAGE->navbar->add(get_string('setresponsessettingsmenu', 'tool_securityquestions'));

$notifysuccess = false;
$notifycontent = '';

if (!empty($SESSION->wantsurl)) {
    $prevurl = $SESSION->wantsurl;
} else {
    $prevurl = new moodle_url('/user/preferences.php');
}

$success = optional_param('success', -1, PARAM_INT);

$active = count(tool_securityquestions_get_active_user_responses($USER));
// If deleting a response, process here
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete != 0) {
    if ($active - 1 < get_config('tool_securityquestions', 'minuserquestions')) {
            // Unable to delete, not enough responses set
            $deletestatus = false;
    } else {
        if (tool_securityquestions_delete_response($delete)) {
            $deletestatus = true;
        } else {
            // unable to delete (question probably doesnt exist)
            $deletestatus = false;
        }
    }
}

$form = new \tool_securityquestions\form\set_responses();
if ($form->is_cancelled()) {
    // Unset wantsurl
    unset($SESSION->wantsurl);
    redirect($prevurl);

} else if ($form->no_submit_button_pressed()) {
    // Delete button pressed somewhere on the form
    $data = $form->get_submitted_data();
    $deletes = $data->preset;
    for ($i = 0; $i < $deletes; $i++) {
        $name = "delete$i";
        $qidname = "qid$i";
        if (!empty($data->$name)) {
            $url = new moodle_url('/admin/tool/securityquestions/set_responses.php', array('delete' => $data->$qidname));
            redirect($url);
        }
    }

} else if ($fromform = $form->get_data()) {
    // Check for updated responses
    $updates = $fromform->preset;
    for ($i = 0; $i < $updates; $i++) {
        $name = "preset$i";
        $qidname = "qid$i";
        if (!empty($fromform->$name)) {
            // Update response
            tool_securityquestions_add_response($fromform->$name, $fromform->$qidname);
        }
    }

    // Add new responses
    $num = $fromform->new;
    for ($i = 0; $i < $num; $i++) {
        $qname = "questions$i";
        $rname = "response$i";
        $qid = $fromform->$qname;
        $response = $fromform->$rname;

        if (!empty($response)) {
            // Check for failure before moving on
            tool_securityquestions_add_response($response, $qid);
        }
    }

    // Redirect after DB actions
    if (!empty($SESSION->wantsurl)) {
        // Got here from a redirect
        unset($SESSION->wantsurl);
        redirect($prevurl);
    } else {
        redirect($prevurl);
    }
}

// Build the page output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('setresponsespagestring', 'tool_securityquestions'));
echo '<br>';

// Output a notification for grace period time remaining, if set
$logintime = get_user_preferences('tool_securityquestions_logintime', null);
if (!get_config('tool_securityquestions', 'mandatoryquestions') && $logintime != null
    && get_config('tool_securityquestions', 'graceperiod')) {

    // Find remaining time in grace period, and output it formatted nicely
    $timerem = ($logintime + get_config('tool_securityquestions', 'graceperiod')) - time();

    // Check if there is time left
    if ($timerem > 0) {
        $duration = sprintf('%02d:%02d:%02d', ($timerem / HOURSECS), (($timerem / MINSECS) % MINSECS), $timerem % MINSECS);
        echo $OUTPUT->notification(get_string('formgraceperiodtimerem', 'tool_securityquestions', $duration), 'notifymessage');
    }
}

if ($active > 0) {
    // output a notification explaining how questions work when already set
    echo $OUTPUT->notification(get_string('formquestioninfo', 'tool_securityquestions'), 'notifymessage');
}

$form->display();
$PAGE->requires->js_call_amd('tool_securityquestions/hide_selector_question', 'init');

if ($delete != 0) {
    if ($deletestatus) {
        echo $OUTPUT->notification(get_string('formresponsedeleted', 'tool_securityquestions'), 'notifysuccess');
    } else {
        echo $OUTPUT->notification(get_string('formresponsenotdeleted', 'tool_securityquestions'), 'notifyerror');
    }
}

// output notifications for status
if ($success == 1) {
    echo $OUTPUT->notification(get_string('formresponserecorded', 'tool_securityquestions'), 'notifysuccess');
}
if ($success == 0) {
    echo $OUTPUT->notification(get_string('formresponsenotrecorded', 'tool_securityquestions'), 'notifyerror');
}

echo $OUTPUT->footer();
