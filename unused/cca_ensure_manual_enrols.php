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


cli_heading('Ensure all courses allow all enrolment types');


$sql = "select id from mdl_course";
$results = $DB->get_records_sql($sql);

foreach ($results as $record) {
    $moodleCourseId = $record->id;

    $sql = "select id from mdl_enrol where enrol='ccaroles' and courseid=$moodleCourseId and roleid=5";
    $results = $DB->get_record_sql($sql);

    // Create ccaroles record if it doesn't exist already
    if (empty($results)) {
        $newrec = new stdClass();
        $newrec->status = 0;                //  0: enrollment type visible in this course.  1: not visible.
        $newrec->enrol = 'ccaroles';
        $newrec->courseid = $moodleCourseId;
        $newrec->roleid = 5;
        $DB->insert_record("enrol", $newrec, false, false);
        echo("Created enrolment type entry CCAROLES for course $moodleCourseId \n");
    }

    // Repeat for guest access
    $sql = "select id from mdl_enrol where enrol='guest' and courseid=$moodleCourseId and roleid=5";
    $results = $DB->get_record_sql($sql);

    if (empty($results)) {
        $newrec = new stdClass();
        $newrec->status = 0;
        $newrec->enrol = 'guest';
        $newrec->courseid = $moodleCourseId;
        $newrec->roleid = 5;
        $DB->insert_record("enrol", $newrec, false, false);
        echo("Created enrolment type entry GUEST for course $moodleCourseId \n");
    }

    // Repeat for manual enrolments
    $sql = "select id from mdl_enrol where enrol='manual' and courseid=$moodleCourseId and roleid=5";
    $results = $DB->get_record_sql($sql);

    if (empty($results)) {
        $newrec = new stdClass();
        $newrec->status = 0;
        $newrec->enrol = 'manual';
        $newrec->courseid = $moodleCourseId;
        $newrec->roleid = 5;
        $DB->insert_record("enrol", $newrec, false, false);
        echo("Created enrolment type entry MANUAL for course $moodleCourseId \n");

    }
}

exit(0); // 0 means success