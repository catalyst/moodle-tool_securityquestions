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
 * Local library for question functions
 * 
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

function tool_securityquestions_can_deprecate_question($qid) {
    global $CFG;
    global $DB;

    $active = tool_securityquestions_get_active_questions();

    if (count($active) >= get_config('tool_securityquestions', 'minquestions')) {
        $question = get_record('tool_securityquestions', array('id' => qid));
        if (!empty($question->deprecated)) {
            if ($question->deprecated) {
                return true;
            }
        }
    } else {
        return false;
    }
}

function tool_securityquestions_get_active_questions() {
    global $DB;
    $active = $DB->get_records('tool_securityquestions', array('deprecated' => 0));
    return $active;
}

function tool_securityquestions_deprecate_question($qid) {
    global $DB;

    if (tool_securityquestions_can_deprecate_question($qid)) {
        $DB->set_field('tool_securityquestions', 'deprecated', 0, array('id' => $qid));
        return true;
    } else {
        return false;
    }
}
