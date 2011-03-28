<?php

// first create database
// then run this script

// php install.php --user=user --pass=pass --db=db --host=host


$longopts  = array(
    "user:",     // Required value
    "pass:",     // Required value
    "db:",     // Required value
    "host::",    // Optional value
);

$options = getopt("",$longopts);

print("User: ".$options["user"]."\n");
print("Pass: ".$options["pass"]."\n");




?>
