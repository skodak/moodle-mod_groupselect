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
 * Group limit interface
 *
 * @package    mod_groupselect
 * @author     Adam Olley <adam.olley@netspot.com.au>
 * @copyright  2012 NetSpot Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('locallib.php');
require_once('limits_form.php');

$id      = required_param('id', PARAM_INT);    // Course Module ID, or
$signup  = optional_param('signup', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$params = array();
$params['id'] = $id;
if ($signup) {
    $params['signup'] = $signup;
}
if ($confirm) {
    $params['confirm'] = $confirm;
}

if (!$cm = get_coursemodule_from_id('groupselect', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

if (!$groupselect = $DB->get_record('groupselect', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

$PAGE->set_url('/mod/groupselect/limits.php');

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('moodle/course:managegroups', $context);

$groups         = groups_get_all_groups($course->id, 0, $groupselect->targetgrouping);

if ($course->id == SITEID) {
    $viewothers = has_capability('moodle/site:viewparticipants', $sitecontext);
} else {
    $viewothers = has_capability('moodle/course:viewparticipants', $context);
}

$strlimit        = get_string('modulename', 'groupselect') . ' - ' . get_string('limits', 'groupselect');
$strgroupselect  = get_string('modulename', 'groupselect');

$PAGE->set_title(format_string($groupselect->name));
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$mform = new mod_groupselect_limits_form($groups);
$formdata = array('id' => $id);
$formdata = array_merge($formdata, groupselect_retrieve_limits_formdata($groupselect->id));

if ($data = $mform->get_data()) {
    // Save form data.
    if ($data->limit) {
        $limits = array();
        foreach ($data->limit as $groupid => $lim) {
            if ($lim === '') {
                continue;
            }
            $lim = intval($lim);
            $limits[$groupid] = $lim;
        }
        groupselect_save_limits($groupselect->id, $limits);
    }
    redirect("$CFG->wwwroot/mod/groupselect/view.php?id=$id");
}

echo $OUTPUT->header();
echo '<div class="managelink"><a href="'."$CFG->wwwroot/group/index.php?id=$course->id".'">'.get_string('managegroups', 'groupselect').'</a></div>';

if (empty($CFG->enablegroupings) or empty($cm->groupingid)) {
    echo $OUTPUT->heading(get_string('pluginname', 'groupselect'));
} else {
    $grouping = groups_get_grouping($cm->groupingid);
    echo $OUTPUT->heading(get_string('pluginname', 'groupselect').' '.format_string($grouping->name));
}

echo $OUTPUT->box(get_string('limits_intro', 'groupselect', intval($groupselect->maxmembers)),
                    'intro generalbox boxwidthnormal boxaligncenter');
if ($groups) {
    $data = array();

    foreach ($groups as $group) {
        $ismember  = isset($mygroups[$group->id]);
        $usercount = isset($counts[$group->id]) ? $counts[$group->id]->usercount : 0;
        $grpname   = format_string($group->name);

        $line = array();
        if ($ismember) {
            $grpname = '<div class="mygroup">'.$grpname.'</div>';
        }
    }
} else {
    echo $OUTPUT->notification(get_string('nogroups', 'groupselect'));
}
$mform->set_data($formdata);
$mform->display();

echo $OUTPUT->footer();
