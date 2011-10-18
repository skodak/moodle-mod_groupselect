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
 * List of all groupselection modules in course
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2008-2011 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'groupselect', 'view all', "index.php?id=$course->id", '');

$strgroupselect  = get_string('modulename', 'mod_groupselect');
$strgroupselects = get_string('modulenameplural', 'mod_groupselect');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/groupselect/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strgroupselects);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strgroupselects);
echo $OUTPUT->header();

if (!$groupselects = get_all_instances_in_course('groupselect', $course)) {
    notice(get_string('thereareno', 'moodle', $strgroupselects), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($groupselects as $groupselect) {
    $cm = $modinfo->cms[$groupselect->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($groupselect->section !== $currentsection) {
            if ($groupselect->section) {
                $printsection = get_section_name($course, $sections[$groupselect->section]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $groupselect->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($groupselect->timemodified)."</span>";
    }

    $class = $groupselect->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed

    $table->data[] = array (
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">".format_string($groupselect->name)."</a>",
        format_module_intro('groupselect', $groupselect, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
