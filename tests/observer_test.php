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
 * Tests for the signup enrolment event observer.
 *
 * @package    enrol_signup
 * @copyright  2026 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_signup;

/**
 * Tests for the signup enrolment event observer.
 *
 * @package enrol_signup
 */
final class observer_test extends \advanced_testcase {
    /**
     * A new user is enrolled through every signup instance in a course.
     *
     * @covers \enrol_signup_handler::user_created
     * @covers \enrol_signup_handler::signup_enrol_user
     */
    public function test_user_is_enrolled_in_each_signup_instance(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $plugin = enrol_get_plugin('signup');
        $studentroles = get_archetype_roles('student');
        $studentrole = reset($studentroles);

        $firstinstanceid = $plugin->add_instance($course, [
            'status' => ENROL_INSTANCE_ENABLED,
            'roleid' => $studentrole->id,
        ]);
        $secondinstanceid = $plugin->add_instance($course, [
            'status' => ENROL_INSTANCE_ENABLED,
            'roleid' => $studentrole->id,
        ]);

        $user = $this->getDataGenerator()->create_user();

        $this->assertTrue($DB->record_exists('user_enrolments', [
            'enrolid' => $firstinstanceid,
            'userid' => $user->id,
        ]));
        $this->assertTrue($DB->record_exists('user_enrolments', [
            'enrolid' => $secondinstanceid,
            'userid' => $user->id,
        ]));
    }
}
