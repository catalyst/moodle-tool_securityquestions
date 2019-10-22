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
 * Form for resetting users that are locked out from resetting password
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_securityquestions\form;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

class reset_lockout extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        // Text box for ID entry
        $mform->addElement('text', 'clearresponses', get_string('formclearresponses', 'tool_securityquestions'),
            array('placeholder' => 'Username or Email'));
        $mform->setType('clearresponses', PARAM_TEXT);

        // Description label
        $mform->addElement('static', 'clearresponsesdesc', get_string('formclearresponsesdesc', 'tool_securityquestions'));

        $this->add_action_buttons(true, get_string('formresetbutton', 'tool_securityquestions'));
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        $foundusers = $DB->get_records('user', array('username' => ($data['clearresponses'])));
        if (!empty($foundusers)) {
            // Get first matching username record
            $user = reset($foundusers);
        } else {
            $foundusers = $DB->get_records('user', array('email' => ($data['clearresponses'])));
            if (!empty($foundusers)) {
                // Get first matching email record (should be unique)
                $user = reset($foundusers);
            }
        }

        if (!isset($user)) {
            $errors['clearresponses'] = get_string('formresetnotfound', 'tool_securityquestions');
        } 

        return $errors;
    }
}