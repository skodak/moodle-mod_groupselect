<?php  // $Id$

function groupselect_is_open($groupselect) {
    $now = time();
    return ($groupselect->timeavailable < $now AND ($groupselect->timedue == 0 or $groupselect->timedue > $now));
}

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

/// Library of functions and constants for module groupselect

function groupselect_add_instance($groupselect) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.

    return insert_record('groupselect', $groupselect);
}


function groupselect_update_instance($groupselect) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.
    $groupselect->timemodified = time();
    $groupselect->id = $groupselect->instance;

    return update_record('groupselect', $groupselect);
}


function groupselect_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

    if (! $groupselect = get_record('groupselect', 'id', $id)) {
        return false;
    }

    $result = true;

    if (! delete_records('groupselect', 'id', $groupselect->id)) {
        $result = false;
    }

    return $result;
}

function groupselect_get_participants($groupselectid) {
//Returns the users with data in one resource
//(NONE, but must exist on EVERY mod !!)

    return false;
}

function groupselect_get_view_actions() {
    return array();
}

function groupselect_get_post_actions() {
    return array();
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function groupselect_reset_userdata($data) {
    return array();
}

?>
