# !!!!!!!!!!!!! RUN update_1.7_rebuildLOs.php BEFORE UPDATING THE PHP CLASSES !!!!!!!!!!!!!!!!!!!!!!!!!

# Update the php classes from svn

ALTER TABLE  `lo_map_authors` ADD UNIQUE (
`userID` ,
`loID`
);

ALTER TABLE  `lo_media` DROP  `scorable`;

RENAME TABLE  `lo_visits` TO  `lo_tracking_visits` ;
RENAME TABLE  `lo_computer_data` TO  `lo_tracking_profile` ;
RENAME TABLE  `lo_attempts` TO  `lo_tracking_attempts` ;
RENAME TABLE  `lo_qscores` TO  `lo_tracking_qscores` ;
RENAME TABLE  `lo_qgroups` TO  `lo_los_qgroups` ;
RENAME TABLE  `lo_answers` TO  `lo_los_answers` ;
RENAME TABLE  `lo_keywords` TO  `lo_los_keywords` ;
RENAME TABLE  `lo_perms_item` TO  `lo_map_perms_to_item` ;
RENAME TABLE  `lo_map_pages` TO  `lo_map_pages_to_lo` ;
RENAME TABLE  `lo_map_keywords` TO  `lo_map_keywords_to_lo` ;
RENAME TABLE  `lo_map_authors` TO  `lo_map_authors_to_lo` ;
RENAME TABLE  `lo_map_qgroup` TO  `lo_map_qgroup_to_lo` ;
RENAME TABLE  `lo_map_roles` TO  `lo_map_roles_to_user` ;
RENAME TABLE  `lo_roles` TO  `lo_users_roles` ;
RENAME TABLE  `lo_instances` TO `lo_los_instances` ;
RENAME TABLE  `lo_instances_deleted` TO  `lo_deleted_instances` ;
RENAME TABLE  `lo_los_deleted` TO  `lo_deleted_los` ;
RENAME TABLE  `lo_media` TO  `lo_los_media` ;
RENAME TABLE  `lo_auth_internal` TO  `lo_users_auth_internal` ;
RENAME TABLE  `lo_auth_ucf` TO  `lo_users_auth_ucf` ;
RENAME TABLE  `lo_map_qalts` TO  `lo_map_qalts_to_qgroup` ;
RENAME TABLE  `lo_map_perms` TO  `lo_map_perms_to_lo` ;
RENAME TABLE  `lo_attempts_extra` TO  `lo_map_extra_attempts_to_user` ;
RENAME TABLE  `lo_languages` TO  `lo_los_languages` ;


RENAME TABLE  `lo_deleted_instances` TO  `obo_deleted_instances` ;
RENAME TABLE  `lo_deleted_los` TO  `obo_deleted_los` ;
RENAME TABLE  `lo_locks` TO  `obo_locks` ;
RENAME TABLE  `lo_los_answers` TO  `obo_los_answers` ;
RENAME TABLE  `lo_los_instances` TO  `obo_los_instances` ;
RENAME TABLE  `lo_los_keywords` TO  `obo_los_keywords` ;
RENAME TABLE  `lo_los_languages` TO  `obo_los_languages` ;
RENAME TABLE  `lo_los_media` TO  `obo_los_media` ;
RENAME TABLE  `lo_los_qgroups` TO  `obo_los_qgroups` ;
RENAME TABLE  `lo_map_authors_to_lo` TO  `obo_map_authors_to_lo` ;
RENAME TABLE  `lo_map_extra_attempts_to_user` TO  `obo_map_extra_attempts_to_user` ;
RENAME TABLE  `lo_map_keywords_to_lo` TO  `obo_map_keywords_to_lo` ;
RENAME TABLE  `lo_map_pages_to_lo` TO  `obo_map_pages_to_lo` ;
RENAME TABLE  `lo_map_perms_to_item` TO  `obo_map_perms_to_item` ;
RENAME TABLE  `lo_map_perms_to_lo` TO  `obo_map_perms_to_item_old` ;
RENAME TABLE  `lo_map_qalts_to_qgroup` TO  `obo_map_qalts_to_qgroup` ;
RENAME TABLE  `lo_map_qgroup_to_lo` TO  `obo_map_qgroup_to_lo` ;
RENAME TABLE  `lo_map_roles_to_user` TO  `obo_map_roles_to_user` ;
RENAME TABLE  `lo_semesters` TO  `obo_semesters` ;
RENAME TABLE  `lo_temp` TO  `obo_temp` ;
RENAME TABLE  `lo_tracking` TO  `obo_tracking` ;
RENAME TABLE  `lo_tracking_attempts` TO  `obo_tracking_attempts` ;
RENAME TABLE  `lo_tracking_profile` TO  `obo_tracking_profile` ;
RENAME TABLE  `lo_tracking_qscores` TO  `obo_tracking_qscores` ;
RENAME TABLE  `lo_tracking_visits` TO  `obo_tracking_visits` ;
RENAME TABLE  `lo_users` TO  `obo_users` ;
RENAME TABLE  `lo_users_auth_internal` TO  `obo_users_auth_internal` ;
RENAME TABLE  `lo_users_auth_ucf` TO  `obo_users_auth_ucf` ;
RENAME TABLE  `lo_users_roles` TO  `obo_users_roles` ;
RENAME TABLE  `obo_los_answers` TO  `obo_lo_answers` ;
RENAME TABLE  `obo_los_instances` TO  `obo_lo_instances` ;
RENAME TABLE  `obo_los_keywords` TO  `obo_lo_keywords` ;
RENAME TABLE  `obo_los_languages` TO  `obo_lo_languages` ;
RENAME TABLE  `obo_los_media` TO  `obo_lo_media` ;
RENAME TABLE  `obo_los_qgroups` TO  `obo_lo_qgroups` ;
RENAME TABLE  `obo_locks` TO  `obo_lo_locks` ;
              
RENAME TABLE  `obo_tracking` TO  `obo_logs` ;
RENAME TABLE  `obo_tracking_attempts` TO  `obo_log_attempts` ;
RENAME TABLE  `obo_tracking_profile` TO  `obo_log_profile` ;
RENAME TABLE  `obo_tracking_qscores` TO  `obo_log_qscores` ;
RENAME TABLE  `obo_tracking_visits` TO  `obo_log_visits` ;
              
RENAME TABLE  `obo_users_auth_internal` TO  `obo_user_auth_internal` ;
RENAME TABLE  `obo_users_auth_ucf` TO  `obo_user_auth_ucf` ;
RENAME TABLE  `obo_users_roles` TO  `obo_user_roles` ;
RENAME TABLE  `obo_temp` TO  `obo_system_temp` ;
RENAME TABLE  `obo_map_qgroup_to_lo` TO  `obo_map_questions_to_qgroup` ;
ALTER TABLE  `obo_map_questions_to_qgroup` DROP  `itemType`;

CREATE TABLE  `obo_map_media_to_lo` (
`mediaID` BIGINT( 255 ) UNSIGNED NOT NULL ,
`loID` BIGINT( 255 ) UNSIGNED NOT NULL ,
INDEX (  `mediaID` ,  `loID` )
) ENGINE = MYISAM ;
ALTER TABLE  `obo_map_media_to_lo` ENGINE = INNODB

#Run update_1.7_mediaLinkage.php

#Run update_1.7_removeScorableFromMedia

#Turn off caching, and run testBuildLOs (make sure you don't see 'scorable' and all los have questions, pages)

DROP TABLE  `obo_lo_answers`;
DROP TABLE  `lo_desc_obj` ,
`lo_los` ,
`lo_los_cache` ,
`lo_los_pages_cache` ,
`lo_map_feedback` ,
`lo_map_items` ,
`lo_map_media` ,
`lo_map_qa` ,
`lo_map_qitems` ,
`lo_pages` ,
`lo_page_items` ,
`lo_page_items_new` ,
`lo_qgroups_cache` ,
`lo_questions`,
`lo_courses` ;


CREATE TABLE `plg_wc_grade_columns` (
  `instID` bigint(255) unsigned NOT NULL,
  `sectionID` bigint(255) unsigned NOT NULL,
  `columnID` bigint(255) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE  `plg_wc_grade_columns` ADD PRIMARY KEY (  `instID` );
ALTER TABLE  `plg_wc_grade_columns` ADD  `userID` BIGINT( 255 ) UNSIGNED NOT NULL AFTER  `instID`;
ALTER TABLE  `plg_wc_grade_columns` ADD  `columnName` VARCHAR( 255 ) NOT NULL;

CREATE TABLE  `plg_wc_grade_log` (
`instID` BIGINT( 255 ) UNSIGNED NOT NULL ,
`userID` BIGINT( 255 ) UNSIGNED NOT NULL ,
`timestamp` INT( 30 ) UNSIGNED NOT NULL ,
`courseID` BIGINT( 255 ) UNSIGNED NOT NULL ,
`columnID` BIGINT( 255 ) UNSIGNED NOT NULL ,
`columnName` VARCHAR( 255 ) NOT NULL ,
`success` ENUM(  "0",  "1" ) NOT NULL ,
INDEX (  `instID` ,  `userID` ,  `success` )
) ENGINE = INNODB;
ALTER TABLE  `plg_wc_grade_log` CHANGE  `courseID`  `sectionID` BIGINT( 255 ) UNSIGNED NOT NULL;
ALTER TABLE  `plg_wc_grade_log` ADD  `studentID` BIGINT( 255 ) UNSIGNED NOT NULL AFTER  `userID` ,
ADD INDEX (  `studentID` );
ALTER TABLE  `plg_wc_grade_log` ADD  `score` INT( 3 ) UNSIGNED NOT NULL AFTER  `columnName`;
ALTER TABLE  `plg_wc_grade_log` ADD UNIQUE (
`instID` ,
`studentID` ,
`sectionID` ,
`columnID`
);
ALTER TABLE  `plg_wc_grade_log` CHANGE  `timestamp`  `createTime` INT( 30 ) UNSIGNED NOT NULL;


# ADD the following options in your cfgLocal:
# add ucfcourses to CORE_PLUGINS:
#const CORE_PLUGINS = 'sets,Kogneato,UCFCourses';
#
#// App key for pushing scores to webcourses
#const UCFCOURSES_APP_KEY = 'aaa';
#const UCFCOURSES_URL_WEB = 'http://endor:8000';
