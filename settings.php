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
 * Group self selection module admin settings and defaults
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2008-2011 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('groupselect/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('groupselectmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configtext_with_advanced('groupselect/maxmembers',
        get_string('maxmembers', 'mod_groupselect'), '',
        array('value'=>0, 'adv'=>false), PARAM_INT, 4));
}
