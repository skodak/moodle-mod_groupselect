<?php //$Id$

require_once($CFG->dirroot.'/lib/formslib.php');

class signup_form extends moodleform {

    // Define the form
    function definition () {
        global $USER, $CFG, $COURSE;

        $mform  =& $this->_form;
        $groupselect = $this->_customdata;

        if ($groupselect->password !== '') {
            $mform->addElement('passwordunmask', 'password', get_string('password', 'groupselect'), 'maxlength="254" size="24"');
            $mform->setType('password', PARAM_RAW);
        }

        $mform->addElement('hidden','id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden','signup');
        $mform->setType('signup', PARAM_INT);

        $this->add_action_buttons(true, get_string('signup', 'groupselect'));
    }

    function validation($data, $files) {
        global $COURSE;
        $errors = parent::validation($data, $files);

        $groupselect = $this->_customdata;


        if ($groupselect->password !== '') {
            $password = stripslashes($data['password']);
            if ($groupselect->password !== $password) {
                $errors['password'] = get_string('incorrectpassword', 'groupselect');
            }
        }

        return $errors;
    }
}

?>
