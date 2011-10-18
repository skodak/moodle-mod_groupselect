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
 * Group self selection instance configuration
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2008-2011 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');


class mod_groupselect_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE; //TODO: get rid of the sloppy $COURSE

        $mform = $this->_form;

        $config = get_config('groupselect');

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------

        $options = array();
        $options[0] = get_string('fromallgroups', 'mod_groupselect');
        if ($groupings = groups_get_all_groupings($COURSE->id)) {
            foreach ($groupings as $grouping) {
                $options[$grouping->id] = format_string($grouping->name);
            }
        }
        $mform->addElement('select', 'targetgrouping', get_string('targetgrouping', 'mod_groupselect'), $options);

        $mform->addElement('passwordunmask', 'password', get_string('password', 'mod_groupselect'), 'maxlength="254" size="24"');
        $mform->setType('password', PARAM_RAW);

        $mform->addElement('text', 'maxmembers', get_string('maxmembers', 'mod_groupselect'), array('size'=>'4'));
        $mform->setType('maxmembers', PARAM_INT);
        $mform->setDefault('maxmembers', $config->maxmembers);
        $mform->setAdvanced('maxmembers', $config->maxmembers_adv);

        $mform->addElement('date_time_selector', 'timeavailable', get_string('timeavailable', 'mod_groupselect'), array('optional'=>true));
        $mform->setDefault('timeavailable', 0);
        $mform->addElement('date_time_selector', 'timedue', get_string('timedue', 'mod_groupselect'), array('optional'=>true));
        $mform->setDefault('timedue', 0);

        //-------------------------------------------------------------------------------
        // buttons
        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $maxmembers = $data['maxmembers'];

        if ($maxmembers < 0) {
            $errors['maxmembers'] = get_string('error');
        }

        return $errors;
    }
}
