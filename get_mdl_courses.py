import os
import sys

import requests

import config

# https://moodle.cca.edu/webservice/rest/server.php?wstoken=...&wsfunction=core_course_get_courses&moodlewsrestformat=json

def get_mdl_course_id():
    """ return the complete list of courses in Moodle

    returns: a list of course objects
    """
    url = config.url
    params = {
        # found at https://moodle.cca.edu/admin/settings.php?section=webservicetokens
        'wstoken': config.token,
        'wsfunction': 'core_course_get_courses',
        'moodlewsrestformat': 'json'
    }

    response = requests.get(url, params=params)
    data = response.json()

    if data and isinstance(data, list):
        for c in data:
            print(c["shortname"])
        print("Found {} total courses".format(len(data)))
        return data
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
    return "Error: {}".format(data)

# CLI use: pass shortname on the command line
if __name__ == "__main__":
    get_mdl_course_id()
