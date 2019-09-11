<?php

// CCA custom utility.
// Prompts for a username and returns that user's enrollments.

/**
 *
 * @package    core
 * @subpackage cli
 * @copyright  2014 CCA (http://cca.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->libdir.'/coursecatlib.php');

// Get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "Enter a person's username when prompted.";
    echo $help;
    die;
}

cli_heading('Enrollment check');
$prompt = "Enter username";
$username = cli_input($prompt);

if (!$user = $DB->get_record('user', array('auth'=>'cas', 'username'=>$username,), $fields='*', $strictness=IGNORE_MISSING)) {
	cli_error("Can not find user '$username'");
} else {
	echo("\nGot user $user->id  $user->firstname $user->lastname \n" );
    $USER = $user;  // Globalize so we can use functions in Moodle coursecatlib etc.
}

$courses=enrol_get_my_courses(); // Get all courses for current $user
echo count($courses) . " courses for this user \n\n" ;

echo("");

echo("Course title - Visibility - Category\n");
echo("------------------------------------\n");
foreach ($courses as $thiscourse) {
    echo("$thiscourse->fullname - $thiscourse->visible - $thiscourse->category \n");
}

exit(0); // 0 means success