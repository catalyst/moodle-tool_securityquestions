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
 *  Page for resetting locked ou user account
 *
 * @package    tool_securityquestion
 * @copyright  2019 Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__.'/reset_lockout_form.php');
require_once(__DIR__.'/locallib.php');

defined('MOODLE_INTERNAL') || die();

admin_externalpage_setup('tool_securityquestions_reset_lockout');

$prevurl = ($CFG->wwwroot.'/admin/category.php?category=securityquestions');

$form = new reset_lockout_form();
if ($form->is_cancelled()) {

    redirect($prevurl);

} else if ($fromform = $form->get_data()) {

}

// Build the page output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('setsecurityquestionspagestring', 'tool_securityquestions'));

$form->display();
generate_table();

echo $OUTPUT->footer();

// =======================================DISPLAY AND VALIDATION FUNCTIONS ====================================

function generate_table() {
    // Render table
    global $DB;
    // Get records from database for populating table
    $lockedusers = $DB->get_records('tool_securityquestions_loc', array('locked' => 1));

    $table = new html_table();
    $table->head = array('User ID', 'Email', 'Full Name');
    $table->colclasses = array('centeralign', 'centeralign', 'centeralign');

    foreach ($lockedusers as $userrecord) {
        $user = $DB->get_record('user', array('id' => $userrecord->userid));
        $table->data[] = array($user->id, $user->email, fullname($user));
    }

    echo html_writer::table($table);
}