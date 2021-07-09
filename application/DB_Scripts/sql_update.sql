-- Execute this if column exists
alter table MAN_MOB_GROUP_NAME drop column leave_approve_manager_status_1;
alter table MAN_MOB_GROUP_NAME drop column leave_approve_manager_status_2;
alter table MAN_MOB_GROUP_NAME drop column leave_approve_manager_status_3;
alter table MAN_MOB_GROUP_NAME drop column leave_approve_manager_status_4;
alter table MAN_MOB_GROUP_NAME drop column leave_approve_manager_status_5;
alter table MAN_MOB_GROUP_NAME drop column leave_approve_manager_level;
alter table MAN_MOB_GROUP_NAME drop column claim_manager_status_1;
alter table MAN_MOB_GROUP_NAME drop column claim_manager_status_2;
alter table MAN_MOB_GROUP_NAME drop column claim_manager_status_3;
alter table MAN_MOB_GROUP_NAME drop column claim_manager_status_4;
alter table MAN_MOB_GROUP_NAME drop column claim_manager_status_5;
alter table MAN_MOB_GROUP_NAME drop column claim_manager_level;

-- Execute for new column and default values
alter table MAN_MOB_GROUP_NAME  add column top_management_esc boolean default true ;
alter table MAN_MOB_GROUP_NAME  add column top_management_emp varchar(50) default 'S1001';

alter table MAN_MOB_GROUP_NAME  add column leave_approve_manager_2 varchar(50) default 'S1001';
alter table MAN_MOB_GROUP_NAME  add column leave_approve_manager_3 varchar(50) default 'S1002';
alter table MAN_MOB_GROUP_NAME  add column leave_approve_manager_4 varchar(50) default 'N/A';
alter table MAN_MOB_GROUP_NAME  add column leave_approve_manager_5 varchar(50) default 'N/A';


alter table MAN_MOB_HRMS_LEAVE_APPLICATION  add column leave_approve_manager_status_1 int default 0;
alter table MAN_MOB_HRMS_LEAVE_APPLICATION  add column leave_approve_manager_status_2 int default 0;
alter table MAN_MOB_HRMS_LEAVE_APPLICATION  add column leave_approve_manager_status_3 int default 0;
alter table MAN_MOB_HRMS_LEAVE_APPLICATION  add column leave_approve_manager_status_4 int default 0;
alter table MAN_MOB_HRMS_LEAVE_APPLICATION  add column leave_approve_manager_status_5 int default 0;

alter table MAN_MOB_HRMS_LEAVE_APPLICATION  add column leave_approve_manager_level int default 1;

alter table MAN_MOB_GROUP_NAME  add column claim_manager_2 varchar(50) default 'S1001';
alter table MAN_MOB_GROUP_NAME  add column claim_manager_3 varchar(50) default 'S1002';
alter table MAN_MOB_GROUP_NAME  add column claim_manager_4 varchar(50) default 'N/A';
alter table MAN_MOB_GROUP_NAME  add column claim_manager_5 varchar(50) default 'N/A';

alter table MAN_MOB_HRMS_CLAIM_TITLE  add column claim_manager_status_1 int default 0;
alter table MAN_MOB_HRMS_CLAIM_TITLE  add column claim_manager_status_2 int default 0;
alter table MAN_MOB_HRMS_CLAIM_TITLE  add column claim_manager_status_3 int default 0;
alter table MAN_MOB_HRMS_CLAIM_TITLE  add column claim_manager_status_4 int default 0;
alter table MAN_MOB_HRMS_CLAIM_TITLE  add column claim_manager_status_5 int default 0;

alter table MAN_MOB_HRMS_CLAIM_TITLE  add column claim_manager_level int default 1;

alter table MAN_MOB_HRMS_LETTER  add column top_management_approve_status int default 1;


DROP TABLE IF EXISTS `MAN_MOB_CLAIM_PROJECT`;
CREATE TABLE `MAN_MOB_CLAIM_PROJECT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `project_type` varchar(60) NOT NULL,
  `contact` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

alter table MAN_MOB_HRMS_CLAIM_TITLE  add column project_name VARCHAR(255) NULL;
alter table MAN_MOB_HRMS_CLAIM_TITLE  add column project_type VARCHAR(50) NULL;
alter table MAN_MOB_HRMS_CLAIM_TITLE  add column contact VARCHAR(255) NULL;