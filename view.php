<?php  // $Id$

    require('../../config.php');
    require_once('lib.php');

    $id      = required_param('id', PARAM_INT);    // Course Module ID, or
    $signup  = optional_param('signup', 0, PARAM_INT);
    $confirm = optional_param('confirm', 0, PARAM_BOOL);

    if (!$cm = get_coursemodule_from_id('groupselect', $id)) {
        error("Course Module ID was incorrect");
    }

    if (!$course = get_record('course', 'id', $cm->course)) {
        error("Course is misconfigured");
    }

    if (!$groupselect = get_record('groupselect', 'id', $cm->instance)) {
        error("Course module is incorrect");
    }

    require_login($course, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $groups         = groups_get_all_groups($course->id, 0, $cm->groupingid);
    $accessall      = has_capability('moodle/site:accessallgroups', $context);
    $viewfullnames  = has_capability('moodle/site:viewfullnames', $context);
    $manage         = has_capability('moodle/course:managegroups', $context);
    $hasgroup       = groups_has_membership($cm, $USER->id); 
    $isopen         = groupselect_is_open($groupselect);
    $groupmode      = groups_get_activity_groupmode($cm, $course);
    $counts         = groupselect_group_member_counts($cm); 
    $mygroups       = groups_get_user_groups($course->id, $USER->id);
    $mygroups       = isset($mygroups[$cm->groupingid]) ? $mygroups[$cm->groupingid] : array();

    if ($course->id == SITEID) {
        $viewothers = has_capability('moodle/site:viewparticipants', $sitecontext);
    } else {
        $viewothers = has_capability('moodle/course:viewparticipants', $context);
    }

    $strgroup        = get_string('group');
    $strgroupdesc    = get_string('groupdescription', 'group');
    $strgroupselect  = get_string('modulename', 'groupselect');
    $strmembers      = get_string('memberslist', 'groupselect');
    $strsignup       = get_string('signup', 'groupselect');
    $straction       = get_string('action', 'groupselect');
    $strcount        = get_string('membercount', 'groupselect');

    $navigation = build_navigation('', $cm);
    
    if (has_capability('moodle/legacy:guest', $context, NULL, false)) {
        print_header_simple(format_string($groupselect->name), '', $navigation, '', '', true, '', navmenu($course, $cm));

        $wwwroot = $CFG->wwwroot.'/login/index.php';
        if (!empty($CFG->loginhttps)) {
            $wwwroot = str_replace('http:', 'https:', $wwwroot);
        }

        notice_yesno(get_string('noguestselect', 'groupselect').'<br /><br />'.get_string('liketologin'),
                     $wwwroot, "$CFG->wwwroot/course/view.php?id=$course->id");
        print_footer($course);
        exit;
    }

    if ($signup and !$hasgroup) {
        require_once('signup_form.php');

        $mform = new signup_form(null, $groupselect);
        $data = array('id'=>$id, 'signup'=>$signup);
        $mform->set_data($data);

        if ($mform->is_cancelled()) {
            //nothing

        } else if ($mform->get_data(false)) {
            require_once("$CFG->dirroot/group/lib.php");
            if (!isset($groups[$signup])) {
                error("Incorrect group id!");
            }
            groups_add_member($signup, $USER->id);
            redirect("$CFG->wwwroot/mod/groupselect/view.php?id=$cm->id");
            
        } else {
            print_header_simple(format_string($groupselect->name), '', $navigation, '', '', true, '', navmenu($course, $cm));
            print_box(get_string('signupconfirm', 'groupselect', format_string($groups[$signup]->name)));
            $mform->display();
            print_footer();
            die;
        }
    }

    print_header_simple(format_string($groupselect->name), '', $navigation, '', '', true,
        update_module_button($cm->id, $course->id, $strgroupselect), navmenu($course, $cm));

    if ($manage) {
        echo '<div class="managelink"><a href="'."$CFG->wwwroot/group/index.php?id=$course->id".'">'.get_string('managegroups', 'groupselect').'</a></div>';
    }

    if (empty($CFG->enablegroupings) or empty($cm->groupingid)) {
        print_heading(get_string('headingsimple', 'groupselect'));
    } else {
        $grouping = groups_get_grouping($cm->groupingid);        
        print_heading(get_string('headinggrouping', 'groupselect', format_string($grouping->name)));
    }

    if (!$accessall and $groupselect->timeavailable > time()) {
        notice(get_string('notavailableyet', 'groupselect', userdate($groupselect->timeavailable)), "$CFG->wwwroot/course/view.php?id=$course->id");
        die; // not reached
    }

    print_box(format_text($groupselect->intro), 'intro generalbox boxwidthnormal boxaligncenter');

    if (!$accessall and $groupselect->timedue != 0 and  $groupselect->timedue < time() and !$hasgroup) {
        notify(get_string('notavailableanymore', 'groupselect', userdate($groupselect->timedue)));
    }

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
            $line[0] = $grpname;
            $line[1] = format_text($group->description);

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
                        if ($member->id == $USER->id) {
                            $membernames[] = '<span class="me">'.fullname($member, $viewfullnames).'</span>';
                        } else {
                            $membernames[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$member->id.'&amp;course='.$course->id.'">' . fullname($member, $viewfullnames) . '</a>';
                        }
                    }
                    $line[3] = implode(', ', $membernames);
                } else {
                    $line[3] = '-';
                }
            } else {
                $line[3] = '<div class="membershidden">'.get_string('membershidden', 'groupselect').'</div>';
            }
            if ($isopen and !$hasgroup and !$accessall) {
                if ($groupselect->maxmembers and $groupselect->maxmembers <= $usercount) {
                    $line[4] = ''; // full - no more members
                } else {
                    $line[4] = "<a title=\"$strsignup\" href=\"view.php?id=$cm->id&amp;signup=$group->id\">$strsignup</a> ";;
                }
            }
            $data[] = $line;
        }

        $table = new object();
        $table->head  = array($strgroup, $strgroupdesc, $strcount, $strmembers);
        $table->size  = array('10%', '30%', '5%', '55%');
        $table->align = array('left', 'center', 'left');
        $table->width = '90%';
        $table->data  = $data;
        if ($isopen and !$hasgroup and !$accessall) {
            $table->head[]  = $straction;
            $table->size    = array('10%', '30%', '5%', '45%', '10%');
            $table->align[] = 'center';
        }
        print_table($table);

    } else {
        notify(get_string('nogroups', 'groupselect'));
    }


    print_footer($course);
?>
