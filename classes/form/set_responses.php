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

namespace tool_securityquestions\form;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__.'/../../locallib.php');

class set_responses extends \moodleform {

    public function definition() {
        global $SESSION, $USER;
        $mform = $this->_form;

        // Setup response options
        $qarray = $this->generate_select_array();

        // Find number of responses required
        $responses = count(tool_securityquestions_get_active_user_responses($USER));
        $numrequired = get_config('tool_securityquestions', 'minuserquestions') - $responses;
        // Always draw at least 1 box, user probably wants to update a response
        if ($numrequired <= 0) {
            $numrequired = 1;
        }

        // Add repeated form elements
        for ($i = 1; $i <= $numrequired; $i++) {
            // Add an unused key at the start
            $unused = get_string('formselectquestion', 'tool_securityquestions');
            $qarray = array(0 => $unused) + $qarray;

            $mform->addElement('header', "header$i", get_string('formquestionnum', 'tool_securityquestions', $i));
            $mform->addElement('select', "questions$i", get_string('formresponseselectbox', 'tool_securityquestions'), $qarray);

            $mform->addElement('text', "response$i", get_string('formresponseentrybox', 'tool_securityquestions'), 'size="50"');
            $mform->setType("response$i", PARAM_TEXT);
            $mform->addRule("response$i", get_string('required'), 'required', null, 'client');
        }

        // Add hidden to track number of elements
        $mform->addElement('hidden', 'elementnum', $numrequired);
        $mform->setType('elementnum', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('formsaveresponse', 'tool_securityquestions'));

        // If questions aren't mandatory, or user is within the grace period
        if (isset($SESSION->presentedresponse) && (!get_config('tool_securityquestions', 'mandatory_questions')) ||
                get_user_preferences('tool_securityquestions_logintime') + get_config('tool_securityquestions', 'graceperiod') >= time()) {
            // If user is allowed to navigate away, build custom buttons

            if (!empty($SESSION->wantsurl)) {
                // User got here from a redirect
                $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('formremindme', 'tool_securityquestions'));
            } else {
                $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
            }
        } else {
            // If user must answer questions, dont show cancel button until enough answered
            if (count(tool_securityquestions_get_active_user_responses($USER)) >= get_config('tool_securityquestions', 'minuserquestions')) {
                $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
            }
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $elementnum = $data['elementnum'];
        $questionarray = array();
        for ($i = 1; $i <= $elementnum; $i++) {

            // Not allowed to answer placeholder
            if ($data["questions$i"] == 0) {
                $errors["questions$i"] = get_string('formselectquestion', 'tool_securityquestions');
            }

            // Check for duplicate responses
            if (in_array($data["questions$i"], $questionarray)) {
                $errors["questions$i"] = get_string('formduplicateresponse', 'tool_securityquestions');
            } else {
                $questionarray[] = $data["questions$i"];
            }
        }

        return $errors;
    }

    // =============================================DISPLAY AND VALIDATION FUNCTIONS======================================

    private function generate_select_array() {
        global $DB;
        global $USER;

        // Generate array for questions
        $questions = $DB->get_records('tool_securityquestions', array('deprecated' => 0));
        $qarray = array();
        foreach ($questions as $question) {
            $qarray[$question->id] = $question->content;
        }

        return $qarray;
    }
}