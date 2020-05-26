<?php

// CCA custom utility.
// One-time script to fix duplicate enrolment types

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


cli_heading('Fix redundant enrolment types');

/*
 For each course:
 1) Find all manual enrolment type records and count them
 2) Loop from 0 to count
 3) Skip the 0th (we'll group all others under the first)
 4) For all remaining:
   5) Find the associated mdl_user_enrolment records
   6) Change their enrol_id field to that of the 0th
   7) Delete the record

*/

// Get all courses
global $DB;

$coursesql = "select id from mdl_course";
$courses = $DB->get_records_sql($coursesql);

foreach ($courses as $course) {
    $courseid = $course->id;
    echo("Course $courseid \n\n");

    // For this course, get all possible record types
    $rectypes = array("manual", "ccaroles", "guest", "self");
    foreach ($rectypes as &$rectype) {
        echo("$rectype \n");
        $recsql = "select * from mdl_enrol where enrol='$rectype' and courseid='$courseid'";
        $recs = $DB->get_records_sql($recsql);

        // Now we have all enrolment type records of a certain type for a certain course.
        // Consolidate them.

        $counter = 0;
        foreach($recs as $record) {
            echo("Counter is $counter\n");
            if ($counter == 0) {
                // Don't do anything - just save the ID of this mdl_enrol record
                $masterid = $record->id;
                echo("Master ID is now $masterid \n");
            } else {
                // Find any related mdl_user_enrolments records and set them to the $masterid
                $subsql = "select id, enrolid from mdl_user_enrolments where enrolid='$record->id'";
                $subrecs = $DB->get_records_sql($subsql);
                foreach($subrecs as $subrec) {
                    echo("Setting user enrolment $subrec->id to enrol_id $masterid \n");
                    $uprec = new stdClass();
                    $uprec->id = $subrec->id;
                    $uprec->enrolid = $masterid;
                    $DB->update_record('user_enrolments', $uprec, false);
                }
            // Delete mdl_enrol record only if it's not the 0th
            if (! $counter==0) {
                echo("Deleting mdl_enrol record $record->id \n\n");
                $DB->delete_records('enrol', array('id' => $record->id));
            }

        }
        $counter++;

        }
        echo("---------\n");

    }
    echo("==============\n\n");

}




exit(0); // 0 means success