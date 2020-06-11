<?php

// https://docs.moodle.org/dev/CLI_scripts
define('CLI_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php'); // cli only functions
require_once($CFG->libdir.'/moodlelib.php'); // has email_to_user()
require_once(__DIR__.'/../../user/lib.php'); // has user_get_users_by_id()

// user_get_users_by_id() accepts an array of IDs and returns an _associative
// array indexed by ID_ so do not try to access its single entry with "[0]"
$syslibID = 4;
$syslibUser = user_get_users_by_id(array($syslibID))[$syslibID];
// https://docs.moodle.org/dev/Data_manipulation_API#General_concepts
// another way to get user is $DB->get_record('user',['email' => '...'])
$subject = "Moodle Disk Space Email";
// input is stream of df disk usage utility piped through sed to get only the
// lines we're interested in: `df -H | sed -ne '1p;9p;9q'`
$msg = stream_get_contents(STDIN);

$status = email_to_user($syslibUser, $syslibUser, $subject, $msg);
if ($status) {
    echo("Email sent successfully.");
    exit;
}
echo("Error, email failed to send.");
exit(1);
