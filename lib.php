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

function tool_securityquestions_extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext) {
    global $USER, $PAGE;

    // Only inject if user is on the preferences page
    $onpreferencepage = $PAGE->url->compare(new moodle_url('/user/preferences.php'), URL_MATCH_BASE);
    if (!$onpreferencepage) {
        return null;
    }

    // Only inject if the plugin is enabled
    if (!get_config('tool_securityquestions', 'enable_plugin')) {
        return null;
    }

    $url = new moodle_url('/admin/tool/securityquestions/set_responses.php');
    $node = navigation_node::create(get_string('setresponsessettingsmenu', 'tool_securityquestions'), $url, 
            navigation_node::TYPE_SETTING, null, null, null);
    $navigation->add_node($node);
}

