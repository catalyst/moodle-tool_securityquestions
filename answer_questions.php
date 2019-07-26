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
 * Page for users to answer the security questions
 *
 * @package    tool_passwordvalidator
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__.'/answer_questions_form.php');
require_once(__DIR__.'/locallib.php');

defined('MOODLE_INTERNAL') || die();
global $DB;
global $USER;
admin_externalpage_setup('tool_securityquestions_setform');

$prevurl = '';

$numquestions = get_config('tool_securityquestions', 'answerquestions');
$answeredquestions = $DB->get_records('tool_securityquestions_res', array('userid' => $USER->id));
$pickedkeys = array_rand($answeredquestions, $numquestions);

// Create array to pass questions ids to the form
$inputarr = array();
$i = 1;
foreach ($pickedkeys as $key) {
    $inputarr[$i] = $answeredquestions[$key]->qid;
    $i++;
}

$form = new answer_questions_form(null, $inputarr);
if ($form->is_cancelled()) {

    redirect($prevurl);

} else if ($fromform = $form->get_data()) {
    // YOU WIN CONGRATS

} else {
    // Build the page output.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('answerquestionspagestring', 'tool_securityquestions'));
    $form->display();

    echo $OUTPUT->footer();
}
