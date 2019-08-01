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
 * Security questions answer page
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


class answer_questions_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        
        $this->display_questions($mform);
        $this->add_action_buttons();
    }

    /*public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }*/

    // ===============================VALIDATION AND DISPLAY FUNCTIONS================================================================

    private function display_questions($mform) {
        global $DB;
        global $USER;

        $numquestions = get_config('tool_securityquestions', 'answerquestions');

        for ($i = 0; $i < $numquestions; $i++) {
            // get qid from customdata
            $qid = $this->_customdata[$i];

            // Get question content
            //$questioncontent = $DB->get_field('tool_securityquestions', 'content', array('id' => $qid));
            // Format and display to the user
            $questionnum = $i + 1;
            //$mform->addElement('html', "<h3>Question $questionnum</h3>");
            $mform->addElement('text', "question$i", get_string('formanswerquestion', 'tool_securityquestions', $questionnum));
            $mform->addElement('hidden',"hiddenq$i", $qid);
        }
    }
}