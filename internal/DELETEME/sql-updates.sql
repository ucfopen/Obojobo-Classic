# !!!!!!!!!!!!! RUN update_1.7_rebuildLOs.php BEFORE UPDATING THE PHP CLASSES !!!!!!!!!!!!!!!!!!!!!!!!!

# Update the php classes from svn

ALTER TABLE  `lo_map_authors` ADD UNIQUE (
`userID` ,
`loID`
);


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


ENAME TABLE  `los`.`lo_deleted_instances` TO  `los`.`obo_deleted_instances` ;
RENAME TABLE  `los`.`lo_deleted_los` TO  `los`.`obo_deleted_los` ;
RENAME TABLE  `los`.`lo_locks` TO  `los`.`obo_locks` ;
RENAME TABLE  `los`.`lo_los` TO  `los`.`obo_los` ;
RENAME TABLE  `los`.`lo_los_answers` TO  `los`.`obo_los_answers` ;
RENAME TABLE  `los`.`lo_los_instances` TO  `los`.`obo_los_instances` ;
RENAME TABLE  `los`.`lo_los_keywords` TO  `los`.`obo_los_keywords` ;
RENAME TABLE  `los`.`lo_los_languages` TO  `los`.`obo_los_languages` ;
RENAME TABLE  `los`.`lo_los_media` TO  `los`.`obo_los_media` ;
RENAME TABLE  `los`.`lo_los_pages` TO  `los`.`obo_los_pages` ;
RENAME TABLE  `los`.`lo_los_qgroups` TO  `los`.`obo_los_qgroups` ;
RENAME TABLE  `los`.`lo_map_authors_to_lo` TO  `los`.`obo_map_authors_to_lo` ;
RENAME TABLE  `los`.`lo_los_questions` TO  `los`.`obo_los_questions` ;
RENAME TABLE  `los`.`lo_map_extra_attempts_to_user` TO  `los`.`obo_map_extra_attempts_to_user` ;
RENAME TABLE  `los`.`lo_map_keywords_to_lo` TO  `los`.`obo_map_keywords_to_lo` ;
RENAME TABLE  `los`.`lo_map_pages_to_lo` TO  `los`.`obo_map_pages_to_lo` ;
RENAME TABLE  `los`.`lo_map_perms_to_item` TO  `los`.`obo_map_perms_to_item` ;
RENAME TABLE  `los`.`lo_map_perms_to_lo` TO  `los`.`obo_map_perms_to_lo` ;
RENAME TABLE  `los`.`lo_map_qalts_to_qgroup` TO  `los`.`obo_map_qalts_to_qgroup` ;
RENAME TABLE  `los`.`lo_map_qgroup_to_lo` TO  `los`.`obo_map_qgroup_to_lo` ;
RENAME TABLE  `los`.`lo_map_roles_to_user` TO  `los`.`obo_map_roles_to_user` ;
RENAME TABLE  `los`.`lo_semesters` TO  `los`.`obo_semesters` ;
RENAME TABLE  `los`.`lo_temp` TO  `los`.`obo_temp` ;
RENAME TABLE  `los`.`lo_tracking` TO  `los`.`obo_tracking` ;
RENAME TABLE  `los`.`lo_tracking_attempts` TO  `los`.`obo_tracking_attempts` ;
RENAME TABLE  `los`.`lo_tracking_profile` TO  `los`.`obo_tracking_profile` ;
RENAME TABLE  `los`.`lo_tracking_qscores` TO  `los`.`obo_tracking_qscores` ;
RENAME TABLE  `los`.`lo_tracking_visits` TO  `los`.`obo_tracking_visits` ;
RENAME TABLE  `los`.`lo_users` TO  `los`.`obo_users` ;
RENAME TABLE  `los`.`lo_users_auth_internal` TO  `los`.`obo_users_auth_internal` ;
RENAME TABLE  `los`.`lo_users_auth_ucf` TO  `los`.`obo_users_auth_ucf` ;
RENAME TABLE  `los`.`lo_users_roles` TO  `los`.`obo_users_roles` ;
RENAME TABLE  `los`.`obo_los_answers` TO  `los`.`obo_lo_answers` ;
RENAME TABLE  `los`.`obo_los_instances` TO  `los`.`obo_lo_instances` ;
RENAME TABLE  `los`.`obo_los_keywords` TO  `los`.`obo_lo_keywords` ;
RENAME TABLE  `los`.`obo_los_languages` TO  `los`.`obo_lo_languages` ;
RENAME TABLE  `los`.`obo_los_media` TO  `los`.`obo_lo_media` ;
RENAME TABLE  `los`.`obo_los_pages` TO  `los`.`obo_lo_pages` ;
RENAME TABLE  `los`.`obo_los_qgroups` TO  `los`.`obo_lo_qgroups` ;
RENAME TABLE  `los`.`obo_los_questions` TO  `los`.`obo_lo_questions` ;
RENAME TABLE  `los`.`obo_locks` TO  `los`.`obo_lo_locks` ;

RENAME TABLE  `los`.`obo_tracking` TO  `los`.`obo_logs` ;
RENAME TABLE  `los`.`obo_tracking_attempts` TO  `los`.`obo_log_attempts` ;
RENAME TABLE  `los`.`obo_tracking_profile` TO  `los`.`obo_log_profile` ;
RENAME TABLE  `los`.`obo_tracking_qscores` TO  `los`.`obo_log_qscores` ;
RENAME TABLE  `los`.`obo_tracking_visits` TO  `los`.`obo_log_visits` ;

RENAME TABLE  `los`.`obo_users_auth_internal` TO  `los`.`obo_user_auth_internal` ;
RENAME TABLE  `los`.`obo_users_auth_ucf` TO  `los`.`obo_user_auth_ucf` ;
RENAME TABLE  `los`.`obo_users_roles` TO  `los`.`obo_user_roles` ;
RENAME TABLE  `los`.`obo_temp` TO  `los`.`obo_system_temp` ;
RENAME TABLE  `los`.`obo_map_qgroup_to_lo` TO  `los`.`obo_map_questions_to_qgroup` ;
ALTER TABLE  `obo_map_questions_to_qgroup` DROP  `itemType`;

CREATE TABLE  `los`.`obo_map_media_to_lo` (
`mediaID` BIGINT( 255 ) UNSIGNED NOT NULL ,
`loID` BIGINT( 255 ) UNSIGNED NOT NULL ,
INDEX (  `mediaID` ,  `loID` )
) ENGINE = MYISAM ;
ALTER TABLE  `obo_map_media_to_lo` ENGINE = INNODB

DROP TABLE  `obo_los_answers`;
