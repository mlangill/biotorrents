<?php

// first create database
// then run this script

// php install.php --user=user --pass=pass --db=db --host=host

// db options not required if secrets.php has already been set up




// read existing secrets.php or create new one


// check if secrets.php has already been created
if (file_exists("../include/secrets.php")) {
    // secrets.php exists
    
    print "Using existing secrets.php \n";

    require_once("../include/secrets.php");

} else {
    // secrets.php does not exist


    // cli options setup
    $longopts  = array(
    // all values optional
        "user::",
        "pass::",
        "db::",
        "host::",    
    );
    // get cli options
    $options = getopt("",$longopts);

    // use localhost if host is not specified
    if (!isset($options["host"])) {
        $options["host"] = "localhost";
    }

    // database parameters for new secrets.php
    $dbsec = array("user","pass","db","host");
    
    // check if the needed parameters exist
    foreach ($dbsec as $value) {
        if (!isset($options[$value])) {
        // needed parameter missing from cli options
        // alert user and exit
            print "Value missing: $value \n";
            print "Please specify with: --$value=something \n";
            exit;
        }
    }


    // create new secrets.php from template
    $cmd = "cp ../include/secrets.php.sample ../include/secrets.php";
    print "Creating new secrets.php from template \n";
    exec($cmd);



    // add parameters to secrets.php    
    foreach ($dbsec as $value) {
        // edit secrets.php        
        $cmd = "sed -i 's/%%".$value."%%/".$options[$value]."/g' ../include/secrets.php";
        print "Editing secrets.php with $value = ".$options[$value]." \n";    
        exec($cmd);
        }  
}




    // load secrets.php
    require_once "../include/secrets.php";


    print("User: $mysql_user \n");
    print("Pass: $mysql_pass \n");
    print("DB: $mysql_db \n");
    print("Host: $mysql_host \n");


?>
