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
 * Main group self selection interface
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2008-2011 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('locallib.php');
require_once('select_form.php');

$id         = optional_param('id', 0, PARAM_INT);       // Course Module ID, or
$g          = optional_param('g', 0, PARAM_INT);        // Page instance ID
$select     = optional_param('select', 0, PARAM_INT);
$unselect   = optional_param('unselect', 0, PARAM_INT);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);

if ($g) {
    $groupselect = $DB->get_record('groupselect', array('id'=>$g), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('groupselect', $groupselect->id, $groupselect->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('groupselect', $id, 0, false, MUST_EXIST);
    $groupselect = $DB->get_record('groupselect', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'groupselect', 'view', 'view.php?id='.$cm->id, $groupselect->id, $cm->id);

$PAGE->set_url('/mod/groupselect/view.php', array('id' => $cm->id));
$PAGE->add_body_class('mod_groupselect');
$PAGE->set_title($course->shortname.': '.$groupselect->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($groupselect);

$mygroups       = groups_get_all_groups($course->id, $USER->id, $groupselect->targetgrouping, 'g.*');
$isopen         = groupselect_is_open($groupselect);
$groupmode      = groups_get_activity_groupmode($cm, $course);
$counts         = groupselect_group_member_counts($cm, $groupselect->targetgrouping);
$groups         = groups_get_all_groups($course->id, 0, $groupselect->targetgrouping);
$accessall      = has_capability('moodle/site:accessallgroups', $context);
$viewfullnames  = has_capability('moodle/site:viewfullnames', $context);
$canselect      = (has_capability('mod/groupselect:select', $context) and is_enrolled($context) and empty($mygroups));
$canunselect    = (has_capability('mod/groupselect:unselect', $context) and is_enrolled($context) and !empty($mygroups));

if ($course->id == SITEID) {
    $viewothers = has_capability('moodle/site:viewparticipants', $context);
} else {
    $viewothers = has_capability('moodle/course:viewparticipants', $context);
}

$strgroup       = get_string('group');
$strgroupdesc   = get_string('groupdescription', 'group');
$strmembers     = get_string('memberslist', 'mod_groupselect');
$straction      = get_string('action', 'mod_groupselect');
$strcount       = get_string('membercount', 'mod_groupselect');

// problem notification
$problems = array();

if (!is_enrolled($context)) {
    $problems[] = get_string('cannotselectnoenrol', 'mod_groupselect');

} else {
    if (!has_capability('mod/groupselect:select', $context)) {
        $problems[] = get_string('cannotselectnocap', 'mod_groupselect');

    } else if ($canselect) {
        if ($groupselect->timeavailable > time()) {
            $problems[] = get_string('notavailableyet', 'mod_groupselect', userdate($groupselect->timeavailable));

        } else if ($groupselect->timedue != 0 and  $groupselect->timedue < time() and empty($mygroups)) {
            $problems[] = get_string('notavailableanymore', 'mod_groupselect', userdate($groupselect->timedue));
        }
    }
}

if ($select and $canselect and isset($groups[$select]) and $isopen) {
    // user selected group
    $grpname = format_string($groups[$select]->name, true, array('context'=>$context));
    $usercount = isset($counts[$select]) ? $counts[$select]->usercount : 0;

    $data = array('id'=>$id, 'select'=>$select);
    $mform = new select_form(null, array($data, $groupselect, $grpname));

    if ($mform->is_cancelled()) {
        redirect($PAGE->url);
    }

    if (!$isopen) {
        $problems[] = get_string('cannotselectclosed', 'mod_groupselect');

    } else if ($groupselect->maxmembers and $groupselect->maxmembers <= $usercount) {
        $problems[] = get_string('cannotselectmaxed', 'mod_groupselect', $grpname);

    } else if ($mform->get_data()) {
        groups_add_member($select, $USER->id);
        add_to_log($course->id, 'groupselect', 'select', 'view.php?id='.$cm->id, $groupselect->id, $cm->id);
        redirect($PAGE->url);

    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('select', 'mod_groupselect', $grpname));
        echo $OUTPUT->box_start('generalbox', 'notice');
        echo '<p>'.get_string('selectconfirm', 'mod_groupselect', $grpname).'</p>';
        $mform->display();
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        die;
    }

} else if ($unselect and $canunselect and isset($mygroups[$unselect])) {
    // user unselected group

    if (!$isopen) {
        $problems[] = get_string('cannotunselectclosed', 'mod_groupselect');

    } else if ($confirm and data_submitted() and confirm_sesskey()) {
        groups_remove_member($unselect, $USER->id);
        add_to_log($course->id, 'groupselect', 'unselect', 'view.php?id='.$cm->id, $groupselect->id, $cm->id);
        redirect($PAGE->url);

    } else {
        $grpname = format_string($mygroups[$unselect]->name, true, array('context'=>$context));
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('unselect', 'mod_groupselect', $grpname));
        $yesurl = new moodle_url('/mod/groupselect/view.php', array('id'=>$cm->id, 'unselect'=>$unselect, 'confirm'=>1,'sesskey'=>sesskey()));
        $message = get_string('unselectconfirm', 'mod_groupselect', $grpname);
        echo $OUTPUT->confirm($message, $yesurl, $PAGE->url);
        echo $OUTPUT->footer();
        die;
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($groupselect->name, true, array('context'=>$context)));

if (trim(strip_tags($groupselect->intro))) {
    echo $OUTPUT->box_start('mod_introbox', 'groupselectintro');
    echo format_module_intro('page', $groupselect, $cm->id);
    echo $OUTPUT->box_end();
}

if (empty($groups)) {
    echo $OUTPUT->notification(get_string('nogroups', 'mod_groupselect'));

} else {
    if ($problems) {
        foreach ($problems as $problem) {
            echo $OUTPUT->notification($problem, 'notifyproblem');
        }
    }

    $data = array();
    $actionpresent = false;

    foreach ($groups as $group) {
        $ismember  = isset($mygroups[$group->id]);
        $usercount = isset($counts[$group->id]) ? $counts[$group->id]->usercount : 0;
        $grpname   = format_string($group->name, true, array('context'=>$context));

        $line = array();
        if ($ismember) {
            $line[0] = '<div class="mygroup">'.$grpname.'</div>';
        } else {
            $line[0] = $grpname;
        }

        $line[1] = groupselect_get_group_info($group);

        if ($groupselect->maxmembers) {
            $line[2] = $usercount.'/'.$groupselect->maxmembers;
        } else {
            $line[2] = $usercount;
        }

        if ($accessall) {
            $canseemembers = true;
        } else {
            if ($groupmode == SEPARATEGROUPS and !$ismember) {
                $canseemembers = false;
            } else {
                $canseemembers = $viewothers;
            }
        }

        if ($canseemembers) {
            if ($members = groups_get_members($group->id)) {
                $membernames = array();
                foreach ($members as $member) {
                    $pic = $OUTPUT->user_picture($member, array('courseid'=>$course->id));
                    if ($member->id == $USER->id) {
                        $membernames[] = '<span class="me">'.$pic.'&nbsp;'.fullname($member, $viewfullnames).'</span>';
                    } else {
                        $membernames[] = $pic.'&nbsp;<a href="'.$CFG->wwwroot.'/user/view.php?id='.$member->id.'&amp;course='.$course->id.'">'.fullname($member, $viewfullnames).'</a>';
                    }
                }
                $line[3] = implode(', ', $membernames);
            } else {
                $line[3] = '';
            }
        } else {
            $line[3] = '<div class="membershidden">'.get_string('membershidden', 'mod_groupselect').'</div>';
        }
        if ($isopen and !$accessall) {
            if (!$ismember and $canselect and $groupselect->maxmembers and $groupselect->maxmembers <= $usercount) {
                $line[4] = '<div class="maxlimitreached">'.get_string('maxlimitreached', 'mod_groupselect').'</div>'; // full - no more members
                $actionpresent = true;
            } else if ($ismember and $canunselect) {
                $line[4] = $OUTPUT->single_button(new moodle_url('/mod/groupselect/view.php', array('id'=>$cm->id, 'unselect'=>$group->id)), get_string('unselect', 'mod_groupselect', $grpname));
                $actionpresent = true;
            } else if (!$ismember and $canselect) {
                $line[4] = $OUTPUT->single_button(new moodle_url('/mod/groupselect/view.php', array('id'=>$cm->id, 'select'=>$group->id)), get_string('select', 'mod_groupselect', $grpname));
                $actionpresent = true;
            }
        }
        $data[] = $line;
    }

    $table = new html_table();
    $table->head  = array($strgroup, $strgroupdesc, $strcount, $strmembers);
    $table->size  = array('10%', '30%', '5%', '55%');
    $table->align = array('left', 'center', 'left', 'left');
    $table->data  = $data;
    if ($actionpresent) {
        $table->head[]  = $straction;
        $table->size    = array('10%', '30%', '5%', '45%', '10%');
        $table->align[] = 'center';
    }
    echo html_writer::table($table);

}

echo $OUTPUT->footer();

