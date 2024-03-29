#!/bin/bash
DB_USER=$MYSQL_USER
DB_PASSWORD=$MYSQL_PASSWORD
DB_NAME=$MYSQL_DATABASE
BACKUP_DIR=/var/backups
BACKUP_FILE=db_$(date +'%Y-%m-%d').sql

/usr/bin/mysqldump -u$DB_USER --password=$DB_PASSWORD $DB_NAME > $BACKUP_DIR/$BACKUP_FILE && gzip $BACKUP_DIR/$BACKUP_FILE
