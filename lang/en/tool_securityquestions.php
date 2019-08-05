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
 *
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Settings menu strings
$string['pluginname'] = 'Security Questions';
$string['setquestionspagename'] = 'Modify Security Questions';
$string['securityquestionssettings'] = 'Security Questions Settings';
$string['setsecurityquestionspagestring'] = 'Modify Security Questions';
$string['answerquestionspagestring'] = 'Security Question Verification Required';
$string['setresponsespagestring'] = 'Set Answers to Security Questions';
$string['setresponsessettingsmenu'] = 'Edit Security Question Responses';

// Form Strings
$string['formselectquestion'] = 'Select Question to Modify';
$string['formquestionentry'] = 'Enter a question to add to the question pool:';
$string['formdeprecateentry'] = 'Enter a question ID to deprecate:';
$string['formresponseselectbox'] = 'Select Question to respond to:';
$string['formresponseentrybox'] = 'Enter response to the selected question:';
$string['formresponsesremaining'] = '{$a} additional responses required.';
$string['formresponserecorded'] = 'Response successfully recorded for question: {$a}';
$string['formresponseempty'] = 'Response cannot be empty.';
$string['formanswerquestion'] = 'Enter a response to question {$a}:';
$string['formanswerfailed'] = 'Response does not match recorded response';

// Setting Strings
$string['settingsenablename'] = 'Enable Plugin';
$string['settingsenabledesc'] = 'Check to enable security question validation';
$string['settingsminquestions'] = 'Minimum number of active security questions';
$string['settingsminquestionsdesc'] = 'Enter the minimum number of security questions that can be active at a single time. More questions must be added before other questions may be deprecated.';
$string['settingsminuserquestions'] = 'Minimum number of user answered questions';
$string['settingsminuserquestionsdesc'] = 'Enter the minimum number of active security questions that a user must answer before they are no longer prompted.';
$string['settingsanswerquestions'] = 'Questions required for verification';
$string['settingsanswerquestionsdesc'] = 'The number of questions required for users to verify themselves to perform account security actions.';
$string['settingsquestionduration'] = 'Question Duration';
$string['settingsquestiondurationdesc'] = 'The duration that questions are active when selected on a high-security page. After this period, new questions will be selected.';
$string['settingsinjectpoints'] = 'Injection Points for Security Questions';
$string['settingsinjectpointsdesc'] = 'Select all forms that the security questions should be injected into.';
$string['settingsinjectchangepw'] = 'Change Password Form.';
$string['settingsinjectsetpw'] = 'Set Password Form.';
