CREATE TABLE `obo_badges` (
  `loID` bigint(255) NOT NULL,
  `badgeID` bigint(255) NOT NULL,
  `minScore` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`loID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_instances` (
  `instID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'Untitled',
  `loID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `userID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `createTime` int(20) unsigned NOT NULL,
  `courseName` varchar(50) NOT NULL,
  `startTime` int(25) unsigned NOT NULL DEFAULT '0',
  `endTime` int(25) unsigned NOT NULL DEFAULT '0',
  `attemptCount` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `scoreMethod` enum('r','m','h') NOT NULL,
  `allowScoreImport` tinyint(1) NOT NULL DEFAULT '1',
  `courseID` bigint(255) unsigned DEFAULT NULL,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `originalID` bigint(255) unsigned DEFAULT '0',
  `externalLink` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`instID`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_keywords` (
  `keywordID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`keywordID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_languages` (
  `languageID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`languageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_locks` (
  `lockID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `loID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `userID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `unlockTime` int(40) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`lockID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_media` (
  `mediaID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `userID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `title` varchar(128) NOT NULL,
  `itemType` varchar(10) NOT NULL,
  `meta` blob NOT NULL,
  `descText` varchar(255) NOT NULL,
  `createTime` int(25) unsigned NOT NULL DEFAULT '0',
  `copyright` varchar(255) NOT NULL,
  `thumb` bigint(255) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL,
  `length` float unsigned NOT NULL,
  `height` int(4) unsigned NOT NULL DEFAULT '0',
  `width` int(4) unsigned NOT NULL DEFAULT '0',
  `attribution` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`mediaID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_media_copy` (
  `mediaID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `userID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `title` varchar(128) NOT NULL,
  `itemType` varchar(10) NOT NULL,
  `meta` blob NOT NULL,
  `descText` varchar(255) NOT NULL,
  `createTime` int(25) unsigned NOT NULL DEFAULT '0',
  `copyright` varchar(255) NOT NULL,
  `thumb` bigint(255) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL,
  `length` float unsigned NOT NULL,
  `height` int(4) unsigned NOT NULL DEFAULT '0',
  `width` int(4) unsigned NOT NULL DEFAULT '0',
  `attribution` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`mediaID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_pages` (
  `pageID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `pageData` blob NOT NULL,
  PRIMARY KEY (`pageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_pages_copy` (
  `pageID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `pageData` blob NOT NULL,
  PRIMARY KEY (`pageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_qgroups` (
  `qGroupID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `userID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `rand` tinyint(1) NOT NULL DEFAULT '0',
  `allowAlts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `altMethod` enum('r','k') NOT NULL DEFAULT 'r',
  PRIMARY KEY (`qGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lo_questions` (
  `questionData` blob NOT NULL,
  `questionID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`questionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_log_attempts` (
  `attemptID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `userID` bigint(255) unsigned NOT NULL,
  `instID` bigint(255) unsigned NOT NULL,
  `loID` bigint(255) unsigned NOT NULL,
  `qGroupID` bigint(255) unsigned NOT NULL,
  `visitID` bigint(255) unsigned NOT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  `unalteredScore` tinyint(3) DEFAULT NULL,
  `startTime` int(25) unsigned NOT NULL,
  `endTime` int(25) unsigned NOT NULL,
  `qOrder` text NOT NULL,
  `linkedAttemptID` bigint(255) unsigned NOT NULL,
  PRIMARY KEY (`attemptID`),
  KEY `qgroup` (`qGroupID`),
  KEY `visit_id` (`visitID`),
  KEY `uid` (`userID`),
  KEY `loID` (`loID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_log_profile` (
  `userID` bigint(255) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `time` int(25) unsigned NOT NULL,
  `appX` smallint(5) unsigned NOT NULL,
  `appY` smallint(5) unsigned NOT NULL,
  `hasAccessibility` tinyint(1) NOT NULL,
  `isDebugger` tinyint(1) NOT NULL,
  `language` tinytext NOT NULL,
  `localFileReadDisable` tinyint(1) NOT NULL,
  `manufacturer` tinytext NOT NULL,
  `os` tinytext NOT NULL,
  `playerType` tinytext NOT NULL,
  `screenResolutionX` smallint(5) unsigned NOT NULL,
  `screenResolutionY` smallint(5) unsigned NOT NULL,
  `version` tinytext NOT NULL,
  `HTTP_REFERER` text NOT NULL,
  `HTTP_USER_AGENT` text NOT NULL,
  `userTime` int(32) NOT NULL,
  `userTimeOffset` tinyint(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_log_qscores` (
  `scoreID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `attemptID` bigint(255) unsigned NOT NULL,
  `loID` bigint(255) unsigned NOT NULL,
  `instID` bigint(255) unsigned NOT NULL,
  `visitID` bigint(255) unsigned NOT NULL,
  `createTime` bigint(255) unsigned NOT NULL,
  `userID` bigint(255) unsigned NOT NULL,
  `qGroupID` bigint(255) unsigned NOT NULL,
  `itemType` enum('q','m') NOT NULL DEFAULT 'q',
  `itemID` bigint(255) unsigned NOT NULL,
  `answerID` bigint(255) unsigned NOT NULL,
  `answer` varchar(255) NOT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`scoreID`),
  UNIQUE KEY `attemptID` (`attemptID`,`itemType`,`itemID`),
  KEY `attempt_id` (`attemptID`),
  KEY `item_id` (`itemID`),
  KEY `loID` (`loID`),
  KEY `instID` (`instID`),
  KEY `createTime` (`createTime`),
  KEY `visitID` (`visitID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_log_visits` (
  `visitID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `userID` bigint(255) unsigned NOT NULL,
  `createTime` int(25) unsigned NOT NULL,
  `ip` varchar(20) NOT NULL,
  `instID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `loID` bigint(255) unsigned NOT NULL,
  `overviewTime` int(30) unsigned DEFAULT NULL,
  `contentTime` int(30) unsigned DEFAULT NULL,
  `practiceTime` int(30) unsigned DEFAULT NULL,
  `assessmentTime` int(30) unsigned DEFAULT NULL,
  PRIMARY KEY (`visitID`),
  KEY `loID` (`loID`),
  KEY `instID` (`instID`),
  KEY `userID` (`userID`),
  KEY `createTime` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_logs` (
  `trackingID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `userID` varchar(255) NOT NULL,
  `itemType` enum('PageChanged','SectionChanged','LoggedIn','LoggedOut','StartAttempt','EndAttempt','ResumeAttempt','SubmitMedia','SubmitQuestion','Visited','DeleteInstance','DeleteLO','ImportScore','CleanOrphans','SubmitLTI','none') NOT NULL DEFAULT 'none',
  `createTime` int(25) unsigned NOT NULL,
  `instID` bigint(20) unsigned NOT NULL,
  `loID` bigint(255) unsigned NOT NULL,
  `visitID` bigint(255) unsigned NOT NULL,
  `valueA` varchar(255) NOT NULL,
  `valueB` varchar(255) NOT NULL,
  `valueC` varchar(255) NOT NULL,
  PRIMARY KEY (`trackingID`),
  KEY `uid` (`userID`),
  KEY `type` (`itemType`),
  KEY `inst_id` (`instID`),
  KEY `visitID` (`visitID`),
  KEY `createTime` (`createTime`),
  KEY `loID` (`loID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_los` (
  `loID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `isMaster` enum('0','1') NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `languageID` bigint(255) NOT NULL,
  `notes` longtext NOT NULL,
  `objective` longtext NOT NULL,
  `learnTime` int(11) NOT NULL DEFAULT '0',
  `pGroupID` bigint(255) unsigned NOT NULL,
  `aGroupID` bigint(255) unsigned NOT NULL,
  `version` int(20) unsigned NOT NULL DEFAULT '0',
  `subVersion` int(20) unsigned NOT NULL DEFAULT '0',
  `rootLoID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `parentLoID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `createTime` int(25) unsigned NOT NULL DEFAULT '0',
  `copyright` longtext NOT NULL,
  `numPages` int(10) unsigned NOT NULL,
  `numPQuestions` int(10) unsigned NOT NULL,
  `numAQuestions` int(10) unsigned NOT NULL,
  `deleted` tinyint(4) NOT NULL,
  PRIMARY KEY (`loID`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_lti` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` varchar(255) NOT NULL,
  `original_item_id` varchar(255) NOT NULL,
  `resource_link` varchar(255) NOT NULL,
  `consumer` varchar(255) NOT NULL,
  `consumer_guid` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `context_id` varchar(255) DEFAULT NULL,
  `context_title` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `obo_map_authors_to_lo` (
  `userID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `loID` bigint(255) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `userID` (`userID`,`loID`),
  KEY `user_id` (`userID`),
  KEY `lo_id` (`loID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_extra_attempts_to_user` (
  `userID` bigint(255) unsigned NOT NULL,
  `instID` bigint(255) unsigned NOT NULL,
  `extraCount` int(3) NOT NULL,
  KEY `user_id` (`userID`,`instID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_keywords_to_lo` (
  `keywordID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `itemType` enum('l','m','lt','mt','lay') NOT NULL DEFAULT 'l',
  `loID` bigint(255) unsigned NOT NULL DEFAULT '0',
  KEY `item_id` (`loID`),
  KEY `keyword_id` (`keywordID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_media_to_lo` (
  `mediaID` bigint(255) unsigned NOT NULL,
  `loID` bigint(255) unsigned NOT NULL,
  KEY `mediaID` (`mediaID`,`loID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_pages_to_lo` (
  `loID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `itemOrder` bigint(255) unsigned NOT NULL DEFAULT '0',
  `pageID` bigint(255) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `loID` (`loID`,`itemOrder`,`pageID`),
  KEY `lo_id` (`loID`),
  KEY `page_id` (`pageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_perms_to_item` (
  `permID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `userID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `roleID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `itemID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `itemType` tinyint(10) NOT NULL DEFAULT '0',
  `perm` int(20) unsigned NOT NULL,
  PRIMARY KEY (`permID`),
  UNIQUE KEY `userID_2` (`userID`,`roleID`,`itemID`,`itemType`,`perm`),
  KEY `userID` (`userID`),
  KEY `roleID` (`roleID`),
  KEY `itemID` (`itemID`),
  KEY `itemType` (`itemType`),
  KEY `perm` (`perm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_perms_to_item_old` (
  `userID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `itemID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `itemType` enum('l','q','m','i') NOT NULL DEFAULT 'l',
  `read` tinyint(1) unsigned NOT NULL,
  `write` tinyint(1) unsigned NOT NULL,
  `copy` tinyint(1) unsigned NOT NULL,
  `publish` tinyint(1) unsigned NOT NULL,
  `giveRead` tinyint(1) unsigned NOT NULL,
  `giveWrite` tinyint(1) unsigned NOT NULL,
  `giveCopy` tinyint(1) unsigned NOT NULL,
  `givePublish` tinyint(1) unsigned NOT NULL,
  `giveGlobal` tinyint(1) unsigned NOT NULL,
  UNIQUE KEY `userID` (`userID`,`itemID`,`itemType`),
  KEY `item_id` (`itemID`,`itemType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_qalts_to_qgroup` (
  `qGroupID` bigint(255) NOT NULL,
  `questionID` bigint(255) NOT NULL,
  `questionIndex` bigint(255) NOT NULL,
  UNIQUE KEY `qGroupID` (`qGroupID`,`questionID`,`questionIndex`),
  KEY `question_id` (`questionID`),
  KEY `qgroup_id` (`qGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_questions_to_qgroup` (
  `qGroupID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `childID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `itemOrder` int(255) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `qGroupID` (`qGroupID`,`childID`,`itemOrder`),
  KEY `group_id` (`qGroupID`),
  KEY `child_id` (`childID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_map_roles_to_user` (
  `userID` bigint(255) unsigned NOT NULL,
  `roleID` bigint(255) unsigned NOT NULL,
  UNIQUE KEY `role_id` (`roleID`,`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_semesters` (
  `semesterID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `semester` varchar(255) NOT NULL,
  `year` int(4) NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  PRIMARY KEY (`semesterID`),
  KEY `Semester` (`semester`),
  KEY `Year` (`year`),
  KEY `StartTime` (`startTime`),
  KEY `EndTime` (`endTime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

CREATE TABLE `obo_system_temp` (
  `name` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_user_meta` (
  `userID` bigint(255) unsigned NOT NULL,
  `meta` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`userID`,`meta`),
  KEY `userID` (`userID`,`meta`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_user_roles` (
  `roleID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`roleID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `obo_users` (
  `userID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `auth_module` varchar(255) NOT NULL,
  `first` varchar(255) NOT NULL DEFAULT '',
  `last` varchar(255) NOT NULL DEFAULT '',
  `mi` char(1) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `createTime` int(40) unsigned NOT NULL DEFAULT '0',
  `lastLogin` int(40) unsigned NOT NULL DEFAULT '0',
  `sessionID` varchar(255) NOT NULL,
  `overrideAuthModRole` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`),
  KEY `login` (`login`),
  KEY `auth_module` (`auth_module`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
