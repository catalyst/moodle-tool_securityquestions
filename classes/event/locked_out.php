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
 * Event for when users are locked out from resetting password
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

class locked_out extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param int $userid the userid of the User whos account was locked
     * @param int $lockeduntil the time when a user can attempt security questions again.
     * @return locked_out the locked out event
     */
    public static function locked_out_event($user, $lockeduntil) {

        $data = array(
            'context' => \context_user::instance($user->id),
            'other' => array (
                'userid' => $user->id,
                'lockeduntil' => userdate($lockeduntil)
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
        return "The user with ID '{$this->other['userid']}' was locked from resetting their password,
            and may reattempt security questions at '{$this->other['lockeduntil']}'";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('userlockedeventname', 'tool_securityquestions');
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/login/forgot_password.php');
    }
}
