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

echo("\n== Teachers in $course->fullname ==\n");
foreach ($teachers as $cmember) {
    echo("$cmember->username - $cmember->firstname $cmember->lastname \n");
}
echo "+" . count($teachers) . " teachers for this course \n\n" ;
echo("\n");

// Now students
$role = $DB->get_record('role', array('shortname' => 'student'));
$students = get_role_users($role->id, $coursecontext);

echo("\n== Students in $course->fullname ==\n");
foreach ($students as $cmember) {
    echo("$cmember->username - $cmember->firstname $cmember->lastname \n");
}
echo "+" . count($students) . " students in this course \n\n" ;
echo("\n");


// Also try the get_enrolled_users() function to see whether there's
// anyone in this course who is neither teacher nor student.
$enrolled = get_enrolled_users($coursecontext);
echo "+" . count($enrolled) . " users of this course \n\n";
if (count($teachers) + count($students) != count($enrolled)) {
    echo("There are non-teacher/non-student users in this course.");
}

echo("\n");

exit(0); // 0 means success