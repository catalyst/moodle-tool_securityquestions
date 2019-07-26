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
 * Password Validation Settings form
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


class set_questions_form extends moodleform {

    public function definition() {

        global $DB;
        $mform = $this->_form;

        $mform->addElement('text', 'questionentry', get_string('formquestionentry', 'tool_securityquestions'));

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
        $mform->addElement('html', html_writer::table($table));

        $mform->addElement('text', 'deprecate', get_string('formdeprecateentry', 'tool_securityquestions'));

        $this->add_action_buttons();
    }

    public function validation($data, $files) {

        $errors = parent::validation($data, $files);
        return $errors;
    }
}