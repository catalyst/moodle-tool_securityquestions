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
 
class provider implements 
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider {
 
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
}