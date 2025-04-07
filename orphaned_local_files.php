<?php

// Check hashes of local files to see if they are referenced in the database
// in the files and/or objectfs_objects tables or check if they are in cloud
// storage, with the option to delete them if all checks pass. Examples:
//   php admin/cca_cli/orphaned_local_files.php --cloud -f=/bitnami/moodledata/hashes.txt
// deletes local files older than two weeks old with copies in cloud storage while
//   php admin/cca_cli/orphaned_local_files.php --local_table --objectfs_table -f=/tmp/hashes.txt
// deletes local files not referenced anywhere in the database
// `moosh -n file-dbcheck` checks for "files present on disk but not in the DB"
// so it does something similar (no objects table check)

/**
 * @package    admin
 * @subpackage cca_cli
 * @copyright  2025 CCA (https://www.cca.edu)
 * @license    https://opensource.org/licenses/ECL-2.0 ECL 2.0
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
// https://github.com/moodle/moodle/blob/MOODLE_310_STABLE/lib/clilib.php
require_once($CFG->libdir.'/clilib.php');

list($options, $unrecognized) = cli_get_params(
    [
        'cloud' => false,
        'debug' => false,
        'delete' => false,
        'file' => false,
        'help' => false,
        'local_table' => false,
        'objectfs_table' => false,
    ], [
        'h' => 'help',
        'f' => 'file',
    ]
);

$help = <<<EOT
Check, and optionally delete, a list of file content hashes against storages.

There are three checking options represented by flags:
  --local_table     Check that hash is not in the local files table
  --objectfs_table  Check that hash is not in the objectfs objects table
  --cloud           Check that the hash's file _is_ in cloud storage

Examples:
  php admin/cca_cli/orphaned_local_files.php --cloud --delete -f=/bitnami/moodledata/hashes.txt
deletes local files older than two weeks old with copies in cloud storage while
  php admin/cca_cli/orphaned_local_files.php --local_table --objectfs_table --delete -f=/tmp/hashes.txt
deletes local files not referenced anywhere in the database

Other Options:
  -h, --help            Print out this help
  -f=FILE, --file=FILE  ABSOLUTE path to a text file with a hash per line
  --debug               Print more information
  --delete              Delete checked files over two weeks old. CAREFUL!

Hashes are absolute paths like /opt/moodledata/filedir/00/11/0011...
Try `find /opt/moodledata/filedir -type f -mtime +14 > /bitnami/moodledata/hashes.txt`
to create a list of local file hashes over two weeks old (should be deleted already).

EOT;

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    echo $help;
    exit(0);
}

cli_writeln('The following orphaned files exist locally in ' . $CFG->dataroot . '/filedir but did not pass all checks.');
if ($options['delete']) {
    cli_writeln('They will be deleted if they are over two weeks old.');
}

// GS bucket depends on environment
$hostname = gethostname();
if (strpos($hostname, 'prod') !== false) {
    $bucket = 'moodle-production-filestore';
} elseif (strpos($hostname, 'stage') !== false) {
    $bucket = 'moodle-staging-filestore';
} else {
    $bucket = getenv('GOOGLE_STORAGE_BUCKET');
}

function in_cloud(string $hash) {
    global $bucket, $options;
    if (!$bucket) {
        cli_error('No cloud storage bucket specified. Check the hostname mapping in this script or set the GOOGLE_STORAGE_BUCKET environment variable.');
        exit(1);
    }
    // shell out to gsutil
    $gs_path = "gs://$bucket/" . substr($hash, 0, 2) . "/" . substr($hash, 2, 2) . "/" . $hash;
    $cmd = "gsutil ls " . escapeshellarg($gs_path);
    exec($cmd, $output, $exit_code);

    if ($exit_code === 0) {
        if ($options['debug']) {
            cli_writeln(implode("\n", $output));
        }
        return true;
    } else {
        if ($options['debug']) {
            cli_writeln($gs_path . ' does not exist or an error occurred.');
            if (count($output) > 0) {
                cli_writeln('Command output: ' . implode("\n", $output));
            }
        }
        return false;
    }
}

function check_db_table(string $hash, string $table) {
    global $DB, $options;
    $exists = $DB->record_exists($table, ['contenthash' => $hash]);
    if ($options['debug']) {
        cli_writeln($hash . ' in ' . $table . ': ' . var_export($exists, true));
    }
    return $exists;
}

function delete_if_old(string $path) {
    global $options;
    $stat = stat($path);
    // We test against modified time because various processes touch files
    // so frequently that atime & ctime are too recent
    // TODO we may want to make the age configurable
    if ($stat["mtime"] < time() - 1209600) {
        cli_writeln('Deleting file: ' . $path);
        return unlink($path);
    } elseif ($options['debug']) {
        cli_writeln('Not deleting ' . $path . ' because it is not over two weeks old.');
    }
    return false;
}

if ($options['file']) {
    // read file line by line
    $handle = fopen($options['file'], "r");
    if ($handle) {
        try {
            while (($path = fgets($handle)) !== false) {
                $path = trim($path);
                $hash = basename($path); // Use basename instead of explode
                $checks = 0;
                $total_checks = 0;

                if ($options['local_table']) {
                    $total_checks++;
                    if (!check_db_table($hash, 'files')) {
                        $checks++;
                    }
                }
                if ($options['objectfs_table']) {
                    $total_checks++;
                    if (!check_db_table($hash, 'tool_objectfs_objects')) {
                        $checks++;
                    }
                }
                if ($options['cloud']) {
                    $total_checks++;
                    if (in_cloud($hash)) {
                        $checks++;
                    }
                }

                if ($checks === $total_checks) {
                    cli_writeln($hash);
                    if ($options['delete']) {
                        delete_if_old($path);
                    }
                } elseif ($options['debug']) {
                    cli_writeln($hash . ' did not pass all checks.');
                }
            }
        } finally {
            fclose($handle); // Ensure file handle is closed even if an error occurs
        }
    } else {
        cli_error("Unable to open file " . getcwd() . DIRECTORY_SEPARATOR . $options['file']);
    }
} else {
    cli_error('Please specify a file of content hashes with --file=hashes.txt');
}

exit(0);
