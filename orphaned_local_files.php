<?php

// Check hashes of local files to see if they are orphaned (not referenced
// in the database). Usage:
// php admin/cca_cli/orphaned_local_files.php --hash=abced...
// php admin/cca_cli/orphaned_local_files.php --file=hashes.txt
// `moosh -n file-dbcheck` checks for "files present on disk but not in the DB"
// so it does something similar (no object check)

/**
 * @package    admin
 * @subpackage cca_cli
 * @copyright  2024 CCA (https://www.cca.edu)
 * @license    https://opensource.org/licenses/ECL-2.0 ECL 2.0
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
// https://github.com/moodle/moodle/blob/MOODLE_310_STABLE/lib/clilib.php
require_once($CFG->libdir.'/clilib.php');

list($options, $unrecognized) = cli_get_params(
    [
        'delete' => false,
        'hash' => false,
        'help' => false,
        'file' => false,
    ], [
        'h' => 'help',
        'd' => 'delete',
        'f' => 'file',
    ]
);

$help = <<<EOT
Check a single hash or file of hashes for orphaned local files.

Options:
 -h, --help             Print out this help
 -f=FILE, --file=FILE   Input text file with one hash on each line
 --hash=HASH            Check a single hash
 -d, --delete               Delete orphaned files over a week old. CAREFUL!

If you are deleting files, you want your hashes to be complete file paths like
/opt/moodledata/filedir/00/11/0011....

EOT;

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    echo $help;
    exit(0);
}

cli_writeln('The following orphaned files exist locally in ' . $CFG->dataroot . '/filedir but are not in the files nor objectfs_objects database tables.');
if ($options['delete']) {
    cli_writeln('They will be deleted if they are over a week old.');
}

function orphaned_hash(string $hash) {
    global $DB;
    $moodle_file = $DB->record_exists('files', ['contenthash' => $hash]);
    $objectfs_file = $DB->record_exists('tool_objectfs_objects', ['contenthash' => $hash]);
    if (!$moodle_file and !$objectfs_file) {
        return true;
    }
    return false;
}

function delete_if_old(string $path) {
    $stat = stat($path);
    // be conservative: take the newest of access, change, modified times
    $age = max(array($stat["atime"], $stat["ctime"], $stat["mtime"]));
    if ($age < strtotime("1 week ago")) {
        cli_writeln('Deleting');
        return unlink($path);
    }
    return false;
}

if ($options['hash']) {
    $path = $options['hash'];
    $hash = end(explode(DIRECTORY_SEPARATOR, $options['hash']));
    if (orphaned_hash($hash)) {
        cli_writeln($path);
        if ($options['delete']) {
            delete_if_old($path);
        }
    }
} elseif ($options['file']) {
    // read file line by line
    $handle = fopen($options['file'], "r");
    if ($handle) {
        while (($path = fgets($handle)) !== false) {
            $hash = end(explode(DIRECTORY_SEPARATOR, $options['hash']));
            if (orphaned_hash($hash)) {
                cli_write($hash);
                if ($options['delete']) {
                    delete_if_old($path);
                }
            }
        }
    } else {
        cli_error("Unable to open file " . getcwd() . DIRECTORY_SEPARATOR . $options['file']);
    }

    fclose($handle);
}

exit(0);
