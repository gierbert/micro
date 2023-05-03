-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2023 年 04 月 29 日 16:01
-- 服务器版本: 5.5.40
-- PHP 版本: 5.3.29

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `micro`
--

-- --------------------------------------------------------

--
-- 表的结构 `micro_access_tokens`
--

CREATE TABLE IF NOT EXISTS `micro_access_tokens` (
  `token` varchar(30) NOT NULL,
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- --------------------------------------------------------

--
-- 表的结构 `micro_aidraw`
--

CREATE TABLE IF NOT EXISTS `micro_aidraw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `drawUrl` varchar(100) NOT NULL,
  `userRandom` varchar(51) NOT NULL,
  `createtime` int(21) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `micro_categories`
--

CREATE TABLE IF NOT EXISTS `micro_categories` (
  `id` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`,`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- --------------------------------------------------------

--
-- 表的结构 `micro_comment`
--

CREATE TABLE IF NOT EXISTS `micro_comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `com_uid` int(20) unsigned NOT NULL,
  `com_content` varchar(100) NOT NULL,
  `com_postid` int(11) NOT NULL,
  `com_createtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `micro_message`
--

CREATE TABLE IF NOT EXISTS `micro_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `random_s` varchar(21) NOT NULL,
  `random_r` varchar(21) NOT NULL,
  `message` varchar(300) DEFAULT NULL,
  `createtime` int(21) DEFAULT NULL,
  `img` varchar(100) DEFAULT NULL,
  `isread` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `micro_posts`
--

CREATE TABLE IF NOT EXISTS `micro_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate` int(10) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  `content` text,
  `createtime` int(15) DEFAULT NULL,
  `praise` int(10) unsigned NOT NULL DEFAULT '0',
  `praise_uid` text,
  `praise_active` int(1) NOT NULL DEFAULT '0',
  `follow` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `micro_users`
--

CREATE TABLE IF NOT EXISTS `micro_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(12) NOT NULL COMMENT '用户名',
  `openid` varchar(100) NOT NULL,
  `face` varchar(200) NOT NULL,
  `random` varchar(30) NOT NULL,
  `regtime` int(15) NOT NULL,
  `experience` int(20) unsigned NOT NULL,
  `postCount` int(10) NOT NULL DEFAULT '0',
  `signature` varchar(50) NOT NULL,
  `gender` int(1) NOT NULL DEFAULT '0',
  `fans` text,
  `follow` text,
  `fans_num` int(10) NOT NULL DEFAULT '0',
  `follow_num` int(10) NOT NULL DEFAULT '0',
  `school` varchar(20) NOT NULL,
  `college` varchar(20) NOT NULL,
  `post_follow` text,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk COMMENT='用户表' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
