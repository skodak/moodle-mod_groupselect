<?php

// This file keeps track of upgrades to 
// the groupselect module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_groupselect_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $result = true;
    $dbman = $DB->get_manager();

    if ($result && $oldversion < 2009020600) {
        $table = new xmldb_table('groupselect');

        // Define field signuptype to be added to groupselect
        $field_signuptype_new = new xmldb_field('signuptype',XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, '0', 'intro');
        
        // Conditionally launch adding fields
        if (!$dbman->field_exists($table, $field_signuptype_new)) {
            $dbman->add_field($table, $field_signuptype_new);
        }

        // Define field timecreated to be added to groupselect
        $field_timecreated_new = new xmldb_field('timecreated',XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'timedue');
        
        // Conditionally launch add temporary fields
        if (!$dbman->field_exists($table, $field_timecreated_new)) {
            $dbman->add_field($table, $field_timecreated_new);
        }
        
        // search savepoint reached
        upgrade_mod_savepoint(true, 2009020600, 'groupselect');        
        
    }

    if ($result && $oldversion < 2009030500) {

        // Define field targetgrouping to be added to groupselect
        $table = new xmldb_table('groupselect');
        $field_targetgrouping_new = new xmldb_field('targetgrouping',XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'intro');
        // Conditionally launch adding fields
        if (!$dbman->field_exists($table, $field_targetgrouping_new)) {
            $dbman->add_field($table, $field_targetgrouping_new);
        }
        
        // search savepoint reached
        upgrade_mod_savepoint(true, 2009030500, 'groupselect');                
        
    }

    return $result;
}
