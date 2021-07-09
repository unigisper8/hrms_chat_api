<?php
defined('BASEPATH') OR exit('No direct script access allowed');
define('TEST_MODE', false);
// define('API_URL', "http://erpmessenger.com/groupchat/Api/");
// define('API_URL', "http://192.168.109.67/Api/");
define('API_URL', "https://erpmessenger.com/hrms_test/HRMS/Api/");
//define('API_URL', "http://erpmessenger.com/groupchat/Api/");
 define('API_TEST_URL', "http://192.168.1.99/erpmessenger/Api/"); // TEST MODE
define('BASE_URL', "http://cepcogp.gotdns.com:3306/");
// define('BASE_URL', "http://localhost/00-android/groupchat/Api/");







define('EMAIL', "EMAIL_ADDRESS");
define('PASSWORD', "PASSWORD");
define('EMPLOYEE_ID', "EMPLOYEE_ID");
define('EMPLOYEE_NAME', "EMPLOYEE_NAME");
define('PHOTO', "PHOTO");
define('DATE_CREATED', "DATE_CREATED");
define('send_status', "send_status");
define('MESSENGER', "MESSENGER");
define('REFERENCE_NO', "REFERENCE_NO");
define('SEND_STATUS', "SEND_STATUS");
define('STATUS', "STATUS");
define('TITLE', "TITLE");
define('DESCRIPTION', "DESCRIPTION");
define('PROJECT_NAME', "project_name");
define('PROJECT_TYPE', "project_type");
define('CONTACT', "contact");
define('GROUP_NAME', "GROUP_NAME");
define('REFERENCES_INVITED', "REFERENCES_INVITED");
define('ID', "id");
define('TYPE', "Type");
define('DATA', "data");
define('EXTRA', "extra");
define('REQUEST', "REQUEST");
define('A_CUSTOMER', "A_CUSTOMER");
define('APPROVAL_DATE', "APPROVAL");
define('APPROVAL_LOGIN', "APPROVAL_LOGIN");
define('REMARK', "Remark");
define('LEAVE_TYPE', "LEAVE_TYPE");
define('LEAVE_BALANCE', "LEAVE_BALANCE");
define('CURRENT_LEAVE_BALANCE', "Current_Leave_Balance");
define('CARRY_FORWARD_LEAVE', "Carry_Forward_Leave");
define('SENDER', "SENDER");
define('RECEIVER', "RECEIVER");
define('MESSAGE', "MESSAGE");
define('ARCHIVE_BY', "ARCHIVE_BY");
define('LATE_IN', "Late_In");
define('DATE_START', "Date_Start");
define('DATE_END', "Date_End");
define('DATE', "Date");
define('REASON', "Reason");
define('HALF_DAY', "Half_Day");
define('SESSION', "Session");
define('MANAGER', "MANAGER");
define('MANAGER_ID', "Manager_ID");
define('MANAGER_ID_2', "Manager_ID_2");
define('MANAGER_ID_3', "Manager_ID_3");
define('MANAGER_NAME', "Manager_NAME");
define('LEAVE_MANAGER', "LEAVE_MANAGER");
define('LEAVE_MANAGER_2', "LEAVE_MANAGER_2");
define('LEAVE_MANAGER_3', "LEAVE_MANAGER_3");
define('DATE_APPROVAL', "Date_Approval");
define('LEAVE_ID', "Leave_ID");
define('LEAVE_STATUS', "Leave_Status");
define('RECEIVE_STATUS', "RECEIVE_STATUS");
define('LEAVE_PENDING_STATUS', "Leave_Pending_Status");
define('TOTAL_WORKING_DAYS', "Total_Working_Days");
define('TRAVEL_TITLE', "Travel_Title");
define('COUNTRY', "Country");
define('CLAIM_ID', "Claim_ID");
define('CLAIM_MANAGER', "CLAIM_MANAGER");
define('AMOUNT', "Amount");
define('TOTAL_AMOUNT', "Total_Amount");
define('TIMEOFF_TITLE', "Timeoff_Title");
define('TIMEOFF_ID', "Timeoff_ID");
define('TIMEOFF_MANAGER_ID', "TIMEOFF_MANAGER");
define('START_HOUR', "StartHour");
define('END_HOUR', "EndHour");
define('FROM', "From");
define('TO', "To");
define('FROM_NAME', "From_Name");
define('TO_NAME', "To_Name");
define('DATE_ISSUED', "Date_Issued");
define('TEMPLATE', "Template");
define('LETTER_ID', "Letter_ID");
define('PARA_NO_1', "Para_No1");
define('PARA_NO_2', "Para_No2");
define('PARA_NO_3', "Para_No3");
define('LIMIT', "Limit");
define('TAX_CODE', "tax_code");
define('TAX_RATE', "tax_rate");
define('TAX_AMOUNT', "tax_amount");
define('SERVICE_CHARGE', "service_charge");
define('TIMEOFF_MONTH_STATISTICS', "timeoff_month_statistics");
define('TIMEOFF_YEAR_STATISTICS', "timeoff_year_statistics");

// New CR
define('LEAVE_PENDING_COUNT', "leave_pending_count");
define('TIMEOFF_PENDING_COUNT', "timeoff_pending_count");
define('CLAIM_PENDING_COUNT', "claim_pending_count");
define('LETTER_PENDING_COUNT', "letter_pending_count");

// New Column for leave and claim approval
define('LEAVE_APPROVE_MANAGER_LEVEL', "leave_approve_manager_level");
define('LEAVE_APPROVE_MANAGER_STATUS_1', "leave_approve_manager_status_1");
define('LEAVE_APPROVE_MANAGER_STATUS_2', "leave_approve_manager_status_2");
define('LEAVE_APPROVE_MANAGER_STATUS_3', "leave_approve_manager_status_3");
define('LEAVE_APPROVE_MANAGER_STATUS_4', "leave_approve_manager_status_4");
define('LEAVE_APPROVE_MANAGER_STATUS_5', "leave_approve_manager_status_5");

define('CLAIM_MANAGER_LEVEL', "claim_manager_level");
define('CLAIM_MANAGER_STATUS_1', "claim_manager_status_1");
define('CLAIM_MANAGER_STATUS_2', "claim_manager_status_2");
define('CLAIM_MANAGER_STATUS_3', "claim_manager_status_3");
define('CLAIM_MANAGER_STATUS_4', "claim_manager_status_4");
define('CLAIM_MANAGER_STATUS_5', "claim_manager_status_5");

// Letter approval status
define('TOP_MANAGEMENT_APPROVE_STATUS', "top_management_approve_status");

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
