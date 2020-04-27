import os
import sys

import requests

# https://moodle.cca.edu/webservice/rest/server.php?wstoken=...&wsfunction=core_course_get_categories&moodlewsrestformat=json&criteria[0][key]=name&criteria[0][value]=2019SP


def get_mdl_categories(filter):
    """ obtain a list of JSON representations of Moodle course categories

    returns an array of category dicts (see their fields below)

    `filter` is a dict like {"name": "2019SP"} which is used to retrieve only
    categories where the given field matches the value. So, for instance, we can
    find the term-level category by filtering on name and then find the children
    of that term by filter on {"parent": {{id of term}} }. You may specify
    multiple keys to filter upon.

    fields are: id, name, parent, coursecount, visible, timemodified, depth, path*

    * `path` looks like /{{parent ID}}/{{category ID}} so it's an effective way
    to find where a category lies in the hierarchy. The list above is only the
    useful fields. To see the full set, look at a returned value. A few fields
    are empty or unused like "idnumber" and "description".
    """
    # constants
    url = 'https://moodle.cca.edu/webservice/rest/server.php'
    token = '...' # found at https://moodle.cca.edu/admin/settings.php?section=webservicetokens
    service = 'core_course_get_categories'
    format = 'json'
    params = {
        'wstoken': token,
        'wsfunction': service,
        'moodlewsrestformat': format,
    }

    if os.path.exists('.token'):
        with open('.token', 'r') as fh:
            params['wstoken'] = fh.read().strip()

    # construct criteria in PHP array query string format
    # because it wouldn't be Moodle without a weird, antiquated nuance
    num_filters = 0
    for key, value in filter.items():
        params["criteria[{}][key]".format(num_filters)] = key
        params["criteria[{}][value]".format(num_filters)] = value
        num_filters += 1

    response = requests.get(url, params=params)
    # @TODO error handling
    return response.json()

# CLI use: pass semester category name on the command line
if __name__ == "__main__":
    print(get_mdl_categories({ "name": sys.argv[1] }))
