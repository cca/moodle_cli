<?php

// CCA custom utility script.
// Prompts for a course ID and returns that course's list of students.


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

if ($options['help']) {
    $help = "Enter a course ID when prompted.";
    echo $help;
    die;
}

cli_heading('Enrollment check');
$prompt = "Enter course ID";
$courseid = cli_input($prompt);

// Moodle oddness - we can't get enrolled users without the context,
// but we can't get the course title without the actual course instance.
// So we need two separate queries here.
$course = get_course($courseid);
$coursecontext = context_course::instance($courseid);

// First teachers
$role = $DB->get_record('role', array('shortname' => 'editingteacher'));
$teachers = get_role_users($role->id, $coursecontext);

echo("\n== Teachers in $course->fullname (course ID $course->id) ==\n");
foreach ($teachers as $cmember) {
    echo("$cmember->username - $cmember->firstname $cmember->lastname \n");
}
echo "+" . count($teachers) . " teachers for this course \n\n" ;
echo("\n");

// Now students
$role = $DB->get_record('role', array('shortname' => 'student'));

// Use get_role_users rather than get_enrolled_users here so we can see inactive users, if any
// $students = get_role_users($role->id, $coursecontext);
$students = get_enrolled_users($coursecontext);

echo("\n== Students in $course->fullname (course ID $course->id) ==\n");
foreach ($students as $cmember) {
    $studentstr = "";
    $studentstr .= "$cmember->username - $cmember->firstname $cmember->lastname";
    if (!is_enrolled($coursecontext, $cmember)) {
        $studentstr .= " - INACTIVE";
    }
    echo($studentstr . "\n");
}
echo "+" . count($students) . " students in this course \n\n" ;
echo("\n");


// Does this course have any child courses?
$childcourses = array();
$select = "enrol = 'meta' AND status = 0 AND courseid = $course->id";

if ($childcourseids = $DB->get_fieldset_select('enrol', 'customint1', $select)) {
    foreach ($childcourseids as $childcourseid) {
        $childcourses[] = get_course($childcourseid);
    }
    echo("This course has " . count($childcourses) . " child courses:\n");
    foreach ($childcourses as $course) {
        echo("$course->id  - $course->fullname \n");
    }
}

echo("\n");

exit(0); // 0 means success