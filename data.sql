-- phpMyAdmin SQL Dump
-- version 3.4.11.1
-- http://www.phpmyadmin.net
--
-- 主机: imysql55
-- 生成日期: 2018 年 06 月 18 日 09:08
-- 服务器版本: 5.5.60
-- PHP 版本: 5.6.36

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `jquery_file_upload`
--

-- --------------------------------------------------------

--
-- 表的结构 `jq_attachment`
--

DROP TABLE IF EXISTS `jq_attachment`;
CREATE TABLE IF NOT EXISTS `jq_attachment` (
  `aid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `jq_attachment_n`
--

DROP TABLE IF EXISTS `jq_attachment_n`;
CREATE TABLE IF NOT EXISTS `jq_attachment_n` (
  `aid` mediumint(8) unsigned NOT NULL,
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `attachment` varchar(255) NOT NULL DEFAULT '',
  `isimage` tinyint(1) NOT NULL DEFAULT '0',
  `width` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `jq_attachment_unused`
--

DROP TABLE IF EXISTS `jq_attachment_unused`;
CREATE TABLE IF NOT EXISTS `jq_attachment_unused` (
  `aid` mediumint(8) unsigned NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `attachment` varchar(255) NOT NULL DEFAULT '',
  `isimage` tinyint(1) NOT NULL DEFAULT '0',
  `width` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `jq_thread`
--

DROP TABLE IF EXISTS `jq_thread`;
CREATE TABLE IF NOT EXISTS `jq_thread` (
  `tid` int(10) NOT NULL AUTO_INCREMENT,
  `subject` char(80) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
