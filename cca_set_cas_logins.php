<?php

// CCA custom utility script.
// Sets all logins to type "cas" except for "guest" and "etadmin".
// Needed because Moodle's "external database enrollment" plugin sets all to "db" with no config option.

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

cli_heading('Set all auth types to cas');

$sql = 'update mdl_user set auth = "cas" where username != "etadmin" and username != "guest" ';
$results = $DB->execute($sql);

echo("\n");

exit(0); // 0 means success
