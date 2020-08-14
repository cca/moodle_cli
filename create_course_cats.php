<?php

// Create our course categories for a semester, which you are prompted to
// provide by the script. So, for instance, creates the (parent) 2020FA,
// 2020FA > Metacourses, 2020FA > ANIMA, etc. categories, one per CCA program.

/**
 *
 * @package    core
 * @subpackage cli
 * @copyright  2020 CCA (https://cca.edu)
 * @license    https://opensource.org/licenses/ECL-2.0 ECL 2.0
 */

define('CLI_SCRIPT', true);

require('/opt/moodle38/config.php');
// https://github.com/moodle/moodle/blob/MOODLE_38_STABLE/lib/clilib.php
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/coursecatlib.php');

$categories = array('Metacourses', 'ANIMA', 'ARCHT', 'ARTED', 'CERAM', 'COMAR', 'COMIC', 'CRAFT', 'CRITI', 'CRTSD', 'CURPR', 'DESGN', 'DIVSM', 'DIVST', 'DSMBA', 'EXCHG', 'EXTED', 'FASHN', 'FILMG', 'FILMS', 'FINAR', 'FNART', 'FURNT', 'FYCST', 'GELCT', 'GLASS', 'GRAPH', 'ILLUS', 'INDIV', 'INDUS', 'INTER', 'IXDGR', 'IXDSN', 'KADZE', 'LITPA', 'MAARD', 'MARCH', 'METAL', 'MOBIL', 'PHCRT', 'PHOTO', 'PNTDR', 'PRINT', 'SCIMA', 'SCULP', 'SFMBA', 'SSHIS', 'TEXTL', 'UDIST', 'VISCR', 'VISST', 'WRITE', 'WRLIT');

$prompt = "Semester category (e.g. 2020FA)";
$semester_str = trim(cli_input($prompt));
if (!strlen($semester_str)) {
    cli_error('Empty semester string, exiting without creating any course categories.');
}

cli_writeln('Creating course cateogories for ' . $semester_str . ' semester');

// create semester, we need to know its ID to create its children
$data = new stdClass();
$data->name = $semester_str;
$data->idnumber = $semester_str;
$semester = \core_course_category::create($data);
cli_writeln($semester->id . ' ' . $semester->name . ' (' . $semester->idnumber . ')');

// position department children under semester parent
foreach ($categories as $category) {
    $data = new stdClass();
    $data->name = $category;
    $data->idnumber = $semester_str . '-' . $category;
    $data->parent = $semester->id;
    $childcat = \core_course_category::create($data);
    cli_writeln($childcat->id . ' ' . $childcat->name . ' (' . $childcat->idnumber .') ');
}

exit(0);
