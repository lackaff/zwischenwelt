-- phpMyAdmin SQL Dump
-- version 2.6.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jul 10, 2005 at 02:05 PM
-- Server version: 4.1.10
-- PHP Version: 4.3.10
-- 
-- Database: `zw`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `action`
-- 

DROP TABLE IF EXISTS `action`;
CREATE TABLE IF NOT EXISTS `action` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `building` int(10) unsigned NOT NULL default '0',
  `cmd` int(10) unsigned NOT NULL default '0',
  `param1` int(10) unsigned NOT NULL default '0',
  `param2` int(10) unsigned NOT NULL default '0',
  `starttime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `building` (`building`),
  KEY `cmd` (`cmd`)
) TYPE=MyISAM AUTO_INCREMENT=23725 ;

-- 
-- Dumping data for table `action`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `army`
-- 

DROP TABLE IF EXISTS `army`;
CREATE TABLE IF NOT EXISTS `army` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` char(64) NOT NULL default '',
  `user` int(11) NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `nextactiontime` int(10) unsigned NOT NULL default '0',
  `frags` float NOT NULL default '0',
  `lumber` int(11) NOT NULL default '0',
  `stone` int(11) NOT NULL default '0',
  `food` int(11) NOT NULL default '0',
  `metal` int(11) NOT NULL default '0',
  `runes` int(11) NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `idle` int(10) unsigned NOT NULL default '0',
  `quest` int(10) unsigned NOT NULL default '0',
  `hellhole` int(10) unsigned NOT NULL default '0',
  `follow` int(10) unsigned NOT NULL default '0',
  `counttolimit` tinyint(3) unsigned NOT NULL default '1',
  `useditem` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pos` (`x`,`y`),
  KEY `user` (`user`),
  KEY `type` (`type`),
  KEY `counttolimit` (`counttolimit`),
  KEY `useditem` (`useditem`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `army`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `armyaction`
-- 

DROP TABLE IF EXISTS `armyaction`;
CREATE TABLE IF NOT EXISTS `armyaction` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `starttime` int(10) unsigned NOT NULL default '0',
  `cmd` int(11) NOT NULL default '0',
  `param1` int(11) NOT NULL default '0',
  `param2` int(11) NOT NULL default '0',
  `orderval` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `cmd` (`cmd`),
  KEY `starttime` (`starttime`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `armyaction`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `bug`
-- 

DROP TABLE IF EXISTS `bug`;
CREATE TABLE IF NOT EXISTS `bug` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `finder` int(10) unsigned NOT NULL default '0',
  `creator` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `closed` int(10) unsigned NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  `text` text NOT NULL,
  `desc` text NOT NULL,
  `topic` tinyint(4) NOT NULL default '0',
  `assigned_user` int(10) unsigned NOT NULL default '0',
  `prio` tinyint(3) unsigned NOT NULL default '0',
  `status` tinyint(3) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `img` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `finder` (`finder`,`creator`,`created`,`topic`,`assigned_user`,`prio`),
  KEY `status` (`status`),
  KEY `closed` (`closed`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `bug`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `building`
-- 

DROP TABLE IF EXISTS `building`;
CREATE TABLE IF NOT EXISTS `building` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `type` tinyint(4) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `level` tinyint(4) unsigned NOT NULL default '0',
  `upgrades` tinyint(4) unsigned NOT NULL default '0',
  `upgradetime` int(10) unsigned NOT NULL default '0',
  `hp` float NOT NULL default '0',
  `mana` float NOT NULL default '0',
  `construction` int(10) unsigned NOT NULL default '0',
  `param` char(4) NOT NULL default '',
  `nwse` char(4) NOT NULL default '',
  `supportslots` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pos` (`x`,`y`),
  KEY `user` (`user`),
  KEY `type` (`type`),
  KEY `construction` (`construction`),
  KEY `flags` (`flags`)
) TYPE=MyISAM AUTO_INCREMENT=164073 ;

-- 
-- Dumping data for table `building`
-- 

INSERT INTO `building` VALUES (32191, 734, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32190, 735, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32188, 735, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32187, 737, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32166, 736, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32186, 736, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32106, 736, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwe', 0);
INSERT INTO `building` VALUES (32165, 737, -35, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32185, 738, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32105, 738, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32192, 734, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32020, 731, -28, 249, 7, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32182, 720, -34, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32295, 742, -24, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32345, 720, -39, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32207, 728, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32180, 725, -33, 249, 10, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32179, 727, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32104, 738, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32258, 721, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32103, 737, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32173, 724, -33, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32164, 737, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32100, 739, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32099, 739, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32030, 733, -27, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32098, 739, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32163, 738, -34, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32097, 739, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32268, 726, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32019, 730, -29, 249, 7, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32162, 720, -36, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32161, 738, -35, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32095, 739, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ws', 0);
INSERT INTO `building` VALUES (32094, 738, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32198, 727, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32160, 738, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32092, 737, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32172, 735, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32091, 736, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32159, 739, -33, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32090, 735, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32089, 734, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32088, 739, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32158, 739, -34, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32087, 738, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32157, 739, -35, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32084, 736, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32083, 735, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32269, 727, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32082, 734, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'se', 0);
INSERT INTO `building` VALUES (32081, 723, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32080, 724, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32079, 725, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32153, 737, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32101, 735, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwe', 0);
INSERT INTO `building` VALUES (32156, 739, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32155, 739, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ws', 0);
INSERT INTO `building` VALUES (32315, 742, -39, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32152, 736, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32151, 735, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'se', 0);
INSERT INTO `building` VALUES (32078, 726, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32077, 727, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32076, 728, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ws', 0);
INSERT INTO `building` VALUES (32181, 735, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32075, 727, -33, 249, 2, 0, 127, 0, 0, 1e+006, 1280, 0, '', '', 110);
INSERT INTO `building` VALUES (32337, 724, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32260, 722, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32250, 720, -23, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32167, 737, -33, 249, 16, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32265, 724, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32333, 727, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32150, 726, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32149, 725, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32148, 724, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32067, 728, -28, 249, 15, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 96);
INSERT INTO `building` VALUES (32154, 738, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32145, 720, -37, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32142, 720, -38, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32143, 724, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32026, 732, -30, 249, 7, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ws', 0);
INSERT INTO `building` VALUES (32177, 725, -34, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32140, 735, -35, 249, 12, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32139, 727, -35, 249, 11, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32063, 732, -32, 249, 9, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32137, 723, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32299, 742, -28, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32285, 740, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32136, 723, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'se', 0);
INSERT INTO `building` VALUES (32135, 723, -33, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32280, 736, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32133, 724, -34, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32062, 730, -32, 249, 9, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32297, 742, -26, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32332, 728, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32146, 724, -35, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32279, 735, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32132, 725, -35, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32277, 733, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32131, 726, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32130, 727, -37, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ws', 0);
INSERT INTO `building` VALUES (32129, 724, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32271, 729, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32328, 731, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32128, 725, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32127, 724, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32126, 724, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32125, 723, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32124, 723, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32123, 723, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32122, 723, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'se', 0);
INSERT INTO `building` VALUES (32121, 723, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32120, 726, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32119, 727, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwe', 0);
INSERT INTO `building` VALUES (32061, 731, -33, 249, 9, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32060, 732, -34, 249, 9, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32118, 727, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32117, 726, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32116, 725, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32059, 730, -34, 249, 9, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32115, 728, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32058, 731, -35, 249, 9, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32113, 726, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwe', 0);
INSERT INTO `building` VALUES (32112, 725, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwe', 0);
INSERT INTO `building` VALUES (32111, 724, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwe', 0);
INSERT INTO `building` VALUES (32110, 738, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwe', 0);
INSERT INTO `building` VALUES (32109, 737, -21, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwe', 0);
INSERT INTO `building` VALUES (32057, 732, -36, 249, 9, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32056, 730, -36, 249, 9, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32246, 720, -25, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32069, 724, -28, 249, 15, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32287, 742, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32254, 720, -19, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32270, 728, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32286, 741, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32220, 720, -29, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32055, 732, -24, 249, 13, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32251, 720, -22, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32054, 732, -22, 249, 13, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32053, 730, -22, 249, 13, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32052, 731, -23, 249, 13, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32051, 730, -24, 249, 13, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32278, 734, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32049, 732, -26, 249, 13, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32048, 730, -26, 249, 13, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32031, 733, -31, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32047, 738, -30, 249, 14, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32018, 731, -29, 249, 1, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32073, 735, -33, 249, 2, 0, 127, 0, 0, 1e+006, 1280, 0, '', '', 110);
INSERT INTO `building` VALUES (32296, 742, -25, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32046, 738, -28, 249, 14, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32169, 737, -34, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32045, 737, -29, 249, 14, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32044, 736, -30, 249, 14, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32043, 736, -28, 249, 14, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32024, 730, -28, 249, 7, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32042, 735, -29, 249, 14, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32023, 730, -30, 249, 7, 0, 127, 0, 0, 1e+006, 0, 0, '', 'se', 0);
INSERT INTO `building` VALUES (32022, 732, -29, 249, 7, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32021, 732, -28, 249, 7, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32041, 734, -30, 249, 14, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32040, 734, -28, 249, 14, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32065, 727, -29, 249, 15, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32068, 725, -29, 249, 15, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32070, 724, -30, 249, 15, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32071, 726, -30, 249, 15, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 110);
INSERT INTO `building` VALUES (32183, 736, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32025, 731, -30, 249, 7, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32184, 737, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32147, 725, -36, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32301, 742, -29, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32102, 736, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32086, 737, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32267, 725, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32028, 729, -31, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32029, 729, -27, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (32294, 742, -23, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32195, 734, -22, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32138, 723, -35, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32320, 739, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32176, 720, -35, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32178, 726, -35, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nw', 0);
INSERT INTO `building` VALUES (32168, 738, -33, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32064, 728, -30, 249, 15, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 96);
INSERT INTO `building` VALUES (32171, 736, -35, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32144, 723, -34, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32199, 725, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32050, 731, -25, 249, 13, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32255, 720, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ne', 0);
INSERT INTO `building` VALUES (32241, 720, -26, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32307, 742, -33, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32194, 734, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nse', 0);
INSERT INTO `building` VALUES (32066, 726, -28, 249, 15, 0, 127, 0, 0, 1e+006, 0, 0, '', '', 124);
INSERT INTO `building` VALUES (32263, 723, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32334, 726, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32204, 728, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32203, 726, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32202, 725, -25, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32201, 724, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32200, 726, -26, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (32197, 720, -33, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32205, 728, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32206, 728, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nws', 0);
INSERT INTO `building` VALUES (32208, 727, -24, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32209, 727, -23, 249, 6, 0, 127, 0, 0, 1e+006, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (32210, 720, -32, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32211, 720, -31, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32214, 720, -30, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32224, 720, -28, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32335, 725, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32331, 729, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32232, 720, -27, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32275, 731, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32249, 720, -24, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32252, 720, -21, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32253, 720, -20, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32340, 722, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32325, 734, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32322, 737, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32281, 737, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32274, 730, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32288, 742, -19, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32338, 723, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32312, 742, -37, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32290, 742, -20, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32324, 735, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32276, 732, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32291, 742, -21, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32282, 738, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32284, 739, -18, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32327, 733, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32310, 742, -35, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32329, 732, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32292, 742, -22, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32311, 742, -36, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32298, 742, -27, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32321, 738, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32323, 736, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32302, 742, -30, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32305, 742, -31, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32306, 742, -32, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32308, 742, -34, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32314, 742, -38, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (32343, 720, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'se', 0);
INSERT INTO `building` VALUES (32342, 721, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32317, 742, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'ws', 0);
INSERT INTO `building` VALUES (32318, 741, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32319, 740, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (32330, 730, -40, 249, 5, 0, 127, 0, 0, 1e+006, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (151502, 459, -483, 249, 3, 0, 10, 0, 0, 6, 0, 0, '', 'e', 0);
INSERT INTO `building` VALUES (151501, 461, -482, 249, 3, 0, 10, 0, 0, 6, 0, 0, '', 'n', 0);
INSERT INTO `building` VALUES (151500, 461, -483, 249, 3, 0, 10, 0, 0, 6, 0, 0, '', 'ws', 0);
INSERT INTO `building` VALUES (151499, 460, -483, 249, 3, 0, 10, 0, 0, 6, 0, 0, '', 'we', 0);
INSERT INTO `building` VALUES (151503, 460, -482, 249, 46, 0, 10, 0, 0, 115, 0, 0, '', '', 0);
INSERT INTO `building` VALUES (151504, 461, -484, 249, 47, 0, 15, 0, 0, 147, 0, 0, '', 'e', 0);
INSERT INTO `building` VALUES (151505, 461, -485, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (151506, 461, -486, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 'nwse', 0);
INSERT INTO `building` VALUES (151507, 461, -487, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 'ns', 0);
INSERT INTO `building` VALUES (151508, 461, -488, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 'wse', 0);
INSERT INTO `building` VALUES (151509, 460, -486, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 'e', 0);
INSERT INTO `building` VALUES (151510, 462, -486, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 'w', 0);
INSERT INTO `building` VALUES (151511, 460, -488, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 'e', 0);
INSERT INTO `building` VALUES (151512, 462, -488, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 'w', 0);
INSERT INTO `building` VALUES (151513, 462, -483, 249, 8, 0, 10, 0, 0, 173, 0, 0, '', '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `buildinglevel`
-- 

DROP TABLE IF EXISTS `buildinglevel`;
CREATE TABLE IF NOT EXISTS `buildinglevel` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `building` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `subtype` int(10) unsigned NOT NULL default '0',
  `level` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `buildinglevel`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `buildingname`
-- 

DROP TABLE IF EXISTS `buildingname`;
CREATE TABLE IF NOT EXISTS `buildingname` (
  `id` int(10) unsigned NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `buildingname`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `buildingparam`
-- 

DROP TABLE IF EXISTS `buildingparam`;
CREATE TABLE IF NOT EXISTS `buildingparam` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `building` int(10) unsigned NOT NULL default '0',
  `name` varchar(32) NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `building` (`building`)
) TYPE=MyISAM AUTO_INCREMENT=52131 ;

-- 
-- Dumping data for table `buildingparam`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `buildingtype`
-- 

DROP TABLE IF EXISTS `buildingtype`;
CREATE TABLE IF NOT EXISTS `buildingtype` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `descr` text NOT NULL,
  `cost_lumber` int(10) unsigned NOT NULL default '0',
  `cost_stone` int(10) unsigned NOT NULL default '0',
  `cost_food` int(10) unsigned NOT NULL default '0',
  `cost_metal` int(10) unsigned NOT NULL default '0',
  `cost_runes` int(10) unsigned NOT NULL default '0',
  `req_geb` varchar(128) NOT NULL default '',
  `req_tech` varchar(128) NOT NULL default '',
  `buildtime` int(10) unsigned NOT NULL default '0',
  `maxhp` int(11) NOT NULL default '0',
  `basemana` int(11) NOT NULL default '0',
  `script` varchar(64) NOT NULL default '',
  `color` varchar(8) NOT NULL default '',
  `letter` varchar(8) NOT NULL default '',
  `lettercolor` varchar(8) NOT NULL default '',
  `speed` int(11) NOT NULL default '0',
  `gfx` varchar(128) NOT NULL default '',
  `special` int(10) unsigned NOT NULL default '0',
  `cssclass` varchar(64) NOT NULL default '',
  `orderval` tinyint(4) NOT NULL default '0',
  `ruinbtype` int(10) unsigned NOT NULL default '0',
  `race` tinyint(3) unsigned NOT NULL default '1',
  `terrain_needed` tinyint(3) unsigned NOT NULL default '0',
  `mod_a` float NOT NULL default '1',
  `mod_v` float NOT NULL default '1',
  `mod_f` float NOT NULL default '1',
  `connectto_terrain` varchar(255) NOT NULL default '',
  `connectto_building` varchar(255) NOT NULL default '',
  `neednear_building` varchar(255) NOT NULL default '',
  `require_building` varchar(255) NOT NULL default '',
  `exclude_building` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `race` (`race`)
) TYPE=MyISAM AUTO_INCREMENT=52 ;

-- 
-- Dumping data for table `buildingtype`
-- 

INSERT INTO `buildingtype` VALUES (1, 'Haupthaus', '+12 Bev&ouml;lkerungsmaximum + 12 je Stufe<br> +10 Slots je Rohstoff Produktion + 10 je Stufe<br> +250 Lagerkapazit&auml;t je Rohstoff + 250 je Stufe<p> \r\nDas maximale Level andere Geb&auml;ude ist anfangs 3.<br> mit jedem Haupthaus-Level sind 3 weitere upgrades m&ouml;glich.<br> Wenn das Haupthaus zerst&ouml;rt oder abgerissen wird,<br> werden auch alle Geb&auml;ude, Armeen und Forschungen zerst&ouml;rt.', 800, 800, 800, 800, 0, '', '', 36, 5000, 0, 'hq', '#FFFF00', 'H', 'black', 0, 'gebaeude-r%R%/hq-%L%.png', 0, 'hq', 1, 42, 0, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (2, 'Magieturm', 'hier kann man Zauber erforschen, Runen produzieren und Zauberer ausbilden<p>\r\n\r\nProduktion:<br>\r\n* Turmzauberer<br>\r\n* Runen<p>\r\n\r\nForschung:<br>\r\n* Effiziente Runenproduktion<p>\r\n\r\nZauber:<br>\r\n* Spieler Defensiv<br>\r\n* Spieler Offensiv<br>\r\n* Area Defensiv<br>\r\n* Area Offensiv<br>\r\n* Armee Defensiv<br>\r\n* Armee Offensiv<br>\r\n* Armeezauberer', 5000, 5000, 5000, 5000, 0, '1:5', '', 69120, 480, 10, 'magic_tower', '#8888FF', 'T', 'yellow', 0, 'gebaeude-r%R%/magitower-%L%.png', 0, 'magitower', 15, 27, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (3, 'Weg', 'auch der l', 5, 5, 0, 0, 0, '1:0', '', 2880, 5, 0, 'way', '#949454', '#', 'black', 60, 'path/path-%NWSE%-%L%.png', 0, 'path_%NWSE%', 11, 0, 0, 0, 1, 1, 1, '', '', '', '1,3,18,17,24', '');
INSERT INTO `buildingtype` VALUES (4, 'BROID - Obelisk', 'Blue Ray Of Instant Death - besser nicht in die n&auml;he kommen =), kann Felder niederbrennen', 9999999, 9999999, 9999999, 9999999, 0, '', '', 69120, 999999999, 0, 'broid', 'black', 'i', 'white', 0, 'gebaeude-r%R%/broid-%L%.png', 1, 'broid', 0, 39, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (5, 'Wall', 'eine fette dicke Mauer, versperrt Armeen den Weg.', 5, 10, 0, 0, 0, '11:0,1:0', '', 5760, 120, 0, 'wall', 'gray', 'W', 'black', 0, 'wall/wall-%NWSE%-%L%.png', 0, 'wall_%NWSE%', 12, 0, 0, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (6, 'Haus', 'Ein sch', 100, 100, 0, 0, 0, '1:0', '', 23040, 100, 0, 'house', 'red', 'm', 'yellow', 0, 'gebaeude-r%R%/house-%L%.png', 0, 'house', 2, 41, 0, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (7, 'Lager', 'das Rohstofflager, je weiter Baustellen vom Lager entfernt\r\n<br>sind, desto l&auml;nger brauchen sie zum bauen\r\n<p>+250 Lagerkapazit&auml;t je Rohstoff + 250 je Stufe', 50, 50, 0, 0, 0, '1:0', '', 28800, 200, 0, 'lager', '#FFFF96', 'L', 'black', 0, 'gebaeude-r%R%/lager-%L%.png', 0, 'lager', 3, 26, 0, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (8, 'Kaserne', 'hier kann man folgende Einheiten produzieren<p>\r\n\r\nProduktion:<br>\r\n* Miliz<br>\r\n* Kämpfer<br>\r\n* SchwertKrieger<br>\r\n* LanzenTräger<br>\r\n* Berserker<br>\r\n* Ritter', 80, 50, 0, 50, 0, '12:0,1:0', '', 34560, 150, 0, 'kaserne', '#FF80FF', 't', 'white', 0, 'gebaeude-r%R%/barracks-%L%.png', 0, 'barracks', 10, 38, 1, 0, 1, 1, 1, '', '', '', '3', '');
INSERT INTO `buildingtype` VALUES (9, 'Bauernhof', 'kikerikiiiii! gack-gack-gack-gack.. grunz-grunz <p>\r\n+10 Slots f', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, 'farm', '#ffcc44', 'F', 'black', 0, 'gebaeude-r%R%/farm-%L%.png', 0, 'farm', 6, 35, 0, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (10, 'Entwickleranstalt', 'Hier sind all die flei&szlig;igen Entwickler dieses tollen Spiels, wenn Sie nicht gerade ganz flei&szlig;ig weiter daran baun :). Vielleicht sollte man sie auch einfach hier drin lassen *G*, aber machen kann man hiermit trotzdem nix.', 5000, 5000, 5000, 5000, 0, '', '', 69120, 100, 0, 'hospital', 'blue', 'h', 'yellow', 0, 'gebaeude-r%R%/hospital-%L%.png', 1, 'hospital', 16, 37, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (11, 'Werkstatt', 'lustige spielzeuge hehehe...<br>\r\nHier werden die mächtigen Rammböcke gefertigt,<br>\r\ndie Mauern und Gebäude dem Erdboden gleichmachen.<p>\r\n\r\nProduktion:<br>\r\n* Rammen<p>\r\n\r\nForschung:<br>\r\n* Architektur', 100, 50, 0, 150, 0, '1:2', '', 34560, 200, 0, 'werkstatt', '#CC8800', 'W', 'black', 0, 'gebaeude-r%R%/werkstatt-%L%.png', 0, 'werkstatt', 8, 34, 1, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (12, 'Schmiede', 'hier werden Klingen geschmiedet, Pfeilspitzen gegossen und R', 50, 100, 0, 300, 0, '1:3', '', 34560, 100, 0, 'schmiede', '#444444', 'S', 'white', 0, 'gebaeude-r%R%/schmiede-%L%.png', 0, 'schmiede', 9, 31, 1, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (13, 'Holzf&auml;ller', 'hier wird fleissig das Hackebeil geschwungen<p>\r\n+10 Slots f', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, 'farm', '#ffcc44', 'H', 'black', 0, 'gebaeude-r%R%/holzfaeller-%L%.png', 0, 'holzfaeller', 4, 40, 0, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (14, 'Steinmetz', 'Hau Druff, Hau Druff, Hau Druff, AUA MEIN DAUMEN!<p>\r\n+10 Slots f', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, 'steinmetz', '#ffcc44', 'S', 'black', 0, 'gebaeude-r%R%/steinmetz-%L%.png', 0, 'steinmetz', 5, 32, 0, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (15, 'Eisenmine', 'in dunstigen, sp', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, 'eisenmine', '#ffcc44', 'S', 'black', 0, 'gebaeude-r%R%/eisenmine-%L%.png', 0, 'eisenmine', 7, 36, 0, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (16, 'Marktplatz', 'Frische Datteln, sch', 50, 50, 0, 10, 0, '', '', 17280, 100, 0, 'marketplace', 'red', 'M', 'white', 0, 'gebaeude-r%R%/marketplace-%L%.png', 0, 'marketplace', 14, 28, 0, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (17, 'Tor', 'man selber kann immer durch seine Tore, aber die anderen\r\n<br>(zB Gildemitglieder, Fremde) nur wenn es offen ist.', 50, 60, 0, 10, 0, '11:2,1:0', '', 5760, 90, 0, 'gate', '#eeeeee', 'G', 'black', 60, 'gate/tor-zu-%NWSE%-%L%.png', 0, 'gate_%NWSE%', 13, 0, 0, 0, 1, 1, 1, '', '', '', '3,5', '');
INSERT INTO `buildingtype` VALUES (18, 'Br&uuml;cke', '...', 80, 50, 0, 10, 0, '11:2,1:0', '', 5760, 10, 0, 'way', 'red', 'B', 'green', 60, 'gate/bridge-%NWSE%-%L%.png', 0, 'bridge_%NWSE%', 11, 0, 0, 2, 1, 1, 1, '', '', '', '3', '');
INSERT INTO `buildingtype` VALUES (19, 'Schild', 'ein kleines nettes Schild, auf das man ganz viele Dinge schreiben kann', 15, 0, 0, 0, 0, '1:0', '', 2880, 10, 0, 'schild', 'brown', 'S', 'black', 60, 'gebaeude-r%R%/schild-%L%.png', 0, 'schild', 19, 0, 0, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (20, 'Tempel', 'hier kann man seine Bewohner opfern, um irgendwelchen Göttern zu huldigen.', 5, 200, 50, 0, 0, '1:5', '', 11520, 60, 0, 'tempel', 'blue', 'T', 'white', 0, 'gebaeude-r%R%/tempel-%L%.png', 0, 'tempel', 16, 33, 1, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (21, 'Höllenschlund', 'hier erscheinen böse Monster', 99999, 99999, 99999, 99999, 0, '', '', 34560, 300, 0, 'hellhole', '#FF0000', 'H', 'black', 0, 'landschaft/hellhole-%L%.gif', 1, 'hellhole', 0, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (22, 'Schachplatz', 'Lust auf eine kleines Schachspiel gegen andere Leute aus der Zwischenwelt...\r\n<br>kein Problem einfach hier Spielen', 500, 500, 50, 50, 0, '1:0', '', 11520, 50, 0, 'schachplatz', 'black', 'C', 'white', 0, 'gebaeude-r%R%/schachplatz-%L%.png', 0, 'schachplatz', 18, 29, 0, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (23, 'Portal', 'Eine wunderbare M', 5500, 5500, 2500, 7500, 5000, '11:15,1:10,2:3', '', 36000, 900, 0, 'portal', '#ccccaa', 'P', 'black', 0, 'gate/portal-zu-%L%.png', 0, 'portal', 17, 0, 0, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (24, 'Torbr&uuml;cke', 'wie die Towerbridge in London ....', 100, 80, 0, 20, 0, '11:3,1:0', '', 5725, 90, 0, 'gate_bridge', '', 'GB', '', 120, 'gate/gb-zu-%NWSE%-%L%.png', 0, 'gb_%NWSE%', 13, 0, 0, 2, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (25, 'Brunnen', 'kleiner Brunnen, der das Stadtbild verschönert', 0, 15, 0, 0, 0, '1:0', '', 2880, 10, 0, 'brunnen', '#666666', '', '', 60, 'gebaeude-r%R%/brunnen-%L%.png', 0, 'brunnen', 20, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (26, 'LagerRuine', 'das Rohstofflager, je weiter Baustellen vom Lager entfernt\r\n<br>sind, desto l&auml;nger brauchen sie zum bauen\r\n<p>+250 Lagerkapazit&auml;t je Rohstoff + 250 je Stufe', 50, 50, 0, 0, 0, '1:0', '', 28800, 200, 0, '', '#FFFF96', 'L', 'black', 180, 'gebaeude-r%R%/lager-dead.png', 1, 'lager_dead', 3, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (27, 'MagieturmRuine', 'hier kann man Zauber erforschen, Runen produzieren und Zauberer ausbilden<p>\r\n\r\nProduktion:<br>\r\n* Turmzauberer<br>\r\n* Runen<p>\r\n\r\nForschung:<br>\r\n* Effiziente Runenproduktion<p>\r\n\r\nZauber:<br>\r\n* Spieler Defensiv<br>\r\n* Spieler Offensiv<br>\r\n* Area Defensiv<br>\r\n* Area Offensiv<br>\r\n* Armee Defensiv<br>\r\n* Armee Offensiv<br>\r\n* Armeezauberer', 5000, 5000, 5000, 5000, 0, '1:5', '', 69120, 480, 10, '', '#8888FF', 'T', 'yellow', 180, 'gebaeude-r%R%/magitower-dead.png', 1, 'magitower_dead', 15, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (28, 'MartkplatzRuine', 'Frische Datteln, sch&ouml;&ouml;&ouml;ne frische Datteln...\r\n<br>hier kann man Rohstoffhandel betreiben', 50, 50, 0, 10, 0, '', '', 17280, 100, 0, '', 'red', 'M', 'white', 180, 'gebaeude-r%R%/marketplace-dead.png', 1, 'marketplace_dead', 14, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (29, 'SchachplatzRuine', 'Lust auf eine kleines Schachspiel gegen andere Leute aus der Zwischenwelt...\r\n<br>kein Problem einfach hier Spielen', 500, 500, 50, 50, 0, '1:0', '', 11520, 50, 0, '', 'black', 'C', 'white', 180, 'gebaeude-r%R%/schachplatz-dead.png', 1, 'schachplatz_dead', 18, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (30, 'SchildRuine', 'ein kleines nettes Schild, auf das man ganz viele Dinge schreiben kann', 15, 0, 0, 0, 0, '1:0', '', 240, 10, 0, '', 'brown', 'S', 'black', 180, 'gebaeude-r%R%/schild-dead.png', 1, 'schild_dead', 19, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (31, 'SchmiedeRuine', 'hier werden Klingen geschmiedet, Pfeilspitzen gegossen und R&uuml;stungen gefertigt.<p>\r\n\r\nForschung:<br>\r\n* Kettenrüstung<br>\r\n* gehärtete Klingen<br>\r\n* Plattenpanzer<br>\r\n* Lederrüstung', 50, 100, 0, 300, 0, '1:3', '', 34560, 100, 0, '', '#444444', 'S', 'white', 180, 'gebaeude-r%R%/schmiede-dead.png', 1, 'schmiede_dead', 9, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (32, 'SteinmetzRuine', 'Hau Druff, Hau Druff, Hau Druff, AUA MEIN DAUMEN!<p>\r\n+10 Slots f&uuml;r Stein Produktion + 10 je Stufe<br>\r\n+2 extra f&uuml;r jeden angrenzenden Berg<p>\r\n\r\nForschung:<br>\r\n* Hammer', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, '', '#ffcc44', 'S', 'black', 180, 'gebaeude-r%R%/steinmetz-dead.png', 1, 'steinmetz_dead', 5, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (33, 'TempelRuine', 'hier kann man seine Bewohner opfern, um irgendwelchen Göttern zu huldigen.', 5, 200, 50, 0, 0, '1:5', '', 11520, 60, 0, '', 'blue', 'T', 'white', 180, 'gebaeude-r%R%/tempel-dead.png', 1, 'tempel_dead', 16, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (34, 'WerkstattRuine', 'lustige spielzeuge hehehe...<br>\r\nHier werden die mächtigen Rammböcke gefertigt,<br>\r\ndie Mauern und Gebäude dem Erdboden gleichmachen.<p>\r\n\r\nProduktion:<br>\r\n* Rammen<p>\r\n\r\nForschung:<br>\r\n* Architektur', 100, 50, 0, 150, 0, '1:2', '', 34560, 200, 0, '', '#CC8800', 'W', 'black', 180, 'gebaeude-r%R%/werkstatt-dead.png', 1, 'werkstatt_dead', 8, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (35, 'BauernhofRuine', 'kikerikiiiii! gack-gack-gack-gack.. grunz-grunz <p>\r\n+10 Slots f&uuml;r Nahrungs-Produktion + 10 je Stufe<br>\r\n+2 extra f&uuml;r jeden angrenzenden Fluss + Bonus f&uuml;r angrenzende Getreidefelder<p>\r\n\r\nForschung:<br>\r\n* Sense', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, '', '#ffcc44', 'F', 'black', 180, 'gebaeude-r%R%/farm-dead.png', 1, 'farm_dead', 6, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (36, 'EisenminenRuine', 'in dunstigen, sp&auml;rlich beleuchteten Stollen, tief unter <br>der Erde, verrichten hier die Bergarbeiter ihr Tagwerk <br>und f&ouml;rdern das wertvolle Eisenerz zu Tage, das dann <br>haupts&auml;chlich für das Schmieden von Waffen verwendet wird.<p>\r\n+10 Slots f&uuml;r Eisen Produktion + 10 je Stufe<br>\r\n+2 extra f&uuml;r jeden angrenzenden Berg<p>\r\n\r\nForschung:<br>\r\n* Spitzhacke', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, '', '#ffcc44', 'S', 'black', 180, 'gebaeude-r%R%/eisenmine-dead.png', 1, 'eisenmine_dead', 7, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (37, 'EntwickleranstaltRuine', 'Hier sind all die flei&szlig;igen Entwickler dieses tollen Spiels, wenn Sie nicht gerade ganz flei&szlig;ig weiter daran baun :). Vielleicht sollte man sie auch einfach hier drin lassen *G*, aber machen kann man hiermit trotzdem nix.', 5000, 5000, 5000, 5000, 0, '', '', 69120, 100, 0, '', 'blue', 'h', 'yellow', 180, 'gebaeude-r%R%/hospital-dead.png', 1, 'hospital_dead', 16, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (38, 'KasernenRuine', 'hier kann man folgende Einheiten produzieren<p>\r\n\r\nProduktion:<br>\r\n* Miliz<br>\r\n* Kämpfer<br>\r\n* SchwertKrieger<br>\r\n* LanzenTräger<br>\r\n* Berserker<br>\r\n* Ritter', 80, 50, 0, 50, 0, '12:0,1:0', '', 34560, 150, 0, '', '#FF80FF', 't', 'white', 180, 'gebaeude-r%R%/barracks-dead.png', 1, 'barracks_dead', 10, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (39, 'BROID - Ruine', 'Blue Ray Of Instant Death - besser nicht in die n&auml;he kommen =), kann Felder niederbrennen', 9999999, 9999999, 9999999, 9999999, 0, '', '', 69120, 999999999, 0, '', 'black', 'i', 'white', 180, 'gebaeude-r%R%/broid-dead.png', 1, 'broid_dead', 0, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (40, 'Holzf&auml;llerRuine', 'hier wird fleissig das Hackebeil geschwungen<p>\r\n+10 Slots f&uuml;r Holz Produktion + 10 je Stufe<br>\r\n+2 extra f&uuml;r jeden angrenzenden Wald<p>\r\n\r\nForschung:<br>\r\n* Axt', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, '', '#ffcc44', 'H', 'black', 180, 'gebaeude-r%R%/holzfaeller-dead.png', 1, 'holzfaeller_dead', 4, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (41, 'HausRuine', 'Ein sch&ouml;nes Wohnhaus, damit die Bev&ouml;lkerung\r\n<br>nicht beim Haupthaus kampieren muss.\r\n<p>+10 Bev&ouml;lkerungsmaximum + 10 je Stufe', 100, 100, 0, 0, 0, '1:0', '', 23040, 100, 0, '', 'red', 'm', 'yellow', 180, 'gebaeude-r%R%/house-dead.png', 1, 'house_dead', 2, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (42, 'HaupthausRuine', '+12 Bev&ouml;lkerungsmaximum + 12 je Stufe<br> +10 Slots je Rohstoff Produktion + 10 je Stufe<br> +250 Lagerkapazit&auml;t je Rohstoff + 250 je Stufe<p> \r\nDas maximale Level andere Geb&auml;ude ist anfangs 3.<br> mit jedem Haupthaus-Level sind 3 weitere upgrades m&ouml;glich.<br> Wenn das Haupthaus zerst&ouml;rt oder abgerissen wird,<br> werden auch alle Geb&auml;ude, Armeen und Forschungen zerst&ouml;rt.', 800, 800, 800, 800, 0, '', '', 36, 5000, 0, '', '#FFFF00', 'H', 'black', 180, 'gebaeude-r%R%/hq-dead.png', 1, 'hq_dead', 1, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (43, 'Teehaus', '', 150, 50, 100, 50, 0, '', '', 11520, 50, 0, 'teahouse', 'black', 'T', 'white', 60, 'gebaeude-r%R%/teahouse-%L%.png', 0, 'teahouse', 21, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (44, 'G&auml;rtnerei', 'Im Moment noch ohne Effekt', 100, 10, 100, 10, 0, '1:15, 25:0', '49:1', 17280, 0, 0, 'garden', '', '', '', 0, 'gebaeude-r%R%/gaertnerei-%L%.png', 0, '', 14, 0, 1, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (45, 'Observatorium', '-', 500, 2000, 800, 5000, 2500, '', '', 86400, 500, 0, 'observe', 'blue', 'O', 'white', 0, 'gebaeude-r%R%/observe-0.png', 0, 'obs', 15, 0, 2, 0, 1, 1, 1, '', '', '', '', '');
INSERT INTO `buildingtype` VALUES (46, 'Schiffswerft', '*säg* *hämmer*', 1000, 500, 500, 1000, 0, '1:10,47:0', '', 43200, 100, 0, 'werft', '#5f5f5f', 'WS', '#000000', 0, 'hafen/schiffswerft-0.png', 0, 'schiffswerft', 20, 0, 1, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (47, 'Hafen', 'hier kann man seine Schiffe zu Flotten machen und ausserdem kann man hier (wenn man stege gebaut hat) auch soldaten einladen', 500, 1000, 500, 1000, 0, '1:10', '', 43200, 120, 0, 'harbor', '#0f0f0f', 'HR', '#ffffff', 0, 'hafen/hafen-%NWSE%-0.png', 0, 'harbor-%NWSE%', 21, 0, 1, 0, 1, 1, 1, '', '', '3', '', '');
INSERT INTO `buildingtype` VALUES (48, 'Steg', '', 100, 100, 0, 50, 0, '47:1', '', 3600, 30, 0, 'steg', '', '', '', 0, 'hafen/steg-%NWSE%-0.png', 0, 'steg-%NWSE%', 0, 0, 1, 6, 1, 1, 1, '', '', '', '48,47', '');
INSERT INTO `buildingtype` VALUES (49, 'Seewall', '', 0, 500, 50, 250, 0, '47:2,48:1', '', 3600, 120, 0, 'seewall', '', '', '', 0, 'hafen/mole-%NWSE%-0.png', 0, 'mole-%NWSE%', 0, 0, 1, 6, 1, 1, 1, '', '', '', '48,49,50,3', '');
INSERT INTO `buildingtype` VALUES (50, 'Seagate', '', 50, 500, 50, 500, 0, '49:1,47:2,48:1', '', 3600, 90, 0, 'seegate', '', '', '', 240, 'hafen/seagate-zu-%NWSE%-0.png', 0, 'seagate_%NWSE%', 0, 0, 1, 6, 1, 1, 1, '', '', '', '49', '');
INSERT INTO `buildingtype` VALUES (51, 'Taverne', 'hier kann man sihc mit anderen Spieler unterhalten', 50, 100, 50, 10, 0, '', '', 11520, 150, 0, 'taverne', 'white', 'T', 'black', 0, 'gebaeude-r%R%/taverne-0.png', 0, 'tav', 10, 0, 0, 0, 1, 1, 1, '', '', '', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `construction`
-- 

DROP TABLE IF EXISTS `construction`;
CREATE TABLE IF NOT EXISTS `construction` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `x` int(10) NOT NULL default '0',
  `y` int(10) NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `priority` int(10) unsigned NOT NULL default '0',
  `param` char(4) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `x` (`x`),
  KEY `y` (`y`),
  KEY `user` (`user`),
  KEY `type` (`type`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `construction`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `deadarmy`
-- 

DROP TABLE IF EXISTS `deadarmy`;
CREATE TABLE IF NOT EXISTS `deadarmy` (
  `id` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `fightlog` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `name` char(64) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `fightlog` (`fightlog`),
  KEY `flags` (`flags`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `deadarmy`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `fight`
-- 

DROP TABLE IF EXISTS `fight`;
CREATE TABLE IF NOT EXISTS `fight` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `log` int(10) unsigned NOT NULL default '0',
  `attacker` int(10) unsigned NOT NULL default '0',
  `defender` int(10) unsigned NOT NULL default '0',
  `soccery` char(128) NOT NULL default '',
  `start` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `attacker` (`attacker`,`defender`),
  KEY `log` (`log`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `fight`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `fightlog`
-- 

DROP TABLE IF EXISTS `fightlog`;
CREATE TABLE IF NOT EXISTS `fightlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fight` int(11) NOT NULL default '0',
  `a_units` varchar(128) NOT NULL default '',
  `t_units` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `figth` (`fight`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `fightlog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `fof_guild`
-- 

DROP TABLE IF EXISTS `fof_guild`;
CREATE TABLE IF NOT EXISTS `fof_guild` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `master` int(10) unsigned NOT NULL default '0',
  `other` int(10) unsigned NOT NULL default '0',
  `class` tinyint(3) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `fof_guild`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `fof_user`
-- 

DROP TABLE IF EXISTS `fof_user`;
CREATE TABLE IF NOT EXISTS `fof_user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `master` int(10) unsigned NOT NULL default '0',
  `other` int(10) unsigned NOT NULL default '0',
  `class` tinyint(3) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1103 ;

-- 
-- Dumping data for table `fof_user`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `global`
-- 

DROP TABLE IF EXISTS `global`;
CREATE TABLE IF NOT EXISTS `global` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM AUTO_INCREMENT=61 ;

-- 
-- Dumping data for table `global`
-- 

INSERT INTO `global` VALUES (1, 'lasttick', '1120948400');
INSERT INTO `global` VALUES (2, 'crontime', '0.801774670747');
INSERT INTO `global` VALUES (3, 'building_hq', '1');
INSERT INTO `global` VALUES (4, 'building_lumber', '13');
INSERT INTO `global` VALUES (5, 'building_stone', '14');
INSERT INTO `global` VALUES (6, 'building_food', '9');
INSERT INTO `global` VALUES (7, 'building_metal', '15');
INSERT INTO `global` VALUES (8, 'prod_slots', '10');
INSERT INTO `global` VALUES (9, 'prod_faktor_slotless', '0.025');
INSERT INTO `global` VALUES (10, 'prod_faktor', '0.5');
INSERT INTO `global` VALUES (11, 'store', '500');
INSERT INTO `global` VALUES (12, 'building_house', '6');
INSERT INTO `global` VALUES (13, 'building_store', '7');
INSERT INTO `global` VALUES (14, 'pop_slots_hq', '12');
INSERT INTO `global` VALUES (15, 'pop_slots_house', '10');
INSERT INTO `global` VALUES (16, 'building_gate', '17');
INSERT INTO `global` VALUES (17, 'building_runes', '2');
INSERT INTO `global` VALUES (18, 'fc_prod_runes', '0.2');
INSERT INTO `global` VALUES (19, 'fc_prod_metal', '0');
INSERT INTO `global` VALUES (20, 'fc_prod_food', '0');
INSERT INTO `global` VALUES (21, 'fc_prod_lumber', '0');
INSERT INTO `global` VALUES (22, 'fc_prod_stone', '0');
INSERT INTO `global` VALUES (23, 'mc_prod_runes', '0.1');
INSERT INTO `global` VALUES (27, 'ticks', '23816');
INSERT INTO `global` VALUES (24, 'stats_nexttime', '1120988858');
INSERT INTO `global` VALUES (25, 'tech_architecture', '34');
INSERT INTO `global` VALUES (26, 'terrain_cornfield', '8');
INSERT INTO `global` VALUES (28, 'building_bridge', '18');
INSERT INTO `global` VALUES (29, 'lc_prod_runes', '0');
INSERT INTO `global` VALUES (30, 'sc_prod_runes', '0');
INSERT INTO `global` VALUES (46, 'lastpngmapcreep', '1120924752');
INSERT INTO `global` VALUES (45, 'lastpngmap-guild', '1120900399');
INSERT INTO `global` VALUES (44, 'lastpngmap', '1120894072');
INSERT INTO `global` VALUES (34, 'minimap_left', '-800');
INSERT INTO `global` VALUES (35, 'minimap_right', '1599');
INSERT INTO `global` VALUES (36, 'minimap_top', '-635');
INSERT INTO `global` VALUES (37, 'minimap_bottom', '1393');
INSERT INTO `global` VALUES (43, 'weather', '1');
INSERT INTO `global` VALUES (41, 'lastminitick', '1120948402');
INSERT INTO `global` VALUES (47, 'testminimap', '1117051657');
INSERT INTO `global` VALUES (48, 'unitresratio', '100');
INSERT INTO `global` VALUES (49, 'kArmyRecalcBlockedRoute_Timeout', '300');
INSERT INTO `global` VALUES (50, 'kArmyAutoAttackRangeMonster_Timeout', '300');
INSERT INTO `global` VALUES (51, 'kArmy_BigArmyGoSlowLimit', '10000');
INSERT INTO `global` VALUES (52, 'kArmy_BigArmyGoSlowFactorPer1000Units', '1.003');
INSERT INTO `global` VALUES (53, 'kBaracksDestMaxDist', '30');
INSERT INTO `global` VALUES (54, 'kPortalTaxUnitNum', '1000');
INSERT INTO `global` VALUES (55, 'kArmyMinIdle_Split', '180');
INSERT INTO `global` VALUES (56, 'kArmyMinIdle_Merge', '180');
INSERT INTO `global` VALUES (57, 'kTerraFormer_SicherheitsAbstand', '25');
INSERT INTO `global` VALUES (58, 'kArmy_AW_for_one_exp', '800.0');
INSERT INTO `global` VALUES (59, 'kArmy_ExpBonus', '10');
INSERT INTO `global` VALUES (60, 'liveupdate', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `guild`
-- 

DROP TABLE IF EXISTS `guild`;
CREATE TABLE IF NOT EXISTS `guild` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `founder` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `color` varchar(32) NOT NULL default 'blue',
  `time` int(10) unsigned NOT NULL default '0',
  `lumber` int(11) NOT NULL default '0',
  `stone` int(11) NOT NULL default '0',
  `food` int(11) NOT NULL default '0',
  `metal` int(11) NOT NULL default '0',
  `runes` int(11) NOT NULL default '0',
  `max_lumber` int(10) unsigned NOT NULL default '0',
  `max_stone` int(10) unsigned NOT NULL default '0',
  `max_food` int(10) unsigned NOT NULL default '0',
  `max_metal` int(10) unsigned NOT NULL default '0',
  `max_runes` int(10) unsigned NOT NULL default '0',
  `profile` text NOT NULL,
  `gfx` varchar(128) NOT NULL default '',
  `internprofile` text NOT NULL,
  `message` text NOT NULL,
  `stdstatus` int(10) unsigned NOT NULL default '0',
  `forumurl` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=40 ;

-- 
-- Dumping data for table `guild`
-- 

INSERT INTO `guild` VALUES (8, 249, 'Weltbank', 'blue', 1108292994, 576542, 576542, 576361, 576361, 640000, 576000, 576000, 576000, 576000, 640000, 'Die Weltbank ist eine zur Unterstützung kleiner Spieler gedachte Gilde. Jeder unter 20k Punkten, der ohne Gilde ist kommt hier rein und kann sich dann bedienen, finanziert wird die Gilde über kurz oder lang durch Einnahmen aus öffentlichen Portalen, und durch das ''Admin'' account', '', 'Bedient euch an den Rohstoffen aber denkt dran auch andere wollen was.', 'Bedient euch an den Rohstoffen aber denkt dran auch andere wollen was.', 15, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `guild_forum`
-- 

DROP TABLE IF EXISTS `guild_forum`;
CREATE TABLE IF NOT EXISTS `guild_forum` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `guild` int(10) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `head` varchar(128) NOT NULL default '',
  `content` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`,`guild`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_forum`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_forum_comment`
-- 

DROP TABLE IF EXISTS `guild_forum_comment`;
CREATE TABLE IF NOT EXISTS `guild_forum_comment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `article` int(10) unsigned NOT NULL default '0',
  `ref` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `head` varchar(128) NOT NULL default '',
  `comment` text NOT NULL,
  `guild` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `article` (`article`,`ref`,`user`,`guild`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_forum_comment`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_forum_read`
-- 

DROP TABLE IF EXISTS `guild_forum_read`;
CREATE TABLE IF NOT EXISTS `guild_forum_read` (
  `user` int(11) unsigned NOT NULL default '0',
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` tinyint(4) NOT NULL default '0',
  `ref` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `user` (`user`),
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=24037 ;

-- 
-- Dumping data for table `guild_forum_read`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_msg`
-- 

DROP TABLE IF EXISTS `guild_msg`;
CREATE TABLE IF NOT EXISTS `guild_msg` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guild` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `guild` (`guild`,`user`,`time`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_msg`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_pref`
-- 

DROP TABLE IF EXISTS `guild_pref`;
CREATE TABLE IF NOT EXISTS `guild_pref` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guild` int(10) unsigned NOT NULL default '0',
  `var` varchar(128) NOT NULL default '',
  `value` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `guild` (`guild`,`var`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_pref`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_request`
-- 

DROP TABLE IF EXISTS `guild_request`;
CREATE TABLE IF NOT EXISTS `guild_request` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `guild` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `guild` (`guild`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_request`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_right`
-- 

DROP TABLE IF EXISTS `guild_right`;
CREATE TABLE IF NOT EXISTS `guild_right` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `right` int(10) unsigned NOT NULL default '0',
  `desc` varchar(128) NOT NULL default '',
  `gfx` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `right` (`right`)
) TYPE=MyISAM AUTO_INCREMENT=9 ;

-- 
-- Dumping data for table `guild_right`
-- 

INSERT INTO `guild_right` VALUES (1, 2, 'Das Mitglied kann die Rechteliste sehen', 'icon/guild-view.png');
INSERT INTO `guild_right` VALUES (2, 3, 'Das Mitglied kann Rohstoffe aus dem Gildelager nehmen', 'icon/guild-out.png');
INSERT INTO `guild_right` VALUES (3, 5, 'Das Mitglied kann Rohstoffe in die Gildelager einzahlen', 'icon/guild-in.png');
INSERT INTO `guild_right` VALUES (4, 7, 'Das Mitglied ist Gilde-Komandeur', 'icon/guild-gc.png');
INSERT INTO `guild_right` VALUES (5, 23, 'Das Mitglied ist Gildeadmin', 'icon/guild-admin.png');
INSERT INTO `guild_right` VALUES (6, 11, 'Das Mitglied darf Gildenachrichten verschicken', 'icon/guild-send.png');
INSERT INTO `guild_right` VALUES (7, 13, 'Das Mitglied darf MessageOfTheDay Texte setzen', 'icon/guild-message.png');
INSERT INTO `guild_right` VALUES (8, 27, 'Mitglied ist Schatzmeister', 'icon/guild-schatz.png');

-- --------------------------------------------------------

-- 
-- Table structure for table `guildlog`
-- 

DROP TABLE IF EXISTS `guildlog`;
CREATE TABLE IF NOT EXISTS `guildlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `user1` int(10) unsigned NOT NULL default '0',
  `user2` int(10) unsigned NOT NULL default '0',
  `guild1` int(10) unsigned NOT NULL default '0',
  `guild2` int(10) unsigned NOT NULL default '0',
  `trigger` varchar(64) NOT NULL default '',
  `what` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `time` (`time`),
  KEY `id1` (`user1`),
  KEY `id2` (`user2`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guildlog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `hellhole`
-- 

DROP TABLE IF EXISTS `hellhole`;
CREATE TABLE IF NOT EXISTS `hellhole` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '0',
  `lastupgrade` int(11) NOT NULL default '0',
  `level` tinyint(4) NOT NULL default '0',
  `armysize` int(10) unsigned NOT NULL default '0',
  `num` int(10) unsigned NOT NULL default '0',
  `spawndelay` int(10) unsigned NOT NULL default '0',
  `spawntime` int(10) unsigned NOT NULL default '0',
  `totalspawns` int(10) unsigned NOT NULL default '0',
  `radius` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=268 ;

-- 
-- Dumping data for table `hellhole`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `item`
-- 

DROP TABLE IF EXISTS `item`;
CREATE TABLE IF NOT EXISTS `item` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '0',
  `army` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `quest` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `spell` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `amount` int(10) unsigned NOT NULL default '1',
  `param` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `pos` (`x`,`y`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `item`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `itemtrade`
-- 

DROP TABLE IF EXISTS `itemtrade`;
CREATE TABLE IF NOT EXISTS `itemtrade` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `amount` int(11) NOT NULL default '0',
  `offer` tinytext NOT NULL,
  `price` tinytext NOT NULL,
  `starttime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `building` (`building`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `itemtrade`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `itemtype`
-- 

DROP TABLE IF EXISTS `itemtype`;
CREATE TABLE IF NOT EXISTS `itemtype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `gfx` varchar(128) NOT NULL default '',
  `descr` text NOT NULL,
  `flags` int(10) unsigned NOT NULL default '0',
  `weight` float NOT NULL default '0',
  `maxamount` float NOT NULL default '0',
  `gammeltype` int(10) unsigned NOT NULL default '0',
  `gammeltime` int(10) unsigned NOT NULL default '0',
  `buildings` tinytext NOT NULL,
  `cost_lumber` int(11) NOT NULL default '0',
  `cost_stone` int(11) NOT NULL default '0',
  `cost_food` int(11) NOT NULL default '0',
  `cost_metal` int(11) NOT NULL default '0',
  `cost_runes` int(11) NOT NULL default '0',
  `value` float NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=104 ;

-- 
-- Dumping data for table `itemtype`
-- 

INSERT INTO `itemtype` VALUES (2, 'Mondstein', 'item/moonstone.png', 'sobald das Mondlicht auf diesen matten länglichen Stein mit seiner seltsamen Oberfläche  fällt, erscheit das Innere auf einmal in solch einer Tiefe, daß man kaum vermag seinen Blick abzuwenden und langsam im Dunkel verschwindet...', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (3, 'Jadefalke', 'item/falke.png', 'dieses weiß schimmernde Abbild eines Falken sieht ganz und gar nicht wie eine Statue aus, ja vielmehr wie das Werk eines mächtigen Bannzaubers', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (8, 'Dorfbewohner', 'item/man.png', 'wurde entführt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (9, 'Dorfbewohnerin', 'item/woman.png', 'wurde entführt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (10, 'Kind', 'item/kid.png', 'wurde entführt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (11, 'Rubin', 'item/edelstein_rot.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (12, 'Smaragd', 'item/edelstein_gruen.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (13, 'Saphir', 'item/edelstein_blau.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (14, 'Amethyst', 'item/edelstein_lila.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (15, 'Bernstein', 'item/edelstein_gelb.png', 'mit mücke', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (19, 'Onyx', 'item/edelstein_schwarz.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (18, 'Diamant', 'item/edelstein_weiss.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (20, 'Topas', 'item/edelstein_gelb.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (21, 'Opal', 'item/edelstein_blau.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (22, 'Erdmännchen', 'item/erdm.png', '-', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (23, 'Blaurohr', 'item/pilz_blau.png', 'dieser seltene Pilz kommt nur in ganz abgelegenen bergigen Gegenden vor', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (24, 'Fliegenpilz', 'item/pilz_rot.png', 'ein kleiner rotschimmernder weißgefleckter Pilz', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (25, 'blanko Urkunde', 'item/urkunde.png', 'ein vergilbtes Stück Papier, das irgend etwas ganz ganz wichtiges bekundet', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (26, 'Retter Urkunde', 'item/urkunde.png', 'Retter von Paindorf', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (28, 'Labyrinth Urkunde', 'item/urkunde.png', 'Meister der Entwirrung', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (29, 'Cheating Urkunde', 'item/urkunde.png', 'Ist beim Cheaten erwischt worden :P ... ok musste sich gegen einen bug verteigigen .. aber trotzdem', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (30, 'blauer Portalstein', 'item/portalstein_blau.png', 'teleportiert die Armee zum nächsten öffentlichen Portal', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (31, 'grüner Portalstein', 'item/portalstein_gruen.png', 'teleportiert die Armee zum nächsten eigenen Lager', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (32, 'schwarzer Portalstein', 'item/portalstein_schwarz.png', 'teleportiert die Armee zur nächsten eigenen Kaserne', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (33, 'roter Portalstein', 'item/portalstein_rot.png', 'teleportiert die Armee zur nächsten anderen eigenen Armee', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (34, 'Blümelein in rot', 'item/blume-rot.png', 'fleischfressend ;)', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (35, 'blümelein blau', 'item/blume-blau.png', 'die ist brav', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (36, 'Pilz', 'item/pilz_orange.png', 'Pilz', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (37, 'Hypnokürbis', 'item/kuerbis.png', 'Portal in die Traumwelt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (38, 'Krone', 'item/krone.png', 'Wer sich heimlich ein König ausrufen will ...', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (39, 'Kaktus', 'item/kaktus.png', 'Vorsicht piekst', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (40, 'Schädel', 'item/schädel.png', 'Mit Smaragden als Augen', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (41, 'Blaues Ding', 'item/blauesding.png', 'Blaues Ding', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (42, 'GeburtstagsTorte', 'item/torte.gif', 'Alles Gute zum Geburtstag !!!!', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (43, 'Drachenei', 'item/drachenei.png', 'das kleine seltene Ei eines Drachens', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (44, 'Holz', 'res_lumber.gif', '', 104, 1, 0, 0, 0, '', 1, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (45, 'Stein', 'res_stone.gif', '', 104, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (46, 'Nahrung', 'res_food.gif', '', 104, 1, 0, 0, 0, '', 0, 0, 1, 0, 0, 1);
INSERT INTO `itemtype` VALUES (47, 'Metall', 'res_metal.gif', '', 104, 1, 0, 0, 0, '', 0, 0, 0, 1, 0, 1);
INSERT INTO `itemtype` VALUES (48, 'Runen', 'res_runes.gif', '', 104, 1, 0, 0, 0, '', 0, 0, 0, 0, 1, 1);
INSERT INTO `itemtype` VALUES (49, 'Zigarren', 'waren/cigar.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (50, 'Wolle', 'waren/wolle.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (51, 'Weintrauben', 'waren/weintrauben.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (52, 'Wein', 'waren/wein.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (53, 'Stoff', 'waren/stoff.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (54, 'Erz', 'waren/erz.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (55, 'Schmuck', 'waren/schmuck.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (56, 'Schafe', 'waren/schaf.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (57, 'Leder', 'waren/leder.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (58, 'Getreide', 'waren/korn.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (59, 'Kleidung', 'waren/kleidung.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (60, 'Kartoffeln', 'waren/kartoffeln.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (61, 'Hopfen', 'waren/hopfen.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (62, 'Gold', 'waren/gold.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (63, 'Fisch', 'waren/fisch.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (64, 'Fell', 'waren/fell.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (65, 'Brot', 'waren/brot.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (66, 'Baumwolle', 'waren/baumwolle.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (67, 'Fässer', 'waren/fass.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (68, 'Bier', 'waren/fass.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (69, 'Whiskey', 'waren/fass.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (70, 'Grog', 'waren/fass.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (71, 'Tee', 'waren/tea.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (72, 'Osterei', 'item/osterei1.png', 'Osterei : erzeugt Höllenhund, roter Portalstein, Flucht vor Monsterkampf', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (73, 'Osterei', 'item/osterei2.png', 'Osterei : erzeugt Wald', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (74, 'Osterei', 'item/osterei3.png', 'Osterei : erzeugt Schutt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (75, 'Osterei', 'item/osterei4.png', 'Osterei : erzeugt Felder', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (76, 'Osterei', 'item/osterei5.png', 'Osterei : erzeugt Hühnchen, grüner Portalstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (77, 'Osterei', 'item/osterei6.png', 'Osterei : teleportiert Ramme zur Armee', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (100, 'Faules Ei', 'item/osterei_faul.png', 'sieht vergammelt aus, und riecht unangenehm', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (101, 'Amboss', 'item/acme.png', 'der legendäre ACME Amboss', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (102, '7 Meilen Stiefel', 'item/stiefel.png', 'damit rennt man doppelt so schnell', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (103, 'Spam', 'item/spam.png', 'reduziert de Nahrungsverbrauch einer Armee, wenn er aktiviert ist', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `log`
-- 

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  `url` varchar(255) default NULL,
  `frame` varchar(64) default NULL,
  `type` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `map`
-- 

DROP TABLE IF EXISTS `map`;
CREATE TABLE IF NOT EXISTS `map` (
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `id` int(11) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `map`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `marketplace`
-- 

DROP TABLE IF EXISTS `marketplace`;
CREATE TABLE IF NOT EXISTS `marketplace` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `building` int(10) unsigned NOT NULL default '0',
  `offer_res` tinyint(3) unsigned NOT NULL default '0',
  `offer_count` int(10) unsigned NOT NULL default '0',
  `price_res` tinyint(3) unsigned NOT NULL default '0',
  `price_count` int(10) unsigned NOT NULL default '0',
  `starttime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `building` (`building`),
  KEY `offer_res` (`offer_res`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `marketplace`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `message`
-- 

DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `folder` int(10) unsigned NOT NULL default '0',
  `from` int(10) unsigned NOT NULL default '0',
  `to` int(10) unsigned NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `date` int(11) NOT NULL default '1',
  `status` tinyint(3) NOT NULL default '1',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `html` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `from` (`from`),
  KEY `to` (`to`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `message`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `message_folder`
-- 

DROP TABLE IF EXISTS `message_folder`;
CREATE TABLE IF NOT EXISTS `message_folder` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` tinyint(4) NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`,`parent`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `message_folder`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `newlog`
-- 

DROP TABLE IF EXISTS `newlog`;
CREATE TABLE IF NOT EXISTS `newlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '0',
  `topic` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `i1` int(11) NOT NULL default '0',
  `i2` int(11) NOT NULL default '0',
  `i3` int(11) NOT NULL default '0',
  `s1` varchar(128) NOT NULL default '',
  `s2` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `topic` (`topic`),
  KEY `user` (`user`)
) TYPE=MyISAM AUTO_INCREMENT=1300 ;

-- 
-- Dumping data for table `newlog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pending`
-- 

DROP TABLE IF EXISTS `pending`;
CREATE TABLE IF NOT EXISTS `pending` (
  `name` varchar(64) NOT NULL default '',
  `mail` varchar(128) NOT NULL default '',
  `time` int(10) unsigned NOT NULL default '0',
  `key` varchar(255) NOT NULL default '',
  `pass` varchar(255) NOT NULL default '',
  `from` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `ip` varchar(16) NOT NULL default '',
  UNIQUE KEY `name` (`name`,`mail`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `pending`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `phperror`
-- 

DROP TABLE IF EXISTS `phperror`;
CREATE TABLE IF NOT EXISTS `phperror` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `datetime` varchar(255) NOT NULL default '',
  `errornum` int(10) unsigned NOT NULL default '0',
  `errortype` varchar(255) NOT NULL default '',
  `errormsg` varchar(255) NOT NULL default '',
  `scriptname` varchar(255) NOT NULL default '',
  `scriptlinenum` int(10) unsigned NOT NULL default '0',
  `code` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `errormsg` (`errormsg`),
  KEY `scriptname` (`scriptname`),
  KEY `scriptlinenum` (`scriptlinenum`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `phperror`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pillage`
-- 

DROP TABLE IF EXISTS `pillage`;
CREATE TABLE IF NOT EXISTS `pillage` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '-1',
  `lumber` float NOT NULL default '0',
  `stone` float NOT NULL default '0',
  `food` float NOT NULL default '0',
  `metal` float NOT NULL default '0',
  `runes` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `type` (`type`),
  KEY `building` (`building`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pillage`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `profile`
-- 

DROP TABLE IF EXISTS `profile`;
CREATE TABLE IF NOT EXISTS `profile` (
  `page` varchar(64) NOT NULL default '',
  `time` float NOT NULL default '0',
  `max` float NOT NULL default '0',
  `sql` int(10) unsigned NOT NULL default '0',
  `sqlmax` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `mem` int(10) unsigned NOT NULL default '0',
  `memmax` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`page`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `profile`
-- 

INSERT INTO `profile` VALUES ('hq.php', 0.295458, 0.0831008, 136, 34, 4, 0, 0);
INSERT INTO `profile` VALUES ('guild.php', 0.028496, 0.028496, 22, 22, 1, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `quest`
-- 

DROP TABLE IF EXISTS `quest`;
CREATE TABLE IF NOT EXISTS `quest` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `running` tinyint(3) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  `dur` int(10) unsigned NOT NULL default '0',
  `repeat` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `lumber` int(10) unsigned NOT NULL default '0',
  `stone` int(10) unsigned NOT NULL default '0',
  `food` int(10) unsigned NOT NULL default '0',
  `metal` int(10) unsigned NOT NULL default '0',
  `runes` int(10) unsigned NOT NULL default '0',
  `rewarditemtype` int(10) unsigned NOT NULL default '0',
  `rewarditemamount` float NOT NULL default '1',
  `flags` int(10) unsigned NOT NULL default '0',
  `params` tinytext NOT NULL,
  `name` tinytext NOT NULL,
  `descr` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=10 ;

-- 
-- Dumping data for table `quest`
-- 

INSERT INTO `quest` VALUES (1, 1, 1120935600, 79200, 86400, 4, -312, 300, 50000, 50000, 50000, 50000, 0, 0, 1, 3, '11|12|13#-312,298|-312,302|-314,300#-309,306#1', 'BaummonsterEdelsteine', 'Der mächtige Zauberer LapisLazuli bietet demjenigen eine hohe Belohnung, der den Baummonstern (u11) bei (-312,300) ihre 3 wertvollen Edelsteine klauen kann.\r\nDie Edelsteine sollen zusammen nach\r\n(-309,306) gebracht werden.');
INSERT INTO `quest` VALUES (4, 1, 1120899600, 79200, 86400, 1, -98, 89, 8000, 8000, 0, 0, 0, 15, 1, 3, '8|9|10#-100,91|-98,91|-96,91#-98,84#1', 'Puschel', 'Rette die drei Dorfbewohner, die von den Puscheln (u20) bei (-98,89) entführt wurden, und bringe sie zurück nach Hause bei (-98,84). Dieses Quest findet jeden Tag von 10:00 bis 24:00 Uhr statt.');
INSERT INTO `quest` VALUES (6, 1, 1120878000, 86400, 86400, 1, 88, -112, 8000, 8000, 8000, 8000, 8000, 26, 1, 3, '8|9#87,-87|89,-87#88,-112#1', 'Orkburg', 'Aus dem sonst so friedlichen Ort Paindorf wurden 2 Bewohner von einer Bande Orks (u21) entführt,<br>\r\ndie Entführer haben sich in einer nahegelegenen Burgruine verschanzt.<br>\r\nDer Bürgermeister bietet eine hohe Belohnung, wenn die entführten Dorfbewohner gerettet, und nach (88,-112) gebracht werden.\r\n(sehr leicht)');
INSERT INTO `quest` VALUES (5, 0, 1121290200, 259200, 604800, 3, -310, 311, 500000, 500000, 500000, 500000, 500000, 20, 1, 3, '21|18|14|12|19#-121,310|-307,86|430,230|-238,89|-470,130#-310,311#1', 'Trollkönige&Edelsteine', 'Der mächtigen Magier LapisLazuli erbittet die Hilfe von mächtigen Helden.<br>\r\nBei Experimenten mit Mächten die ein sterblicher Verstand nicht begreiffen kann,<br>\r\nhat er durch eine gewaltige Explosion ein tiefes loch in den Boden gerissen, und herraus strömten Heerschaaren von Trollkönigen (u14).<br>\r\nEs gelingt ihm wohl nur noch für ungefähr 3 Tage sie in Schach zu halten,<br>\r\naber spätestens dann braucht er die Kraft von 5 mächtigen Edelsteinen, die überall in dieser Welt verstreut sind.<br>\r\nWo genau sie zu finden sind ist unbekannt, aber es gibt ein paar Hinweise :<br>\r\neinen Opal(i21) am Wasser [-,+], <br>\r\neinen Diamant(i18) im Gebirge [-,+],<br>\r\neinen Amethyst(i14) aus einer Wüste [++,+],<br>\r\neinen Smaragd(i12) in einem Kreis aus Blumen [-,+],<br>\r\neinen Onyx(i19) im Herzen eines Waldes [--,+]<br>\r\nDoch viele Monster lieben Edelsteine, also werden sie kaum unbewacht sein...<br>\r\nDerjenige der dem Magus rechtzeitig alle 5 Edelsteine nach (-310,311) bringt, soll reich belohnt werden !<br>\r\nVielen Dank für die Idee für dieses Quest an Saphira !\r\n');
INSERT INTO `quest` VALUES (7, 1, 1120945980, 3480, 3600, 3, -20, 140, 0, 0, 0, 2000, 0, 0, 1, 4, '36|36|36|24#-12,122|-14,127|-10,128|-13,138|-6,137|-21,143|-29,140|-30,146|-34,133|-24,151|-26,152|-17,149|-15,151|-30,159|-25,146#-34,146#0', 'Pilze', 'Der "Gasthof zur Hexe" braucht Zutaten für seine berühmte PilzSuppe.<br>\r\nDie Gesuchten Pilze (i36) und (i24) wachsen zu hauff in dem nahegelegenen Wald.<br>\r\nFür jeden einzelnen Pilz gibt es eine Belohnung, sie sind bei (-34,146) abzuliefern.<br>');
INSERT INTO `quest` VALUES (8, 1, 1120768380, 345600, 432000, 3, 144, 144, 9000, 9000, 9000, 9000, 9000, 28, 1, 15, '18#130,130#138,138#1', 'Labyrinth', 'Such dir den Weg durch das Labyrinth und bringe den Diamanten (i18) von (130,130) nach (138,138)');
INSERT INTO `quest` VALUES (9, 1, 1120946460, 3600, 3600, 3, -25, 227, 0, 0, 0, 0, 0, 0, 1, 4, '30|30|30|30|31|32|33#-29,243|-22,237|-19,238|-11,238|-9,235|-11,227|-17,224|-29,232|-33,237|-32,224|-17,218|-33,213|-32,214|-13,220|-12,223|-44,222|-43,237|-42,244|-19,244##0', 'Questname', 'In der Höhle bei (-25,227) gibt es Portalsteine, mit denen man die Armee auch ohne Portal teleportieren kann.<br>\r\n(i30) zum nächsten öffentlichen Portal<br>\r\n(i31) zum nächsten eigenen Lager<br>\r\n(i32) zur nächsten eigenen Kaserne<br>\r\n(i33) zur nächsten eignen anderen Armee<br>');

-- --------------------------------------------------------

-- 
-- Table structure for table `race`
-- 

DROP TABLE IF EXISTS `race`;
CREATE TABLE IF NOT EXISTS `race` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `desc` text NOT NULL,
  `gfx` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `race`
-- 

INSERT INTO `race` VALUES (1, 'Menschen', 'lala wir sind die lustigen kleinen Menschchen', 'race/human.png');
INSERT INTO `race` VALUES (2, 'Gnome', 'wer will fleissige Handwerker sehen, der muss zu uns Gnomen gehen...', 'race/gnome.png');

-- --------------------------------------------------------

-- 
-- Table structure for table `session`
-- 

DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sid` varchar(64) NOT NULL default '',
  `ip` varchar(16) NOT NULL default '',
  `userid` int(10) unsigned NOT NULL default '0',
  `lastuse` int(10) unsigned NOT NULL default '0',
  `agent` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=6157 ;

-- 
-- Dumping data for table `session`
-- 

INSERT INTO `session` VALUES (6155, 'logout-JDEkeGY0Lm1EMy4kNzE0N3N5YXBybEd1', '127.0.0.1', 2, 1120996035, 'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.8) Gecko/20050511 Firefox/1.0.4');
INSERT INTO `session` VALUES (6156, 'JDEkYWUzLmJuMy4kbVluNFNnRzB6TFVi', '127.0.0.1', 249, 1120997087, 'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.8) Gecko/20050511 Firefox/1.0.4');

-- --------------------------------------------------------

-- 
-- Table structure for table `siege`
-- 

DROP TABLE IF EXISTS `siege`;
CREATE TABLE IF NOT EXISTS `siege` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `building` (`building`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `siege`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `spell`
-- 

DROP TABLE IF EXISTS `spell`;
CREATE TABLE IF NOT EXISTS `spell` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `radius` int(10) unsigned NOT NULL default '0',
  `target` int(10) unsigned NOT NULL default '0',
  `targettype` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `owner` int(10) unsigned NOT NULL default '0',
  `lasts` int(10) unsigned NOT NULL default '0',
  `mod` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `target` (`target`),
  KEY `owner` (`owner`),
  KEY `x` (`x`),
  KEY `y` (`y`),
  KEY `type` (`type`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `spell`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `spelltype`
-- 

DROP TABLE IF EXISTS `spelltype`;
CREATE TABLE IF NOT EXISTS `spelltype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `target` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `baserange` int(11) NOT NULL default '0',
  `basetime` int(11) NOT NULL default '0',
  `baseeffect` float NOT NULL default '0',
  `basemod` float NOT NULL default '0',
  `desc` text NOT NULL,
  `primetech` int(10) unsigned NOT NULL default '0',
  `cost_lumber` int(10) unsigned NOT NULL default '0',
  `cost_food` int(10) unsigned NOT NULL default '0',
  `cost_metal` int(10) unsigned NOT NULL default '0',
  `cost_stone` int(10) unsigned NOT NULL default '0',
  `cost_runes` int(10) unsigned NOT NULL default '0',
  `cost_mana` int(10) unsigned NOT NULL default '0',
  `req_tech` varchar(128) NOT NULL default '',
  `req_building` varchar(128) NOT NULL default '',
  `gfx` varchar(90) NOT NULL default '',
  `orderval` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `target` (`target`)
) TYPE=MyISAM AUTO_INCREMENT=21 ;

-- 
-- Dumping data for table `spelltype`
-- 

INSERT INTO `spelltype` VALUES (1, 2, 'FruchtbaresLand', 0, 43200, 0.05, 0.025, 'Dieser Zauber sorgt fuer eine erhoehte Nahrungsproduktion', 19, 0, 80, 0, 0, 600, 1, '19:1', '', 'zauber/land.png', 3);
INSERT INTO `spelltype` VALUES (2, 2, 'Erzbaron', 0, 43200, 0.05, 0.025, 'Hoehere Erzproduktion', 19, 0, 0, 80, 0, 600, 1, '19:1', '', 'zauber/erz.png', 4);
INSERT INTO `spelltype` VALUES (3, 2, 'Zauberwald', 0, 43200, 0.05, 0.025, 'Hoehere Holzproduktion', 19, 80, 0, 0, 0, 600, 1, '19:1', '', 'zauber/wald.png', 1);
INSERT INTO `spelltype` VALUES (4, 2, 'Steinreich', 0, 43200, 0.05, 0.025, 'Hoehere Steinproduktion', 19, 0, 0, 0, 80, 600, 1, '19:1', '', 'zauber/stein.png', 2);
INSERT INTO `spelltype` VALUES (5, 3, 'Erdbeben', 1, 7200, 15, 10, 'Ein haeftiges Erdbeben, das Haeuser beschaedigt ', 31, 300, 300, 300, 300, 4000, 50, '31:1,30:8', '', 'zauber/erdbeben.png', 1);
INSERT INTO `spelltype` VALUES (6, 3, 'ArmeeDerToten', 5, 3600, 0, 0, '', 33, 15, 0, 1500, 15, 1600, 40, '33:1', '', 'zauber/skelett.png', 10);
INSERT INTO `spelltype` VALUES (7, 3, 'Strike', 0, 0, 1, 0, '-1*level HP auf ein Gebaeude', 35, 2500, 2500, 1500, 1500, 10000, 50, '35:1,30:10', '', 'zauber/strike.png', 2);
INSERT INTO `spelltype` VALUES (8, 2, 'LoveAndJoy', 0, 43200, 0, 0.1, '', 37, 100, 100, 100, 100, 800, 10, '37:1,19:2', '', 'zauber/p-pos.png', 5);
INSERT INTO `spelltype` VALUES (9, 2, 'Regen', 0, 43200, 0, 0, '', 64, 0, 500, 0, 0, 0, 20, '64>1', '', 'zauber/wasser.png', 0);
INSERT INTO `spelltype` VALUES (10, 2, 'Dürre', 0, 86400, 0.05, 0.025, '', 62, 20000, 50000, 50000, 20000, 50000, 200, '62>1,58>1', '', 'zauber/duerre.png', 0);
INSERT INTO `spelltype` VALUES (11, 2, 'Pest', 0, 86400, 0, 0.1, '', 63, 50000, 100000, 100000, 50000, 100000, 250, '63>1,58>2', '', 'zauber/pest.png', 0);
INSERT INTO `spelltype` VALUES (12, 3, 'Portalstein', 0, 0, 0, 0, '', 60, 0, 0, 5000, 5000, 5000, 20, '60>1,58>1', '', 'item/portalstein_blau.png', 0);
INSERT INTO `spelltype` VALUES (13, 2, '7-Meilen-Stiefel', 0, 3600, 0, 0, '', 61, 0, 60000, 0, 0, 60000, 150, '61>1,58>2', '', 'item/stiefel.png', 0);
INSERT INTO `spelltype` VALUES (14, 3, 'Steinschlag', 0, 0, 0, 0, '', 69, 0, 0, 0, 100, 10000, 100, '69>1,58>1', '', 'zauber/stein.png', 0);
INSERT INTO `spelltype` VALUES (15, 3, 'Komet', 0, 0, 0, 0, '', 70, 0, 0, 0, 100000, 150000, 250, '70>1,58>2', '', 'zauber/komet.png', 0);
INSERT INTO `spelltype` VALUES (16, 3, 'Schatzsuche', 0, 0, 0, 0, '', 66, 0, 0, 0, 0, 30000, 30, '66>1,58>1', '', 'units/schatztruhe.png', 0);
INSERT INTO `spelltype` VALUES (17, 3, 'Spinnennetz', 0, 1800, 0, 0, '', 68, 0, 0, 50000, 0, 100000, 100, '68>1,58>2', '', 'zauber/netz.png', 0);
INSERT INTO `spelltype` VALUES (19, 3, 'Höllenauge', 0, 0, 0, 0, '', 65, 0, 0, 60000, 0, 60000, 50, '65>1,58>1', '', 'zauber/hoellenauge.png', 0);
INSERT INTO `spelltype` VALUES (20, 3, 'Bann', 0, 0, 0, 0, '', 67, 40000, 0, 0, 0, 40000, 200, '67>1,58>2', '', 'zauber/entwickler.png', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `sqlerror`
-- 

DROP TABLE IF EXISTS `sqlerror`;
CREATE TABLE IF NOT EXISTS `sqlerror` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL default '0',
  `self` varchar(64) NOT NULL default '',
  `query` varchar(255) NOT NULL default '',
  `sqlquery` varchar(255) NOT NULL default '',
  `error` varchar(255) NOT NULL default '',
  `stacktrace` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `time` (`time`)
) TYPE=MyISAM AUTO_INCREMENT=1514 ;

-- 
-- Dumping data for table `sqlerror`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `stats`
-- 

DROP TABLE IF EXISTS `stats`;
CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `i1` int(11) NOT NULL default '0',
  `i2` int(11) NOT NULL default '0',
  `i3` int(11) NOT NULL default '0',
  `f1` float NOT NULL default '0',
  `f2` float NOT NULL default '0',
  `f3` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `type` (`type`),
  KEY `time` (`time`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `stats`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `technology`
-- 

DROP TABLE IF EXISTS `technology`;
CREATE TABLE IF NOT EXISTS `technology` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `current_level` tinyint(3) NOT NULL default '0',
  `level` tinyint(3) unsigned NOT NULL default '0',
  `upgrades` tinyint(3) unsigned NOT NULL default '0',
  `upgradetime` int(10) unsigned NOT NULL default '0',
  `upgradebuilding` int(10) unsigned NOT NULL default '0',
  `status` tinyint(3) NOT NULL default '0',
  `statuschange` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `user` (`user`),
  KEY `upgradebuilding` (`upgradebuilding`),
  KEY `status` (`status`)
) TYPE=MyISAM AUTO_INCREMENT=9189 ;

-- 
-- Dumping data for table `technology`
-- 

INSERT INTO `technology` VALUES (1344, 34, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1356, 3, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1349, 24, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1348, 23, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1347, 21, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1346, 22, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1345, 26, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1352, 25, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1359, 38, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1354, 48, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1358, 27, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1357, 2, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1355, 1, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8619, 60, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8618, 35, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8617, 31, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8616, 30, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8615, 33, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8614, 32, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8613, 63, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8612, 62, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8611, 64, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8610, 61, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8609, 37, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8608, 19, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8607, 58, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (7200, 49, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (7762, 51, 249, 0, 3, 0, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (7763, 52, 249, 0, 0, 5, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (7764, 53, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (7765, 54, 249, 0, 5, 0, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (7766, 55, 249, 0, 4, 1, 1121080091, 151503, 0, 0);
INSERT INTO `technology` VALUES (7767, 56, 249, 0, 4, 1, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (8909, 65, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8910, 66, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8911, 67, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8912, 69, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8913, 70, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8914, 68, 249, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `technologygroup`
-- 

DROP TABLE IF EXISTS `technologygroup`;
CREATE TABLE IF NOT EXISTS `technologygroup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `buildingtype` int(10) unsigned NOT NULL default '0',
  `group` int(10) unsigned NOT NULL default '0',
  `name` tinytext NOT NULL,
  `descr` text NOT NULL,
  `gfx` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=21 ;

-- 
-- Dumping data for table `technologygroup`
-- 

INSERT INTO `technologygroup` VALUES (1, 2, 0, 'Spieler Defensiv', 'zu den defensiven Zaubern z&auml;hlen unter anderem Schutz und Boost Zauber, und Zauber zur Erh&ouml;hung der Produktion<p>\r\n\r\n* Allgemeine DefensivZauber - Spieler<br>\r\n* Love & Joy', 'zauber/p-pos.png');
INSERT INTO `technologygroup` VALUES (7, 2, 0, 'Spieler Offensiv', 'Destruktive Zauber auf Spieler', 'zauber/p-neg.png');
INSERT INTO `technologygroup` VALUES (8, 2, 0, 'Area Defensiv', 'Defensiv Zauber f&uuml;r Gebiete und Gel&auml;ndefl&uuml;che, die nicht direkt Schaden zuf&uuml;gen<p>\r\n\r\n* Armee der Toten', 'zauber/g-pos.png');
INSERT INTO `technologygroup` VALUES (10, 2, 0, 'Area Offensiv', 'destruktive Zauber auf Gebieten<p>\r\n\r\n* Erdbeben<br>\r\n* Strike', 'zauber/g-neg.png');
INSERT INTO `technologygroup` VALUES (11, 2, 0, 'Armee Defensiv', '', 'zauber/a-pos.png');
INSERT INTO `technologygroup` VALUES (12, 2, 0, 'Armee Offensiv', 'destruktive zauber auf armeen', 'zauber/a-neg.png');
INSERT INTO `technologygroup` VALUES (13, 2, 0, 'Armeezauberer', 'zauberer, die zum supporten von armeen mitgenommen werden koennen, muessen auch erst erforscht werden', 'zauber/a-.png');
INSERT INTO `technologygroup` VALUES (14, 20, 0, 'Element Feuer', 'Feuer ist seit Menschengedenken ein Bestandteil unserer Kulturen, es ist lebensspendend und bietet einen Schutz gegen Kälte. Doch wie es unser  Leben auch fördert kann es auch vernichtend wirken, Anhänger des Feuers müssen sich also entscheiden, ob sie sich auf die heilenden und schutzbietenden Aspekte des Feuers berufen wollen oder ob sie seine zerstörerische Macht verehren wollen.', 'zauber/feuer.png');
INSERT INTO `technologygroup` VALUES (15, 20, 0, 'Element Wasser', 'Eine Balance zu Feuer, wie sein Gegenteil ist es lebensspendend. Wasser ist das Element mächtiger Schutzmagie und macht viele magische Dinge effektiver. Wer das Wasser verehrt kann sich auf günstige Winde bei Schiffsreisen verlassen und bei Seekämpfen kommen ihm vielleicht sogar Seeungeheuer zur Hilfe.', 'zauber/wasser.png');
INSERT INTO `technologygroup` VALUES (16, 20, 0, 'Element Luft', 'Die Macht der Veränderung. Luft unterstützt Feuer. ', 'zauber/luft.png');
INSERT INTO `technologygroup` VALUES (17, 20, 0, 'Element Erde', 'Basis menschlichen Lebens ', 'zauber/erde.png');
INSERT INTO `technologygroup` VALUES (18, 20, 0, 'Zwischenwelt-Entwickler', 'Alternativ zu den Elementen kann man auch die zw-Entwickler verehren, diese stellen sich vielleicht als launische Götter heraus, aber es kann von Zeit zu Zeit vielleicht auch nützlich sein. Wer sie nervt wird übel bestraft, wer sich nach ihrem Willen richtet mag belohnt werden oder auch nicht.  Für Anhänger der Entwickler gibt es kaum feste Features oder verbesserte Wert. Veränderungen der  std. Werte können zufällig temporär auftreten aber sie müssen nicht positiv sein.<p>\r\n\r\nHEIL ERIS\r\nHEIL DISCORDIA\r\n\r\n*g* ', 'zauber/entwickler.png');
INSERT INTO `technologygroup` VALUES (19, 1, 0, 'Ausbildung', 'Hier können der Bevölkerung verschiedene Fertigkeiten beigebracht werden, um neue Berufsgruppen entstehen zu lassen:<p>\r\n\r\n-Landschaftsgestaltung', 'upgrades/upgrade_base.png');
INSERT INTO `technologygroup` VALUES (20, 2, 0, 'Synthese', '', 'res_mana.gif');

-- --------------------------------------------------------

-- 
-- Table structure for table `technologytype`
-- 

DROP TABLE IF EXISTS `technologytype`;
CREATE TABLE IF NOT EXISTS `technologytype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `buildingtype` int(10) unsigned NOT NULL default '0',
  `buildinglevel` int(10) unsigned NOT NULL default '0',
  `group` int(10) unsigned NOT NULL default '0',
  `name` tinytext NOT NULL,
  `descr` text NOT NULL,
  `basecost_lumber` int(10) unsigned NOT NULL default '0',
  `basecost_stone` int(10) unsigned NOT NULL default '0',
  `basecost_food` int(10) unsigned NOT NULL default '0',
  `basecost_metal` int(10) unsigned NOT NULL default '0',
  `basecost_runes` int(10) unsigned NOT NULL default '0',
  `basetime` int(10) unsigned NOT NULL default '0',
  `maxlevel` int(10) unsigned NOT NULL default '10',
  `increment` float NOT NULL default '0',
  `req_tech` tinytext NOT NULL,
  `req_geb` tinytext NOT NULL,
  `gfx` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=72 ;

-- 
-- Dumping data for table `technologytype`
-- 

INSERT INTO `technologytype` VALUES (1, 12, 5, 0, 'Kettenr&uuml;stung', 'Durch das verflechten unzähliger Metallringe entsteht ein wirkungsvoller Schutz gegen Klingen und Stichwaffen. Gegen Hiebwaffen nützt dies jedoch wenig.<br>\r\nWird für SchwertKrieger, LanzenTräger und Berserker benötigt, und stärkt deren Verteidigung.', 100, 700, 500, 1500, 0, 43200, 4, 0.5, '3:3,27:5', '12:10,8:5', 'tech/kette.png');
INSERT INTO `technologytype` VALUES (3, 12, 0, 0, 'geh&auml;rtete Klingen', 'durch die Verwendung ausgefeilter Schmiedetechniken, besonderer Metalle, und einem besonders starkem Schliff  gelingt es die Qualit&auml;t der Klingen immer weiter zu steigern.<br>\r\nSteigert den Angriff von SchwertKrieger und Ritter\r\n', 400, 100, 400, 2000, 0, 14400, 10, 1, '', '', 'tech/klinge.png');
INSERT INTO `technologytype` VALUES (2, 12, 0, 0, 'Plattenpanzer', 'Bietet wirkungvollen Schutz selbst gegen Hiebwaffen, schränkt aber die Beweglichkeit ein.<br>\r\nWird für Ritter benötigt, und stärkt deren Verteidigung.', 3000, 1000, 3000, 4000, 0, 86400, 4, 1, '1>2,58<0', '', 'tech/panzer.png');
INSERT INTO `technologytype` VALUES (19, 2, 0, 1, 'Spieler Defensiv', 'Generelle Forschung f&uuml;r DefensivZauber und produktionssteigernde Zauber<p>\r\n* FruchtbaresLand<br>\r\n* Erzbaron<br>\r\n* Zauberwald<br>\r\n* Steinreich<p>\r\nErh&ouml;hen die Effizienz eurer Arbeiter (ca 10% mehr Produktion für 10 Stunden - Achtung Werte zufallsbedingt!)', 750, 750, 750, 750, 1500, 36000, 10, 0.5, '', '', 'zauber/p-pos.png');
INSERT INTO `technologytype` VALUES (30, 2, 0, 10, 'Area Offensiv', 'Generelle Forschung f&uuml;r fl&auml;chendeckende Offensivzauber', 700, 700, 700, 700, 1400, 42800, 15, 0.3, '', '', 'zauber/g-neg.png');
INSERT INTO `technologytype` VALUES (21, 14, 0, 0, 'Hammer', 'Um Steine sinnvoll aus dem Fels hauen zu k&ouml;nnen m&uuml;ssen gute H&auml;mmer her...<p>\r\n\r\n+ 1.5% Produktion pro Stufe', 1000, 2000, 500, 1500, 0, 43200, 15, 0.4, '', '14:7', 'tech/hammer.png');
INSERT INTO `technologytype` VALUES (22, 13, 0, 0, 'Axt', 'Fr&ouml;hlich W&auml;lder kleinhauen...<p>\r\n\r\n+ 1.5% Produktion pro Stufe', 1500, 0, 500, 1500, 0, 43200, 15, 0.4, '', '13:7', 'tech/axt.png');
INSERT INTO `technologytype` VALUES (31, 2, 5, 10, 'Erdbeben', 'Besch&auml;digt Geb&auml;ude im Umkreis des Zentrums mit abfallendem Schaden<br>\r\n[(15+(level-1)*10 HP)] bis zur maximalen Reichweite von [1+LEVEL]<br>\r\nDieser Spruch kann Geb&auml;ude nur bis auf eine bestimmte HP-Zahl besch&auml;digen:<br>\r\nF&uuml;r HQ: [100+5*level]<br>\r\nandere: [1+level]<p>\r\n\r\n[level] Stufe des zu besch&auml;digenden Geb&auml;udes<br>\r\n[LEVEL] Erforschte Stufe des Zaubers', 5000, 5000, 5000, 5000, 10000, 40000, 10, 0.8, '30:8', '', 'zauber/erdbeben.png');
INSERT INTO `technologytype` VALUES (23, 9, 0, 0, 'Sense', 'Hier gehts nicht um den Tod...<p>\r\n\r\n+ 1.5% Produktion pro Stufe', 1500, 500, 1000, 1500, 0, 43200, 15, 0.4, '', '9:7', 'tech/sense.png');
INSERT INTO `technologytype` VALUES (24, 15, 0, 0, 'Spitzhacke', 'Um das wertvolle Erz schneller f&ouml;rdern zu k&ouml;nnen, braucht man besseres Werkzeug.<p>\r\n\r\n+ 1.5% Produktion pro Stufe', 2000, 1500, 700, 2000, 0, 43200, 15, 0.4, '', '15:7', 'tech/spitzhacke.png');
INSERT INTO `technologytype` VALUES (25, 2, 5, 0, 'Effiziente Runenprod.', '+ 50% Produktion', 5000, 5000, 5000, 5000, 5000, 43200, 10, 0.5, '', '', 'tech/runenprod.png');
INSERT INTO `technologytype` VALUES (26, 1, 5, 0, 'Schichtarbeit', 'ab Haupthaus Stufe 5 erforschbar.\r\n+ 0.5 slots in den Produktionsstellen\r\n', 1500, 1500, 3000, 1500, 0, 43200, 10, 0.5, '', '1>5', 'tech/schicht.png');
INSERT INTO `technologytype` VALUES (27, 12, 0, 0, 'Lederrüstung', 'ein leichter Schutzpanzer, wiegt wenig, bietet aber auch wenig Schutz', 500, 500, 500, 0, 0, 36000, 5, 0.5, '', '', 'tech/leder.png');
INSERT INTO `technologytype` VALUES (32, 2, 0, 8, 'Area Defensiv', 'generelle defensiv flaechenzauberforschung', 500, 500, 500, 500, 1000, 46200, 15, 0.25, '', '', 'zauber/g-pos.png');
INSERT INTO `technologytype` VALUES (33, 2, 5, 8, 'ArmeeDerToten', 'bei K&auml;mpfen in der verfluchten Gegend kommen die gefallenen als untote Ritter wieder und k&auml;mpfen an der Seite ihrer Kameraden weiter', 2500, 2500, 2500, 2500, 5000, 36000, 10, 0.3, '32:5', '20:5,8:10,2:10', 'zauber/skelett.png');
INSERT INTO `technologytype` VALUES (34, 11, 0, 0, 'Architektur', 'beschleunigt das Bauen von Geb&auml;uden', 500, 500, 500, 500, 0, 18000, 10, 2, '', '', 'tech/architektur.png');
INSERT INTO `technologytype` VALUES (35, 2, 10, 10, 'Strike', 'Macht genau ein Punkt Schaden. Wenn die HP unter 1 fallen, wird das Geb&auml;ude zerst&ouml;rt.', 15000, 15000, 15000, 15000, 25000, 172800, 1, 0, '30:10', '', 'zauber/strike.png');
INSERT INTO `technologytype` VALUES (37, 2, 2, 1, 'LoveAndJoy', 'Erh&ouml;ht die Geburtenrate um zus&auml;tzliche<br>\r\n[(40 + MAX_POP/100)*LEVEL*0.1]<br>\r\npro stunde', 1000, 1000, 1000, 50, 500, 36000, 10, 0.25, '19:2', '2:2', 'zauber/p-pos.png');
INSERT INTO `technologytype` VALUES (38, 12, 5, 0, 'Lanze', 'Lanzentraeger brauchen ja auch was zum kaempfen', 500, 500, 500, 1000, 0, 43200, 5, 0.9, '3:3,58<1', '', 'tech/lanze.png');
INSERT INTO `technologytype` VALUES (48, 11, 10, 0, 'Verbesserte Rammen', '+3 Schaden pro Level', 7000, 7000, 3500, 10000, 0, 43200, 5, 0.4, '', '1:15, 11:15', 'tech/rammen.png');
INSERT INTO `technologytype` VALUES (49, 1, 15, 19, 'Landschaftsgestaltung', '', 50000, 25000, 50000, 25000, 10000, 86400, 15, 1, '', '1>15', 'upgrades/upgrade_base.png');
INSERT INTO `technologytype` VALUES (51, 46, 5, 0, 'Einmaster', 'edit me', 3000, 50, 3000, 3000, 50, 36000, 3, 0.7, '', '', 'units/schiff-1.png');
INSERT INTO `technologytype` VALUES (52, 46, 10, 0, 'Kampfschiffe', 'edit me', 5000, 5000, 10000, 5000, 5000, 21600, 5, 1, '', '', 'units/schiff-2.png');
INSERT INTO `technologytype` VALUES (53, 46, 15, 0, 'Rammbock', 'edit me', 5000, 0, 4000, 10000, 5000, 43200, 5, 1, '', '', 'tech/rammen.png');
INSERT INTO `technologytype` VALUES (54, 46, 0, 0, 'Segelkunst', 'edit me', 2500, 2500, 2500, 2500, 0, 21600, 5, 0.3, '', '', 'tech/segelkunst.png');
INSERT INTO `technologytype` VALUES (55, 46, 0, 0, 'Enterhaken', 'edit me', 500, 500, 1000, 5000, 0, 43200, 5, 0.7, '', '', 'upgrades/upgrade_base.png');
INSERT INTO `technologytype` VALUES (56, 46, 0, 0, 'Seekampf', 'edit me', 5000, 2500, 2500, 5000, 0, 43200, 5, 0.5, '', '', 'upgrades/upgrade_base.png');
INSERT INTO `technologytype` VALUES (58, 2, 10, 0, 'Magie-Meisterschaft', '', 200000, 200000, 200000, 200000, 200000, 432000, 2, 1, '2<0', '2:10', 'turmzauberer2.png');
INSERT INTO `technologytype` VALUES (60, 2, 10, 20, 'Portalstein', '', 100000, 100000, 100000, 100000, 100000, 86400, 1, 1, '58>1', '2>10', 'item/portalstein_blau.png');
INSERT INTO `technologytype` VALUES (61, 2, 10, 1, '7-Meilen-Stiefel', '', 200000, 200000, 200000, 200000, 200000, 86400, 1, 1, '58>2', '2>10', 'item/stiefel.png');
INSERT INTO `technologytype` VALUES (62, 2, 10, 7, 'Dürre', '', 100000, 100000, 100000, 100000, 100000, 86400, 3, 1, '58>1', '2>10', 'zauber/duerre.png');
INSERT INTO `technologytype` VALUES (63, 2, 10, 7, 'Pest', '', 300000, 300000, 300000, 300000, 300000, 172800, 3, 1, '58>2', '2>10', 'zauber/pest.png');
INSERT INTO `technologytype` VALUES (64, 2, 0, 1, 'Regen', '', 500, 500, 500, 0, 500, 259200, 1, 1, '', '', 'zauber/wasser.png');
INSERT INTO `technologytype` VALUES (65, 2, 10, 8, 'Höllenauge', '', 0, 0, 0, 60000, 60000, 86400, 3, 0, '58>1', '2>10', 'zauber/hoellenauge.png');
INSERT INTO `technologytype` VALUES (66, 2, 0, 8, 'Schatzsuche', '', 0, 0, 0, 300000, 300000, 86400, 3, 1, '58>1', '', 'units/schatztruhe.png');
INSERT INTO `technologytype` VALUES (67, 2, 0, 8, 'Bann', '', 100000, 0, 0, 0, 100000, 86400, 2, 1, '58>2', '', 'zauber/entwickler.png');
INSERT INTO `technologytype` VALUES (68, 2, 10, 20, 'Spinnennetz', '', 0, 0, 0, 100000, 100000, 86400, 1, 1, '58>2', '2>10', 'zauber/netz.png');
INSERT INTO `technologytype` VALUES (69, 2, 10, 10, 'Steinschlag', '', 0, 100000, 0, 0, 100000, 86400, 2, 1, '58>1', '2>10', 'zauber/stein.png');
INSERT INTO `technologytype` VALUES (70, 2, 10, 10, 'Komet', '', 500000, 500000, 500000, 500000, 500000, 604800, 2, 1, '58>2', '2>10', 'zauber/komet.png');
INSERT INTO `technologytype` VALUES (71, 51, 0, 0, 'Brauereikunst', 'Brauereikunst dient nur der Freude und der Punkte. Hier geht es nur darum, wer das beste Bier braut.', 100, 100, 100, 100, 100, 60, 255, 100, '', '', 'tech/bier.png');

-- --------------------------------------------------------

-- 
-- Table structure for table `terrain`
-- 

DROP TABLE IF EXISTS `terrain`;
CREATE TABLE IF NOT EXISTS `terrain` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `param` char(4) NOT NULL default '',
  `nwse` char(4) NOT NULL default '?',
  `kills` int(10) unsigned NOT NULL default '0',
  `steps` int(10) unsigned NOT NULL default '0',
  `creator` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pos` (`x`,`y`),
  KEY `type` (`type`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `terrain`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `terrainsubtype`
-- 

DROP TABLE IF EXISTS `terrainsubtype`;
CREATE TABLE IF NOT EXISTS `terrainsubtype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `terraintype` int(10) unsigned NOT NULL default '0',
  `terrainconnecttype` int(10) unsigned NOT NULL default '0',
  `gfx` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `terraintype` (`terraintype`,`terrainconnecttype`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `terrainsubtype`
-- 

INSERT INTO `terrainsubtype` VALUES (1, 6, 7, 'see/wueste-%NWSE%.png');

-- --------------------------------------------------------

-- 
-- Table structure for table `terraintype`
-- 

DROP TABLE IF EXISTS `terraintype`;
CREATE TABLE IF NOT EXISTS `terraintype` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `descr` text NOT NULL,
  `speed` int(11) NOT NULL default '0',
  `buildable` tinyint(4) NOT NULL default '0',
  `color` varchar(8) NOT NULL default '',
  `gfx` varchar(128) NOT NULL default '',
  `cssclass` varchar(64) NOT NULL default '',
  `mod_a` float NOT NULL default '1',
  `mod_v` float NOT NULL default '1',
  `mod_f` float NOT NULL default '1',
  `movable_flag` int(10) unsigned NOT NULL default '0',
  `connectto_terrain` varchar(255) NOT NULL default '',
  `connectto_building` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `movable_flag` (`movable_flag`)
) TYPE=MyISAM AUTO_INCREMENT=19 ;

-- 
-- Dumping data for table `terraintype`
-- 

INSERT INTO `terraintype` VALUES (1, 'Gras', 'eine grüne Wiese', 120, 1, '#66AA55', 'landschaft/grass.png', 'gr', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (2, 'Fluss', 'ein pl&auml;tschender Fluss', 120, 0, 'blue', 'river/river-%NWSE%.png', 'fluss_%NWSE%', 1, 1, 1, 8, '', '');
INSERT INTO `terraintype` VALUES (3, 'Berg', 'riesige un&uuml;berwindbare Berge', 0, 0, '#484848', 'mountain/berg-%NWSE%.png', 'hill_%NWSE%', 1, 1, 1, 4, '15', '');
INSERT INTO `terraintype` VALUES (4, 'Wald', 'dichter dunkler Wald', 180, 0, '#0D7F24', 'wald/wald-%NWSE%.png', 'forest_%NWSE%', 1, 1, 1, 2, '', '');
INSERT INTO `terraintype` VALUES (5, 'Loch', 'da klafft ein gro&szlig;es Loch', 180, 0, '#666666', 'landschaft/loch.png', 'loch', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (6, 'See', 'ganz viel Wasser...', 240, 0, 'blue', 'see/see-%NWSE%.png', 'see_%NWSE%', 1, 1, 1, 16, '2,6,7,16,18', '47');
INSERT INTO `terraintype` VALUES (7, 'Wueste', 'trockene, leblose Wueste', 300, 0, '#F9BC06', 'wueste/wueste-%NWSE%.png', 'wueste-%NWSE%', 1, 1, 1, 1, '6,9', '');
INSERT INTO `terraintype` VALUES (8, 'Kornfeld', 'bringt der angrenzenden Farm einen Bonus', 120, 1, 'yellow', 'landschaft/cornfield.png', 'cfield', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (9, 'Oase', 'Teich mit Palmen in der Wüste :-)', 120, 0, '#6666aa', 'wueste/oase.png', 'oase', 1, 1, 1, 1, '7', '');
INSERT INTO `terraintype` VALUES (10, 'Blumen', 'Wiese mit Blümchen', 120, 1, '#CC3366', 'landschaft/blumen.png', 'blumen', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (11, 'Geröll', 'zerbrochenes Gestein', 180, 1, '#686868', 'landschaft/geroell.png', 'sc', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (12, 'Baumstumpf', 'die Reste eines abgeholzten Waldes', 120, 1, '#66AA55', 'landschaft/baumstumpf.png', 'bs', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (13, 'junger Wald', 'kleine Bäumchen fangen hier an zu wachsen', 120, 1, '#66AA55', 'landschaft/jungwald.png', 'yw', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (14, 'Schnee', 'eine zugeschneite Wiese', 120, 1, '#66AA55', 'winter/landschaft/grass.png', 'wgr', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (15, 'schneebedeckter Berg', 'riesige schneebedeckte Berge', 0, 0, '#484848', 'mountain/snow-%NWSE%.png', 'snowhill_%NWSE%', 1, 1, 0, 4, '3', '');
INSERT INTO `terraintype` VALUES (16, 'Sumpf', '.', 300, 0, '#774900', 'swamp/swamp-%NWSE%.png', 'sw-%NWSE%', 1, 1, 1, 1, '6', '');
INSERT INTO `terraintype` VALUES (17, 'Dschungel', '.', 240, 0, 'green', 'dschungel/dschungel-%NWSE%.png', 'dsch_%NWSE%', 1, 1, 1, 1, '', '');
INSERT INTO `terraintype` VALUES (18, 'tiefe See', 'ganz viel tiefes Wasser...', 240, 0, 'blue', 'see/tief-%NWSE%.png', 'tsee_%NWSE%', 1, 1, 1, 32, '18', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `ticket`
-- 

DROP TABLE IF EXISTS `ticket`;
CREATE TABLE IF NOT EXISTS `ticket` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `assigned_user` int(10) unsigned NOT NULL default '0',
  `assigned_bug` int(10) unsigned NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `created` int(10) unsigned NOT NULL default '0',
  `topic` tinyint(3) unsigned NOT NULL default '0',
  `prio` tinyint(3) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `img` varchar(128) NOT NULL default '0',
  `eventtime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`,`assigned_user`,`created`,`topic`,`prio`),
  KEY `flags` (`flags`),
  KEY `x` (`x`,`y`),
  KEY `eventtime` (`eventtime`),
  KEY `assigned_bug` (`assigned_bug`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `ticket`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `ticket_reply`
-- 

DROP TABLE IF EXISTS `ticket_reply`;
CREATE TABLE IF NOT EXISTS `ticket_reply` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ticket` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `body` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ticket` (`ticket`,`user`,`created`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `ticket_reply`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `title`
-- 

DROP TABLE IF EXISTS `title`;
CREATE TABLE IF NOT EXISTS `title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `time` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) TYPE=MyISAM AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `title`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `triggerlog`
-- 

DROP TABLE IF EXISTS `triggerlog`;
CREATE TABLE IF NOT EXISTS `triggerlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `id1` int(10) unsigned NOT NULL default '0',
  `id2` int(10) unsigned NOT NULL default '0',
  `trigger` varchar(64) NOT NULL default '',
  `what` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `time` (`time`),
  KEY `id1` (`id1`),
  KEY `id2` (`id2`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `triggerlog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `unit`
-- 

DROP TABLE IF EXISTS `unit`;
CREATE TABLE IF NOT EXISTS `unit` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `transport` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `amount` float NOT NULL default '0',
  `spell` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `building` (`building`),
  KEY `type` (`type`),
  KEY `amount` (`amount`),
  KEY `transport` (`transport`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `unit`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `unittype`
-- 

DROP TABLE IF EXISTS `unittype`;
CREATE TABLE IF NOT EXISTS `unittype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `descr` text NOT NULL,
  `orderval` int(10) unsigned NOT NULL default '0',
  `a` int(10) unsigned NOT NULL default '0',
  `v` int(10) unsigned NOT NULL default '0',
  `f` int(10) unsigned NOT NULL default '0',
  `r` int(10) unsigned NOT NULL default '0',
  `cooldown` int(10) unsigned NOT NULL default '0',
  `speed` float NOT NULL default '1',
  `pillage` int(10) unsigned NOT NULL default '0',
  `can` int(10) unsigned NOT NULL default '0',
  `weight` float NOT NULL default '0',
  `cost_lumber` int(10) unsigned NOT NULL default '0',
  `cost_stone` int(10) unsigned NOT NULL default '0',
  `cost_food` int(10) unsigned NOT NULL default '0',
  `cost_metal` int(10) unsigned NOT NULL default '0',
  `cost_runes` int(10) unsigned NOT NULL default '0',
  `last` int(10) unsigned NOT NULL default '0',
  `buildtime` int(10) unsigned NOT NULL default '0',
  `gfx` varchar(64) NOT NULL default '',
  `buildingtype` int(10) unsigned NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '0',
  `req_tech_a` varchar(128) NOT NULL default '',
  `req_tech_v` varchar(128) NOT NULL default '',
  `req_geb` varchar(128) NOT NULL default '',
  `movable_flag` int(10) unsigned NOT NULL default '0',
  `eff_sail` float unsigned NOT NULL default '0',
  `eff_fightondeck` float unsigned NOT NULL default '0',
  `eff_capture` float unsigned NOT NULL default '0',
  `eff_siege` float NOT NULL default '0',
  `elite` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `f` (`f`,`r`),
  KEY `cooldown` (`cooldown`),
  KEY `f_2` (`f`,`r`),
  KEY `can` (`can`),
  KEY `movable_flag` (`movable_flag`),
  KEY `elite` (`elite`)
) TYPE=MyISAM AUTO_INCREMENT=46 ;

-- 
-- Dumping data for table `unittype`
-- 

INSERT INTO `unittype` VALUES (1, 'Miliz', 'Mit Spitzhacken und Heugabeln kann man zwar kämpfen, aber wohl kaum einen Krieg gewinnen.', 2, 5, 10, 0, 0, 0, 301, 10, 0, 0.2, 20, 0, 5, 5, 0, 60, 60, 'units/unit_1.png', 8, 0, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (2, 'K&auml;mpfer', 'mit einfachen Schwertern bewaffnet,\r\naber ohne Rüstung, zwar beweglich aber auch nicht besonders widerstandsfähig.\r\n\r\nbenötigt<br>\r\n<br>\r\ngehärtete Klingen: 1<br>\r\nLederrüstung: 1<br>\r\nKettenrüstung: 0<br>\r\nPlattenpanzer: 0<br>', 3, 15, 5, 0, 0, 0, 121, 10, 0, 0.5, 5, 0, 5, 20, 0, 40, 60, 'units/unit_2.png', 8, 0, '3:1', '27:1', '8:1', 3, 0, 0, 0, 0, 26);
INSERT INTO `unittype` VALUES (21, 'Ork', 'stinkenden grüne Fieslinge', 17, 40, 20, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 5, 0, 'units/ork.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (22, 'Huhn', 'Gaaaack ! gackgackgack', 18, 10, 10, 0, 0, 0, 1, 0, 0, 0, 0, 0, 100, 0, 0, 80, 60, 'units/huhn.png', 9, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (3, 'SchwertKrieger', 'Mit einem gehärteten Schwert und einer starken Rüstung bereit sich in den Kampf zu stürzen.\r\n\r\nbenötigt<br>\r\n<br>\r\ngehärtete Klingen: 3<br>\r\nLederrüstung: 2<br>\r\nKettenrüstung: 0<br>\r\nPlattenpanzer: 0<br>', 5, 20, 20, 0, 0, 0, 181, 10, 0, 0.6, 5, 0, 5, 40, 0, 40, 60, 'units/unit_3.png', 8, 0, '3:3', '27:2', '8:2', 3, 0, 0, 0, 0, 25);
INSERT INTO `unittype` VALUES (4, 'LanzenTr&auml;ger', 'Nicht so schlagkräftig wie die SchwertKrieger,\r\nkann aber dafür seine Gegner mit der Lanze auf Abstand halten.\r\n\r\nbenötigt<br>\r\n<br>\r\nLanze: 1<br>\r\nLederrüstung: 0<br>\r\nKettenrüstung: 1<br>\r\nPlattenpanzer: 0<br>', 7, 15, 75, 0, 0, 0, 241, 10, 0, 1.1, 30, 0, 5, 30, 0, 50, 60, 'units/unit_4.png', 8, 0, '38:1', '1:1,58<1', '8:5', 3, 0, 0, 0, 0, 27);
INSERT INTO `unittype` VALUES (5, 'Berserker', 'Mit zwei wuchtigen Streitäxten bewaffnet und von starken Panzerung geschützt\r\nmetzeln diese furchteinflössenden Barbaren alles weg was sich bewegt =)\r\n\r\nbenötigt<br>\r\n<br>\r\ngehärtete Klingen: 6<br>\r\nLederrüstung: 0<br>\r\nKettenrüstung: 2<br>\r\nPlattenpanzer: 0<br>', 9, 80, 30, 0, 0, 0, 121, 10, 0, 1.3, 20, 0, 10, 60, 0, 80, 60, 'units/unit_5.png', 8, 0, '3:6', '1:2,58<1', '8:8', 3, 0, 0, 0, 0, 28);
INSERT INTO `unittype` VALUES (6, 'Ritter', 'Berittene Krieger sind nicht so leicht zu Fall zu bringen, und metzeln ordentlich was weg\r\n\r\nbenötigt<br>\r\n<br>\r\ngehärtete Klingen: 10<br>\r\nLederrüstung: 0<br>\r\nKettenrüstung: 0<br>\r\nPlattenpanzer: 1<br>', 11, 60, 100, 0, 0, 0, 60, 10, 0, 1.9, 20, 0, 10, 100, 0, 120, 100, 'units/unit_6.png', 8, 0, '3:10', '2>1,58<0', '8:10', 3, 0, 0, 0, 0, 29);
INSERT INTO `unittype` VALUES (10, 'Ramme', 'Mit diesem mächtigen Rammbock kann man Mauern und Gebäude dem Erdboden gleichmachen.', 15, 15, 0, 0, 0, 0, 1, 0, 0, 0, 2000, 0, 0, 1000, 0, 5, 36000, 'units/ramme.png', 11, 0, '48:0', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (11, 'Baummonster', 'Ein Dämon in Gestalt eines Baumes verbreitet Furcht und Schrecken.', 8, 200, 400, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 160, 3600, 'units/baum.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (12, 'EisenGolem', 'Ein Mechanisches Monster, geschaffen um Verwüstung und Tod in die Welt zu tragen.', 8, 400, 400, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 160, 3600, 'units/golem.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (13, 'SteinGolem', 'Ein steinernes sehr robustes Monster, geschaffen um Verwüstung und Tod in die Welt zu tragen.', 8, 500, 600, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 180, 3600, 'units/steingolem.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (14, 'Trollkönig', 'der grausame, stinkende und menschenfressende Herrscher über alles Übel in der Zwischenwelt.', 10, 1000, 1000, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 400, 0, 'units/trollking.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (15, 'Schatzkiste', 'Juwelen, Diamanten, Goldmünzen und arghhh vieles vieles mehr *g*.', 11, 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 560, 0, 'units/schatztruhe.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (16, 'Turmzauberer', 'ein Turmzauberer ist ein alter weisshaariger Zauberer das Geschlecht ist in grauer Vorzeit vergessen denn er/sie/es beschäftigt sich seit langer Zeit nur mit kraftvoller Magie, er/sie/es kann aus seinem Turm heraus wirken und das Turmmana verbrauchen', 12, 0, 0, 0, 0, 0, 0, 0, 0, 0, 500, 500, 1000, 700, 2500, 5, 43200, 'units/turmzauberer.png', 2, 0, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (17, 'GhostKnight', 'Durch Armee der Toten auferstandene Krieger', 1, 50, 30, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 5, 0, 'units/ghostknight.png', 0, 0, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (18, 'Bug', 'Ups, da ist wohl irgendwo ein Bug aufgetreten. Wird zeit, daß den jemand plättet.', 14, 10, 10, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 80, 0, 'units/bug.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (19, 'Blob', 'der böse BLoB owned euch alle voll KraSS WÄg', 15, 300, 300, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 120, 0, 'units/blob.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (20, 'Puschel', 'wer kennt die süßen kleinen Tierchen auf SOM nicht *G*', 16, 5, 5, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 'units/pogo.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (23, 'Schlange', 'Giftiges Kriechtier', 19, 100, 20, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 160, 0, 'units/wurm.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (24, 'Squid', 'Schleimiges Seemonster', 20, 100, 50, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 666, 320, 60, 'units/squid.png', 25, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (25, 'Schwertmeister', 'Durch langes Training können Schwertkrieger ihr Talent und ihre Technik im Umgang mit dem Schwert bis zur vervollkommnung steigern', 6, 40, 40, 0, 0, 0, 181, 10, 0, 0.2, 10, 0, 10, 80, 0, 50, 0, 'units/unit_3-elite.png', 8, 2, '3:3', '27:2', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (26, 'Elitekämpfer', '', 4, 25, 15, 0, 0, 0, 121, 10, 0, 0.5, 10, 0, 10, 40, 0, 60, 0, 'units/unit_2-elite.png', 8, 2, '3:1', '27:1', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (27, 'Lanzenträgerveteran', '', 8, 25, 115, 0, 0, 0, 241, 10, 0, 0.8, 60, 0, 10, 60, 0, 70, 0, 'units/unit_4-elite.png', 8, 2, '38:1', '1:1', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (28, 'Berserkerhäuptling', '', 10, 120, 50, 0, 0, 0, 121, 10, 0, 0.9, 40, 0, 20, 120, 0, 100, 0, 'units/unit_5-elite.png', 8, 2, '3:8', '1:2', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (29, 'Rittermeister', '', 12, 100, 155, 0, 0, 0, 61, 10, 0, 1.7, 40, 0, 20, 200, 0, 140, 0, 'units/unit_6-elite.png', 8, 2, '3:10', '2:1', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (30, 'Zentaur', '', 20, 150, 200, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 400, 0, 'units/zentaur.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (31, 'Höllenhund', 'eine zähnefletschende geifernde Bestie, der Wächter Höllenpforte', 15, 1500, 1000, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 80, 0, 'units/hellhound.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (32, 'Gurke', 'eine klein grüne völlig nutzlose Gurke', 20, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 100, 0, 0, 10, 60, 'units/gurke.png', 9, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (33, 'HyperBlob', 'blub und überall sind Blümchen...', 20, 100, 100, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 80, 0, 'hyperblob/blob-.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (34, 'Drip', 'Drips sind kleine dreiäugige Tierchen, die total knuffig sind, bis sie zubeißen', 5, 88, 111, 0, 0, 0, 1, 10, 0, 0, 20, 0, 10, 100, 0, 40, 100, 'units/drip.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (35, 'Nebellicht', 'ein kleines blau leuchtendes Licht mit vier seidigen kleinen Flügeln', 50, 5, 50, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 5, 0, 'units/nebellicht.png', 0, 1, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (38, 'Gnomschütze', 'HIHIHI', 60, 1, 1, 10, 5, 120, 1, 0, 0, 0, 0, 0, 0, 0, 0, 10, 3600, 'units/gnome-range.png', 0, 0, '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (39, 'Einmaster', '', 15, 10, 40, 0, 0, 0, 240, 0, 0, 3, 500, 50, 50, 500, 0, 30, 120, 'units/schiff-1.png', 47, 0, '', '51>1', '', 48, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (40, 'Schlachtschiff', 'grosse schlachtschiffe', 18, 50, 100, 0, 0, 0, 480, 0, 0, 10, 1000, 300, 500, 1000, 50, 80, 180, 'units/schiff-2.png', 47, 0, '53>1', '51>3,52>1', '', 48, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (41, 'Einfacher Matrose', 'edit me', 20, 1, 30, 0, 0, 0, 181, 0, 5, 0.5, 20, 5, 30, 15, 0, 10, 180, 'units/simplematrose.png', 8, 0, '56>1', '54>1', '', 1, 0.5, 2, 0, 0, 0);
INSERT INTO `unittype` VALUES (42, 'Matrose', 'edit me', 21, 2, 35, 0, 0, 0, 241, 0, 5, 0.8, 30, 10, 60, 30, 0, 15, 240, 'units/matrose.png', 8, 0, '56>1', '54>2', '', 1, 0.7, 2.3, 0, 0, 0);
INSERT INTO `unittype` VALUES (43, 'Marinesoldat', 'edit me', 22, 15, 40, 0, 0, 0, 241, 0, 6, 1.3, 30, 20, 60, 50, 0, 0, 240, 'units/marinematrose.png', 8, 0, '56>2', '54>3', '', 1, 0, 4, 0.2, 0, 0);
INSERT INTO `unittype` VALUES (44, 'Enterer', 'edit me', 22, 15, 40, 0, 0, 0, 301, 0, 6, 1.5, 30, 30, 100, 60, 0, 0, 300, 'units/entermatrose.png', 8, 0, '55>1,56>3', '54>2', '', 1, 0, 1, 5, 0, 0);
INSERT INTO `unittype` VALUES (45, 'Katamaran', 'ein kleines schnelles Schiff', 17, 5, 25, 0, 0, 0, 60, 0, 0, 2, 800, 0, 500, 1000, 1000, 20, 900, 'units/katamaran.png', 47, 0, '', '', '', 56, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `user`
-- 

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `pass` varchar(255) NOT NULL default '',
  `mail` varchar(128) NOT NULL default '',
  `homepage` varchar(128) NOT NULL default '',
  `admin` tinyint(3) unsigned NOT NULL default '0',
  `logins` int(10) unsigned NOT NULL default '0',
  `lastlogin` int(10) unsigned NOT NULL default '0',
  `iplock` tinyint(3) unsigned NOT NULL default '1',
  `gfxpath` varchar(128) NOT NULL default '',
  `usegfxpath` tinyint(3) unsigned NOT NULL default '0',
  `guild` int(10) unsigned NOT NULL default '0',
  `guildstatus` int(10) unsigned NOT NULL default '1',
  `pop` float unsigned NOT NULL default '10',
  `maxpop` int(10) unsigned NOT NULL default '10',
  `lumber` float NOT NULL default '1500',
  `stone` float NOT NULL default '1500',
  `food` float NOT NULL default '800',
  `metal` float NOT NULL default '800',
  `runes` float NOT NULL default '0',
  `max_lumber` int(10) unsigned NOT NULL default '1500',
  `max_stone` int(10) unsigned NOT NULL default '1500',
  `max_food` int(10) unsigned NOT NULL default '800',
  `max_metal` int(10) unsigned NOT NULL default '800',
  `max_runes` int(10) unsigned NOT NULL default '0',
  `worker_lumber` float unsigned NOT NULL default '25',
  `worker_stone` float unsigned NOT NULL default '25',
  `worker_food` float unsigned NOT NULL default '25',
  `worker_metal` float unsigned NOT NULL default '25',
  `worker_runes` float unsigned NOT NULL default '0',
  `prod_runes` float NOT NULL default '0',
  `prod_lumber` float NOT NULL default '2.5',
  `prod_stone` float NOT NULL default '2.5',
  `prod_food` float NOT NULL default '2.5',
  `prod_metal` float NOT NULL default '2.5',
  `color` varchar(8) NOT NULL default '#00ff00',
  `mapmode` tinyint(3) unsigned NOT NULL default '1',
  `lastusedarmy` int(10) unsigned NOT NULL default '0',
  `guildpoints` int(8) NOT NULL default '0',
  `general_pts` int(11) NOT NULL default '0',
  `army_pts` int(11) NOT NULL default '0',
  `registered` int(10) unsigned NOT NULL default '0',
  `msgmode` tinyint(3) unsigned NOT NULL default '0',
  `flatview` tinyint(4) NOT NULL default '0',
  `race` tinyint(3) unsigned NOT NULL default '1',
  `flags` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `guild` (`guild`),
  KEY `pop` (`pop`),
  KEY `general_pts` (`general_pts`,`army_pts`),
  KEY `army_pts` (`army_pts`),
  KEY `guildstatus` (`guildstatus`),
  KEY `race` (`race`)
) TYPE=MyISAM AUTO_INCREMENT=1030 ;

-- 
-- Dumping data for table `user`
-- 

INSERT INTO `user` VALUES (249, 'Admin', '*B26D4ED01307E356E8897B4EBD3CD52E50F8975C', 'elara@gmx.de', '', 1, 2, 1120996840, 1, '', 0, 8, 18648630, 147456, 147456, 576000, 576000, 576000, 576000, 4745, 576000, 576000, 576000, 576000, 640000, 30, 30, 20, 20, 0, 0, 162580, 162580, 108380, 108380, '#000000', 1, 160074, 858897487, 4647457, 6, 1108292583, 0, 1, 1, 2);

-- --------------------------------------------------------

-- 
-- Table structure for table `userprofil`
-- 

DROP TABLE IF EXISTS `userprofil`;
CREATE TABLE IF NOT EXISTS `userprofil` (
  `id` int(10) unsigned NOT NULL default '0',
  `profil` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `userprofil`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `userrecord`
-- 

DROP TABLE IF EXISTS `userrecord`;
CREATE TABLE IF NOT EXISTS `userrecord` (
  `userid` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  UNIQUE KEY `userid` (`userid`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `userrecord`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `uservalue`
-- 

DROP TABLE IF EXISTS `uservalue`;
CREATE TABLE IF NOT EXISTS `uservalue` (
  `user` int(10) unsigned NOT NULL default '0',
  `name` varchar(32) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`user`,`name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `uservalue`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `waypoint`
-- 

DROP TABLE IF EXISTS `waypoint`;
CREATE TABLE IF NOT EXISTS `waypoint` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `priority` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `waypoint`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `weather`
-- 

DROP TABLE IF EXISTS `weather`;
CREATE TABLE IF NOT EXISTS `weather` (
  `time` int(10) unsigned NOT NULL default '0',
  `weather` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`time`),
  KEY `weather` (`weather`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `weather`
-- 

        