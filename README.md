Introduction
============
The CUL Rollover tool has been built to meet the specific requirements of City, University of London. Many of the options available in Moodles core back up and restore pages have been stripped out of our GUI and defaults used. For this reason, it may not be flexible enough to suit the neeeds of other institutions.

The rollovers run as a scheduled task.

How Rollover Works
===================

Delete first
============

The destination course is backed up.
All destination course activities and resources are deleted. 
Source course activities, resources and settings are copied over. This excludes:
	mod_lti
	mod_turnitin
	mod_peerassessment (in house plugin)
	mod_hvp (optionally excluded based on admin setting)
Groups and groupings are copied over (if selected).
Any copied News Forums are deleted from the destination course.
Role assignments are copied over (if selected).
The destination course format is set to our default (CUL Collapsed Topics) and the default blocks are added. 
The number of sections is copied over.
The visibility of the destination course is set (as selected).
The start date of the destination course is set (if selected).
The assignments/forums with TII enabled are deleted from the destination course. NB Other mods can be enabled to use TII.

What does not get deleted:
--------------------------

Roles and enrolments.
Groups and groupings.
Course back up files.

NB If the source course uses a different format to the default, and the user changes the destination course format to match, the settings for the source course format will have been copied and will apply to the destination course.


Merge
=====

All source course activities and resources are copied to relevant section and number of sections is updated.


Future development
==================

Automated tests.