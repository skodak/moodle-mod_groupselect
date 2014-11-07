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
 * Define all the restore steps that will be used by the restore_groupselect_activity_task
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2011 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one groupselect activity
 */
class restore_groupselect_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('groupselect', '/activity/groupselect');
        $paths[] = new restore_path_element('groupselect_limit', '/activity/groupselect/limits/limit');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_groupselect($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        if (!empty($data->targetgrouping)) {
            $data->targetgrouping = $this->get_mappingid('grouping', $data->targetgrouping);
        }

        // insert the groupselect record
        $newitemid = $DB->insert_record('groupselect', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_groupselect_limit($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->groupselect = $this->get_new_parentid('groupselect');
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        $newitemid = $DB->insert_record('groupselect_limits', $data);
    }

    protected function after_execute() {
        // Add groupselect related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_groupselect', 'intro', null);
        $this->add_related_files('mod_groupselect', 'content', null);
    }
}
