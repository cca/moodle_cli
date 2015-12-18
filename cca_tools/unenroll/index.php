<?php
    /*
    Unenroll a user from a class by User ID and Course ID
    */
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manual Unenroll</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>


    <div class="container">

        <?php

        require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
        include_once($CFG->libdir . '/coursecatlib.php');


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                $user_id = ($_POST["user_id"]);
                $course_id = ($_POST["course_id"]);

                // Need the mdl_user_enrolments_id to do the drop
                $subsql = "select ue.id, ue.status, e.courseid from mdl_user_enrolments as ue join mdl_enrol as e on ue.enrolid=e.id " .
                    "where e.courseid=$course_id and ue.userid=$user_id";

                $subresults = $DB->get_records_sql($subsql);
                foreach ($subresults as $subresult) {
                    // print_r($subresult);

                    $enrolments_id = $subresult->id;
                    // echo(" enrolment id $enrolments_id \n");
                    // Drop the  enrolment records
                    if ($DB->delete_records('user_enrolments', array('id' => $enrolments_id, 'userid' => $user_id))) {
                        echo("<p>User $user_id was dropped from course $course_id</p>");

                    } else {
                        echo("<p>User NOT dropped from course - check the participants list and your IDs to make sure they're already enrolled.</p>");
                    }
                }


        } else {
            if (is_siteadmin()){  // Superusers only
                ?>

                <form class="navbar-form navbar-left" role="search" action="" method="post">
                <h3>Unenroll User from Course</h3>
                <p>Because we have Moodle configured for fully automated enrollments, it's not possible to
                    unenroll a user from the Moodle admin UI. However, there are edge cases where this capability is needed.
                    Use the screenshots below to learn how to get a Moodle user ID and course ID.
                </p>
                  <div class="form-group">
                    <p>User ID:<br /><input type="text" name="user_id" class="form-control"></p>
                    <p>Course ID:<br /><input type="text" name="course_id" class="form-control"></p>
                  </div>
                  <p><input type="submit" value="Submit" class="btn btn-primary"></p>
                </form>

                <hr style="width: 100%; color: gray; height: 1px; background-color:black;" />

                <h4>Do not confuse Moodle user/course IDs with CCA/Colleague IDs!</h4>
                <p>
                    To find the Moodle User and Course IDs, view the Participants list for a specific course and click
                    on the user's name in that list. The resulting page is the user's membership page for that course, and the URL
                    of the page includes both IDs. "id=" is the user ID and "course=" is the course ID.
                </p>
                <p><img src="moodle_url_ids.png" width="600"></p>

                <?php
            } else {
                echo("<p>Please log in to Moodle first.</p>");
            };


        }?>

    <div>
</body>
</html>
