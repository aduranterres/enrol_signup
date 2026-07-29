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
 * Signup enrolment plugin.
 *
 * This plugin allows you to set courses to enrol users to when they sign up to Moodle
 *
 * @package    enrol_signup
 * @copyright  2011 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/enrol/locallib.php");

/**
 * Signup enrolment plugin implementation.
 *
 * @package enrol_signup
 */
class enrol_signup_plugin extends enrol_plugin {
    /**
     * Indicates whether assigned roles are protected from changes.
     *
     * @return bool
     */
    public function roles_protected() {
        // Users with role assign cap may tweak the roles later.
        return false;
    }

    /**
     * Indicates whether users can be unenrolled from an instance.
     *
     * @param stdClass $instance Enrolment instance.
     * @return bool
     */
    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    /**
     * Indicates whether an instance allows manual management of enrolments.
     *
     * @param stdClass $instance Enrolment instance.
     * @return bool
     */
    public function allow_manage(stdClass $instance) {
        return true;
    }

    /**
     * Indicates whether the enrolment link should be displayed.
     *
     * @param stdClass $instance Enrolment instance.
     * @return bool
     */
    public function show_enrolme_link(stdClass $instance) {
        return false;
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/guest:config', $context);
    }

    /**
     * Sets up navigation entries.
     *
     * @param object $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'signup') {
             throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);

        if (has_capability('enrol/signup:config', $context)) {
            $managelink = new moodle_url('/enrol/signup/edit.php', ['courseid' => $instance->courseid,
                        'id' => $instance->id]);
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'signup') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = [];

        if (has_capability('enrol/signup:config', $context)) {
            $editlink = new moodle_url("/enrol/signup/edit.php", ['courseid' => $instance->courseid,
                        'id' => $instance->id]);
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon(
                't/edit',
                get_string('edit'),
                'core',
                ['class' => 'icon']
            ));
        }

        return $icons;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = context_course::instance($courseid);

        if (!has_capability('moodle/course:enrolconfig', $context) || !has_capability('enrol/signup:config', $context)) {
            return null;
        }

        return new moodle_url('/enrol/signup/edit.php', ['courseid' => $courseid]);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/signup:config', $context);
    }
}
