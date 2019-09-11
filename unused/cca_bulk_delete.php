#!/usr/bin/php
<?php
// Bulk-delete all courses in the system.

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/course/lib.php');

// Moodle's get_courses() call skips hidden courses, so set all to visible before we can mass delete.
$sql = 'update mdl_course set visible = 1';
$results = $DB->execute($sql);

print_r("Deleting all courses...\n");
$courses = get_courses();
print_r("Courses count: " . count($courses) . "\n");
if(count($courses) > 1) { // there is one default course of moodle
    foreach ($courses as &$course) {
        print_r($course);
        delete_course($course);
        fix_course_sortorder(); // important!
    }
} else {
    print_r("\nNo courses in the system!\n");
}

exit;
?>
