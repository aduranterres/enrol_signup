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


defined('MOODLE_INTERNAL') || die();


/**
 * Event handler for signup enrol plugin.
 */
class enrol_signup_handler {

    public static function user_created (\core\event\user_created $event) {
        global $CFG, $DB;

        $user = $event->get_record_snapshot('user', $event->objectid);

        $signupinstances = $DB->get_records('enrol', array('enrol' => 'signup'));
        foreach ($signupinstances as $si) {
            $courseid = $si->courseid;
            self::signup_enrol_user ($user->username, $courseid, $si->roleid);
        }
    }

    public static function signup_enrol_user ($username, $courseid, $roleid = 5) {
        global $CFG, $DB, $PAGE;

        require_once("$CFG->dirroot/enrol/locallib.php");

        $conditions = array ('username' => $username);
        $user = $DB->get_record('user', $conditions);
        $conditions = array ('id' => $courseid);
        $course = $DB->get_record('course', $conditions);

        // First, check if user is already enroled but suspended, so we just need to enable it.
        $conditions = array ('courseid' => $courseid, 'enrol' => 'manual');
        $enrol = $DB->get_record('enrol', $conditions);

        $conditions = array ('username' => $username);
        $user = $DB->get_record('user', $conditions);

        $conditions = array ('enrolid' => $enrol->id, 'userid' => $user->id);
        $ue = $DB->get_record('user_enrolments', $conditions);

        if ($ue) {
            // User already enroled but suspended. Just activate enrolment and return.
            $ue->status = 0; // Active.
            $DB->update_record('user_enrolments', $ue);
            return 1;
        }

        $manager = new course_enrolment_manager($PAGE, $course);
        $instances = $manager->get_enrolment_instances();
        $plugins = $manager->get_enrolment_plugins();

        $today = time();
        $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), date ('H', $today),
                    date ('i', $today), date ('s', $today));

        $timestart = $today;
        $timeend = 0;

        foreach ($instances as $instance) {
            if ($instance->enrol == 'signup') {
                break;
            }
        }

        $plugin = $plugins['signup'];

        if ( $instance->enrolperiod) {
            $timeend   = $timestart + $instance->enrolperiod;
        }
        $plugin->enrol_user($instance, $user->id, $roleid, $timestart, $timeend);

        return 1;
    }


}
