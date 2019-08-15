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
 * Form for setting questions to be used on the site
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


class set_questions_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        // Add Question entry
        $mform->addElement('html', '<h4>Add Question</h4>');
        $mform->addElement('text', 'questionentry', get_string('formquestionentry', 'tool_securityquestions'));
        $mform->setType('questionentry', PARAM_TEXT);
        $mform->setDefault('questionentry', '');

        // Add Question Deprecation
        $mform->addElement('html', '<h4>Deprecate Question</h4>');
        $mform->addElement('text', 'deprecate', get_string('formdeprecateentry', 'tool_securityquestions'));
        $mform->setType('deprecate', PARAM_TEXT);
        $mform->addRule('deprecate',  get_string('formresetnotnumber', 'tool_securityquestions'), 'numeric');
        $mform->setDefault('questionentry', '');

        // Add checkbox to confirm deprecation
        $mform->addElement('advcheckbox', 'confirmdeprecate', '', get_string('formconfirmdeprecate', 'tool_securityquestions'));
        $mform->setDefault('confirmdeprecate', 0);
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Check whether question actually exists before passing it on
        if (is_numeric($data['deprecate'])) {
            $exists = $DB->record_exists('tool_securityquestions', array('id' => $data['deprecate']));
            if (!$exists) {
                $errors['deprecate'] = get_string('formdeprecatenotfound', 'tool_securityquestions');
            }
        }
    return $errors;
    }
}