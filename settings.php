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


global $CFG;

if ($hassiteconfig) {

    // Create validator category for page and external page
    $ADMIN->add('tools', new admin_category('securityquestions', get_string('pluginname', 'tool_securityquestions')));

    // Add External admin page for validation
    $ADMIN->add('securityquestions', new admin_externalpage('tool_securityquestions_setform',
        get_string('setquestionspagename', 'tool_securityquestions'),
        new moodle_url('/admin/tool/securityquestions/set_questions.php')));

    $settings = new admin_settingpage('securitysettings', get_string('securityquestionssettings', 'tool_securityquestions'));
    $ADMIN->add('securityquestions', $settings);

    if (!during_initial_install()) {
        $settings->add(new admin_setting_configcheckbox('tool_securityquestions/enable_plugin', get_string('settingsenablename', 'tool_securityquestions'),
                    get_string('settingsenabledesc', 'tool_securityquestions'), 0));

        $settings->add(new admin_setting_configtext('tool_securityquestions/minquestions', get_string('settingsminquestions', 'tool_securityquestions'),
                    get_string('settingsminquestionsdesc', 'tool_securityquestions'), 10, PARAM_INT));

        $settings->add(new admin_setting_configtext('tool_securityquestions/minuserquestions', get_string('settingsminuserquestions', 'tool_securityquestions'),
                    get_string('settingsminuserquestionsdesc', 'tool_securityquestions'), 3, PARAM_INT));

        $settings->add(new admin_setting_configtext('tool_securityquestions/answerquestions', get_string('settingsanswerquestions', 'tool_securityquestions'),
                    get_string('settingsanswerquestionsdesc', 'tool_securityquestions'), 2, PARAM_INT));

        $settings->add(new admin_setting_configduration('tool_passwordvalidator/questionduration', get_string('settingsquestionduration', 'tool_securityquestions'),
                    get_string('settingsquestiondurationdesc', 'tool_securityquestions'), 5 * MINSECS, MINSECS));
    }
}