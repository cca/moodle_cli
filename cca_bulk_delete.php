#!/usr/bin/php
<?php
// Place this script in /<moodle-root-path>/course/ directory and run it
// * To delete one specific course with id:
//   ~# php bulk_delete.php <course_id>
//
// * To delete all courses in the system:
//   ~# php bulk_delete.php
//
// Tested Moodle version:
// * Moodle 2.7 - Web Jun 4, 2014.
//
// Authors: Trinh Nguyen
// Email: dangtrinhnt[at]gmail[dot]com

// to be able to run this file as command line script
define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/course/lib.php');

// to delete a specific course id
if (isset($argv[1])) {
    print_r("Deleting course {$argv[1]}...\n");
    $id = $argv[1];
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
    delete_course($course);
    fix_course_sortorder(); // important!
} else {
    print_r("Deleting all courses...\n");
    // Get array of all courses
    $courses = get_courses();
    print_r($courses);
    print_r("Courses count: " . count($courses) . "\n");
    if(count($courses) > 1) { // there is one default course of moodle
        foreach ($courses as &$course) {
            delete_course($course);
            fix_course_sortorder(); // important!
        }
    } else {
        print_r("\nNo course in the system!\n");
    }
}
exit;
?>
