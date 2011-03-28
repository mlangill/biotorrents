<?php

// first create database
// then run this script

// php install.php --user=user --pass=pass --db=db --host=host

// db options not required if secrets.php has already been set up







// check if secrets.php has already been created
if file_exists("../include/secrets.php") {
    // secrets.php exists
    
    print "Using existing secrets.php";

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

    // required parameters for new secrets.php
    $reqopt = array("user","pass","db");
    // check for required options
    foreach ($reqopt as $value) {
        if (isset($options[$value])) {
            print "Value missing: $value \n";
            print "Please specify with: --$value=something \n";
            exit;
        }
    }

    // use localhost if host is not specified
    if (!isset($options["host"])) {
        $options["host"] = "localhost";
    }

    $user = $options["user"];
    $pass = $options["pass"];
    $db = $options["db"];
    $host = $options["host"];

    // create new secrets.php from template
    $cmd = "cp ../include/secrets.php.sample ../include/secrets.php";
    print "Creating new secrets.php from template";
    $exec($cmd);


    // write db info to secrets.php
    $cmd = "sed -i 's/%%user%%/".$user."/g' ../include/secrets.php";
    print "Modifying secrets.php with user: $user \n";    
    exec($cmd);
    
    
    print "Modifying secrets.php with user: $user \n";    
    
}


    print("User: $user \n");
    print("Pass: $pass \n");
    print("DB: $db \n");
    print("Host: $host \n");



?>
