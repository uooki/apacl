/*
Navicat MySQL Data Transfer

Source Server         : test_mysql.com
Source Server Version : 50095
Source Host           : 192.168.78.20:3306
Source Database       : tw_xydb

Target Server Type    : MYSQL
Target Server Version : 50095
File Encoding         : 65001

Date: 2016-06-22 11:07:41
*/


SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for admin_users
-- ----------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT 'user id',
  `name` varchar(255) NOT NULL COMMENT 'user name',
  `nickname` varchar(255) default NULL COMMENT 'user nickname',
  `password` varchar(255) NOT NULL COMMENT 'user password',
  `email` varchar(255) default NULL COMMENT 'user email',
  `tel` varchar(255) default NULL COMMENT 'user telephone',
  `channel` varchar(255) default NULL COMMENT 'channel 暂未用到',
  `role_id` int(11) unsigned NOT NULL COMMENT 'user role id',
  `is_role_leader` tinyint(1) default '0' COMMENT '是否是角色组管理者',
  `is_root` tinyint(1) default '0' COMMENT '是否是根用户',
  `is_valid` tinyint(1) default '1' COMMENT 'user if had valid',
  `is_delete` tinyint(1) default '0' COMMENT 'user if had deleted',
  `backup1` varchar(255) default NULL COMMENT 'backup field',
  `backup2` varchar(255) default NULL COMMENT 'backup field',
  `createDate` int(11) NOT NULL default '0' COMMENT 'create date',
  `updateDate` int(11) NOT NULL default '0' COMMENT 'update date',
  `deleteDate` int(11) default '0' COMMENT 'delete date',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `email` (`name`),
  UNIQUE KEY `tel` (`name`),
  KEY `user_rid` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8  COMMENT='admin users table';

-- ----------------------------
-- Table structure for admin_roles
-- ----------------------------
DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT 'role id',
  `name` varchar(255) NOT NULL COMMENT 'role name',
  `show_name` varchar(255) default NULL COMMENT 'role show name',
  `desc` varchar(255) default NULL COMMENT 'role description',
  `type` enum('other','normal','super') NOT NULL default 'other' COMMENT '角色类型',
  `pid` int(11) unsigned NOT NULL default '0' COMMENT 'parent role id',
  `is_delete` tinyint(1) default '0' COMMENT 'role if have deleted',
  `backup1` varchar(255) default NULL COMMENT 'backup field',
  `backup2` varchar(255) default NULL COMMENT 'backup field',
  `createDate` int(11) NOT NULL default '0' COMMENT 'create date',
  `updateDate` int(11) NOT NULL default '0' COMMENT 'update date',
  `deleteDate` int(11) default '0' COMMENT 'delete date',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `role_pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8  COMMENT='admin roles table';

-- ----------------------------
-- Table structure for admin_resources
-- ----------------------------
DROP TABLE IF EXISTS `admin_resources`;
CREATE TABLE `admin_resources` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT 'id',
  `name` varchar(255) default NULL COMMENT 'name',
  `title` varchar(255) default NULL COMMENT 'title',
  `desc` varchar(255) default NULL COMMENT 'resource description',
  `uri` varchar(255) NOT NULL COMMENT '经路由解析后的URI',
  `controller` varchar(255) NOT NULL default 'Index' COMMENT 'controller 名，若位于子控制器目录，需要带上子控制器目录，如 admin/controller_name',
  `action` varchar(255) NOT NULL default 'index' COMMENT 'controller 中的方法名',
  `backup1` varchar(255) default NULL COMMENT 'backup for future',
  `backup2` varchar(255) default NULL,
  `is_delete` tinyint(1) NOT NULL default '0' COMMENT 'if have deleted',
  `createDate` int(11) NOT NULL default '0' COMMENT 'create date',
  `updateDate` int(11) NOT NULL default '0' COMMENT 'update date',
  `deleteDate` int(11) default '0' COMMENT 'delete date',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name_uri` (`name`,`uri`),
  KEY `controller` (`controller`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8    COMMENT='admin resource table';

-- ----------------------------
-- Table structure for admin_permissions
-- ----------------------------
DROP TABLE IF EXISTS `admin_permissions`;
CREATE TABLE `admin_permissions` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT 'id',
  `resource_id` int(11) NOT NULL COMMENT 'resource id',
  `caller_id` int(11) NOT NULL COMMENT '授予权限的对象id ： 用户id 或角色id',
  `caller_type` enum('role','user') NOT NULL default 'user' COMMENT '权限授予对象类型',
  `allow` tinyint(1) NOT NULL default '0' COMMENT '允许或禁止',
  `backup1` varchar(255) default NULL COMMENT 'backup for future',
  `backup2` varchar(255) default NULL COMMENT 'backup for future',
  `createDate` int(11) NOT NULL default '0' COMMENT 'create date',
  `updateDate` int(11) NOT NULL default '0' COMMENT 'update date',
  `deleteDate` int(11) default '0' COMMENT 'delete date',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `caller_id` (`caller_id`,`caller_type`,`resource_id`),
  KEY `per_rid` (`resource_id`),
  KEY `per_cid` (`caller_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8    COMMENT='admin premissions table';