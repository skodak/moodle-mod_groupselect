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
 * Group self selection interface
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2008 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');

class select_form extends moodleform {

    // Define the form
    function definition () {
        $mform = $this->_form;
        list($data, $groupselect) = $this->_customdata;

        if ($groupselect->password !== '') {
            $mform->addElement('passwordunmask', 'password', get_string('password', 'mod_groupselect'), 'maxlength="254" size="24"');
            $mform->setType('password', PARAM_RAW);
        }

        $mform->addElement('hidden','id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden','select');
        $mform->setType('select', PARAM_INT);

        $this->add_action_buttons(true, get_string('select', 'mod_groupselect'));
        $this->set_data($data);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        list($data, $groupselect) = $this->_customdata;

        if ($groupselect->password !== '') {
            if ($groupselect->password !== $data['password']) {
                $errors['password'] = get_string('incorrectpassword', 'mod_groupselect');
            }
        }

        return $errors;
    }
}