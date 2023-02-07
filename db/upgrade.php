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
 * Security Questions upgrade library.
 *
 * @package    tool_securityquestions
 * @copyright  2020 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 * @param string $oldversion
 * @return bool
 */
function xmldb_tool_securityquestions_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020041700) {
        // Define field timefailed to be added to tool_securityquestions_loc.
        $table = new xmldb_table('tool_securityquestions_loc');
        $field = new xmldb_field('timefailed', XMLDB_TYPE_INTEGER, '15', null, null, null, '0', 'counter');
        // Conditionally launch add field timefailed.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Rename field locked on table tool_securityquestions_loc to tier.
        $table = new xmldb_table('tool_securityquestions_loc');
        $field = new xmldb_field('locked', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'userid');

        // Launch rename field tier.
        $dbman->rename_field($table, $field, 'tier');

        // Define index timefailed (not unique) to be added to tool_securityquestions_loc.
        $table = new xmldb_table('tool_securityquestions_loc');
        $index = new xmldb_index('timefailed', XMLDB_INDEX_NOTUNIQUE, array('timefailed'));

        // Conditionally launch add index timefailed.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Securityquestions savepoint reached.
        upgrade_plugin_savepoint(true, 2020041700, 'tool', 'securityquestions');
    }

    if ($oldversion < 2020091100) {

        // Define key qid (foreign) to be added to tool_securityquestions_ans.
        $table = new xmldb_table('tool_securityquestions_ans');
        $key = new xmldb_key('qid', XMLDB_KEY_FOREIGN, ['qid'], 'tool_securityquestions', ['id']);

        // Launch add key qid.
        $dbman->add_key($table, $key);

        // Define key userid (foreign) to be added to tool_securityquestions_ans.
        $table = new xmldb_table('tool_securityquestions_ans');
        $key = new xmldb_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Launch add key userid.
        $dbman->add_key($table, $key);

        // Define key userid (foreign) to be added to tool_securityquestions_loc.
        $table = new xmldb_table('tool_securityquestions_loc');
        $key = new xmldb_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Launch add key userid.
        $dbman->add_key($table, $key);

        // Securityquestions savepoint reached.
        upgrade_plugin_savepoint(true, 2020091100, 'tool', 'securityquestions');
    }

    return true;
}
