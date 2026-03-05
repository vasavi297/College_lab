#!/bin/bash
# Import script for database
echo 'Importing database...'
mysql -u root -p < database_proper_order.sql
echo '✅ Database imported successfully!'
echo 'Verifying foreign keys...'
mysql -u root -p -e "USE college_lab; SELECT TABLE_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' AND TABLE_SCHEMA = 'college_lab';"
