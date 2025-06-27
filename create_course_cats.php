<?php

// Create our course categories for a semester, which you are prompted to
// provide by the script. So, for instance, creates the (parent) 2020FA,
// 2020FA > Metacourses, 2020FA > ANIMA, etc. categories, one per CCA program.
// Can run interactively with no arguments or accept a single argument that is
// the parent semester category: `php create_course_cats.php 2021FA`

/**
 *
 * @package    core
 * @subpackage cli
 * @copyright  2022 CCA (https://cca.edu)
 * @license    https://opensource.org/licenses/ECL-2.0 ECL 2.0
 */

define('CLI_SCRIPT', true);

// Bitnami container config is in a consistent place
require('/bitnami/moodle/config.php');
// https://github.com/moodle/moodle/blob/MOODLE_310_STABLE/lib/clilib.php
require_once($CFG->libdir.'/clilib.php');

$categories = array('Metacourses', 'ANIMA', 'ARCHT', 'ARTED', 'CERAM', 'CMDSN', 'COMAR', 'COMIC', 'COMIX', 'CRAFT', 'CRITI', 'CRTSD', 'CURPR', 'DESGN', 'DSMBA', 'ETHSM', 'ETHST', 'EXCHG', 'EXTED', 'FASHN', 'FILMG', 'FILMS', 'FINAR', 'FNART', 'FURNT', 'FYCST', 'GAMES', 'GELCT', 'GLASS', 'GRAPH', 'HAAVC', 'ILLUS', 'INDIV', 'INDUS', 'INTDS', 'INTER', 'IXDGR', 'IXDSN', 'KADZE', 'LITPA', 'MAARD', 'MARCH', 'METAL', 'MOBIL', 'PHCRT', 'PHOTO', 'PNTDR', 'PRECO', 'PRINT', 'SCIMA', 'SCULP', 'SSHIS', 'TEXTL', 'TRAVL', 'UDIST', 'VISCR', 'WRITE', 'WRLIT');

if (isset($argv[1])) {
    $semester_str = trim($argv[1]);
} else {
    $prompt = "Semester category (e.g. 2020FA)";
    $semester_str = trim(cli_input($prompt));
    if (!strlen($semester_str)) {
        cli_error('Empty semester string, exiting without creating any course categories.');
    }
}

cli_writeln('Creating course categories for ' . $semester_str . ' semester');

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
