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

// Get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "Finds all inactive course enrolments and drops students from the class.";
    echo $help;
    die;
}

cli_heading('Dropping inactive users');

// Deleting rows from user_enrolments will drop user from course
$count = $DB->count_records_select('user_enrolments', "status != 0");

$prompt = "$count inactive records found. Proceed to drop users from courses? (y/n)";
$yn = cli_input($prompt);

if ($yn == "y") {
    $DB->delete_records_select('user_enrolments', "status != 0");

    if ($count > 0) {
        echo($count . " inactive enrolment records dropped\n");
    } else {
        echo("No inactive records found.\n");
    }

} else {
    echo("Bailing out.\n");
    die;
}


exit(0); // 0 means success