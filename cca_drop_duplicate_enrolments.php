<?php


/**
 * In some cases, a student may be manually enrolled by an instructor, then
 * later enrolled again via datatel, giving them a duplicate enrolment (same
 * user, course, and role) but with a different enrolment type ("manual" vs "ccaroles").
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

cli_heading('Duplicate enrolment scrubber');

/*
for course in courses:
    get all enrollments in course
        for enrolment in enrolments:
            check for duplicate
            if found, delete the manual enrolment
*/

$courses = get_courses();
$dupecount = 0;
$dupestring = "";

echo("\n== Looping through all courses ==\n");
foreach ($courses as $course) {
    $moodleCourseId = $course->id;
    echo("$course->fullname ($moodleCourseId)\n");

    $coursecontext = context_course::instance($moodleCourseId);
    $students = get_enrolled_users($coursecontext);

    foreach ($students as $student) {
        echo("\t$student->firstname $student->lastname \n");
        $moodleUserId = $student->id;

        $sql = "select ue.id, ue.status, e.courseid from mdl_user_enrolments as ue join mdl_enrol as e on ue.enrolid=e.id " .
            "where e.courseid=$moodleCourseId and ue.userid=$moodleUserId";

        // We should only have one enrolment per user for this course, but use get_records rather than get_record
        // so we can detect duplicate/multiple enrolment records.
        $results = $DB->get_records_sql($sql);

        // Check for double enrolment
        if (count($results) > 1) {
            echo("$sql \n");
            echo("Found multiple enrolments for user $moodleUserId in course $moodleCourseId - fixing.\n");


            // Need the mdl_user_enrolments_id to do the drop
            $subsql = "select ue.id, ue.status, e.courseid from mdl_user_enrolments as ue join mdl_enrol as e on ue.enrolid=e.id " .
                "where e.courseid=$moodleCourseId and ue.userid=$moodleUserId and e.enrol='manual'";

            $subresults = $DB->get_records_sql($subsql);
            foreach ($subresults as $subresult) {
                print_r($subresult);

                $enrolments_id = $subresult->id;
                echo(" enrolment id $enrolments_id \n");
                // Drop the manual enrolment
                if ($DB->delete_records('user_enrolments', array('id' => $enrolments_id, 'userid' => $moodleUserId))) {
                    $dupetext = "User $moodleUserId was dropped from course $moodleCourseId\n";
                    $dupecount++;
                    $dupestring .= $dupetext;
                    echo($dupetext);
                }
            }
        }

    }

    echo("\n");

}


echo("\n" . count($courses) . " courses processed\n");
echo("$dupecount duplicate enrolments dropped:\n");
echo($dupestring);

exit(0); // 0 means success