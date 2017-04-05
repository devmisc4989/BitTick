<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$config['client_db_create_script_db'] = <<<SQL
CREATE DATABASE if not exists %s;
SQL;

$config['client_db_create_script_request_events'] = <<<SQL
CREATE TABLE  `request_events` (
  `request_eventsid` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `type` int(11) NOT NULL COMMENT '1=landing page request\n2=success page request\n',
  `conversion_value` float DEFAULT '0',
  `impression_type` int(11) NOT NULL DEFAULT '1',
  `goal_id` int(10) unsigned DEFAULT NULL,
  `conversion` varchar(4) DEFAULT NULL,
  `visitor` int(11) DEFAULT NULL,
  `landingpage_collectionid` int(11) NOT NULL,
  `clientid` int(11) NOT NULL,
  `landing_pageid` int(11) NOT NULL,
  `conversion_details` varchar(2048) DEFAULT NULL,
  `page_groupid` int(11) DEFAULT '-1',
  `impressions` int(11) DEFAULT '0',
  `conversions` int(11) DEFAULT '0',
  `conversion_value_aggregation` float DEFAULT '0',
  PRIMARY KEY (`request_eventsid`) USING BTREE,
  KEY `landing_pageid` (`landing_pageid`),
  KEY `visitor` (`visitor`),
  KEY `landingpage_collectionid` (`landingpage_collectionid`),
  KEY `type` (`type`),
  KEY `page_groupid_idx` (`page_groupid`) USING BTREE,
  KEY `date_idx` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=%d DEFAULT CHARSET=latin1;
SQL;

$config['client_db_create_script_delivery_plan'] = <<<SQL
CREATE TABLE  `delivery_plan` (
  `delivery_planid` int(11) NOT NULL AUTO_INCREMENT,
  `landingpage_collectionid` int(11) NOT NULL,
  `visitorid` int(11) NOT NULL,
  `date` datetime DEFAULT NULL,
  `delivery_plan` text,
  PRIMARY KEY (`delivery_planid`),
  KEY `visitorid_idx` (`landingpage_collectionid`,`visitorid`),
  KEY `collectionid_idx` (`landingpage_collectionid`)
) ENGINE=InnoDB AUTO_INCREMENT=%d DEFAULT CHARSET=latin1;
SQL;

$config['client_db_create_script_visitor'] = <<<SQL
CREATE TABLE  `visitor` (
  `visitorid` int(11) NOT NULL AUTO_INCREMENT,
  `pagecode` double DEFAULT NULL,
  PRIMARY KEY (`visitorid`)
) ENGINE=InnoDB AUTO_INCREMENT=%d DEFAULT CHARSET=latin1;
SQL;


?>