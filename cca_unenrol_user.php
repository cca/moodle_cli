<?php


/**
 * Takes a Moodle user ID and a Moodle course ID and unenrols user from that course.
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

cli_heading('Enter a Moodle UID and Course ID to permanently unenrol them.');

$prompt = "Enter a Moodle USER ID (not a CCA ID):";
$moodleUserId = cli_input($prompt);

$prompt = "Enter a Moodle COURSE ID (not a CCA course ID):";
$moodleCourseId = cli_input($prompt);



// Need the mdl_user_enrolments_id to do the drop
$subsql = "select ue.id, ue.status, e.courseid from mdl_user_enrolments as ue join mdl_enrol as e on ue.enrolid=e.id " .
    "where e.courseid=$moodleCourseId and ue.userid=$moodleUserId";

$subresults = $DB->get_records_sql($subsql);
foreach ($subresults as $subresult) {
    print_r($subresult);

    $enrolments_id = $subresult->id;
    echo(" enrolment id $enrolments_id \n");
    // Drop the  enrolment records
    if ($DB->delete_records('user_enrolments', array('id' => $enrolments_id, 'userid' => $moodleUserId))) {
        echo("User $moodleUserId was dropped from course $moodleCourseId\n");

    } else {
        echo("User NOT dropped from course - check the UID and course ID and make sure they're already enrolled. \n");
    }
}


exit(0); // 0 means success