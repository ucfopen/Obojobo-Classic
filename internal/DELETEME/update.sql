CREATE TABLE `obo_badges` (
  `loID` bigint(255) NOT NULL,
  `badgeID` bigint(255) NOT NULL,
  `minScore` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`loID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;