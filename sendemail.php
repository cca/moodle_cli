<?php

// https://docs.moodle.org/dev/CLI_scripts
define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php'); // cli only functions
require_once($CFG->libdir.'/moodlelib.php'); // has email_to_user()
require_once(__DIR__.'/../../../user/lib.php'); // has user_get_users_by_id()

// Systems Librarian userid = 4, fn accepts & returns arrays
$syslibUser = user_get_users_by_id(array(4))[0];
// https://docs.moodle.org/dev/Data_manipulation_API#General_concepts
// another way to get user is $DB->get_record('user',['email' => '...'])
// but does this return a user _object_ or mere assoc array?
$subject = "Test Moodle Email Subject";
$msg = "Plain text message. We will turn this into a CLI parameter.";

$status = email_to_user($syslibUser, $syslibUser, $subject, $msg, false);
if ($status) {
    echo("Email sent successfully.");
    exit;
}
echo("Error, email failed to send.");
exit(1);
