<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'CUL Rollover';
$string['defaultoptions_header'] = '1. Default Options';
$string['nextdate'] = "Next available date";
$string['specificdate'] = "Specify a date";
$string['copy_everything'] = "Everything";
$string['copy_content'] = "Content only ";
$string['copy_unknown'] = "unknown ";
$string['merge'] = "Merge ";
$string['delete'] = "Delete first";
$string['groups'] = "Yes";
$string['nogroups'] = "No";
$string['rolestocopy'] = "Copy role assignments";
$string['norolestocopy'] = "No selectable roles!";
$string['visible'] = " Make Visible";
$string['notvisible'] = "Hide";
$string['visiblefrom'] = "Make visible on a given date";
$string['visibledate'] = "Set start date (module visibility)";
$string['migratedate'] = "Rollover date";
$string['selectcourses_header'] = "2. Select Modules";
$string['reviewoptions_header'] = "3. Review and Amend Settings";
$string['rolestoinform'] = "Select roles to be notified of rollover success";
$string['addmore'] = "Add another";
$string['automatedbackupschedule'] = "Automated Backup Schedule";
$string['successemail'] = 'This is to let you know that the Moodle module called {$a->coursename} has been rolled over and the requestor has asked for you to be notified.<br><a href="http://moodle.city.ac.uk/course/view.php?id={$a->courseid}">Click this link to access the module</a>
';
$string['successemail_subject'] = 'Moodle module rollover notification ({$a->coursename})';
$string['successemail_subject_requestor'] = 'Moodle module rollover notification ({$a->coursename})';
$string['successemail_requestor'] = 'Your requested rollover for the Moodle module called {$a->coursename} was successful and is now ready for editing. If requested module roles (except students) have also been notifed.<br><a href="http://moodle.city.ac.uk/course/view.php?id={$a->courseid}">Click this link to access the module</a>
';
$string['visiblity'] = 'Course Visibility';
$string['whentorollover'] = 'When should rollover be carried out?';
$string['howtorollover'] = 'Merge or delete first existing content in destination module';
$string['whattoroolover'] = 'Rollover type';
$string['shouldgroupsbecopied'] = 'Copy groups and groupings';
$string['shouldcoursebevisible'] = 'Module visibility';
$string['source_courses'] = "select module";
$string['destination_courses'] = "select module";
$string['reviewmessage'] = 'Destination modules that already have content (and therefore may have been rolled over already) are highlighted. Click the link to view a module in a new window. You can remove the rollover from the list using the Remove from queue link.';
$string['selectcoursesmessage'] = 'Modules are filtered according to current year and rollover period. You can only see modules that you are enroled on. Destination modules that already have a rollover pending will be shown in the destination list but you can not select them until the rollover has processed.';

// Course visibility Strings.
$string['hidecourse'] = 'Make hidden';
$string['visiblecourse'] = 'Make visible';
$string['coursevisibleon'] ='Make Course Visible on:  ';

// Table Headers.
$string['source_header'] = "Source";
$string['dest_header'] = "Destination";
$string['type_header'] = "Type";
$string['migration_date'] = "Date";
$string['type'] = "Type";
$string['method'] = "Merge or Delete";
$string['group'] = "Groups";
$string['roles'] = "Enrolments";
$string['visibility'] = "Course Visibility";
$string['visiblefromdate'] = "Visibility Date";
$string['deleterow'] = "Remove from queue?";
$string['selectsourcecourses'] = "Source Modules";
$string['selectdestinationcourses'] = "Destination Modules";

// Action Buttons.
$string['choosecourses'] = 'Next -> Select Modules';
$string['review'] = 'Next -> Review and Amend';
$string['finish'] = 'Last  -> Submit to Queue';

// Index Page.
$string['addformigration'] = "Add to Rollover Queue";
$string['breadcrumb'] = 'Rollover Tool';
$string['date'] = "Date";
$string['existingrecords'] = 'Module Rollover Tool';
$string['norequests'] = "No rollover requests in queue";
$string['status'] = "Status";
$string['title'] = 'Moodle: CUL Rollover Tool';
$string['user'] = "User";

// Tooltip Strings.
$string['tooltipwarning'] = 'click to edit (red background indicates destination has content already!)';

// Settings.
$string['src_regex_identifier'] = "Specify Source Course RegEx Filter";
$string['dest_regex_identifier'] = "Specify Destination Course RegEx Filter";
$string['allowedroles'] = 'Select Roles';
$string['rollover_debugging'] = "Enable Rollover Debugging";
$string['rollover_debugging_desc'] = "Enable extra messages to be displayed in the cron log for the rollover";
$string['rollover_chars'] = "Number of characters.";
$string['rollover_chars_desc'] = "Set the number of characters that must be typed before the search starts.";
$string['rollover_delay'] = "Delay.";
$string['rollover_delay_desc'] = "Set select box typing delay.";
$string['includeh5p'] = "Include H5P Activities.";
$string['includeh5p_desc'] = "After the upgrade this setting may be used to exclude  H5P activities from rollover.";
$string['allowedroles_desc'] = 'Select roles which can be rolled forward';
$string['startat_helper'] ='What time should the rollover cron be allowed to run';
$string['stopat_helper'] = 'What time should the rollover cron be stopped?';
$string['src_regex_identifier_helper'] = 'Specify the Regular Expression to filter the course selection for Source Courses. No delimiters are required. For syntax see:
<a href ="http://dev.mysql.com/doc/refman/5.7/en/regexp.html" target="_blank">SQL Regex</a>';
$string['dest_regex_identifier_helper'] = 'Specify the Regular Expression to filter the course selection for Destination Courses. No delimiters are required. For syntax see:
<a href ="http://dev.mysql.com/doc/refman/5.7/en/regexp.html" target="_blank">SQL Regex</a>';
$string['allowedroles_helper'] = 'Select roles which can be rolled forward';
$string['delete_turnitin_v2'] = "Delete Turnitinv2 Assignments";
$string['delete_turnitin_v2_desc'] = "Delete Turnitin Version 2 Assignments from the Destination Course? - Please note that this cannot be undone!";
$string['alwaysbackupdestination'] = "Backup Destination Course?";
$string['backupdestination_desc'] = "Always backup destination course before a rollover takes place. this might increase processing times and significantly reduce performance!";

// Error Strings.
$string['nocoursestomigrate'] = "You do not have sufficient access rights to use  module rollover. 

Clicking Continue will take you back to Moodle home.";
$string['invalidcourseshortname'] = "A module shortname supplied is invalid! Please review the CSV upload file and retry.";
$string['invalidcourseformigration'] = "The specified module is not a valid source module or a valid destination module. Please contact your school administrator or Service Desk for assistance.";
$string['invalidextparam'] = 'The backup option {$a} is invalid';
$string['invaliddstcourseid'] = 'The destination course id {$a} is invalid';
$string['invalidsrccourseid'] = 'The source course id {$a} is invalid';
$string['emptycourseselection'] = "You have not selected any courses for rollover. Please select a course from the previous page";
$string['inserterror'] = "There was a problem adding one or more of your rollovers";
$string['backupprecheckerrors'] = 'There were errors in the backup precheck: {$a}';


// Help Icons Text.
$string['defaultoptions_helptext_help'] = "Set default/bulk options here. These can be overridden for individual rollovers later on. Please see <a href=\"https://sleguidance.atlassian.net/wiki/display/Moodle/Rollover+tool\" target=\"_blank\">guidance notes</a> for more help.";
$string['defaultoptions_helptext'] = "Default Options";
$string['whengroup_helptext_help'] = "Determines which date the rollover is performed. If Next available is chosen then rollovers will be added to the queue and run during the next available window (usually overnight). To specify a date use the calendar selector to select a date.";
$string['whengroup_helptext'] = "Rollover Date";
$string['whatgroup_helptext_help'] = "Rollover has changed. There is no need to select a type. Rollover will copy all content (activities and resources, except Turnitin) from the Source to the Destination module. Blocks are no longer copied. These are setup using the new CUL Collapsed Topics format which will be applied automatically when the module is created.";
$string['whatgroup_helptext'] = "Rollover Type";
$string['mergegroup_helptext_help'] = "What to do if destination module contains any content. Merge will just add content from source module to it, delete first will remove any content from destination module before rollover.";
$string['mergegroup_helptext'] = "Merge Options";
$string['groupsgroup_helptext_help'] = "Selecting Yes for this option will copy any groups or groupings from the source module 
to the destination. Note this does not include group members, just group names and groupings. 
You will need to add your students into groups before your activities go live.";
$string['groupsgroup_helptext'] = "Group Copy";
$string['roles_helptext_help'] = "Choose the roles assignments that you would like to copy from source to destination. You can select multiple roles. Currently rollover prevents the copying of student enrolments.";
$string['roles_helptext'] = "Copy Roles";
$string['noroles_helptext_help'] = "Roles currently cannot be copied across.";
$string['noroles_helptext'] = "No roles!";
$string['visiblegroup_helptext_help'] = "Use these options to control the visibility of the module. If a visibility date is set then the module will remain unavailable to students until that date.";
$string['visiblegroup_helptext'] = "Module Visibility";
$string['rolestoinform_help'] = 'Select roles to inform when rollover complete';
$string['rolestoinform_help_help'] = 'Select roles to inform when rollover complete';

// Capability Strings.
$string['culrollover:delete'] = "Delete rollovers";
$string['culrollover:view'] = "View rollovers";

// Event strings.
$string['eventrollover_deleted'] = "Rollover delete";
$string['eventrollover_completed'] = "Rollover complete";
$string['eventrollover_failed'] = "Rollover failed";
$string['eventdestinationlocked'] = "The course has been edited";

// Task strings.
$string['rollovertask'] = "Rollover processing";

// Privacy API
$string['privacy:metadata:cul_rollover'] = 'cul_rollover';
$string['privacy:metadata:cul_rollover_config'] = 'cul_rollover_config';
$string['privacy:metadata:local_culrollover'] = 'local_culrollover';
$string['privacy:metadata:local_culrollover'] = 'Stores the rollover details.';
$string['privacy:metadata:local_culrollover:sourceid'] = 'The id of the source course.';
$string['privacy:metadata:local_culrollover:destid'] = 'The id of the destination course';
$string['privacy:metadata:local_culrollover:userid'] = 'The id of the user.';
$string['privacy:metadata:local_culrollover:datesubmitted'] = 'The date the rollover was submited.';
$string['privacy:metadata:local_culrollover:status'] = 'The status of the rollover: complete/pending/failed.';
$string['privacy:metadata:local_culrollover:schedule'] = 'The scheduled date for the rollover.';
$string['privacy:metadata:local_culrollover:type'] = 'The type of rollover: content/all - deprecated.';
$string['privacy:metadata:local_culrollover:merge'] = 'Indicates if the rollover is: merge/delete first.';
$string['privacy:metadata:local_culrollover:groups'] = 'Indicates whether to include groups and groupings.';
$string['privacy:metadata:local_culrollover:enrolments'] = 'Indicates whether to include enrolments.';
$string['privacy:metadata:local_culrollover:visible'] = 'Indicates whether to: Make visible/hide/make visible on a given date.';
$string['privacy:metadata:local_culrollover:visibledate'] = 'The date to make the course visible.';
$string['privacy:metadata:local_culrollover:completiondate'] = 'The date the rollover completed.';
$string['privacy:metadata:local_culrollover:notify'] = 'The list of roles to notify when the rollover is complete.';
$string['privacy:metadata:local_culrollover:template'] = 'The template to use - deprecated.';
$string['privacy:metadata:local_culrollover_config'] = 'Stores extra rollover config data.';
$string['privacy:metadata:local_culrollover:courseid'] = 'The course id.';
$string['privacy:metadata:local_culrollover:name'] = 'The config setting name.';
$string['privacy:metadata:local_culrollover:value'] = 'The config setting value.';
$string['privacy:metadata:local_culrollover:timemodified'] = 'The time the config setting was modified.';

