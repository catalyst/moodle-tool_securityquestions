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

        /*$configlist = array(1,2,3,4,5,6,7,8,9,10);

        $settings->add(new admin_setting_configselect('tool_securityquestions/questionentrybox', get_string('settingsquestionboxname', 'tool_securityquestions'),
                    get_string('settingsquestionboxdesc', 'tool_securityquestions'), 0, $configlist));
        $settings->add(new admin_setting_configtextarea('tool_securityquestions/questionentryarea', get_string('settingsquestionentryname', 'tool_securityquestions'),
                    get_string('settingsquestionentryname', 'tool_securityquestions'), 'blah'));*/
    }
}