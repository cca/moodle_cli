<?php
    /*
    CCA custom Moodle stats. Exports query results to HTML table and provides
    CSV export via http://ngiriraj.com/pages/htmltable_export/demo.php
    Column header sorting via https://github.com/drvic10k/bootstrap-sortable
    */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="bootstrap-sortable/moment.min.js"></script>
    <script type="text/javascript" src="bootstrap-sortable/bootstrap-sortable.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap-sortable/bootstrap-sortable.css" rel="stylesheet">
</head>
<body>
    <div class="container">

        <?php

        $moodle_dir = '/opt/moodle';
        require($moodle_dir . '/config.php');
        include_once($moodle_dir . '/lib/coursecatlib.php');
        include_once($moodle_dir . '/lib/datalib.php');

        global $DB;
        $results = Array();

        if (is_siteadmin()) {
            // Query for active courses. Exclude the generic "Moodle" course with ID #1
            $sql = "SELECT parents.name as parent_category_name, cc.name as category_name, c.*
            FROM mdl_course c
            JOIN mdl_course_categories cc ON (c.category = cc.id)
            LEFT JOIN mdl_course_categories parents ON (cc.parent = parents.id)
            WHERE c.visible = 1
            GROUP BY parents.name
            SORT BY parents.name DESC, cc.name ASC";

            $results = $DB->get_records_sql($sql);
        } else {
            echo("<p>Please log in to Moodle first.</p>");
        }
        ?>

        <p><strong>&laquo; Click column headers to sort &raquo;</strong></p>
        <p><strong><a href="#" id="dl_link">Download as CSV</a> | <a href="index.php">To Queries Index</a></strong></p>
        <table class="table table-striped sortable" id="htmltable">
            <thead>
                <tr>
                    <th>Moodle ID</th>
                    <th>CCA Course ID</th>
                    <th>Category</th>
                    <th>Term</th>
                    <th>Shortname</th>
                    <th>Fullname</th>
                    <th>Teachers</th>
                    <th>Enrolled</th>
                    <th>Hits</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($results as $result) {
                        echo('<tr>' . $result);
                    }
                ?>
            </tbody>
        </table>
    <div>
<script>
$(document).ready(function () {

    function exportTableToCSV($table, filename) {

        var $rows = $table.find('tr'),
            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character
            // actual delimiter characters for CSV format
            colDelim = '\t',
            rowDelim = '\r\n',

            // Grab text from table into CSV formatted string
            csv = $rows.map(function (i, row) {
                var $row = $(row),
                    $cols = $row.find('td, th');

                return $cols.map(function (j, col) {
                    var $col = $(col),
                        text = $col.text();

                    return text.replace('"', '""'); // escape double quotes

                }).get().join(tmpColDelim);

            }).get().join(tmpRowDelim)
                .split(tmpRowDelim).join(rowDelim)
                .split(tmpColDelim).join(colDelim),

            // Data URI
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

        $(this).attr({
            'download': filename,
                'href': csvData,
                'target': '_blank'
        });
    }

    // This must be a hyperlink
    $("#dl_link").on('click', function (event) {
        exportTableToCSV.apply(this, [$('#htmltable'), 'moodle_stats_export.csv']);
    });
});
</script>
</body>
</html>
