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
 * Event for logging locked users
 *
 * @package    tool_securityquestions
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_securityquestions\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event for when users are unlocked from resetting password
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
class user_unlocked extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param stdClass $user the user who unlocked another user
     * @param stdClass $unlockeduser the user whos account was unlocked
     * @return user_unlocked the locked out event
     */
    public static function user_unlocked_event($user, $unlockeduser) {

        $data = array(
            'context' => \context_user::instance($user->id),
            'relateduserid' => $unlockeduser->id,
            'other' => array (
                'unlockeduserid' => $unlockeduser->id,
                'userid' => $user->id,
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
        return "The user with ID '{$this->other['unlockeduserid']}' was unlocked from
         resetting their password by user with ID '{$this->other['userid']}'.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('userunlockedeventname', 'tool_securityquestions');
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/admin/tool/securityquestions/reset_lockout.php');
    }
}
