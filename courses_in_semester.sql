SELECT parents.name as parent_category_name, cc.name as category_name, COUNT(*) as course_count
FROM {course} c
JOIN {course_categories} cc ON (c.category = cc.id)
LEFT JOIN {course_categories} parents ON (cc.parent = parents.id)
WHERE c.visible = 1
AND parents.name != "Communities"
GROUP BY parents.name, cc.name
ORDER BY parent_category_name DESC, category_name ASC
