-- phpMyAdmin SQL Dump
-- version 2.11.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 29, 2009 at 05:36 PM
-- Server version: 5.0.41
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `los`
--

-- --------------------------------------------------------

--
-- Table structure for table `lo_answers`
--

CREATE TABLE `lo_answers` (
  `answerID` bigint(255) unsigned NOT NULL auto_increment,
  `userID` bigint(255) unsigned NOT NULL default '0',
  `answer` longtext NOT NULL,
  PRIMARY KEY  (`answerID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5553 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_attempts`
--

CREATE TABLE `lo_attempts` (
  `attemptID` bigint(255) unsigned NOT NULL auto_increment,
  `userID` bigint(255) unsigned NOT NULL,
  `instID` bigint(255) unsigned NOT NULL,
  `qGroupID` bigint(255) unsigned NOT NULL,
  `visitID` bigint(255) unsigned NOT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  `startTime` int(25) unsigned NOT NULL,
  `endTime` int(25) unsigned NOT NULL,
  PRIMARY KEY  (`attemptID`),
  KEY `qgroup` (`qGroupID`),
  KEY `visit_id` (`visitID`),
  KEY `uid` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=23039 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_attempts_extra`
--

CREATE TABLE `lo_attempts_extra` (
  `userID` bigint(255) unsigned NOT NULL,
  `instID` bigint(255) unsigned NOT NULL,
  `extraCount` int(3) NOT NULL,
  KEY `user_id` (`userID`,`instID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_auth_internal`
--

CREATE TABLE `lo_auth_internal` (
  `userID` bigint(255) unsigned NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `resetPassKey` varchar(255) NOT NULL,
  `resetPasswordDate` int(32) NOT NULL,
  `lastPassChange` int(32) NOT NULL,
  KEY `avatar` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_auth_modules`
--

CREATE TABLE `lo_auth_modules` (
  `modID` bigint(255) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `active` enum('0','1') NOT NULL,
  `itemOrder` int(255) NOT NULL,
  PRIMARY KEY  (`modID`),
  KEY `order` (`itemOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_auth_ucf`
--

CREATE TABLE `lo_auth_ucf` (
  `userID` bigint(255) unsigned NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` varchar(32) NOT NULL,
  PRIMARY KEY  (`userID`),
  KEY `avatar` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_computer_data`
--

CREATE TABLE `lo_computer_data` (
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
  `HTTP_USER_AGENT` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_desc_obj`
--

CREATE TABLE `lo_desc_obj` (
  `descID` bigint(255) unsigned NOT NULL auto_increment,
  `text` longtext NOT NULL,
  PRIMARY KEY  (`descID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=855 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_instances`
--

CREATE TABLE `lo_instances` (
  `instID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default 'Untitled',
  `loID` bigint(255) unsigned NOT NULL default '0',
  `userID` bigint(255) unsigned NOT NULL default '0',
  `createTime` int(20) unsigned NOT NULL,
  `courseID` varchar(50) NOT NULL,
  `startTime` int(25) unsigned NOT NULL default '0',
  `endTime` int(25) unsigned NOT NULL default '0',
  `attemptCount` tinyint(3) unsigned NOT NULL default '1',
  `scoreMethod` enum('r','m','h') NOT NULL,
  PRIMARY KEY  (`instID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=679 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_instances_deleted`
--

CREATE TABLE `lo_instances_deleted` (
  `instID` bigint(255) unsigned NOT NULL,
  `name` varchar(255) NOT NULL default 'Untitled',
  `loID` bigint(255) unsigned NOT NULL default '0',
  `userID` bigint(255) unsigned NOT NULL default '0',
  `createTime` int(25) unsigned NOT NULL,
  `courseID` varchar(50) NOT NULL,
  `startTime` int(25) unsigned NOT NULL default '0',
  `endTime` int(25) unsigned NOT NULL default '0',
  `attemptCount` tinyint(3) unsigned NOT NULL default '1',
  `scoreMethod` enum('r','m','h') NOT NULL,
  `scoreData` blob NOT NULL,
  PRIMARY KEY  (`instID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_keywords`
--

CREATE TABLE `lo_keywords` (
  `keywordID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`keywordID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=177 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_languages`
--

CREATE TABLE `lo_languages` (
  `languageID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`languageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_layouts`
--

CREATE TABLE `lo_layouts` (
  `layoutID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `thumb` bigint(255) unsigned NOT NULL default '0',
  `items` longtext NOT NULL,
  PRIMARY KEY  (`layoutID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_layout_components`
--

CREATE TABLE `lo_layout_components` (
  `componentID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(127) NOT NULL default '',
  PRIMARY KEY  (`componentID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_layout_items`
--

CREATE TABLE `lo_layout_items` (
  `layoutItemID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `component` bigint(255) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `width` int(11) NOT NULL default '0',
  `height` int(11) NOT NULL default '0',
  `data` longtext NOT NULL,
  PRIMARY KEY  (`layoutItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_layout_tags`
--

CREATE TABLE `lo_layout_tags` (
  `layoutID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`layoutID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_locks`
--

CREATE TABLE `lo_locks` (
  `lockID` bigint(255) unsigned NOT NULL auto_increment,
  `loID` bigint(255) unsigned NOT NULL default '0',
  `userID` bigint(255) unsigned NOT NULL default '0',
  `unlockTime` int(40) unsigned NOT NULL default '0',
  PRIMARY KEY  (`lockID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2421 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_los`
--

CREATE TABLE `lo_los` (
  `loID` bigint(255) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `languageID` bigint(255) NOT NULL,
  `notesID` bigint(255) unsigned NOT NULL default '0',
  `objID` bigint(255) unsigned NOT NULL default '0',
  `learnTime` int(11) NOT NULL default '0',
  `pGroupID` bigint(255) unsigned NOT NULL default '0',
  `aGroupID` bigint(255) unsigned NOT NULL default '0',
  `version` int(20) unsigned NOT NULL default '0',
  `subVersion` int(20) unsigned NOT NULL default '0',
  `rootID` bigint(255) unsigned NOT NULL default '0',
  `parentID` bigint(20) unsigned NOT NULL default '0',
  `createTime` int(25) unsigned NOT NULL default '0',
  `copyright` longtext NOT NULL,
  PRIMARY KEY  (`loID`),
  KEY `practice_group` (`pGroupID`),
  KEY `assessment_group` (`aGroupID`),
  KEY `description` (`notesID`),
  KEY `objective` (`objID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=8356 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_los_cache`
--

CREATE TABLE `lo_los_cache` (
  `loID` bigint(255) unsigned NOT NULL,
  `createTime` int(32) NOT NULL,
  `cache` longblob NOT NULL,
  PRIMARY KEY  (`loID`),
  KEY `created` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_los_deleted`
--

CREATE TABLE `lo_los_deleted` (
  `loID` bigint(255) unsigned NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `version` int(20) unsigned NOT NULL default '0',
  `rootID` bigint(255) unsigned NOT NULL default '0',
  `parentID` bigint(20) unsigned NOT NULL default '0',
  `createTime` int(25) unsigned NOT NULL default '0',
  `cache` longblob NOT NULL,
  PRIMARY KEY  (`loID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_los_pages_cache`
--

CREATE TABLE `lo_los_pages_cache` (
  `pageID` bigint(20) unsigned NOT NULL,
  `createTime` varchar(25) NOT NULL,
  `cache` blob NOT NULL,
  PRIMARY KEY  (`pageID`),
  KEY `time` (`createTime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_authors`
--

CREATE TABLE `lo_map_authors` (
  `userID` bigint(255) unsigned NOT NULL default '0',
  `loID` bigint(255) unsigned NOT NULL default '0',
  KEY `user_id` (`userID`),
  KEY `lo_id` (`loID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_feedback`
--

CREATE TABLE `lo_map_feedback` (
  `questionID` bigint(255) NOT NULL,
  `incorrect` text NOT NULL,
  `correct` text NOT NULL,
  KEY `question_id` (`questionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_items`
--

CREATE TABLE `lo_map_items` (
  `pageID` bigint(255) unsigned NOT NULL default '0',
  `itemOrder` int(10) unsigned NOT NULL default '0',
  `pageItemID` bigint(255) unsigned NOT NULL default '0',
  KEY `page_id` (`pageID`),
  KEY `item_id` (`pageItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_keywords`
--

CREATE TABLE `lo_map_keywords` (
  `keywordID` bigint(255) unsigned NOT NULL default '0',
  `itemType` enum('l','m','lt','mt','lay') NOT NULL default 'l',
  `itemID` bigint(255) unsigned NOT NULL default '0',
  KEY `item_id` (`itemID`),
  KEY `keyword_id` (`keywordID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_media`
--

CREATE TABLE `lo_map_media` (
  `pageItemID` bigint(255) unsigned NOT NULL default '0',
  `itemOrder` bigint(255) unsigned NOT NULL default '0',
  `mediaID` bigint(255) unsigned NOT NULL default '0',
  KEY `item_id` (`pageItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_pages`
--

CREATE TABLE `lo_map_pages` (
  `loID` bigint(255) unsigned NOT NULL default '0',
  `itemOrder` bigint(255) unsigned NOT NULL default '0',
  `pageID` bigint(255) unsigned NOT NULL default '0',
  KEY `lo_id` (`loID`),
  KEY `page_id` (`pageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_perms`
--

CREATE TABLE `lo_map_perms` (
  `userID` bigint(255) unsigned NOT NULL default '0',
  `itemID` bigint(255) unsigned NOT NULL default '0',
  `itemType` enum('l','q','m','i') NOT NULL default 'l',
  `read` tinyint(1) unsigned NOT NULL,
  `write` tinyint(1) unsigned NOT NULL,
  `copy` tinyint(1) unsigned NOT NULL,
  `publish` tinyint(1) unsigned NOT NULL,
  `giveRead` tinyint(1) unsigned NOT NULL,
  `giveWrite` tinyint(1) unsigned NOT NULL,
  `giveCopy` tinyint(1) unsigned NOT NULL,
  `givePublish` tinyint(1) unsigned NOT NULL,
  `giveGlobal` tinyint(1) unsigned NOT NULL,
  KEY `item_id` (`itemID`,`itemType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_qa`
--

CREATE TABLE `lo_map_qa` (
  `questionID` bigint(255) unsigned NOT NULL default '0',
  `itemOrder` bigint(255) unsigned NOT NULL default '0',
  `weight` tinyint(3) unsigned NOT NULL default '0',
  `answerID` bigint(255) unsigned NOT NULL default '0',
  `feedback` text NOT NULL,
  KEY `question_id` (`questionID`),
  KEY `answer_id` (`answerID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_qalts`
--

CREATE TABLE `lo_map_qalts` (
  `qGroupID` bigint(255) NOT NULL,
  `questionID` bigint(255) NOT NULL,
  `questionIndex` bigint(255) NOT NULL,
  KEY `question_id` (`questionID`),
  KEY `qgroup_id` (`qGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_qgroup`
--

CREATE TABLE `lo_map_qgroup` (
  `qGroupID` bigint(255) unsigned NOT NULL default '0',
  `childID` bigint(255) unsigned NOT NULL default '0',
  `itemType` enum('q','m') NOT NULL,
  `itemOrder` int(255) unsigned NOT NULL default '0',
  KEY `group_id` (`qGroupID`),
  KEY `child_id` (`childID`,`itemType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_qitems`
--

CREATE TABLE `lo_map_qitems` (
  `questionID` bigint(255) NOT NULL,
  `itemOrder` int(1) NOT NULL,
  `pageItemID` bigint(255) NOT NULL,
  KEY `question_id` (`questionID`),
  KEY `item_id` (`pageItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_qorder`
--

CREATE TABLE `lo_map_qorder` (
  `attemptID` bigint(255) unsigned NOT NULL,
  `questionID` bigint(255) unsigned NOT NULL,
  `itemOrder` tinyint(3) NOT NULL,
  KEY `attempt_id` (`attemptID`,`questionID`),
  KEY `c_order` (`itemOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_map_roles`
--

CREATE TABLE `lo_map_roles` (
  `userID` bigint(255) unsigned NOT NULL,
  `roleID` bigint(255) unsigned NOT NULL,
  UNIQUE KEY `role_id` (`roleID`,`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_media`
--

CREATE TABLE `lo_media` (
  `mediaID` bigint(255) unsigned NOT NULL auto_increment,
  `userID` bigint(255) unsigned NOT NULL default '0',
  `title` varchar(128) NOT NULL,
  `itemType` varchar(10) NOT NULL,
  `version` varchar(30) NOT NULL,
  `scorable` tinyint(1) NOT NULL default '0',
  `descText` varchar(255) NOT NULL,
  `createTime` int(25) unsigned NOT NULL default '0',
  `copyright` varchar(255) NOT NULL,
  `thumb` bigint(255) NOT NULL,
  `url` varchar(255) NOT NULL default '',
  `size` int(10) unsigned NOT NULL,
  `length` float unsigned NOT NULL,
  `height` int(4) unsigned NOT NULL default '0',
  `width` int(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`mediaID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1207 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_pages`
--

CREATE TABLE `lo_pages` (
  `pageID` bigint(255) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `userID` bigint(255) unsigned NOT NULL default '0',
  `layoutID` bigint(255) NOT NULL default '0',
  `questionID` bigint(255) unsigned default '0',
  `createTime` int(25) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=7460 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_page_items`
--

CREATE TABLE `lo_page_items` (
  `pageItemID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `layoutItemID` bigint(255) unsigned NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY  (`pageItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=10316 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_qgroups`
--

CREATE TABLE `lo_qgroups` (
  `qGroupID` bigint(255) unsigned NOT NULL auto_increment,
  `userID` bigint(255) unsigned NOT NULL default '0',
  `isMaster` int(1) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `rand` tinyint(1) NOT NULL default '0',
  `allowAlts` tinyint(1) unsigned NOT NULL default '0',
  `altMethod` enum('r','k') NOT NULL default 'r',
  PRIMARY KEY  (`qGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3418 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_qgroups_cache`
--

CREATE TABLE `lo_qgroups_cache` (
  `qgroupID` bigint(255) unsigned NOT NULL,
  `createTime` int(32) unsigned NOT NULL,
  `cache` blob NOT NULL,
  PRIMARY KEY  (`qgroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_qscores`
--

CREATE TABLE `lo_qscores` (
  `scoreID` bigint(255) unsigned NOT NULL auto_increment,
  `attemptID` bigint(255) unsigned NOT NULL,
  `qGroupID` bigint(255) unsigned NOT NULL,
  `itemType` enum('q','m') NOT NULL default 'q',
  `itemID` bigint(255) unsigned NOT NULL,
  `answerID` bigint(255) unsigned NOT NULL,
  `answer` varchar(255) NOT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`scoreID`),
  KEY `attempt_id` (`attemptID`),
  KEY `item_id` (`itemID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=271752 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_questions`
--

CREATE TABLE `lo_questions` (
  `questionID` bigint(255) unsigned NOT NULL auto_increment,
  `userID` bigint(255) unsigned NOT NULL default '0',
  `itemType` varchar(255) NOT NULL default 'QA',
  `createTime` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`questionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4753 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_roles`
--

CREATE TABLE `lo_roles` (
  `roleID` bigint(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`roleID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_temp`
--

CREATE TABLE `lo_temp` (
  `name` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_tracking`
--

CREATE TABLE `lo_tracking` (
  `userID` bigint(20) NOT NULL,
  `itemType` varchar(255) NOT NULL,
  `createTime` int(25) unsigned NOT NULL,
  `instID` bigint(20) unsigned NOT NULL,
  `data` blob NOT NULL,
  KEY `uid` (`userID`),
  KEY `type` (`itemType`),
  KEY `inst_id` (`instID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lo_users`
--

CREATE TABLE `lo_users` (
  `userID` bigint(255) unsigned NOT NULL auto_increment,
  `first` varchar(255) NOT NULL default '',
  `last` varchar(255) NOT NULL default '',
  `mi` char(1) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `createTime` int(40) unsigned NOT NULL default '0',
  `lastLogin` int(40) unsigned NOT NULL default '0',
  `sessionID` varchar(255) NOT NULL,
  `overrideAuthModRole` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4760 ;

-- --------------------------------------------------------

--
-- Table structure for table `lo_visits`
--

CREATE TABLE `lo_visits` (
  `visitID` bigint(255) unsigned NOT NULL auto_increment,
  `userID` bigint(255) unsigned NOT NULL,
  `createTime` int(25) unsigned NOT NULL,
  `ip` varchar(20) NOT NULL,
  `instID` bigint(255) unsigned NOT NULL default '0',
  PRIMARY KEY  (`visitID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=20566 ;
