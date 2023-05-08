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
 * Extra validation for tool_securityquestions.
 *
 * @package   tool_securityquestions
 * @author    Sarah Cotton (sara.cotton@catalyst-eu.net)
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extra validation for tool_securityquestions/answerquestions.
 *
 * @package   tool_securityquestions
 * @author    Sarah Cotton (sara.cotton@catalyst-eu.net)
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_securityquestions_admin_setting_configtext extends admin_setting_configtext {

    /**
     * We need to overwrite the validate function to make sure the minimum
     * number of questions answered is 2.
     *
     * @param string $data Form data.
     * @return string Empty when no errors.
     */
    public function validate($data) {

        if ($data < 2) {
            return get_string('minimumquestions', 'tool_securityquestions');
        }

        return parent::validate($data);
    }
}
