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
 * File containing Lang strings for the plugin
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Settings menu strings.
$string['pluginname'] = 'Security Questions';
$string['setquestionspagename'] = 'Modify security questions';
$string['securityquestionssettings'] = 'Security questions settings';
$string['setsecurityquestionspagestring'] = 'Modify security questions';
$string['answerquestionspagestring'] = 'Security question verification required';
$string['setresponsespagestring'] = 'Set answers to security questions';
$string['setresponsessettingsmenu'] = 'Security question responses';
$string['resetuserpagename'] = 'Reset security question lockouts';

// Form Strings.
$string['formselectquestion'] = 'Select question to modify';
$string['formquestionentry'] = 'Enter a question to add to the question pool:';
$string['formdeprecateentry'] = 'Enter a question ID to deprecate:';
$string['formdeprecatenotfound'] = 'Question does not exist';
$string['formresponseselectbox'] = 'Select question to respond to:';
$string['formresponseentrybox'] = 'Enter response to the selected question:';
$string['formresponsesremaining'] = '{$a} additional responses required.';
$string['formresponserecorded'] = 'Response(s) successfully recorded.';
$string['formresponsenotrecorded'] = 'There was an error when setting response(s). Please try again.';
$string['formresponseempty'] = 'Response cannot be empty.';
$string['formanswerquestion'] = 'Enter a response to question {$a}:';
$string['formanswerfailed'] = 'Responses do not match recorded responses.';
$string['formlockedout'] = 'Account is locked from resetting password. Please contact System Administrators for further assistance.';
$string['formresetid'] = 'Enter account ID to be unlocked:';
$string['formresetnotnumber'] = 'ID must be a number';
$string['formresetnotfound'] = 'User does not exist';
$string['formuserunlocked'] = 'User successfully unlocked.';
$string['formuserresponsescleared'] = 'User responses successfully cleared';
$string['formresetbutton'] = 'Clear responses';
$string['formclearresponses'] = 'Also clear user question responses';
$string['formquestionadded'] = 'Question successfully added: {$a}';
$string['formdeprecate'] = 'Deprecate question';
$string['formquestiondeprecated'] = 'Question ID {$a} successfully deprecated.';
$string['formremindme'] = 'Remind me on next login';
$string['formsaveresponse'] = 'Save response(s)';
$string['formgraceperiodtimerem'] = 'You are currently in a grace period for security questions. In this time, you may choose not to answer security questions, and will be reminded on next login.
                                     After the grace period ends, you must answer security questions before accessing the rest of the system. Grace period duration remaning: {$a}';
$string['formresetlockout'] = 'Reset lockout';
$string['formclearresponses'] = 'Clear user responses:';
$string['formclearresponsestable'] = 'Clear responses';
$string['formclearresponsesdesc'] = 'Enter a username or email to clear responses for user.';
$string['formnolockedusers'] = 'No locked users found';
$string['formlockedoutusers'] = 'Locked out users';
$string['formaddquestionbutton'] = 'Add question';
$string['formtablequestion'] = 'Question';
$string['formtabledeprecate'] = 'Deprecated';
$string['formnoquestions'] = 'No questions set';
$string['formcurrentquestions'] = 'Current questions: {$a}';
$string['formquestiondeleted'] = 'Question ID {$a} successfully deleted.';
$string['formquestionnotdeleted'] = 'Unable to delete Question {$a}.';
$string['formtablecount'] = 'Usage count';
$string['formstatusactive'] = 'Active - {$a} additional questions required.';
$string['formstatusnotactive'] = 'Not active - {$a} additional questions required.';
$string['formquestionnotdeprecated'] = 'Unable to deprecate question {$a}.';
$string['formnewquestion'] = ' New question:';
$string['formselectquestion'] = 'Select a question';
$string['formduplicateresponse'] = 'You may not answer the same question twice.';
$string['formalreadyanswered'] = 'Questions with responses set';
$string['formdeleteresponse'] = 'Delete response';
$string['formresponsedeleted'] = 'Response successfully deleted.';
$string['formresponsenotdeleted'] = 'Unable to delete response. Add more responses first, or record a new response for the question.';
$string['formrecordnewresponse'] = 'Record a new response for question:';
$string['formquestionnumtext'] = 'Question: {$a}';
$string['formquestioninfo'] = 'For added security, you cannot see the saved responses. You can only save new responses to the questions, or delete the response.';

// Setting Strings.
$string['settingsenablename'] = 'Enable plugin';
$string['settingsenabledesc'] = 'Check to enable security question validation';
$string['settingsminquestions'] = 'Minimum number of active security questions';
$string['settingsminquestionsdesc'] = 'Enter the minimum number of security questions that can be active at a single time. More questions must be added before other questions may be deprecated.';
$string['settingsminuserquestions'] = 'Minimum number of user answered questions';
$string['settingsminuserquestionsdesc'] = 'Enter the minimum number of active security questions that a user must answer before they are no longer prompted.';
$string['settingsanswerquestions'] = 'Questions required for verification';
$string['settingsanswerquestionsdesc'] = 'The number of questions required for users to verify themselves to perform account security actions.';
$string['settingsquestionduration'] = 'Question duration';
$string['settingsquestiondurationdesc'] = 'The duration that questions are active when selected on a high-security page. After this period, new questions will be selected.';
$string['settingsinjectpoints'] = 'Injection points for security questions';
$string['settingsinjectpointsdesc'] = 'Select all forms that the security questions should be injected into.';
$string['settingsinjectchangepw'] = 'Change password form.';
$string['settingsinjectsetpw'] = 'Set password form.';
$string['settingslockoutnum'] = 'Answer attempts before lockout';
$string['settingslockoutnumdesc'] = 'The number of attempts at answering questions a user is allowed before being locked out of password resetting. Set this control to 0 to disable locking.';
$string['settingsquestionfile'] = 'Question file to use';
$string['settingsquestionfiledesc'] = 'Enter the filepath of a question file you would like to use to add questions.';
$string['settingsmandatoryquestions'] = 'Mandatory security questions';
$string['settingsmandatoryquestionsdesc'] = 'Enable this control to make security questions mandatory. If disabled, users will still be prompted to answer questions on login,
                                             but they will be able to navigate away. On next login they will again be prompted.';
$string['settingsgraceperiod'] = 'Response grace period';
$string['settingsgraceperioddesc'] = 'A period of time during which a user can choose to not set question responses, even when mandatory questions are enabled. After this period users who
                                      have not set reponses will be forced to set responses. Set this control to 0 to disable the grace period.';

// Template Strings.
$string['forcedconfig'] = 'Settings are read only, configuration is forced in template file: ';
$string['badconfigload'] = 'Unable to load template configuration file. Check variables are correct, and that template is inside the templates folder. - ';
$string['configloc'] = ' at location: ';
$string['configpath'] = '/config_policies/{$a}.php';
$string['templateforced-on'] = 'This template enforces the default settings for the security controls, and ensures that users will always have enough questions to respond to, and enough responses to use for validation.';

// Event Strings.
$string['userlockedeventname'] = 'User locked from resetting password due to failed question attempts.';
$string['userunlockedeventname'] = 'User unlocked from resetting password by an Administrator.';
$string['questionaddedeventname'] = 'Administrator added security question.';
$string['questiondeprecatedeventname'] = 'Administrator deprecated security question.';
$string['questiondeletedeventname'] = 'Administrator deleted security question.';

// Injected Element Strings.
$string['injectedquestiontitle'] = 'Security Question {$a->num}: {$a->content}';

// Task Strings.
$string['taskcleantables'] = 'Clean plugin database of deleted users';

// Privacy API Strings.
$string['privacy:metadata:tool_securityquestions_res'] = 'This table stores information about user responses to security questions, including hashed responses, and which questions have been responded to.';
$string['privacy:metadata:tool_securityquestions_res:userid'] = 'The ID of the user with this response.';
$string['privacy:metadata:tool_securityquestions_res:response'] = 'The securely hashed response that a user has entered as a response to a question.';
$string['privacy:metadata:tool_securityquestions_res:qid'] = 'The question ID that a response corresponds to.';

$string['privacy:metadata:tool_securityquestions_loc'] = 'This table stores information about whether a user is locked from resetting their password, as well as the number of failed attempts at entering security questions.';
$string['privacy:metadata:tool_securityquestions_loc:userid'] = 'The ID of the user to keep track of the locked status.';
$string['privacy:metadata:tool_securityquestions_loc:counter'] = 'The amount of times that a user has failed security question validation since last password reset.';
$string['privacy:metadata:tool_securityquestions_loc:locked'] = 'The locked status of a user.';

$string['privacy:metadata:tool_securityquestions_ans'] = 'This table stores information about whether about which random questions were picked to present to a user, and when the last random selection was.';
$string['privacy:metadata:tool_securityquestions_ans:userid'] = 'The ID of the user to keep track of questions to be answered.';
$string['privacy:metadata:tool_securityquestions_ans:qid'] = 'The question ID of a question that was picked for a user.';
$string['privacy:metadata:tool_securityquestions_ans:timecreated'] = 'The time that this question was picked for a user.';

$string['privacy:metadata:tool_securityquestions'] = 'Security questions data';

// Capability String.
$string['securityquestions:questionsaccess'] = 'Security questions interaction';
