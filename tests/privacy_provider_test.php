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
 * Privacy provider tests.
 *
 * @package    local_culrollover
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// vendor/bin/phpunit local/culrollover/tests/privacy_provider_test.php

use core_privacy\local\metadata\collection;
use local_culrollover\privacy\provider;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider tests class.
 *
 * @package    local_culrollover
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_culrollover_privacy_provider_testcase extends \core_privacy\tests\provider_testcase {

    /**
     * Test for provider::get_metadata().
     */
    public function test_get_metadata() {
        $collection = new collection('local_culrollover');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(2, $itemcollection);

        $rollovertable = array_shift($itemcollection);
        $this->assertEquals('cul_rollover', $rollovertable->get_name());

        $privacyfields = $rollovertable->get_privacy_fields();
        $this->assertArrayHasKey('sourceid', $privacyfields);
        $this->assertArrayHasKey('destid', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('datesubmitted', $privacyfields);
        $this->assertArrayHasKey('status', $privacyfields);
        $this->assertArrayHasKey('schedule', $privacyfields);
        $this->assertArrayHasKey('type', $privacyfields);
        $this->assertArrayHasKey('merge', $privacyfields);
        $this->assertArrayHasKey('includegroups', $privacyfields);
        $this->assertArrayHasKey('enrolments', $privacyfields);
        $this->assertArrayHasKey('visible', $privacyfields);
        $this->assertArrayHasKey('visibledate', $privacyfields);
        $this->assertArrayHasKey('completiondate', $privacyfields);
        $this->assertArrayHasKey('notify', $privacyfields);
        $this->assertArrayHasKey('template', $privacyfields);
        
        $this->assertEquals('privacy:metadata:cul_rollover', $rollovertable->get_summary());

        $rolloverconfigtable = array_shift($itemcollection);
        $this->assertEquals('cul_rollover_config', $rolloverconfigtable->get_name());

        $privacyfields = $rolloverconfigtable->get_privacy_fields();
        $this->assertArrayHasKey('courseid', $privacyfields);
        $this->assertArrayHasKey('name', $privacyfields);
        $this->assertArrayHasKey('value', $privacyfields);
        $this->assertArrayHasKey('timemodified', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        
        $this->assertEquals('privacy:metadata:cul_rollover_config', $rolloverconfigtable->get_summary());
    }

    /**
     * Test for provider::get_contexts_for_userid()
     */
    public function test_get_contexts_for_userid() {
        $this->resetAfterTest();

        $context = \context_system::instance();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        // Test nothing is found before rollover is created.
        $contextlist = provider::get_contexts_for_userid($user1->id);
        $this->assertCount(0, $contextlist);
        // Test nothing is found before course is edited.
        $contextlist = provider::get_contexts_for_userid($user2->id);
        $this->assertCount(0, $contextlist);

        $this->create_rollover($course1->id, $course2->id, $user1->id, time() - (9 * DAYSECS));

        // Test for the rollover creator.
        $contextlist = provider::get_contexts_for_userid($user1->id);
        $this->assertCount(1, $contextlist);
        $context = $contextlist->current();
        $this->assertEquals(
                context_system::instance()->id,
                $context->id);

        $this->create_rollover_lock($course1->id, $user2->id, time() - (9 * DAYSECS));

        // Test for the course editor.
        $contextlist = provider::get_contexts_for_userid($user2->id);
        $this->assertCount(1, $contextlist);
        $context = $contextlist->current();
        $this->assertEquals(
                context_system::instance()->id,
                $context->id);
    }    

    /**
     * Test for provider::get_users_in_context() when there is a notification between users.
     */
    public function test_get_users_in_context() {
        $this->resetAfterTest();

        $context = \context_system::instance();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $user1context = context_user::instance($user1->id);
        $user2context = context_user::instance($user2->id);

        // Test nothing is found before rollover is created.
        $userlist = new \core_privacy\local\request\userlist($context, 'local_culrollover');
        \local_culrollover\privacy\provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);        

        // Test for the rollover user.
        $this->create_rollover($course1->id, $course2->id, $user1->id, time() - (9 * DAYSECS));

        $userlist = new \core_privacy\local\request\userlist($context, 'local_culrollover');
        \local_culrollover\privacy\provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
        $userincontext1 = $userlist->current();
        $this->assertEquals($user1->id, $userincontext1->id);

        // Test for the course editor.
        $this->create_rollover_lock($course2->id, $user2->id, time() - (9 * DAYSECS));

        $userlist = new \core_privacy\local\request\userlist($context, 'local_culrollover');
        \local_culrollover\privacy\provider::get_users_in_context($userlist);
        $this->assertCount(2, $userlist);
        $userlist->next();
        $userincontext2 = $userlist->current();
        $this->assertEquals($user2->id, $userincontext2->id);
    }

    /**
     * Test for provider::export_user_data().
     */
    public function test_export_for_context() {
        $this->resetAfterTest();

        $context = \context_system::instance();

        // Create users to test with.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $now = time();

        $this->create_rollover($course1->id, $course2->id, $user1->id, time() - (9 * DAYSECS));
        $this->create_rollover_lock($course2->id, $user2->id, time() - (9 * DAYSECS));       

        // Confirm the rollovers.
        $this->export_context_data_for_user($user1->id, $context, 'local_culrollover');
        $writer = writer::with_context($context);

        $this->assertTrue($writer->has_any_data());

        $rollovers = (array) $writer->get_data([get_string('privacy:metadata:cul_rollover', 'local_culrollover')]);

        $this->assertCount(1, $rollovers);

        // Confirm the edits.
        $this->export_context_data_for_user($user2->id, $context, 'local_culrollover');
        $writer = writer::with_context($context);

        $this->assertTrue($writer->has_any_data());

        $edits = (array) $writer->get_data([get_string('privacy:metadata:cul_rollover_config', 'local_culrollover')]);

        $this->assertCount(1, $edits);
    }    

    /**
     * Test for provider::delete_data_for_all_users_in_context().
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        $this->resetAfterTest();

        $context = \context_system::instance();

        // Create users to test with.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $now = time();

        // Create rollover and edit.
        $this->create_rollover($course1->id, $course2->id, $user1->id, time() - (9 * DAYSECS));
        $this->create_rollover_lock($course2->id, $user2->id, time() - (9 * DAYSECS));

        // There should be 1 rollover and 1 edit.
        $this->assertEquals(1, $DB->count_records('cul_rollover'));
        $this->assertEquals(1, $DB->count_records('cul_rollover_config'));
        provider::delete_data_for_all_users_in_context($context);
        // Confirm there are no rollovers.
        $this->assertEquals(0, $DB->count_records('cul_rollover'));
        // Confirm there are no edits.
        $this->assertEquals(0, $DB->count_records('cul_rollover_config'));
    }

    /**
     * Test for provider::delete_data_for_user().
     */
    public function test_delete_data_for_user() {
        global $DB;

        $this->resetAfterTest();

        $context = \context_system::instance();

        // Create users to test with.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $now = time();

        // Create rollover and edit.
        $this->create_rollover($course1->id, $course2->id, $user1->id, time() - (9 * DAYSECS));
        $this->create_rollover_lock($course2->id, $user2->id, time() - (9 * DAYSECS));

         // There should be 1 rollover and 1 edit.
        $this->assertEquals(1, $DB->count_records('cul_rollover'));
        $this->assertEquals(1, $DB->count_records('cul_rollover_config'));
        $contextlist = new \core_privacy\local\request\approved_contextlist($user2, 'local_culrollover',
            [$context->id]);

        provider::delete_data_for_user($contextlist);
        // Confirm there is still 1 rollover.
        $this->assertEquals(1, $DB->count_records('cul_rollover'));
        // Confirm there are no edits.
        $this->assertEquals(0, $DB->count_records('cul_rollover_config'));

        // Confirm the user 1 data still exists.
        $rollovers = $DB->get_records('cul_rollover');
        $this->assertCount(1, $rollovers);
        ksort($rollovers);

        $rollover = array_shift($rollovers);
        $this->assertEquals($user1->id, $rollover->userid);
    }

    /**
     * Test for provider::delete_data_for_users().
     */
    public function test_delete_data_for_users() {
                global $DB;

        $this->resetAfterTest();

        $context = \context_system::instance();

        // Create users to test with.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $now = time();

        // Create rollover and edit.
        $this->create_rollover($course1->id, $course2->id, $user1->id, time() - (9 * DAYSECS));
        $this->create_rollover_lock($course2->id, $user2->id, time() - (9 * DAYSECS));

        // There should be 1 rollover and 1 edit.
        $this->assertEquals(1, $DB->count_records('cul_rollover'));
        $this->assertEquals(1, $DB->count_records('cul_rollover_config'));

        $approveduserlist = new \core_privacy\local\request\approved_userlist($context, 'local_culrollover',
                [$user1->id, $user2->id]);
        provider::delete_data_for_users($approveduserlist);

        // There should be 0 rollover and 0 edit.
        $this->assertEquals(0, $DB->count_records('cul_rollover'));
        $this->assertEquals(0, $DB->count_records('cul_rollover_config'));
    }   

    /**
     * Creates a rollover to be used for testing.
     *
     * @param int $sourceid The course id ofthe source
     * @param int $destid The course id ofthe destination
     * @param int $userid The user id who created the rollover
     * @param int|null $datesubmitted The time the rollover was submitted
     * @return int The id of the rollover
     * @throws dml_exception
     */
    private function create_rollover(int $sourceid, int $destid, int $userid, int $datesubmitted = null) {
        global $DB;

        if (is_null($datesubmitted)) {
            $datesubmitted = time();
        }

        $schedule = $visibledate = $completiondate = $datesubmitted;  

        $record = new stdClass();
        $record->sourceid = $sourceid;
        $record->destid = $destid;
        $record->userid = $userid;
        $record->datesubmitted = $datesubmitted;
        $record->status = 'Pending';
        $record->schedule = $schedule;
        $record->type = 1;
        $record->merge = 0;
        $record->includegroups = 0;
        $record->enrolments = '9,3,4,11,19,16';
        $record->visible = 0;
        $record->visibledate = $visibledate;
        $record->completiondate = $completiondate;
        $record->notify = '9,3,4,11,19,16';
        $record->template = null;
  
        return $DB->insert_record('cul_rollover', $record);
    }

    /**
     * Creates a rollover lock to be used for testing.
     *
     * @param int $courseid The course id of an edited course
     * @param int $userid The user id who edited the course
     * @param int|null $timemodified The time the course was edited
     * @return int The id of the rollover
     * @throws dml_exception
     */
    private function create_rollover_lock(int $courseid, int $userid, int $timemodified = null) {
        global $DB;

        if (is_null($timemodified)) {
            $timemodified = time();
        }

        $record = new stdClass();
        $record->courseid = $courseid;
        $record->name = 'rolloverlocked';
        $record->value = 1;
        $record->timemodified = $timemodified;
        $record->userid = $userid;
  
        return $DB->insert_record('cul_rollover_config', $record);
    }
}