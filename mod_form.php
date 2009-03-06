<?php // $Id$
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_groupselect_mod_form extends moodleform_mod {

    function definition() {
        global $COURSE;

        $mform    =& $this->_form;

        $mform->addElement('text', 'name', get_string('groupselectname', 'groupselect'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'intro', get_string('intro', 'groupselect'));
        $mform->setType('intro', PARAM_RAW);
        $mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('intro', array('questions', 'richtext'), false, 'editorhelpbutton');

        $options = array();
        $options[0] = get_string('fromallgroups', 'groupselect');
        if ($groupings = groups_get_all_groupings($COURSE->id)) {
            foreach ($groupings as $grouping) {
                $options[$grouping->id] = format_string($grouping->name);
            }
        }
        $mform->addElement('select', 'targetgrouping', get_string('targetgrouping', 'groupselect'), $options);

        $mform->addElement('passwordunmask', 'password', get_string('password', 'groupselect'), 'maxlength="254" size="24"');
        $mform->setType('password', PARAM_RAW);

        $mform->addElement('text', 'maxmembers', get_string('maxmembers', 'groupselect'), array('size'=>'4'));
        $mform->setType('maxmembers', PARAM_INT);
        $mform->setDefault('maxmembers', 0);

        $mform->addElement('date_time_selector', 'timeavailable', get_string('timeavailable', 'groupselect'), array('optional'=>true));
        $mform->setDefault('timeavailable', 0);
        $mform->addElement('date_time_selector', 'timedue', get_string('timedue', 'groupselect'), array('optional'=>true));
        $mform->setDefault('timedue', 0);

        $features = array('groups'=>true, 'groupings'=>true, 'groupmembersonly'=>true,
                          'outcomes'=>false, 'gradecat'=>false, 'idnumber'=>false);
    
        $this->standard_coursemodule_elements($features);

//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons();

    }

    function validation($data, $files) {
        global $COURSE;
        $errors = parent::validation($data, $files);

        $mform =& $this->_form;

        $maxmembers = $data['maxmembers'];

        if ($maxmembers < 0) {
            $errors['maxmembers'] = get_string('error');
        }
        
        return $errors;
    }
}
?>
