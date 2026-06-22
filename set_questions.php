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
 * Page for setting questions to be used on the site
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__.'/locallib.php');

defined('MOODLE_INTERNAL') || die();

admin_externalpage_setup('tool_securityquestions_setform');

// Setup notification block.
$notifyadd = false;
$notifyaddcontent = '';
$notifydep = false;
$notifydepcontent = '';
$notifydeptype = 'notifyerror';
$notifydelete = false;
$notifydeletetype = 'notifyerror';
$notifydeletecontent = '';

// Deprecate question from action if set.
$deprecate = optional_param('deprecate', 0, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);
$delete = optional_param('delete', 0, PARAM_INT);

$seturl = $CFG->wwwroot . '/admin/tool/securityquestions/set_questions.php';

$qcount = count(tool_securityquestions_get_active_questions());
if (get_config('tool_securityquestions', 'minquestions') - $qcount <= 0) {
    $qremaining = 0;
} else {
    $qremaining = (get_config('tool_securityquestions', 'minquestions') - $qcount);
}

$prevurl = ($CFG->wwwroot.'/admin/category.php?category=securityquestions');
$form = new \tool_securityquestions\form\set_questions();
if ($form->is_cancelled()) {

    redirect($prevurl);

} else if ($fromform = $form->get_data()) {
    global $DB;

    // Check if there is a question to be added.
    $question = $fromform->questionentry;
    if ($question != '') {
        // Check whether record with that question exists.
        tool_securityquestions_insert_question($question);

        // Setup success notification.
        $notifyaddcontent = $question;
        $notifyadd = true;
    }

    // Redraw page.
    redirect($PAGE->url);
}

// Build the page output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('setsecurityquestionspagestring', 'tool_securityquestions'));

if ($deprecate) {
    if ($confirm) {
        require_sesskey();
        $result = tool_securityquestions_deprecate_question($deprecate);
        if ($result) {
            echo $OUTPUT->notification(
                get_string('formquestiondeprecated', 'tool_securityquestions', $deprecate),
                \core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            echo $OUTPUT->notification(
                get_string('formquestionnotdeprecated', 'tool_securityquestions', $deprecate),
                \core\output\notification::NOTIFY_ERROR
            );
        }
    } else {
        echo $OUTPUT->confirm(
            get_string('formconfirmdeprecate', 'tool_securityquestions'),
            new moodle_url($PAGE->url, ['deprecate' => $deprecate, 'confirm' => 1]),
            new moodle_url($PAGE->url)
        );
        echo $OUTPUT->footer();
        exit;
    }
} else if ($delete) {
    if ($confirm) {
        require_sesskey();
        $result = tool_securityquestions_delete_question($delete);
        if ($result) {
            echo $OUTPUT->notification(
                get_string('formquestiondeleted', 'tool_securityquestions', $delete),
                \core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            echo $OUTPUT->notification(
                get_string('formquestionnotdeleted', 'tool_securityquestions', $delete),
                \core\output\notification::NOTIFY_ERROR
            );
        }
    } else {
        echo $OUTPUT->confirm(
            get_string('formconfirmdelete', 'tool_securityquestions'),
            new moodle_url($PAGE->url, ['delete' => $delete, 'confirm' => 1]),
            new moodle_url($PAGE->url)
        );
        echo $OUTPUT->footer();
        exit;
    }
}

echo '<br>';
$form->display();

// Echo notifications for actions.
if ($notifyadd == true) {
    echo $OUTPUT->notification(get_string('formquestionadded', 'tool_securityquestions', $notifyaddcontent), 'notifysuccess');
}

echo '<br>';
echo $OUTPUT->heading(get_string('formcurrentquestions', 'tool_securityquestions', $qcount), 3);
if ($qremaining == 0) {
    echo $OUTPUT->heading(get_string('formstatusactive', 'tool_securityquestions', $qremaining), 4);
} else {
    echo $OUTPUT->heading(get_string('formstatusnotactive', 'tool_securityquestions', $qremaining), 4);
}

// Output questionst table if questions are set.
$table = \tool_securityquestions\local\table_manager::get_questions_table();
if ($table) {
    echo $table;
}

echo $OUTPUT->footer();
