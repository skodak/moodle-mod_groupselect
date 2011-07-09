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
 * Define all the backup steps that will be used by the backup_groupselect_activity_task
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2011 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete groupselect structure for backup, with file and id annotations
 */
class backup_groupselect_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $groupselect = new backup_nested_element('groupselect', array('id'), array(
            'name', 'intro', 'introformat', 'targetgrouping', 'contentformat',
            'password', 'maxmembers', 'timeavailable', 'timedue',
            'timecreated', 'timemodified'));

        // Build the tree
        // (love this)

        // Define sources
        $groupselect->set_source_table('groupselect', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations
        // (none)

        // Define file annotations
        $groupselect->annotate_files('mod_groupselect', 'intro', null); // This file areas haven't itemid
        $groupselect->annotate_files('mod_groupselect', 'content', null); // This file areas haven't itemid

        // Return the root element (groupselect), wrapped into standard activity structure
        return $this->prepare_activity_structure($groupselect);
    }
}
