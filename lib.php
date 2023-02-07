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

/**
 * After require login functionality.
 * @return void
 */
function tool_securityquestions_after_require_login(): void {
    if (get_config('tool_securityquestions', 'enable_plugin')) {
        require_once(__DIR__.'/locallib.php');
        tool_securityquestions_require_question_responses();
    }
}

/**
 * Extend the navigation.
 * @param navigation $navigation
 * @param stdClass $user
 * @param context $usercontext
 * @param course $course
 * @param context $coursecontext
 * @return void
 */
function tool_securityquestions_extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext): void {
    if (get_config('tool_securityquestions', 'enable_plugin')) {
        require_once(__DIR__.'/locallib.php');
        tool_securityquestions_inject_navigation_node($navigation, $user, $usercontext, $course, $coursecontext);
    }
}

/**
 * Extend the password form.
 * @param mform $mform
 * @param stdClass $user
 * @return void
 */
function tool_securityquestions_extend_set_password_form($mform, $user): void {
    if (get_config('tool_securityquestions', 'enable_plugin')) {
        require_once(__DIR__.'/locallib.php');
        tool_securityquestions_inject_security_questions($mform, $user);
    }
}

/**
 * Validate the password form.
 * @param array $data
 * @param stdClass $user
 * @return array
 */
function tool_securityquestions_validate_extend_set_password_form($data, $user): array {
    if (get_config('tool_securityquestions', 'enable_plugin')) {
        require_once(__DIR__.'/locallib.php');
        $errors = tool_securityquestions_validate_injected_questions($data, $user);
        return $errors;
    }
    return [];
}

/**
 * Reset the lock counter.
 * @param array $data
 * @param stdClass $user
 * @return void
 */
function tool_securityquestions_post_set_password_requests($data, $user): void {
    if (get_config('tool_securityquestions', 'enable_plugin')) {
        // If password reset is successful, reset counter to 0.
        require_once(__DIR__.'/locallib.php');
        tool_securityquestions_reset_lockout_counter($user);
    }
}
