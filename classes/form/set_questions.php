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

namespace tool_securityquestions\form;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

/**
 * Form for setting questions to be used on the site
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_questions extends \moodleform {

    /**
     * Form definition
     * @return void
     * @throws \coding_exception
     */
    public function definition() {
        $mform = $this->_form;

        // Add Question entry.
        $header = \html_writer::tag('h4', get_string('formaddquestion', 'tool_securityquestions'));
        $mform->addElement('html', $header);
        $mform->addElement('text', 'questionentry', get_string('formquestionentry', 'tool_securityquestions'));
        $mform->setType('questionentry', PARAM_TEXT);
        $mform->setDefault('questionentry', '');

        $this->add_action_buttons(true, get_string('formaddquestion', 'tool_securityquestions'));
    }
}
