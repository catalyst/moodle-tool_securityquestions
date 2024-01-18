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
 * Privacy provider for Security Questions plugin
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;
use core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for implementing the privacy provider
 */
class provider implements
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Get the metadata
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'tool_securityquestions_res',
             [
                'userid' => 'privacy:metadata:tool_securityquestions_res:userid',
                'response' => 'privacy:metadata:tool_securityquestions_res:response',
                'qid' => 'privacy:metadata:tool_securityquestions_res:qid'
             ],
            'privacy:metadata:tool_securityquestions_res'
        );

        $collection->add_database_table(
            'tool_securityquestions_loc',
             [
                'userid' => 'privacy:metadata:tool_securityquestions_loc:userid',
                'counter' => 'privacy:metadata:tool_securityquestions_loc:counter',
                'tier' => 'privacy:metadata:tool_securityquestions_loc:tier',
                'timefailed' => 'privacy:metadata:tool_securityquestions_loc:timefailed'
             ],
            'privacy:metadata:tool_securityquestions_loc'
        );

        $collection->add_database_table(
            'tool_securityquestions_ans',
             [
                'userid' => 'privacy:metadata:tool_securityquestions_ans:userid',
                'qid' => 'privacy:metadata:tool_securityquestions_ans:qid',
                'timecreated' => 'privacy:metadata:tool_securityquestions_ans:timecreated'
             ],
            'privacy:metadata:tool_securityquestions_ans'
        );

        // Add sitewide user preference.
        $collection->add_user_preference('tool_securityquestions_logintime',
            'privacy:metadata:preference:tool_securityquestions_logintime');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int         $userid     The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();
        $contextlist->add_user_context($userid);
        $contextlist->add_system_context();
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        // If current context is system, all users are contained within, get all users.
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $sql = "
            SELECT *
            FROM {tool_securityquestions_ans}";
            $userlist->add_from_sql('userid', $sql, array());

            $sql = "
            SELECT *
            FROM {tool_securityquestions_loc}";
            $userlist->add_from_sql('userid', $sql, array());

            $sql = "
            SELECT *
            FROM {tool_securityquestions_res}";
            $userlist->add_from_sql('userid', $sql, array());
        }
    }

    /**
     * Export user data
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist as $context) {

            // If not in system context, exit loop.
            if ($context->contextlevel == CONTEXT_SYSTEM) {

                $parentclass = array();

                // Get records for user ID.
                $ans = $DB->get_records('tool_securityquestions_ans', array('userid' => $userid));
                $loc = $DB->get_records('tool_securityquestions_loc', array('userid' => $userid));
                $res = $DB->get_records('tool_securityquestions_res', array('userid' => $userid));

                if (count($ans) > 0) {
                    $i = 0;
                    foreach ($ans as $answer) {
                        $parentclass['Answer'][$i]['userid'] = $answer->userid;
                        $parentclass['Answer'][$i]['qid'] = $answer->qid;
                        $time = transform::datetime($answer->timecreated);
                        $parentclass['Answer'][$i]['timecreated'] = $time;
                        $i++;
                    }
                }

                if (count($loc) > 0) {
                    $j = 0;
                    foreach ($loc as $locked) {
                        $parentclass['Locked'][$j]['userid'] = $locked->userid;
                        $parentclass['Locked'][$j]['counter'] = $locked->counter;
                        $parentclass['Locked'][$j]['tier'] = $locked->tier;
                        $parentclass['Locked'][$j]['timefailed'] = $locked->timefailed;
                        $j++;
                    }
                }

                if (count($res) > 0) {
                    $k = 0;
                    foreach ($res as $response) {
                        $parentclass['Response'][$k]['userid'] = $response->userid;
                        $parentclass['Response'][$k]['response'] = $response->response;
                        $parentclass['Response'][$k]['qid'] = $response->qid;
                        $k++;
                    }
                }

                writer::with_context($context)->export_data(
                    [get_string('privacy:metadata:tool_securityquestions', 'tool_securityquestions')],
                    (object) $parentclass);
            }
        }
    }

    /**
     * Delete data for users in context
     * @param \context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        // If not in user context, exit loop.
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $sql = "
            DELETE
            FROM {tool_securityquestions_ans}";
            $DB->execute($sql);

            $sql = "
            DELETE
            FROM {tool_securityquestions_loc}";
            $DB->execute($sql);

            $sql = "
            DELETE
            FROM {tool_securityquestions_res}";
            $DB->execute($sql);
        }
    }

    /**
     * Delete data for a user
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist as $context) {

            // If not in user context, exit loop.
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                $DB->delete_records('tool_securityquestions_res', ['userid' => $userid]);
                $DB->delete_records('tool_securityquestions_ans', ['userid' => $userid]);
                $DB->delete_records('tool_securityquestions_loc', ['userid' => $userid]);
            }
        }
    }

    /**
     * Delete data for selected users
     * @param \core_privacy\local\request\approved_userlist $userlist
     * @return void
     */
    public static function delete_data_for_users(\core_privacy\local\request\approved_userlist $userlist) {
        $users = $userlist->get_users();
        foreach ($users as $user) {
            // Create a contextlist with only system context.
            $contextlist = new approved_contextlist($user, 'tool_mfa', [\context_system::instance()]);
            self::delete_data_for_user($contextlist);
        }
    }
}
