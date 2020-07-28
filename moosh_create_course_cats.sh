#!/bin/bash

# Create course categories for a given term
# Run this once per term at the beginning

# We use a PHP script now so we can create categories complete with a predictable
# idnumber hook for our enrollment database.
# Note that we also could have used this script with `moosh category-config-set`
# https://moosh-online.com/commands/#category-config-set to create idnumbers
echo 'DO NOT USE! Run create_course_cats.php instead.'
exit 1

echo -n "Enter semester (in form '2017SU'):"
read term

# meow
cats=(Metacourses ANIMA ARCHT ARTED CERAM COMAR COMIC CRAFT CRITI CRTSD CURPR DESGN DIVSM DIVST DSMBA EXCHG EXTED FASHN FILMG FILMS FINAR FNART FURNT FYCST GELCT GLASS GRAPH ILLUS INDIV INDUS INTER IXDGR IXDSN LITPA MAARD MARCH METAL MOBIL PHCRT PHOTO PNTDR PRINT SCIMA SCULP SFMBA SSHIS TEXTL UDIST VISCR VISST WRITE WRLIT)

# moosh category-create returns the ID of the category
echo "Creating course category for ${term}..."
parent=$(moosh -n category-create -v 1 ${term})

for cat in ${cats[*]}; do
    echo "Creating course category for ${cat}..."
    moosh -n category-create -p ${parent} -v 1 $cat
done
