#!/bin/bash

# Create course categories for a given term
# Run this once per term at the beginning

echo -n "Enter semester (in form '2017SU'):"
read term

# meow
cats=(Metacourses ANIMA ARCHT ARTED CERAM COMAR COMIC CRAFT CRITI CRTSD CURPR DESGN DIVSM DIVST DSMBA EXCHG EXTED FASHN FILMG FILMS FINAR FNART FURNT FYCST GELCT GLASS GRAPH ILLUS INDIV INDUS INTER IXDGR IXDSN LITPA MARCH METAL PHCRT PHOTO PNTDR PRINT SCIMA SCULP SFMBA SSHIS TEXTL UDIST VISCR VISST WRITE WRLIT)

# moosh category-create returns the ID of the category
echo "Creating course category for ${term}..."
parent=$(moosh -n category-create -v 1 ${term})

for cat in ${cats[*]}; do
    echo "Creating course category for ${cat}..."
    moosh -n category-create -p ${parent} -v 1 $cat
done
