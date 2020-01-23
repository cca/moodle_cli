<?php

    // NOTE: returns an associative array with strings for names & usernames
    function get_instructors_for_course($courseid) {
        global $DB;
        $instuctors = array("names" => "", "emails" => "");

        $course = get_course($courseid);
        $coursecontext = context_course::instance($courseid);

        // Get instructors
        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $teachers = get_role_users($role->id, $coursecontext);

        foreach ($teachers as $id => $teacher) {
            $instructors["names"] .= "$teacher->firstname $teacher->lastname, ";
            $instructors["emails"] .= "$teacher->email, ";
        }
        // Strip trailing comma
        $instructors["names"] = rtrim($instructors["names"], ", ");
        $instructors["emails"] = rtrim($instructors["emails"], ", ");
        return $instructors;
    }

    function get_term_for_coursecat($catid) {
        global $DB;

        $select = "id = $catid";
        $catrec = $DB->get_record_select('course_categories', $select);
        $catpath = $catrec->path;
        $parentpath = explode("/", $catpath);
        // Category paths are stored like "/72/34/28" with each integer being a nested reference to a category ID
        // [0] is empty since string starts with /, so we actually want [1]
        // Get the category with that ID and that will be the term
        $select = "id = $parentpath[1]";
        $catrec = $DB->get_record_select('course_categories', $select);
        return $catrec->name;
    }

    function get_catname($catid) {
        global $DB;

        $select = "id = $catid";
        $catrec = $DB->get_record_select('course_categories', $select);
        return $catrec->name;
    }

    function get_hits_for_course($course_id) {
        // Get hit count for a single course.
        global $DB;

        $sql = "SELECT COUNT(l.id) hits, l.courseid courseId, c.fullname coursename, c.category coursecat
        FROM {logstore_standard_log} l INNER JOIN {course} c ON l.courseid = c.id
        WHERE c.id = $course_id
        ";
        $results = $DB->get_record_sql($sql);
        return $results->hits;
    }

    function get_html_rows($courses) {
        // Generate HTML table rows from an array of courses
        $rowstring = "";
        foreach ($courses as $course) {
            $coursecontext = context_course::instance($course->id);
            $enrolnum = count_enrolled_users($coursecontext);
            $coursehits = get_hits_for_course($course->id);
            $category = get_catname($course->category);
            $instructors = get_instructors_for_course($course->id);
            $names = $instructors["names"];
            $emails = $instructors["emails"];
            $term = get_term_for_coursecat($course->category);
            $visibility = ($course->visible == "1" ? "visible" : "hidden");

            $rowstring = $rowstring . "<tr><td>$course->id</td><td>$category</td><td>$term</td><td>$course->shortname</td><td>$course->fullname</td><td>$names</td><td>$emails</td><td>$enrolnum</td><td>$coursehits</td><td>$visibility</td></tr>\n";
        }
        return $rowstring;
    }

?>
