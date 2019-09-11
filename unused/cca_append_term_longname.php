<?php

// CCA custom utility script.
// Retroactively appends term ID to course full names for SP16


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

global $DB;

// Get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "Enter a Moodle (not datatel) course ID when prompted.";
    echo $help;
    die;
}

cli_heading('Add term ID to course fullnames');

// PHP doesn't have a built-in "endswith" ???
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

$sql = "select * from mdl_course where shortname LIKE '%Sp16%'";
$results = $DB->get_records_sql($sql);

if ($results) {

    // $DB->Update_record('course',$record,false);
    foreach ($results as $result) {

        echo("$result->id \n");
        echo("$result->shortname \n");
        echo("$result->fullname \n");

        $pieces = explode("-", $result->shortname);
        $suffix = array_pop($pieces);

        if (endswith($result->fullname, $suffix)) {
            $newtitle = $result->fullname;  # Do nothing
        } else {
            $newtitle = $result->fullname . " - " . $suffix;
        }

        $uprec = new stdClass();
        $uprec->id = $result->id;
        $uprec->fullname = $newtitle;
        $DB->update_record('course', $uprec, false);


        echo("Fixed: $newtitle \n");
        echo("\n");

        // echo generateRandomString();
    }

} else {
    echo("Could not find courses\n");
    exit();
}




echo("\n");

exit(0); // 0 means success