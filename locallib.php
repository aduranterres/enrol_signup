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
 * Signup event handlers
 *
 * @package    enrol_signup
 * @copyright  2011 Qontori Pte Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Event handler for signup enrol plugin.
 */
class enrol_signup_handler {
    /**
     * Enrols a newly created user in configured signup enrolment instances.
     *
     * @param \core\event\user_created $event User-created event.
     * @return void
     */
    public static function user_created(\core\event\user_created $event) {
        global $DB;

        $user = $event->get_record_snapshot('user', $event->objectid);

        $signupinstances = $DB->get_records('enrol', ['enrol' => 'signup', 'status' => ENROL_INSTANCE_ENABLED]);
        foreach ($signupinstances as $instance) {
            self::signup_enrol_user($user->username, $instance);
        }
    }

    /**
     * Enrols a user using the specified signup enrolment instance.
     *
     * @param string $username Username to enrol.
     * @param stdClass $instance Signup enrolment instance.
     * @return int Success indicator.
     */
    public static function signup_enrol_user($username, stdClass $instance) {
        global $DB;

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return 0;
        }

        $conditions = ['id' => $instance->courseid];
        $course = $DB->get_record('course', $conditions);

        if (!$course) {
            return 0;
        }

        $today = time();
        $today = make_timestamp(
            date('Y', $today),
            date('m', $today),
            date('d', $today),
            date('H', $today),
            date('i', $today),
            date('s', $today)
        );

        $timestart = $today;
        $timeend = 0;

        $plugin = enrol_get_plugin('signup');

        if ($instance->enrolperiod) {
            $timeend = $timestart + $instance->enrolperiod;
        }

        $plugin->enrol_user($instance, $user->id, $instance->roleid, $timestart, $timeend);

        return 1;
    }
}
