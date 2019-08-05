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
 * Security Questions plugin for changing user passwords
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function tool_securityquestions_after_require_login() {
    require_once(__DIR__.'/locallib.php');
    require_question_responses();
}

function tool_securityquestions_extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext) {
    if (get_config('tool_securityquestions', 'enable_plugin')) {
        require_once(__DIR__.'/locallib.php');
        tool_securityquestions_inject_navigation_node($navigation, $user, $usercontext, $course, $coursecontext);
    }
}

function tool_securityquestions_extend_login_form($mform, $user) {
    if (get_config('tool_securityquestions', 'enable_plugin')) {
        // Check if injection point is enabled
        global $PAGE;

        // Change Password form
        if ((strpos($PAGE->url, 'change_password.php') !== false) &&
        (strpos(get_config('tool_securityquestions', 'injectpoints'), 'changepw')!== false)) {
            require_once(__DIR__.'/locallib.php');
            tool_securityquestions_inject_security_questions($mform, $user);

        // Set Pasword form
        } else if ((strpos($PAGE->url, 'set_password.php') !== false) &&
        (strpos(get_config('tool_securityquestions', 'injectpoints'), 'setpw')!== false)) {
            require_once(__DIR__.'/locallib.php');
            tool_securityquestions_inject_security_questions($mform, $user);
        }
    }
}

function tool_securityquestions_extend_login_validation($data, $errors, $user) {
    if (get_config('tool_securityquestions', 'enable_plugin')) {
        require_once(__DIR__.'/locallib.php');
        $errors = tool_securityquestions_validate_injected_questions($data, $errors, $user);
        return $errors;
    }
}

