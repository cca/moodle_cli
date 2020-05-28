import os
import sys

import requests

import config

# https://moodle.cca.edu/webservice/rest/server.php?wstoken=...&wsfunction=core_course_get_courses_by_field&moodlewsrestformat=json&field=shortname&value=EXCHG-3740-1-2019FA

def get_mdl_course_id(shortname):
    """ find out Moodle's internal ID for a course (so you can link to it)

    returns: a string composed of numbers e.g. "8452"

    `shortname` is a string structured like so:

        {section code}-{any cross-listed section codes}-{term}

    e.g. for an unlisted section from Fall 2019 it looks like:

        EXCHG-3740-1-2019FA

    For a once-crosslisted section it looks like:

        LITPA-2000-15-WRLIT-2100-13-2019FA

    For a multi-crosslisted section, sections _appear_ (@TODO confirm) to be
    listed in first alphabetical order by department and within department by
    ascending numerical order, thus a triple-crosslisted section across two
    (CERAM & CRAFT) departments looks like:

        CERAM-1000-1-CERAM-2700-2-CERAM-3700-2-CRAFT-2700-3-2019FA
    """
    url = config.url
    params = {
        # found at https://moodle.cca.edu/admin/settings.php?section=webservicetokens
        'wstoken': config.token,
        'wsfunction': 'core_course_get_courses_by_field',
        'moodlewsrestformat': 'json',
        # theoretically we can search using ID, a list of IDs, idnumber,
        # or category but in reality shortname is only viable option
        'field': 'shortname',
        'value': shortname,
    }

    response = requests.get(url, params=params)
    data = response.json()
    courses = data.get('courses')

    if type(courses) == list:
        if len(courses) > 0:
            # theoretically this is always a single-entry array
            return str(courses[0]["id"])
        """
        If no course matches the shortname, there's no "exception" in the response
        and we receive a pair of empty arrays:
        { courses: [ ], warnings: [ ] }

        For now, we return empty string but Portal may want some specific
        handling for this situation (which will definitely occur).
        """
        return ''
    else:
        """
        Moodle sends an HTTP 200 response back on errors with details in the JSON.
        Below are just a few examples I've run into.

        If it doesn't recognize the structure of the criteria parameter in the URL,
        you get this error message:
        { exception: "invalid_parameter_exception", errorcode: "invalidparameter",
        message: "Invalid parameter value detected" }

        If the web service being specified doesn't exist, you get:
        { exception: "dml_missing_record_exception", errorcode: "invalidrecord",
        message: "Can not find data record in database table external_functions."}

        If the token you're using is related to a Service that doesn't have the
        necessary permissions you get:
        { exception: "webservice_access_exception", errorcode: "accessexception",
        message: "Access control exception" }
        """
        return "Error: {}".format(data["message"])
    return data

# CLI use: pass shortname on the command line
if __name__ == "__main__":
    print(get_mdl_course_id(sys.argv[1]))
