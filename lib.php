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
 * Library of functions and constants of Group selection module
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2008-2011 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in groupselect module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function groupselect_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPS:                  return true;  // only separate mode makes sense - you hide members of other groups here
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;  // this could be very confusing
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function groupselect_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames');
}

/**
 * Given an object containing all the necessary data, (defined by the form in mod.html)
 * this function will create a new instance and return the id number of the new instance.
 *
 * @param object $groupselect Object containing all the necessary data defined by the form in mod_form.php
 * $return int The id of the newly created instance
 */
function groupselect_add_instance($groupselect) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/calendar/lib.php');

    $groupselect->timecreated = time();
    $groupselect->timemodified = time();

    $groupselect->id = $DB->insert_record('groupselect', $groupselect);

    if ($groupselect->timedue) {
        $event = new stdClass();
        $event->name         = $groupselect->name;
        $event->description  = format_module_intro('groupselect', $groupselect, $groupselect->coursemodule); // TODO: this is weird
        $event->courseid     = $groupselect->course;
        $event->groupid      = 0;
        $event->userid       = 0;
        $event->modulename   = 'groupselect';
        $event->instance     = $groupselect->id;
        $event->eventtype    = 'due';
        $event->timestart    = $groupselect->timedue;
        $event->timeduration = 0;

        calendar_event::create($event);
    }

    return $groupselect->id;
}


/**
 * Update an existing instance with new data.
 *
 * @param object $groupselect An object containing all the necessary data defined by the mod_form.php
 * @return bool
 */
function groupselect_update_instance($groupselect) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/calendar/lib.php');

    $groupselect->timemodified = time();
    $groupselect->id = $groupselect->instance;

    $DB->update_record('groupselect', $groupselect);

    if ($groupselect->timedue) {
        if ($event->id = $DB->get_field('event', 'id', array('modulename'=>'groupselect', 'instance'=>$groupselect->id))) {
            $event->name         = $groupselect->name;
            $event->description  = format_module_intro('groupselect', $groupselect, $groupselect->coursemodule);
            $event->timestart    = $groupselect->timedue;

            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);

        } else {
            $event = new stdClass();
            $event->name         = $groupselect->name;
            $event->description  = format_module_intro('groupselect', $groupselect, $groupselect->coursemodule);// TODO: this is weird
            $event->courseid     = $groupselect->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'groupselect';
            $event->instance     = $groupselect->id;
            $event->eventtype    = 'due';
            $event->timestart    = $groupselect->timedue;
            $event->timeduration = 0;

            calendar_event::create($event);
        }

    } else {
        $DB->delete_records('event', array('modulename'=>'groupselect', 'instance'=>$groupselect->id));
    }

    return true;
}


/**
 * Permanently delete the instance of the module and any data that depends on it.
 *
 * @param int $id Instance id
 * @return bool
 */
function groupselect_delete_instance($id) {
    global $DB;

    $DB->delete_records('groupselect', array('id'=>$id));

    return true;
}


/**
 * Returns the users with data in this module
 *
 * We have no data/users here but this must exists in every module
 *
 * @param int $groupselectid
 * @return bool
 */
function groupselect_get_participants($groupselectid) {
    // no participants here - all data is stored in the group tables
    return false;
}


/**
 * groupselect_get_view_actions
 *
 * @return array
 */
function groupselect_get_view_actions() {
    return array('view');
}


/**
 * groupselect_get_post_actions
 *
 * @return array
 */
function groupselect_get_post_actions() {
    return array('select', 'unselect');
}


/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function groupselect_reset_userdata($data) {
    // no resetting here - all data is stored in the group tables
    return array();
}
