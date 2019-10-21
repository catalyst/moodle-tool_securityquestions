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
 * Admin settings page for Security Questions plugin
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


global $CFG;

if ($hassiteconfig) {

    // First, check values for items are set to sane amounts, if not, fix
    // Min Questions > Min user questions > Min user responses
    if (get_config('tool_securityquestions', 'minuserquestions') > get_config('tool_securityquestions', 'minquestions')) {
        set_config('minuserquestions', get_config('tool_securityquestions', 'minquestions'), 'tool_securityquestions');
    }

    if (get_config('tool_securityquestions', 'answerquestions') > get_config('tool_securityquestions', 'minuserquestions')) {
        set_config('answerquestions', get_config('tool_securityquestions', 'minuserquestions'), 'tool_securityquestions');
    }

    // Create validator category for page and external page
    $ADMIN->add('tools', new admin_category('securityquestions', get_string('pluginname', 'tool_securityquestions')));

    // Add External admin page for setting security questions
    $ADMIN->add('securityquestions', new admin_externalpage('tool_securityquestions_setform',
        get_string('setquestionspagename', 'tool_securityquestions'),
        new moodle_url('/admin/tool/securityquestions/set_questions.php')));

    // Add External admin page for resetting lockedout users
    $ADMIN->add('securityquestions', new admin_externalpage('tool_securityquestions_reset_lockout',
        get_string('resetuserpagename', 'tool_securityquestions'),
        new moodle_url('/admin/tool/securityquestions/reset_lockout.php')));

    $settings = new admin_settingpage('securitysettings', get_string('securityquestionssettings', 'tool_securityquestions'));
    $ADMIN->add('securityquestions', $settings);

    if (!during_initial_install()) {

        // Alert if using config template
        $name = get_config('tool_securityquestions', 'chosen_template');
        if (trim($name) != '') {
            // Construct the display text
            $text = get_string('forcedconfig', 'tool_securityquestions') . $name;
            $text .= get_string('configloc', 'tool_securityquestions');
            $text .= (__DIR__ . get_string('configpath', 'tool_securityquestions', $name).'<br>');
            $text .= get_string("template$name", 'tool_securityquestions');

            // Add the control
            $templatedesc = $OUTPUT->notification($text, 'notifymessage');
            $settings->add(new admin_setting_heading('tool_securityquestions/template_heading', '', $templatedesc));
        }

        $settings->add(new admin_setting_configcheckbox('tool_securityquestions/enable_plugin', get_string('settingsenablename', 'tool_securityquestions'),
                    get_string('settingsenabledesc', 'tool_securityquestions'), 0));

        $settings->add(new admin_setting_configcheckbox('tool_securityquestions/mandatory_questions', get_string('settingsmandatoryquestions', 'tool_securityquestions'),
                    get_string('settingsmandatoryquestionsdesc', 'tool_securityquestions'), 1));

        $settings->add(new admin_setting_configduration('tool_securityquestions/graceperiod', get_string('settingsgraceperiod', 'tool_securityquestions'),
                    get_string('settingsgraceperioddesc', 'tool_securityquestions'), 48 * HOURSECS, HOURSECS));

        $settings->add(new admin_setting_configtext('tool_securityquestions/minquestions', get_string('settingsminquestions', 'tool_securityquestions'),
                    get_string('settingsminquestionsdesc', 'tool_securityquestions'), 10, PARAM_INT));

        $settings->add(new admin_setting_configtext('tool_securityquestions/minuserquestions', get_string('settingsminuserquestions', 'tool_securityquestions'),
                    get_string('settingsminuserquestionsdesc', 'tool_securityquestions'), 3, PARAM_INT));

        $settings->add(new admin_setting_configtext('tool_securityquestions/answerquestions', get_string('settingsanswerquestions', 'tool_securityquestions'),
                    get_string('settingsanswerquestionsdesc', 'tool_securityquestions'), 2, PARAM_INT));

        $settings->add(new admin_setting_configduration('tool_securityquestions/questionduration', get_string('settingsquestionduration', 'tool_securityquestions'),
                    get_string('settingsquestiondurationdesc', 'tool_securityquestions'), 5 * MINSECS, MINSECS));

        $settings->add(new admin_setting_configtext('tool_securityquestions/lockoutnum', get_string('settingslockoutnum', 'tool_securityquestions'),
                    get_string('settingslockoutnumdesc', 'tool_securityquestions'), 3, PARAM_INT));

        $settings->add(new admin_setting_configtext('tool_securityquestions/questionfile', get_string('settingsquestionfile', 'tool_securityquestions'),
                    get_string('settingsquestionfile', 'tool_securityquestions'), '', PARAM_TEXT));
    }
}