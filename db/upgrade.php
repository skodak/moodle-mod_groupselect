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

    global $CFG, $THEME, $db;

    $result = true;

    if ($result && $oldversion < 2009020600) {

    // Define field signuptype to be added to groupselect
        $table = new XMLDBTable('groupselect');
        $field = new XMLDBField('signuptype');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, '0', 'intro');
        $result = $result && add_field($table, $field);

    // Define field timecreated to be added to groupselect
        $table = new XMLDBTable('groupselect');
        $field = new XMLDBField('timecreated');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'timedue');
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2009030500) {

    // Define field targetgrouping to be added to groupselect
        $table = new XMLDBTable('groupselect');
        $field = new XMLDBField('targetgrouping');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'intro');
        $result = $result && add_field($table, $field);
    }

    return $result;
}
