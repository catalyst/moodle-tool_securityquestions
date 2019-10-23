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
 * Event for logging deprecated questions
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_securityquestions\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event for when questions are deprecated
 *
 * @property-read array $other {
 *      Extra information about event.
 * }
 *
 * @package    tool_securityquestions
 * @since      Moodle 3.5
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class question_deprecated extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param int $user the User object of the User who added the question
     * @param string $content the content of the added question
     * @return question_deprecated the question_deprecated event
     */
    public static function question_deprecated_event($user, $content) {

        $data = array(
            'relateduserid' => null,
            'context' => \context_user::instance($user->id),
            'other' => array (
                'userid' => $user->id,
                'content' => $content
            )
        );

        return self::create($data);
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with ID '{$this->other['userid']}' Deprecated Security Question '{$this->other['content']}'.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('questiondeprecatedeventname', 'tool_securityquestions');
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/admin/tool/securityquestions/set_questions.php');
    }
}

