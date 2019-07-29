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
 * Security questions response form for users
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


class set_responses_form extends moodleform {

    public function definition() {

        global $DB;
        global $USER;
        $mform = $this->_form;

        // Generate array for questions
        $questions = $DB->get_records('tool_securityquestions');
        $qarray = array();
        foreach ($questions as $question) {
            $qarray[$question->id] = $question->content;
        }
        //Get number of additional responses required
        $answered = $DB->get_records('tool_securityquestions_res', array('userid' => $USER->id));

        //Check all answered questions for how many are currently valid
        $active = 0;
        foreach ($answered as $answer) {
            //Get field and check if deprecated
            $deprecated = $DB->get_field('tool_securityquestions', 'deprecated', array('id' => $answer->qid));
            if ($deprecated == 0) {
                $active++;
            }
        }

        $numrequired = get_config('tool_securityquestions','minuserquestions');
        $numremaining = $numrequired - $active;
        $displaystring = get_string('formresponsesremaining','tool_securityquestions', $numremaining);
        
        $mform->addElement('html', "<h3>$displaystring</h3>");
        $mform->addElement('select', 'questions', get_string('formresponseselectbox', 'tool_securityquestions'), $qarray);
        $mform->addElement('text', 'response', get_string('formresponseentrybox', 'tool_securityquestions'));

        $this->add_action_buttons();
    }

    public function validation($data, $files) {

        $errors = parent::validation($data, $files);
        return $errors;
    }
}