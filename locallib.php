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

require_once("$CFG->dirroot/group/lib.php");
require_once("$CFG->dirroot/mod/groupselect/lib.php");


function groupselect_get_group_info($group) {
    $group = clone($group);
    $context = get_context_instance(CONTEXT_COURSE, $group->courseid);

    $group->description = file_rewrite_pluginfile_urls($group->description, 'pluginfile.php', $context->id, 'group', 'description', $group->id);
    if (!isset($group->descriptionformat)) {
        $group->descriptionformat = FORMAT_MOODLE;
    }
    $options = new stdClass;
    $options->overflowdiv = true;
    return format_text($group->description, $group->descriptionformat, array('overflowdiv'=>true, 'context'=>$context));
}

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
 * @param $targetgrouping The id of grouping the user can select a group from
 * @return array of objects: [id] => object(->usercount ->id) where id is group id
 */
function groupselect_group_member_counts($cm, $targetgrouping=0) {
    global $DB;

    //TODO: join into enrolment table

    if (empty($targetgrouping)) {
        //all groups
        $sql = "SELECT g.id, COUNT(gm.userid) AS usercount
                  FROM {groups_members} gm
                       JOIN {groups} g ON g.id = gm.groupid
                 WHERE g.courseid = :course
              GROUP BY g.id";
        $params = array('course'=>$cm->course);

    } else {
        $sql = "SELECT g.id, COUNT(gm.userid) AS usercount
                  FROM {groups_members} gm
                       JOIN {groups} g            ON g.id = gm.groupid
                       JOIN {groupings_groups} gg ON gg.groupid = g.id
                 WHERE g.courseid = :course
                       AND gg.groupingid = :grouping
              GROUP BY g.id";
        $params = array('course'=>$cm->course, 'grouping'=>$targetgrouping);
    }

    return $DB->get_records_sql($sql, $params);
}

function groupselect_save_limits($groupselectid, $limits) {
    global $DB;
    $groupselectid = intval($groupselectid);
    # query for existing records which we can update or delete
    if ($rs = $DB->get_recordset_select('groupselect_limits', "groupselect = $groupselectid",
                                        null, '', 'id, groupselect, groupid, lim')) {
        # array to store IDs of rows we want to delete
        $delete = array();
        foreach ($rs as $grouplimit) {
            if (isset($limits[$grouplimit->groupid])) {
                if ($limits[$grouplimit->groupid] != $grouplimit->lim) {
                    # only need to update the row if the new limit is different to the
                    # existing record
                    $grouplimit->lim = $limits[$grouplimit->groupid];
                    $DB->update_record('groupselect_limits', $grouplimit);
                }
            } else {
                # a limit for this groupid was left blank, so remove the row
                $delete[] = $grouplimit->id;
            }
            unset($limits[$grouplimit->groupid]);
        }
        $rs->close();

        if (!empty($delete)) {
            list($insql, $params) = $DB->get_in_or_equal($delete);
            $DB->delete_records_select('groupselect_limits', "id $insql", $params);
        }
    }

    # insert all remaining limits
    foreach ($limits as $groupid => $lim) {
        $grouplimit = new object();
        $grouplimit->groupselect = $groupselectid;
        $grouplimit->groupid = $groupid;
        $grouplimit->lim = $lim;
        $DB->insert_record('groupselect_limits', $grouplimit);
    }
}

function groupselect_retrieve_limits_formdata($groupselectid) {
    global $DB;
    $formdata = array();
    if ($grouplimits = $DB->get_records("groupselect_limits", array("groupselect" => $groupselectid))) {
        foreach ($grouplimits as $grouplimit) {
            $formdata['limit['.$grouplimit->groupid . ']'] = $grouplimit->lim;
        }
    }
    return $formdata;
}

function groupselect_get_limits($groupselectid) {
    global $DB;
    $limits = array();
    if ($grouplimits = $DB->get_records("groupselect_limits", array("groupselect" => $groupselectid))) {
        foreach ($grouplimits as $grouplimit) {
            $limits[$grouplimit->groupid] = $grouplimit->lim;
        }
    }

    return $limits;
}
