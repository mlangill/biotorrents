<?php

// creates secret.php and config.php with user supplied values and performs initial database import
// see README for usage

print "\n";

// cli options setup
$longopts  = array(
// all values optional
    "user::",
    "pass::",
    "db::",
    "host::",
    "baseurl::",        
);
// get cli options
$options = getopt("",$longopts);

// use localhost if host is not specified
if (!isset($options["host"])) {
    $options["host"] = "localhost";
}

// needed options
// all needed options
$optlist = array("user","pass","db","host","baseurl");
// options needed for secret.php
$seclist = array("user","pass","db","host");

// check if the needed options exist
foreach ($optlist as $value) {
    if (!isset($options[$value])) {
    // needed parameter missing from cli options
    // alert user and exit
        print "Value missing: $value \n";
        print "Please specify with: --$value=something \n";
        exit;
    }
}

// read existing secrets.php or create new one
// check if secrets.php has already been created
if (file_exists("../include/secrets.php")) {
    // secrets.php exists
    
    print "Using existing secrets.php \n";

    require_once("../include/secrets.php");

} else {
    // secrets.php does not exist

    // create new secrets.php from template
    $cmd = "cp ../include/secrets.php.sample ../include/secrets.php";
    print "Creating new secrets.php from template \n";
    exec($cmd);



    // add parameters to secrets.php    
    print "Adding parameters to secrets.php\n";
    foreach ($seclist as $value) {
        // edit secrets.php        
        $cmd = "sed -i 's/%%".$value."%%/".$options[$value]."/g' ../include/secrets.php";
        exec($cmd);
    }  
}



    // load secrets.php
    require_once "../include/secrets.php";


    print("User: $mysql_user \n");
    print("Pass: $mysql_pass \n");
    print("DB: $mysql_db \n");
    print("Host: $mysql_host \n");



    // set up loop for sql import    
    $cmd = 'for f in tb avps countries searchcloud categories reputationlevel stylesheets licenses; do '
    . "mysql -u $mysql_user -p$mysql_pass -h $mysql_host $mysql_db < ".'../SQL/$f.sql'
    . '; done';

    // load sql
    print "Loading SQL statemnts\n";
    exec($cmd);





// check if config.php has been created
if (file_exists("../include/config.php")) {
    // config.php exists
    print "Existing config.php found \n";

} else {
    // config.php does not exist
    // copy from template
    print "Creating config.php from template \n";
    $cmd = "cp ../include/config.php.sample ../include/config.php";
    exec($cmd);
   
}

// add baseurl to config.php
    print "Adding base url to config.php\n"
        .'$DEFAULTBASEURL = '
        .$options["baseurl"]
        ."\n";
    $cmd = 'sed -i "s/%%BASEURL%%/'
        .addcslashes($options["baseurl"],"/")
        .'/g" ../include/config.php';
    exec($cmd);
    



print "\nInstall complete. Please visit ".$options["baseurl"]." in your browser\n\n";

?>
