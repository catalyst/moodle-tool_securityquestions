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


class reset_lockout_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        // Text box for ID entry
        $mform->addElement('text', 'resetid', get_string('formresetid', 'tool_securityquestions'));
        $mform->addRule('resetid',  get_string('formresetnotnumber', 'tool_securityquestions'), 'numeric');

        //Checkbox for clearing responses as well
        $mform->addElement('advcheckbox', 'clearresponses', '', get_string('formclearresponses', 'tool_securityquestions'));

        $this->add_action_buttons(true, get_string('formresetbutton', 'tool_securityquestions'));
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        if (is_numeric($data['resetid'])) {
            $exists = $DB->record_exists('user', array('id' => $data['resetid']));
            if (!$exists) {
                $errors['resetid'] = get_string('formresetnotfound', 'tool_securityquestions');
            }
        }

        return $errors;
    }
}