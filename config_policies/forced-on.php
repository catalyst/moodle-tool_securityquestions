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
 * A tool to validate passwords against particular password policies.
 *
 * NIST Password Standards file, from NIST 800-63-B
 * File used to force configurations for the plugin
 *
 * @package   tool_passwordvalidator
 * @copyright 2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Coding standards ignored due to no config inclusion or moodle internal check
// @codingStandardsIgnoreStart
$CFG->forced_plugin_settings['tool_securityquestions']['chosen_template'] = basename(__FILE__, '.php');
// @codingStandardsIgnoreEnd
$CFG->forced_plugin_settings['tool_securityquestions']['minquestions'] = 10;
$CFG->forced_plugin_settings['tool_securityquestions']['minuserquestions'] = 3;
$CFG->forced_plugin_settings['tool_securityquestions']['answerquestions'] = 2;
$CFG->forced_plugin_settings['tool_securityquestions']['questionduration'] = 300;