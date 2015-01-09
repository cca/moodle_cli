<?php

// CCA custom utility script.
// Prompts for a course ID and returns that course's parent course


/**
 * This script allows you to reset any local user password.
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


cli_heading('Find parent course');
$prompt = "Enter course ID";
$courseid = cli_input($prompt);

// Does this course's ID exist in the customint1 field of any mdl_enrol records?
$sql = "select courseid, customint1 from mdl_enrol where customint1=$courseid";
$result = $DB->get_record_sql($sql);

if ($result) {
    echo("Parent course is $result->courseid \n");
} else {
    echo("This course has no parent \n" );
}

exit(0); // 0 means success