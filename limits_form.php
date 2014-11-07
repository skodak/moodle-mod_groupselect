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
 * Group limit interface form
 *
 * @package    mod_groupselect
 * @author     Adam Olley <adam.olley@netspot.com.au>
 * @copyright  2012 NetSpot Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_groupselect_limits_form extends moodleform {

    public function mod_groupselect_limits_form ($groups) {
        $this->groups = $groups;
        parent::moodleform();
    }

    public function definition() {
        global $COURSE;

        $mform    =& $this->_form;

        $strlimit = get_string('limit', 'groupselect');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        foreach ($this->groups as $group) {
            $elname = 'limit[' . $group->id . ']';
            $mform->addElement('text', $elname, $group->name . ' ' . $strlimit, array('size' => 4));
            $mform->setType($elname, PARAM_INT);
        }

        $this->add_action_buttons();
    }
}
