<?php

/**
 * CCA custom utility script.
 * Finds all child courses and sets their visibility to zero
 * Alternative technique for future reference:
 * https://github.com/paulholden/moodle-local_metagroups/blob/6361548cb05135369866526a9e45b0ba0e32306c/locallib.php#L28-55
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

cli_heading('Hide child courses');
$prompt = "This script will detect all child courses and set their visibility to hidden. Continue? (y/n)";
if (cli_input($prompt) == "y") {

    $sql = "select id, courseid from mdl_enrol where enrol = 'meta'";
    $results = $DB->get_records_sql($sql);

    foreach ($results as $result) {
        // print_r($result);
        $courseid = $result->courseid;

        // Moodle oddness - we can't get enrolled users without the context,
        // but we can't get the course title without the actual course instance.
        // So we need two separate queries here.
        $course = get_course($courseid);
        $coursecontext = context_course::instance($courseid);

        // Does this course have any child courses?
        $childcourses = array();
        $select = "enrol = 'meta' AND status = 0 AND courseid = $course->id";

        if ($childcourseids = $DB->get_fieldset_select('enrol', 'customint1', $select)) {
            foreach ($childcourseids as $childcourseid) {
                $childcourses[] = get_course($childcourseid);
            }
            echo("\n\nCourse $courseid has " . count($childcourses) . " child courses:\n");
            foreach ($childcourses as $course) {
                echo("$course->id - $course->fullname \n");
                // Set course row with this id to have no visibility
                $childsql = "update mdl_course set visible = 0 where id = $course->id";
                $DB->execute($childsql);
            }
            echo("Child courses set to invisible\n");

        }
    }

} else {
    echo("Bailing.\n");
    exit();
}


echo("\n");

exit(0); // 0 means success
