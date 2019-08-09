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
 
namespace tool_securityquestions\privacy;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
 
class provider implements 
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider  {

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
                'locked' => 'privacy:metadata:tool_securityquestions_loc:locked'
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
        /*$sql = "
            SELECT l.contextid
              FROM {tool_securityquestions_res} l
             WHERE l.userid = :userid";

        $contextlist->add_from_sql($sql, ['userid' => $userid]);

        $sql = "
            SELECT l.contextid
              FROM {tool_securityquestions_loc} l
             WHERE l.userid = :userid";

        $contextlist->add_from_sql($sql, ['userid' => $userid]);

        $sql = "
            SELECT l.contextid
              FROM {tool_securityquestions_ans} l
             WHERE l.userid = :userid";

        $contextlist->add_from_sql($sql, ['userid' => $userid]);*/
        $contextlist->add_user_context($userid);
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        
        $context = $userlist->get_context();

        $sql = "
        SELECT l.userid
        FROM {tool_securityquestions_ans} l
        WHERE l.contextid = :context";

        $userlist->add_from_sql('userid', $sql, ['context' => $context]);

        $sql = "
        SELECT l.contextid
        FROM {tool_securityquestions_loc} l
        WHERE l.userid = :userid";

        $userlist->add_from_sql('userid', $sql, ['context' => $context]);

        $sql = "
        SELECT l.contextid
        FROM {tool_securityquestions_res} l
        WHERE l.userid = :userid";

        $userlist->add_from_sql('userid', $sql, ['context' => $context]);
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist as $context){
            // Get records for user ID
            $ans = $DB->get_records('tool_securityquestions_ans', array('userid' => $userid));
            $loc = $DB->get_records('tool_securityquestions_loc', array('userid' => $userid));
            $res = $DB->get_records('tool_securityquestions_res', array('userid' => $userid));

            writer::with_context($context)->export_data(
                [get_string('privacy:metadata:tool_securityquestions_ans', 'tool_securityquestions'), $context],
                (object) $ans);

            writer::with_context($context)->export_data(
                [get_string('privacy:metadata:tool_securityquestions_loc', 'tool_securityquestions'), $context],
                (object) $loc);

            writer::with_context($context)->export_data(
                [get_string('privacy:metadata:tool_securityquestions_res', 'tool_securityquestions'), $context],
                (object) $res);
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $sql = "
        DELETE
        FROM {tool_securityquestions_ans} l
        WHERE l.contextid = :context";

        $DB->execute($sql, ['context' => $context]);

        $sql = "
        DELETE
        FROM {tool_securityquestions_loc} l
        WHERE l.contextid = :context";

        $DB->execute($sql, ['context' => $context]);

        $sql = "
        DELETE
        FROM {tool_securityquestions_res} l
        WHERE l.contextid = :context";

        $DB->execute($sql, ['context' => $context]);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            $sql = "
            DELETE
            FROM {tool_securityquestions_ans} l
            WHERE l.userid = :userid";
    
            $DB->execute($sql, ['userid' => $userid]);

            $sql = "
            DELETE
            FROM {tool_securityquestions_loc} l
            WHERE l.userid = :userid";
    
            $DB->execute($sql, ['userid' => $userid]);

            $sql = "
            DELETE
            FROM {tool_securityquestions_res} l
            WHERE l.userid = :userid";
    
            $DB->execute($sql, ['userid' => $userid]);
        }
    }
}

