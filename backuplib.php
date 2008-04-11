<?php //$Id$
    //This php script contains all the stuff to backup/restore
    //groupselect mods

    //This is the "graphical" structure of the groupselect mod:
    //
    //                       groupselect
    //                     (CL,pk->id)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //This function executes all the backup procedure about this mod
    function groupselect_backup_mods($bf,$preferences) {
        global $CFG;

        $status = true; 

        ////Iterate over groupselect table
        if ($groupselects = get_records ("groupselect","course", $preferences->backup_course,"id")) {
            foreach ($groupselects as $groupselect) {
                if (backup_mod_selected($preferences,'groupselect',$groupselect->id)) {
                    $status = groupselect_backup_one_mod($bf,$preferences,$groupselect);
                }
            }
        }
        return $status;
    }
   
    function groupselect_backup_one_mod($bf,$preferences,$groupselect) {

        global $CFG;
    
        if (is_numeric($groupselect)) {
            $groupselect = get_record('groupselect','id',$groupselect);
        }
    
        $status = true;

        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print assignment data
        fwrite ($bf,full_tag("ID",4,false,$groupselect->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"groupselect"));
        fwrite ($bf,full_tag("NAME",4,false,$groupselect->name));
        fwrite ($bf,full_tag("INTRO",4,false,$groupselect->intro));
        fwrite ($bf,full_tag("PASSWORD",4,false,$groupselect->password));
        fwrite ($bf,full_tag("TIMEAVAILABLE",4,false,$groupselect->timeavailable));
        fwrite ($bf,full_tag("TIMEDUE",4,false,$groupselect->timedue));
        fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$groupselect->timemodified));
        //End mod
        $status = fwrite ($bf,end_tag("MOD",3,true));

        return $status;
    }

    ////Return an array of info (name,value)
    function groupselect_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += groupselect_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        
         //First the course data
         $info[0][0] = get_string("modulenameplural","groupselect");
         $info[0][1] = count_records("groupselect", "course", "$course");
         return $info;
    } 

    ////Return an array of info (name,value)
    function groupselect_check_backup_mods_instances($instance,$backup_unique_code) {
         //First the course data
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';
        return $info;
    }

?>
