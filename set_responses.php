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

$success = optional_param('success', -1, PARAM_INT);

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

$form = new \tool_securityquestions\form\set_responses();
if ($form->is_cancelled()) {
    // Unset wantsurl
    unset($SESSION->wantsurl);
    redirect($prevurl);

} else if ($fromform = $form->get_data()) {
    $fail = false;

    $num = $fromform->elementnum;
    for ($i = 1; $i <= $num; $i++) {
        $qname = "questions$i";
        $rname = "response$i";
        $qid = $fromform->$qname;
        $response = $fromform->$rname;

        // Check for failure before moving on
        if (tool_securityquestions_add_response($response, $qid)) {
            $fail = true;
        }
    }

    if (!empty($SESSION->wantsurl)) {
        // Got here from a redirect
        unset($SESSION->wantsurl);
        redirect($prevurl);
    } else {
        if ($fail) {
            redirect(new moodle_url($PAGE->url, array('success' => 0)));
        } else {
            redirect(new moodle_url($PAGE->url, array('success' => 1)));
        }
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

$form->display();

// output notifications for status
if ($success == 1) {
    echo $OUTPUT->notification(get_string('formresponserecorded', 'tool_securityquestions'), 'notifysuccess');
}
if ($success == 0) {
    echo $OUTPUT->notification(get_string('formresponsenotrecorded', 'tool_securityquestions'), 'notifyerror');
}

echo $OUTPUT->footer();

