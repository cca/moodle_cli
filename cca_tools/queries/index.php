<?php
    /*
    CCA custom Moodle stats. Exports query results to HTML table and provides
    CSV export via http://ngiriraj.com/pages/htmltable_export/demo.php
    Column header sorting via https://github.com/drvic10k/bootstrap-sortable
    */
?>

<!DOCTYPE html>
<html>
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

        // Most of our custom lookup logic is here:
        require_once('include/functions.php');
        global $DB;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (isset($_POST["term_id"])) {  // Term Summary
                $term_id = ($_POST["term_id"]);
                $term = get_catname($term_id);

                // Get all children of a given category ID. We can't query for courses in subcats,
                // so loop through categories in this term and get cat IDs, then for each of those,
                // get courses.
                $rowstrings = "";
                $coursecount = 0;
                $categories = coursecat::get($term_id)->get_children();
                foreach ($categories as $cat) {
                    $courses = get_courses($cat->id);
                    $coursecount += count($courses);
                    $rowstrings .= get_html_rows($courses);
                }

            } elseif (isset($_POST["minhits"])) {  // Most active

                $minhits = ($_POST["minhits"]);
                $rowstrings = "";

                // Query for active courses. Exclude the generic "Moodle" course with ID #1
                $sql = "SELECT COUNT(l.id) hits, l.courseid courseId, c.fullname coursename, c.category coursecat
                FROM {logstore_standard_log} l INNER JOIN {course} c ON l.courseid = c.id
                WHERE c.id != 1
                GROUP BY courseId
                HAVING COUNT(l.id) > $minhits
                ORDER BY hits DESC
                ";

                $results = $DB->get_records_sql($sql);

                // That gives us sql results but we need real Moodle course objects.
                // Generate new array. Missing Python's list comprehensions!
                $courseset = array();
                foreach ($results as $result) {
                    $courseset[] = get_course($result->courseid);
                }
                $rowstrings .= get_html_rows($courseset);

            }


            if (isset($_POST["term_id"])) {
                echo("<h3>Term Summary for $term</h3>\n");
                echo("<p>Total number of courses listed for term: <strong>$coursecount.</strong></p>\n");
            } elseif (isset($_POST["minhits"])) {
                echo("<h3>Most active courses</h3>\n");
            }

            echo("<p><strong>&laquo; Click column headers to sort &raquo;</strong></p>\n");
            echo("<p><strong><a href=\"#\" id=\"dl_link\">Download as CSV</a> | <a href=\"index.php\">Return</a></strong></p>");
            echo("<table  class=\"table table-striped sortable\" id=\"htmltable\"><thead><tr><th>Moodle ID</th><th>CCA Course ID</th><th>Category</th><th>Term</th><th>Shortname</th><th>Fullname</th><th>Teachers</th><th>Enrolled</th><th>Hits</th></tr></thead><tbody>$rowstrings</tbody></table>");
            ?>

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

                        $(this)
                            .attr({
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

            <?php

        } else {
            if (is_siteadmin()){  // Superusers only

                // Query for top-level categories. Exclude the "Miscellaneous" cat which is meaningless here.
                $sql = "SELECT id, name
                FROM {course_categories}
                WHERE depth=1
                AND id != 1
                ORDER BY id ASC
                ";

                $results = $DB->get_records_sql($sql);
                ?>

                <form class="navbar-form navbar-left" role="search" action="" method="post">
                <h3>Term Summary</h3>
                  <div class="form-group">
                    <select name="term_id" class="form-control">
                    <option value="" selected="true" disabled="disabled">Select a term</option>
                    <?php
                        foreach ($results as $term) {
                            echo("<option value=\"$term->id\">$term->name</option>\n");
                        }
                    ?>
                    </select>
                  </div>
                  <input type="submit" value="Query" class="btn btn-primary">
                </form>


                <form class="navbar-form navbar-left" role="search" action="" method="post">
                <h3>Most Active Courses</h3>
                  <div class="form-group">
                    <input type="text" name="minhits" value="4000" class="form-control">
                  </div>
                  <input type="submit" value="Query" class="btn btn-primary">
                </form>

                <?php
            } else {
                echo("<p>Please log in to Moodle first.</p>");
            };
        }?>

    <div>
</body>
</html>
