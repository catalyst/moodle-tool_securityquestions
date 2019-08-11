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
 * @package    tool_securityquestion
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__.'/set_questions_form.php');
require_once(__DIR__.'/locallib.php');

defined('MOODLE_INTERNAL') || die();

admin_externalpage_setup('tool_securityquestions_setform');

// If a template is in use, apply it
tool_securityquestions_use_template_file();

$prevurl = ($CFG->wwwroot.'/admin/category.php?category=securityquestions');

$questions = $DB->get_records('tool_securityquestions');

$notifyadd = false;
$notifyaddcontent = '';

$notifydep = false;
$notifydepcontent = '';

$form = new set_questions_form();
if ($form->is_cancelled()) {

    redirect($prevurl);

} else if ($fromform = $form->get_data()) {
    global $DB;

    // Check if there is a question to be added
    $question = $fromform->questionentry;
    if ($question != '') {
        // Check whether record with that question exists
        tool_securityquestions_insert_question($question);

        // Setup success notification
        $notifyaddcontent = $question;
        $notifyadd = true;
    }

    // Check if there is a question to be deprecated
    $depid = $fromform->deprecate;
    if ($depid != '' && $fromform->confirmdeprecate) {
        if (tool_securityquestions_deprecate_question($depid)) {
            // Setup success notification
            $notifydepcontent = $depid;
            $notifydep = true;
        }
    }
}

// Build the page output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('setsecurityquestionspagestring', 'tool_securityquestions'));
echo '<br>';
$form->display();

if ($notifyadd == true) {
    $notifyadd == false;
    echo $OUTPUT->notification(get_string('formquestionadded', 'tool_securityquestions', $notifyaddcontent), 'notifysuccess');
}
if ($notifydep == true) {
    $notifydep == false;
    echo $OUTPUT->notification(get_string('formquestiondeprecated', 'tool_securityquestions', $notifydepcontent), 'notifysuccess');
}

echo '<br>';
echo '<h3>Current Questions</h3>';
generate_table();
echo $OUTPUT->footer();

// =============================================DISPLAY AND VALIDATION FUNCTIONS=========================================

function generate_table() {
    // Render table
    global $DB;
    // Get records from database for populating table
    $questions = $DB->get_records('tool_securityquestions');

    $table = new html_table();
    $table->head = array('ID', 'Question', 'Deprecated');
    $table->colclasses = array('centeralign', 'leftalign', 'centeralign');

    foreach ($questions as $question) {
        if ($question->deprecated == 1) {
            $dep = 'Yes';
        } else {
            $dep = 'No';
        }

        $table->data[] = array($question->id, $question->content, $dep);
    }
    echo html_writer::table($table);
}

