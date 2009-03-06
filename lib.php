<?php  // $Id$

/**
 * Library of functions and constants of Group selection module
 *
 * @package mod/groupselect
 */


/**
 * Is the given group selection open for students to select their group at the moment? 
 * 
 * @param object $groupselect Groupselect record
 * @return bool True if the group selection is open right now, false otherwise
 */
function groupselect_is_open($groupselect) {
    $now = time();
    return ($groupselect->timeavailable < $now AND ($groupselect->timedue == 0 or $groupselect->timedue > $now));
}


/**
 * Get the number of members in all groups the user can select from in this activity
 *
 * @param $cm Course module slot of the groupselect instance
 * @return array of objects: [id] => object(->usercount ->id) where id is group id
 */
function groupselect_group_member_counts($cm) {
    global $CFG;

    if (empty($CFG->enablegroupings) or empty($cm->groupingid)) {
        //all groups
        $sql = "SELECT g.id, COUNT(gm.userid) AS usercount
                  FROM {$CFG->prefix}groups_members gm
                       JOIN {$CFG->prefix}groups g ON g.id = gm.groupid
                 WHERE g.courseid = $cm->course
              GROUP BY g.id";  

    } else {
        $sql = "SELECT g.id, COUNT(gm.userid) AS usercount
                  FROM {$CFG->prefix}groups_members gm
                       JOIN {$CFG->prefix}groups g            ON g.id = gm.groupid
                       JOIN {$CFG->prefix}groupings_groups gg ON gg.groupid = g.id
                 WHERE g.courseid = $cm->course
                       AND gg.groupingid = $cm->groupingid
              GROUP BY g.id";  
    }
    return get_records_sql($sql);
}


/**
 * Given an object containing all the necessary data, (defined by the form in mod.html) 
 * this function will create a new instance and return the id number of the new instance.
 *
 * @param object $groupselect Object containing all the necessary data defined by the form in mod_form.php
 * $return int The id of the newly created instance
 */
function groupselect_add_instance($groupselect) {
    $groupselect->timecreated = time();
    $groupselect->timemodified = time();

    return insert_record('groupselect', $groupselect);
}


/**
 * Update an existing instance with new data.
 *
 * @param object $groupselect An object containing all the necessary data defined by the mod_form.php 
 * @return bool
 */
function groupselect_update_instance($groupselect) {
    $groupselect->timemodified = time();
    $groupselect->id = $groupselect->instance;

    return update_record('groupselect', $groupselect);
}


/**
 * Permanently delete the instance of the module and any data that depends on it.  
 *
 * @param int $id Instance id
 * @return bool
 */
function groupselect_delete_instance($id) {
 
    if (! $groupselect = get_record('groupselect', 'id', $id)) {
        return false;
    }

    $result = true;

    if (! delete_records('groupselect', 'id', $groupselect->id)) {
        $result = false;
    }

    return $result;
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
    return false;
}


/**
 * groupselect_get_view_actions 
 * 
 * @return array
 */
function groupselect_get_view_actions() {
    return array();
}


/**
 * groupselect_get_post_actions 
 * 
 * @return array
 */
function groupselect_get_post_actions() {
    return array();
}


/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function groupselect_reset_userdata($data) {
    return array();
}

?>
