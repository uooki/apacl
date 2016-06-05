/*
CREATE TABLE IF NOT EXISTS `roles` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'role id',
`name` varchar(255) NOT NULL  COMMENT 'role name',
`display_name` varchar(255) NULL COMMENT 'display role name',
`description` varchar(255) NULL COMMENT 'role description',
`pid` int(11)  NOT NULL DEFAULT 0 COMMENT 'parent role id',
`backup` varchar(255) DEFAULT NULL COMMENT 'backup for future',
`is_valid` tinyint(2) NOT NULL DEFAULT 1 COMMENT 'if have valid',
`is_delete` tinyint(2) NOT NULL DEFAULT 0 COMMENT 'if have deleted',
`createTime` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create date',
`updateTime` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update date',
`deleteTime` timestamp  NULL COMMENT 'delete date',
UNIQUE (`name`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARACTER SET utf8  COLLATE utf8_general_ci  COMMENT='role table';
*/

CREATE TABLE IF NOT EXISTS `acl_notes` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
`name` varchar(255) NOT NULL DEFAULT 'noname' COMMENT 'name',  -- username or usergroupname
`description` varchar(255) NULL COMMENT 'description',
`pid` int(11)  NOT NULL DEFAULT 0 COMMENT 'parent note id',
`type`  enum('group','user') NOT NULL COMMENT 'caller type',
`link_id` int(11) COMMENT 'user id or other caller id', --userid 
`is_child` tinyint(1) DEFAULT 0 COMMENT 'if have child',
`is_admin` tinyint(1) DEFAULT 0 COMMENT 'Whether the group administrator',
`backup` varchar(255) DEFAULT NULL COMMENT 'backup for future',
`is_valid` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'note if have valid',
`is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'role or user if have deleted',
`createTime` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create date',
`updateTime` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update date',
`deleteTime` timestamp  NULL COMMENT 'delete date',
UNIQUE (`name`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARACTER SET utf8  COLLATE utf8_general_ci  COMMENT='access request object table';

-- 一个用户若对应多个节点，没必要且会找出开发上的困难
-- 一个用户不能再多个组节点下，用户节点必须唯一对应一个用户；如要添加或消减权限在用户节点上设置
-- 角色和用户组重合到note 中，不单独拿出来；note对外表现为用户组
-- 小组管理员给小组成员授权，只能授予小组以及给组长有的权限; 目前简单根据is_admin 判断是否可以授权和取消授权


--  用户，资源，操作，许可 四个因素组成权限

CREATE TABLE IF NOT EXISTS `acl_resources` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
`name` varchar(255) NOT NULL COMMENT 'name',  
`display_name` varchar(255) NULL COMMENT 'display resource name',
`description` varchar(255) NULL COMMENT 'description',
`path` varchar(255) NULL COMMENT 'url path',
`type`  enum('post','user','log') NOT NULL  COMMENT 'access control object type,eg data user ...',
`backup` varchar(255) DEFAULT NULL COMMENT 'backup for future',
`is_valid` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'if have valid',
`is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if have deleted',
`createTime` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create date',
`updateTime` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update date',
`deleteTime` timestamp  NULL COMMENT 'delete date',
UNIQUE (`name`),
UNIQUE (`path`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARACTER SET utf8  COLLATE utf8_general_ci  COMMENT='resource table';

-- resource 可以是某个实体数据，如文章；也可以是数据集合，如一个最新文章列表 ；
-- 通常一个url 返回的数据都可以作为资源  ; 就是说资源是主观定义的
-- resource type : list , entity data, page
-- 把任何请求都作为一种资源来对待
-- 基于url的权限控制：http://www.programgo.com/article/79452935919/
-- 需要根据参数判断是否有权限的操作， 如何判断权限？是否需要放到权限表？
-- note 表用户构建用户树   资源 posts ,self-posts  逻辑上是冲突的；有继承关系
--  self-posts 的权限应该覆盖 posts的权限 ；如何体现这种关系？
-- 添加 self-post ,unself-post 资源以及用户操作他们的权限，取消用户对posts 的权限
-- 原则：用户不可以同时拥有相冲突的资源权限；如果要让用户拥有更少资源的权限，则就必须先取消更大资源的权限

CREATE TABLE IF NOT EXISTS `acl_permissions`(
`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
`name` varchar(255) NOT NULL  COMMENT 'name',
`descibe` varchar(255) NULL COMMENT 'permissions describe',
`note_id`  int(11) COMMENT 'note id',
`note_type` varchar(255)  COMMENT 'user,group,...',
`resource_id` int(11)  COMMENT 'resource id',  -- null 表示所有资源
`action`  int(11) NOT NULL DEFAULT 15 COMMENT 'action:1-read,2-update,3-create,4-delete',
`privilege`, tinyint(1) DEFAULT 0 COMMENT 'if privilege',
`is_delete` tinyint(2) NOT NULL DEFAULT 0 COMMENT 'if have deleted',
`backup` varchar(255) DEFAULT NULL COMMENT 'backup for future',
`createTime` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create date',
`updateTime` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update date',
`deleteTime` timestamp  NULL COMMENT 'delete date',
UNIQUE (`name`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARACTER SET utf8  COLLATE utf8_general_ci  COMMENT='premissions table';

--  note 和  resource 是多对多的关系
-- 同一个note 中不能出现相互冲突的权限，如允许读某个资源的同时又禁止读该资源
--  如出现则最近的设置覆盖之前的设置。
--  权限的判断定位在控制器的方法？？

/*
CREATE TABLE IF NOT EXISTS `usergroups` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'usergroup id',
`name` varchar(255) NOT NULL  COMMENT 'usergroup name',
`display_name` varchar(255) NULL COMMENT 'display usergroup name',
`description` varchar(255) NULL COMMENT 'usergroup description',
`pid` int(11)  NOT NULL DEFAULT 0 COMMENT 'parent usergroup id',
`backup` varchar(255) DEFAULT NULL COMMENT 'backup for future',
`is_valid` tinyint(2) NOT NULL DEFAULT 1 COMMENT 'if have valid',
`is_delete` tinyint(2) NOT NULL DEFAULT 0 COMMENT 'if have deleted',
`createTime` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create date',
`updateTime` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update date',
`deleteTime` timestamp  NULL COMMENT 'delete date',
UNIQUE (`name`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARACTER SET utf8  COLLATE utf8_general_ci  COMMENT='role table';
*/
/*
CREATE TABLE IF NOT EXISTS `caller_role` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
`note_id` int(11) NOT NULL  COMMENT 'user  or other caller id',
`caller_type` varchar(255) NULL COMMENT 'user or other type caller',
`rid` varchar(255) NULL COMMENT 'role id',
`backup` varchar(255) DEFAULT NULL COMMENT 'backup for future',
`is_valid` tinyint(2) NOT NULL DEFAULT 1 COMMENT 'if have valid',
`is_delete` tinyint(2) NOT NULL DEFAULT 0 COMMENT 'if have deleted',
`createTime` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create date',
`updateTime` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update date',
`deleteTime` timestamp  NULL COMMENT 'delete date',
INDEX (`is_valid`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARACTER SET utf8  COLLATE utf8_general_ci  COMMENT='role table';
*/

-------------基于URL的表结构
--  基于url 权限管理的思路： 

CREATE TABLE IF NOT EXISTS `acl_permissions`(
`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
`name` varchar(255) NOT NULL  COMMENT 'permission name',
`description` varchar(255) NULL COMMENT 'permissions describe',
`note_id`  int(11) COMMENT 'note id',
`route` varchar(255) COMMENT 'path in url',
`privilege` tinyint(1) DEFAULT 0 COMMENT 'if privilege',
`is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if have deleted',
`backup` varchar(255) DEFAULT NULL COMMENT 'backup for future',
`createTime` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create date',
`updateTime` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update date',
`deleteTime` timestamp  NULL COMMENT 'delete date',
UNIQUE (`name`),
UNIQUE (`route`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARACTER SET utf8  COLLATE utf8_general_ci  COMMENT='premissions table';

