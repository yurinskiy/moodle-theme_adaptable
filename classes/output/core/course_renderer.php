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
 * Version details
 *
 * @package    theme_adaptable
 * @copyright  2015-2019 Jeremy Hopkins (Coventry University)
 * @copyright  2015-2019 Fernando Acedo (3-bits.com)
 * @copyright  2017-2019 Manoj Solanki (Coventry University)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace theme_adaptable\output\core;

defined('MOODLE_INTERNAL') || die();

/******************************************************************************************
 *
 * Overridden Core Course Renderer for Adaptable theme
 *
 * @package    theme_adaptable
 * @copyright 2015 Jeremy Hopkins (Coventry University)
 * @copyright 2015 Fernando Acedo (3-bits.com)
 * @copyright 2015 Moodlerooms Inc. (http://www.moodlerooms.com) (activity further information functionality)
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

use cm_info;
use core_text;
use html_writer;
use context_course;
use moodle_url;
use coursecat_helper;
use lang_string;
use core_course_list_element;
use stdClass;
use renderable;
use action_link;

/**
 * Course renderer implementation.
 *
 * @package   theme_adaptable
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_renderer extends \core_course_renderer {
    protected function coursecat_tree(coursecat_helper $chelper, $coursecat) {
        $type = theme_adaptable_get_setting('frontpagerenderer');

        if ($type == 5) {
            $chelper->set_attributes(array('class' => 'tiles-grid'));
        }

        return parent::coursecat_tree($chelper, $coursecat);
    }

    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null) {
        $type = theme_adaptable_get_setting('frontpagerenderer');

        if ($type == 5) {
            $chelper->set_attributes(array('class' => 'tiles-grid'));
        }

        return parent::coursecat_courses($chelper, $courses, $totalcount);
    }

    /**
     * Returns HTML to print list of available courses for the frontpage
     *
     * @return string
     */
    public function frontpage_available_courses() {
        $type = theme_adaptable_get_setting('frontpagerenderer');

        if ($type != 5) {
            return parent::frontpage_available_courses();
        }

        global $CFG;
        $chelper = new coursecat_helper();
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
        set_courses_display_options(array(
            'recursive' => true,
            'limit' => $CFG->frontpagecourselimit,
            'viewmoreurl' => new moodle_url('/course/index.php'),
            'viewmoretext' => new lang_string('fulllistofcourses')));
        $chelper->set_attributes(array('class' => 'frontpage-course-list-all tiles-grid'));
        $courses = \core_course_category::get(0)->get_courses($chelper->get_courses_display_options());
        $totalcount = \core_course_category::get(0)->get_courses_count($chelper->get_courses_display_options());
        if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', context_system::instance())) {
            // Print link to create a new course, for the 1st available category.
            return $this->add_new_course_button();
        }
        return $this->coursecat_courses($chelper, $courses, $totalcount);
    }

    /**
     * Build the HTML for the module chooser javascript popup
     *
     * @param array $modules A set of modules as returned form
     * @see get_module_metadata
     * @param object $course The course that will be displayed
     * @return string The composed HTML for the module
     */
    public function course_modchooser($modules, $course) {
        if (!$this->page->requires->should_create_one_time_item_now('core_course_modchooser')) {
            return '';
        }
        $modchooser = new \theme_adaptable\output\core_course\output\modchooser($course, $modules);
        return $this->render($modchooser);
    }

    /**
     * Render course tiles in the fron page
     *
     * @param coursecat_helper $chelper
     * @param string $course
     * @param string $additionalclasses
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        global $CFG, $PAGE;
        $type = theme_adaptable_get_setting('frontpagerenderer');

        if ($type == 5) {

            if (!isset($this->strings->summary)) {
                $this->strings->summary = get_string('summary');
            }
            if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
                return '';
            }
            if ($course instanceof stdClass) {
                if ($CFG->version < 2018051799) {
                    require_once($CFG->libdir.'/coursecatlib.php');
                }
                $course = new course_in_list($course);
            }
            $content = '';
            $coursename = $chelper->get_course_formatted_name($course);

            $btnInfo = '';
            $popup = '';
            $summary = '';
            $contacts = '';

            $coursecontacts = theme_adaptable_get_setting('tilesshowcontacts');

            if ($course->has_summary()) {
                $summary .= $chelper->get_course_formatted_summary($course, array(
                    'overflowdiv' => true,
                    'noclean' => true,
                    'para' => false
                ));
            }

            if ($coursecontacts) {
                $coursecontacttitle = theme_adaptable_get_setting('tilescontactstitle');
                // Display course contacts. See course_in_list::get_course_contacts().
                if ($course->has_course_contacts()) {
                    $contacts .= html_writer::start_tag('ul', array('class' => 'teachers'));
                    foreach ($course->get_course_contacts() as $userid => $coursecontact) {
                        $name = ($coursecontacttitle ? $coursecontact['rolename'].': ' : html_writer::tag('i', '&nbsp;',
                                array('class' => 'fa fa-graduation-cap')) ).
                            html_writer::link(new moodle_url('/user/view.php',
                                array('id' => $userid, 'course' => SITEID)),
                                $coursecontact['username']);
                        $contacts .= html_writer::tag('li', $name);
                    }
                    $contacts .= html_writer::end_tag('ul'); // Teachers.
                }
            }

            if ($summary != '' || $contacts != '') {
                $btnInfo = html_writer::link('#popupCourse' . $course->id, html_writer::tag('i', '', array('class' => 'fa fa-info')), array(
                    'class' => 'tile-link',
                    'onclick' => '$("body").css("overflow", "hidden");'
                ));

                $popup .= html_writer::start_tag('div', array(
                    'class' => 'overlay',
                    'id' => 'popupCourse' . $course->id
                ));
                $popup .= html_writer::tag('div', $coursename, array(
                    'class' => 'background',
                    'onclick' => 'location.hash="#_";$("body").css("overflow", "auto");'
                ));
                $popup .= html_writer::start_tag('div', array('class' => 'popup'));
                $popup .= html_writer::start_tag('div', array('class' => 'popup-header'));
                $popup .= html_writer::tag('h4', $coursename, array());
                $popup .= html_writer::link('#_', '&times;', array(
                    'class' => 'close',
                    'onclick' => '$("body").css("overflow", "auto");'
                ));
                $popup .= html_writer::end_tag('div'); // End .popup-header.

                $popup .= html_writer::start_tag('div', array('class' => 'popup-content'));

                if($contacts != '') {
                    $popup .= html_writer::tag('div', $contacts, array('class' => 'contacts'));
                }

                if ($summary != '') {
                    $popup .= html_writer::tag('div', $summary, array('class' => 'summary'));
                }

                $popup .= html_writer::end_tag('div'); // End .content.
                $popup .= html_writer::end_tag('div'); // End .popup.
                $popup .= html_writer::end_tag('div'); // End .overlay.
            }

            // Display course image.
            $urlImage = '';
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();

                if ($isimage) {
                    $urlImage = file_encode_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                        $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                }
            }
            if (strlen($urlImage) == 0) {
                // Default image.
                $urlImage = $PAGE->theme->setting_file_url('frontpagerendererdefaultimage', 'frontpagerendererdefaultimage');
            }
            if (strlen($urlImage) == 0) {
                // Empty default image.
                $urlImage = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIj8+PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMjAiIGhlaWdodD0iMzIwIj48cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJyZ2IoMTYyLCAxNTUsIDI1NCkiIC8+PGNpcmNsZSBjeD0iMCIgY3k9IjAiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjIyIiBzdHlsZT0ib3BhY2l0eTowLjEzMjY2NjY2NjY2NjY3O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMzIwIiBjeT0iMCIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiMyMjIiIHN0eWxlPSJvcGFjaXR5OjAuMTMyNjY2NjY2NjY2Njc7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIwIiBjeT0iMzIwIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzIyMiIgc3R5bGU9Im9wYWNpdHk6MC4xMzI2NjY2NjY2NjY2NztzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjMyMCIgY3k9IjMyMCIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiMyMjIiIHN0eWxlPSJvcGFjaXR5OjAuMTMyNjY2NjY2NjY2Njc7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSI1My4zMzMzMzMzMzMzMzMiIGN5PSIwIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4xMDY2NjY2NjY2NjY2NztzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjUzLjMzMzMzMzMzMzMzMyIgY3k9IjMyMCIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGQiIHN0eWxlPSJvcGFjaXR5OjAuMTA2NjY2NjY2NjY2Njc7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIxMDYuNjY2NjY2NjY2NjciIGN5PSIwIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4wNTQ2NjY2NjY2NjY2Njc7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIxMDYuNjY2NjY2NjY2NjciIGN5PSIzMjAiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZGRkIiBzdHlsZT0ib3BhY2l0eTowLjA1NDY2NjY2NjY2NjY2NztzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjE2MCIgY3k9IjAiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjIyIiBzdHlsZT0ib3BhY2l0eTowLjExNTMzMzMzMzMzMzMzO3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMTYwIiBjeT0iMzIwIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzIyMiIgc3R5bGU9Im9wYWNpdHk6MC4xMTUzMzMzMzMzMzMzMztzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjIxMy4zMzMzMzMzMzMzMyIgY3k9IjAiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjIyIiBzdHlsZT0ib3BhY2l0eTowLjA5ODtzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjIxMy4zMzMzMzMzMzMzMyIgY3k9IjMyMCIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiMyMjIiIHN0eWxlPSJvcGFjaXR5OjAuMDk4O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMjY2LjY2NjY2NjY2NjY3IiBjeT0iMCIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGQiIHN0eWxlPSJvcGFjaXR5OjAuMDM3MzMzMzMzMzMzMzMzO3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMjY2LjY2NjY2NjY2NjY3IiBjeT0iMzIwIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4wMzczMzMzMzMzMzMzMzM7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIwIiBjeT0iNTMuMzMzMzMzMzMzMzMzIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzIyMiIgc3R5bGU9Im9wYWNpdHk6MC4wNDY7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIzMjAiIGN5PSI1My4zMzMzMzMzMzMzMzMiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjIyIiBzdHlsZT0ib3BhY2l0eTowLjA0NjtzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjUzLjMzMzMzMzMzMzMzMyIgY3k9IjUzLjMzMzMzMzMzMzMzMyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiMyMjIiIHN0eWxlPSJvcGFjaXR5OjAuMDgwNjY2NjY2NjY2NjY3O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMTA2LjY2NjY2NjY2NjY3IiBjeT0iNTMuMzMzMzMzMzMzMzMzIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzIyMiIgc3R5bGU9Im9wYWNpdHk6MC4xMTUzMzMzMzMzMzMzMztzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjE2MCIgY3k9IjUzLjMzMzMzMzMzMzMzMyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGQiIHN0eWxlPSJvcGFjaXR5OjAuMTA2NjY2NjY2NjY2Njc7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIyMTMuMzMzMzMzMzMzMzMiIGN5PSI1My4zMzMzMzMzMzMzMzMiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZGRkIiBzdHlsZT0ib3BhY2l0eTowLjEyNDtzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjI2Ni42NjY2NjY2NjY2NyIgY3k9IjUzLjMzMzMzMzMzMzMzMyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGQiIHN0eWxlPSJvcGFjaXR5OjAuMTI0O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMCIgY3k9IjEwNi42NjY2NjY2NjY2NyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGQiIHN0eWxlPSJvcGFjaXR5OjAuMTI0O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMzIwIiBjeT0iMTA2LjY2NjY2NjY2NjY3IiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4xMjQ7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSI1My4zMzMzMzMzMzMzMzMiIGN5PSIxMDYuNjY2NjY2NjY2NjciIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjIyIiBzdHlsZT0ib3BhY2l0eTowLjEzMjY2NjY2NjY2NjY3O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMTA2LjY2NjY2NjY2NjY3IiBjeT0iMTA2LjY2NjY2NjY2NjY3IiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzIyMiIgc3R5bGU9Im9wYWNpdHk6MC4xNTtzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjE2MCIgY3k9IjEwNi42NjY2NjY2NjY2NyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiMyMjIiIHN0eWxlPSJvcGFjaXR5OjAuMDI4NjY2NjY2NjY2NjY3O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMjEzLjMzMzMzMzMzMzMzIiBjeT0iMTA2LjY2NjY2NjY2NjY3IiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzIyMiIgc3R5bGU9Im9wYWNpdHk6MC4wOTg7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIyNjYuNjY2NjY2NjY2NjciIGN5PSIxMDYuNjY2NjY2NjY2NjciIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZGRkIiBzdHlsZT0ib3BhY2l0eTowLjEyNDtzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjAiIGN5PSIxNjAiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZGRkIiBzdHlsZT0ib3BhY2l0eTowLjAyO3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMzIwIiBjeT0iMTYwIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4wMjtzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjUzLjMzMzMzMzMzMzMzMyIgY3k9IjE2MCIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiMyMjIiIHN0eWxlPSJvcGFjaXR5OjAuMDgwNjY2NjY2NjY2NjY3O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMTA2LjY2NjY2NjY2NjY3IiBjeT0iMTYwIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4wNzI7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIxNjAiIGN5PSIxNjAiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZGRkIiBzdHlsZT0ib3BhY2l0eTowLjAyO3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMjEzLjMzMzMzMzMzMzMzIiBjeT0iMTYwIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4xMjQ7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIyNjYuNjY2NjY2NjY2NjciIGN5PSIxNjAiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZGRkIiBzdHlsZT0ib3BhY2l0eTowLjEwNjY2NjY2NjY2NjY3O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMCIgY3k9IjIxMy4zMzMzMzMzMzMzMyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiMyMjIiIHN0eWxlPSJvcGFjaXR5OjAuMTE1MzMzMzMzMzMzMzM7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIzMjAiIGN5PSIyMTMuMzMzMzMzMzMzMzMiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjIyIiBzdHlsZT0ib3BhY2l0eTowLjExNTMzMzMzMzMzMzMzO3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iNTMuMzMzMzMzMzMzMzMzIiBjeT0iMjEzLjMzMzMzMzMzMzMzIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzIyMiIgc3R5bGU9Im9wYWNpdHk6MC4wODA2NjY2NjY2NjY2Njc7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIxMDYuNjY2NjY2NjY2NjciIGN5PSIyMTMuMzMzMzMzMzMzMzMiIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZGRkIiBzdHlsZT0ib3BhY2l0eTowLjEwNjY2NjY2NjY2NjY3O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMTYwIiBjeT0iMjEzLjMzMzMzMzMzMzMzIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4xNDEzMzMzMzMzMzMzMztzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjIxMy4zMzMzMzMzMzMzMyIgY3k9IjIxMy4zMzMzMzMzMzMzMyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGQiIHN0eWxlPSJvcGFjaXR5OjAuMTI0O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMjY2LjY2NjY2NjY2NjY3IiBjeT0iMjEzLjMzMzMzMzMzMzMzIiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4wNTQ2NjY2NjY2NjY2Njc7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIwIiBjeT0iMjY2LjY2NjY2NjY2NjY3IiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RkZCIgc3R5bGU9Im9wYWNpdHk6MC4xMDY2NjY2NjY2NjY2NztzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjMyMCIgY3k9IjI2Ni42NjY2NjY2NjY2NyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGQiIHN0eWxlPSJvcGFjaXR5OjAuMTA2NjY2NjY2NjY2Njc7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSI1My4zMzMzMzMzMzMzMzMiIGN5PSIyNjYuNjY2NjY2NjY2NjciIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZGRkIiBzdHlsZT0ib3BhY2l0eTowLjA4OTMzMzMzMzMzMzMzMztzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjEwNi42NjY2NjY2NjY2NyIgY3k9IjI2Ni42NjY2NjY2NjY2NyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiMyMjIiIHN0eWxlPSJvcGFjaXR5OjAuMDQ2O3N0cm9rZS13aWR0aDoxMy4zMzMzMzMzMzMzMzNweDsiIC8+PGNpcmNsZSBjeD0iMTYwIiBjeT0iMjY2LjY2NjY2NjY2NjY3IiByPSI0Ni42NjY2NjY2NjY2NjciIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzIyMiIgc3R5bGU9Im9wYWNpdHk6MC4wNjMzMzMzMzMzMzMzMzM7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48Y2lyY2xlIGN4PSIyMTMuMzMzMzMzMzMzMzMiIGN5PSIyNjYuNjY2NjY2NjY2NjciIHI9IjQ2LjY2NjY2NjY2NjY2NyIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjIyIiBzdHlsZT0ib3BhY2l0eTowLjA5ODtzdHJva2Utd2lkdGg6MTMuMzMzMzMzMzMzMzMzcHg7IiAvPjxjaXJjbGUgY3g9IjI2Ni42NjY2NjY2NjY2NyIgY3k9IjI2Ni42NjY2NjY2NjY2NyIgcj0iNDYuNjY2NjY2NjY2NjY3IiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGQiIHN0eWxlPSJvcGFjaXR5OjAuMDI7c3Ryb2tlLXdpZHRoOjEzLjMzMzMzMzMzMzMzM3B4OyIgLz48L3N2Zz4=';
            }

            $content .= html_writer::start_tag('div', array('class' => 'tile'));

            $tile = '';

            $tile .= html_writer::start_tag('div', array('class' => 'tile-header'));
            $tile .= html_writer::link(
                new moodle_url('/course/view.php', array('id' => $course->id)),
                html_writer::empty_tag('img', array('src' => $urlImage, 'class' => 'tile-img', 'alt' => $coursename)),
                array('class' => 'tile-title')
            );
            $tile .= html_writer::end_tag('div'); // End .tile-header.

            $tile .= html_writer::start_tag('div', array('class' => 'tile-body'));

            $tile .= $btnInfo;
            $tile .= html_writer::tag('h5', html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
                $coursename, array('title' => $coursename)), array('class' => 'tile-title'));


            $tile .= html_writer::end_tag('div'); // End .tile-body.

            $content .= html_writer::tag('div', $tile, array(
                'class' => 'tile-inner' . ($course->visible ? '' : ' dimmed')
            ));

            $content .= html_writer::end_tag('div'); // End .tile.

            $content .= $popup;

            return $content;
        }

        if ($type == 3 || $this->output->body_id() != 'page-site-index') {
            return parent::coursecat_coursebox($chelper, $course, $additionalclasses = '');
        }

        $additionalcss = '';

        if ($type == 2) {
            $additionalcss = 'hover';
        }

        if ($type == 4) {
            $additionalcss = 'hover covtiles';
        }

        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }

        $showcourses = $chelper->get_show_courses();

        if ($showcourses <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        $content = '';
        $classes = trim($additionalclasses);

        if ($showcourses < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $classes .= ' collapsed';
        }

        // Number of tiles per row: 12=1 tile / 6=2 tiles / 4 (default)=3 tiles / 3=4 tiles / 2=6 tiles.
        $spanclass = $this->page->theme->settings->frontpagenumbertiles;

        // Display course tiles depending the number per row.
        $content .= html_writer::start_tag('div',
              array('class' => 'col-xs-12 col-sm-'.$spanclass.' panel panel-default coursebox '.$additionalcss));

        // Add the course name.
        $coursename = $chelper->get_course_formatted_name($course);
        if (($type == 1) || ($showcourses < self::COURSECAT_SHOW_COURSES_EXPANDED)) {
            $content .= html_writer::start_tag('div', array('class' => 'panel-heading'));
            $content .= html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
                $coursename, array('class' => $course->visible ? '' : 'dimmed', 'title' => $coursename));
        }

        // If we display course in collapsed form but the course has summary or course contacts, display the link to the info page.
        if ($showcourses < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $arrow = html_writer::tag('span', '', array('class' => 'fa fp-chevron ml-1'));
            $content .= html_writer::link('#coursecollapse' . $course->id , '' . $arrow,
                array('class' => 'fpcombocollapse collapsed', 'data-toggle' => 'collapse',
                      'data-parent' => '#frontpage-category-combo'));
        }

        if ($type == 1) {
            $content .= $this->coursecat_coursebox_enrolmenticons($course);
        }

        if (($type == 1) || ($showcourses < self::COURSECAT_SHOW_COURSES_EXPANDED)) {
            $content .= html_writer::end_tag('div'); // End .panel-heading.
        }

        if ($showcourses < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $content .= html_writer::start_tag('div', array('id' => 'coursecollapse' . $course->id,
                'class' => 'panel-collapse collapse'));
        }

        $content .= html_writer::start_tag('div', array('class' => 'panel-body clearfix'));

        // This gets the course image or files.
        $content .= $this->coursecat_coursebox_content($chelper, $course, $type);

        if ($showcourses >= self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $icondirection = 'left';
            if ('ltr' === get_string('thisdirection', 'langconfig')) {
                $icondirection = 'right';
            }
            $arrow = html_writer::tag('span', '', array('class' => 'fa fa-chevron-'.$icondirection));
            $btn = html_writer::tag('span', get_string('course', 'theme_adaptable') . ' ' .
                    $arrow, array('class' => 'get_stringlink'));

            if (($type != 4) || (empty($this->page->theme->settings->covhidebutton))) {
                $content .= html_writer::link(new moodle_url('/course/view.php',
                    array('id' => $course->id)), $btn, array('class' => " coursebtn submit btn btn-info btn-sm"));
            }
        }

        $content .= html_writer::end_tag('div'); // End .panel-body.

        if ($showcourses < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $content .= html_writer::end_tag('div'); // End .collapse.
        }

        $content .= html_writer::end_tag('div'); // End .panel.

        return $content;
    }

    /**
     * Returns enrolment icons
     *
     * @param string $course
     * @return string
     */
    protected function coursecat_coursebox_enrolmenticons($course) {
        $content = '';
        if ($icons = enrol_get_course_info_icons($course)) {
            $content .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
            foreach ($icons as $pixicon) {
                $content .= $this->render($pixicon);
            }
            $content .= html_writer::end_tag('div'); // Enrolmenticons.
        }
        return $content;
    }

    /**
     * Returns course box content for categories
     *
     * Type - 1 = No Overlay.
     * Type - 2 = Overlay.
     * Type - 3 = Moodle default.
     * Type - 4 = Coventry tiles.
     *
     * @param coursecat_helper $chelper
     * @param string $course
     * @param int $type = 3
     * @return string
     */
    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course, $type = 3) {
        global $CFG;

        if ($course instanceof stdClass) {
            $course = new \core_course_list_element($course);
        }
        if ($type == 3 || $this->output->body_id() != 'page-site-index') {
            return parent::coursecat_coursebox_content($chelper, $course);
        }
        $content = '';

        // Display course overview files.
        $contentimages = '';
        $contentfiles = '';
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                if ($type == 1) {
                    $contentimages .= html_writer::start_tag('div', array('class' => 'courseimage'));
                    $link = new moodle_url('/course/view.php', array('id' => $course->id));
                    $contentimages .= html_writer::link($link, html_writer::empty_tag('img', array('src' => $url)));
                    $contentimages .= html_writer::end_tag('div');
                } else {
                    $cimboxattr = array(
                        'class' => 'cimbox',
                        'style' => 'background-image: url(\''.$url.'\');'
                    );
                    if ($type == 4) {
                        $cimtag = 'a';
                        $cimboxattr['href'] = new moodle_url('/course/view.php', array('id' => $course->id));
                    } else {
                        $cimtag = 'div';
                    }
                    $contentimages .= html_writer::tag($cimtag, '', $cimboxattr);
                }
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
                $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
                $contentfiles .= html_writer::tag('span',
                        html_writer::link($url, $filename),
                        array('class' => 'coursefile fp-filename-icon'));
            }
        }
        if (strlen($contentimages) == 0 && (($type == 2) || ($type == 4))) {
            // Default image.
            $cimboxattr = array('class' => 'cimbox');
            $url = $this->page->theme->setting_file_url('frontpagerendererdefaultimage', 'frontpagerendererdefaultimage');
            if (!empty($url)) {
                $cimboxattr['style'] = 'background-image: url(\''.$url.'\');';
            }
            if ($type == 2) {
                $cimtag = 'div';
            } else { // Type is 4.
                $cimboxattr['href'] = new moodle_url('/course/view.php', array('id' => $course->id));
                $cimtag = 'a';
            }
            $contentimages .= html_writer::tag($cimtag, '', $cimboxattr);
        }
        $content .= $contentimages.$contentfiles;

        if (($type == 2) || ($type == 4)) {
            $content .= $this->coursecat_coursebox_enrolmenticons($course);
            $content .= html_writer::start_tag('div', array(
                'class' => 'coursebox-content'
                )
            );
            $coursename = $chelper->get_course_formatted_name($course);
            $content .= html_writer::start_tag('a', array('href' => new moodle_url('/course/view.php', array('id' => $course->id))));
            $content .= html_writer::tag('h3', $coursename, array('class' => $course->visible ? '' : 'dimmed'));
            $content .= html_writer::end_tag('a');
        }
        $content .= html_writer::start_tag('div', array('class' => 'summary'));
        // Display course summary.
        if ($course->has_summary()) {
            $summs = $chelper->get_course_formatted_summary($course, array('overflowdiv' => false, 'noclean' => true,
                    'para' => false));
            $summs = strip_tags($summs);
            $truncsum = mb_strimwidth($summs, 0, 70, "...", 'utf-8');
            $content .= html_writer::tag('span', $truncsum, array('title' => $summs));
        }
        $coursecontacts = theme_adaptable_get_setting('tilesshowcontacts');
        if ($coursecontacts) {
            $coursecontacttitle = theme_adaptable_get_setting('tilescontactstitle');
            // Display course contacts. See ::get_course_contacts().
            if ($course->has_course_contacts()) {
                $content .= html_writer::start_tag('ul', array('class' => 'teachers'));
                foreach ($course->get_course_contacts() as $userid => $coursecontact) {
                    $cct = ($coursecontacttitle ? $coursecontact['rolename'].': ' : html_writer::tag('i', '&nbsp;',
                        array('class' => 'fa fa-graduation-cap')));
                    $name = html_writer::link(new moodle_url('/user/view.php',
                        array('id' => $userid, 'course' => $course->id)),
                        $cct.$coursecontact['username']);
                    $content .= html_writer::tag('li', $name);
                }
                $content .= html_writer::end_tag('ul'); // Teachers.
            }
        }
        $content .= html_writer::end_tag('div'); // Summary.

        // Display course category if necessary (for example in search results).
        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT) {
            require_once($CFG->libdir. '/coursecatlib.php');
            if ($cat = core_course_category::get($course->category, IGNORE_MISSING)) {
                $content .= html_writer::start_tag('div', array('class' => 'coursecat'));
                $content .= get_string('category').': '.
                        html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)),
                                $cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
                        $content .= html_writer::end_tag('div'); // Coursecat.
            }
        }
        if (($type == 2) || ($type == 4)) {
            $content .= html_writer::end_tag('div');
            // End coursebox-content.
        }

        $content .= html_writer::tag('div', '', array('class' => 'boxfooter')); // Coursecat.

        return $content;
    }

    /**
     * Frontpage course list
     *
     * @return string
     */
    public function frontpage_my_courses() {
        global $CFG, $DB;
        $output = '';
        if (!isloggedin() or isguestuser()) {
            return '';
        }
        // Calls a core renderer method (render_mycourses) to get list of a user's current courses that they are enrolled on.
        $sortedcourses = $this->render_mycourses();

        if (!empty($sortedcourses) || !empty($rcourses) || !empty($rhosts)) {
            $chelper = new coursecat_helper();
            if (count($sortedcourses) > $CFG->frontpagecourselimit) {
                // There are more enrolled courses than we can display, display link to 'My courses'.
                $totalcount = count($sortedcourses);
                $courses = array_slice($sortedcourses, 0, $CFG->frontpagecourselimit, true);
                $chelper->set_courses_display_options(array(
                        'viewmoreurl' => new moodle_url('/my/'),
                        'viewmoretext' => new lang_string('mycourses')
                ));
            } else {
                // All enrolled courses are displayed, display link to 'All courses' if there are more courses in system.
                $chelper->set_courses_display_options(array(
                        'viewmoreurl' => new moodle_url('/course/index.php'),
                        'viewmoretext' => new lang_string('fulllistofcourses')
                ));
                $totalcount = $DB->count_records('course') - 1;
            }
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->set_attributes(
                array('class' => 'frontpage-course-list-enrolled'));
            $output .= $this->coursecat_courses($chelper, $sortedcourses, $totalcount);

            if (!empty($rcourses)) {
                $output .= html_writer::start_tag('div', array('class' => 'courses'));
                foreach ($rcourses as $course) {
                    $output .= $this->frontpage_remote_course($course);
                }
                $output .= html_writer::end_tag('div');
            } else if (!empty($rhosts)) {
                $output .= html_writer::start_tag('div', array('class' => 'courses'));
                foreach ($rhosts as $host) {
                    $output .= $this->frontpage_remote_host($host);
                }
                $output .= html_writer::end_tag('div');
            }
        }
        return $output;
    }

    /**
     * Overridden. Renders html to display a name with the link to the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link.
     *
     * Note that for course modules that never have separate pages (i.e. labels)
     * this function return an empty string.
     *
     * This method has only been overriden in order to strip -24 and similar from icon image filenames
     * to allow using of local theme icons in /pix_core/f.
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name(cm_info $mod, $displayoptions = array()) {
        // If use adaptable icons is set to false, then just run parent method as normal.
        if (empty($this->page->theme->settings->coursesectionactivityuseadaptableicons)) {
            return parent::course_section_cm_name($mod, $displayoptions);
        }

        if (!$mod->uservisible && empty($mod->availableinfo)) {
            // Nothing to be displayed to the user.
            return '';
        }

        if (!$mod->url) {
            return '';
        }

        $templateclass = new \core_course\output\course_module_name($mod, $this->page->user_is_editing(), $displayoptions);
        $data = $this->adaptable_course_section_cm_name($mod, $templateclass);

        return $this->output->render_from_template('core/inplace_editable', $data['templatedata']);
    }

    /**
     * Common course_section_cm_name code.
     *
     * @param cm_info $mod
     * @param course_module_name $templateclass
     *
     * @return array('templatedata', 'groupinglabel').
     */
    protected function adaptable_course_section_cm_name(cm_info $mod, $templateclass) {
        $url = $mod->url;

        // Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        /* Avoid unnecessary duplication: if e.g. a forum name already
           includes the word forum (or Forum, etc) then it is unhelpful
           to include that in the accessible description that is added. */
        if (false !== strpos(core_text::strtolower($instancename),
                core_text::strtolower($altname))) {
                    $altname = '';
        }

        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' '.$altname);
        }

        /* For items which are hidden but available to current user
           ($mod->uservisible), we show those as dimmed only if the user has
           viewhiddenactivities, so that teachers see 'items which might not
           be available to some students' dimmed but students do not see 'item
           which is actually available to current student' dimmed. */
        $linkclasses = '';
        $accesstext = '';
        $textclasses = '';
        if ($mod->uservisible) {

            $conditionalhidden = $this->is_cm_conditionally_hidden($mod);
            $accessiblebutdim = (!$mod->visible || $conditionalhidden) &&
            has_capability('moodle/course:viewhiddenactivities', $mod->context);
            if ($accessiblebutdim) {
                $linkclasses .= ' dimmed';
                $textclasses .= ' dimmed_text';
                if ($conditionalhidden) {
                    $linkclasses .= ' conditionalhidden';
                    $textclasses .= ' conditionalhidden';
                }
                // Show accessibility note only if user can access the module himself.
                $accesstext = get_accesshide(get_string('hiddenfromstudents').':'. $mod->modfullname);
            }

        } else {
            $linkclasses .= ' dimmed';
            $textclasses .= ' dimmed_text';
        }

        /* Get on-click attribute value if specified and decode the onclick - it
           has already been encoded for display. */
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        $groupinglabel = $mod->get_grouping_label($textclasses);

        /* Display link itself.
           Get icon url, but strip -24, -64, -256  etc from the end of filetype icons so we
           only need to provide one SVG, see MDL-47082. (Used from snap theme). */
        $imageurl = \preg_replace('/-\d\d\d?$/', '', $mod->get_icon_url());

        $activitylink = html_writer::empty_tag('img', array('src' => $imageurl,
                'class' => 'iconlarge activityicon', 'alt' => ' ', 'role' => 'presentation')) . $accesstext .
                html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));

        $outputlink = '';
        if ($mod->uservisible) {
            $outputlink .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick)) .
            $groupinglabel;
        } else {
            /* We may be displaying this just in order to show information
               about visibility, without the actual link ($mod->uservisible).*/
            $outputlink .= html_writer::tag('div', $activitylink, array('class' => $textclasses)) .
            $groupinglabel;
        }

        $templatedata = $templateclass->export_for_template($this->output);

        // Variable displayvalue element is purposely overriden below with link including custom icon created above.
        $templatedata['displayvalue'] = $outputlink;

        return array('templatedata' => $templatedata, 'groupinglabel' => $groupinglabel);
    }

    // New methods added for activity styling below.  Adapted from snap theme by Moodleroooms.

    /**
     * Overridden.  Customise display.  Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * core_course_renderer::course_section_cm_name()
     * core_course_renderer::course_section_cm_text()
     * core_course_renderer::course_section_cm_availability()
     * core_course_renderer::course_section_cm_completion()
     * course_get_cm_edit_actions()
     * core_course_renderer::course_section_cm_edit_actions()
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        // We return empty string (because course module will not be displayed at all) if
        // 1) The activity is not visible to users and
        // 2) The 'availableinfo' is empty, i.e. the activity was hidden in a way that leaves no info, such as using the
        // eye icon.

        if ( (method_exists($mod, 'is_visible_on_course_page')) && (!$mod->is_visible_on_course_page())
                || (!$mod->uservisible && empty($mod->availableinfo)) ) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $output .= html_writer::start_tag('div');

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Start a wrapper for the actual content to keep the indentation consistent.
        $output .= html_writer::start_tag('div', array('class' => 'ad-activity-wrapper'));

        // Display the link to the module (or do nothing if module has no url).
        $cmname = $this->course_section_cm_name($mod, $displayoptions);

        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
            $output .= $cmname;

            // Module can put text after the link (e.g. forum unread).
            $output .= $mod->afterlink;

            // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // End .activityinstance class.
        }

        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case icons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.
        $contentpart = $this->course_section_cm_text($mod, $displayoptions);
        $url = $mod->url;
        if (empty($url)) {
            $output .= $contentpart;
        }

        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }

        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        if (!empty($modicons)) {
            $output .= html_writer::start_tag('div', array('class' => 'actions-right'));
            $output .= html_writer::span($modicons, 'actions');
            $output .= html_writer::end_tag('div');
        }

        // Get further information.
        $settingname = 'coursesectionactivityfurtherinformation'. $mod->modname;
        if (isset ($this->page->theme->settings->$settingname) && $this->page->theme->settings->$settingname == true) {
            $output .= html_writer::start_tag('div', array('class' => 'ad-activity-meta-container'));
            $output .= $this->course_section_cm_get_meta($mod);
            $output .= html_writer::end_tag('div');
            // TO BE DELETED    $output .= '<div style="clear: both;"></div>'; ????
        }

        // If there is content AND a link, then display the content here.
        // (AFTER any icons). Otherwise it was displayed before.
        if (!empty($url)) {
            $output .= $contentpart;
        }

        // Show availability info (if module is not available).
        $output .= $this->course_section_cm_availability($mod, $displayoptions);

        $output .= html_writer::end_tag('div');

        // End of indentation div.
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Get the module meta data for a specific module.
     *
     * @param cm_info $mod
     * @return string
     */
    protected function course_section_cm_get_meta(cm_info $mod) {
        global $COURSE;

        $content = '';

        if (is_guest(context_course::instance($COURSE->id))) {
            return '';
        }

        // If module is not visible to the user then don't bother getting meta data.
        if (!$mod->uservisible) {
            return '';
        }

        // Do we have an activity function for this module for returning meta data?
        $meta = \theme_adaptable\activity::module_meta($mod);
        if (($meta == null) || (!$meta->is_set(true))) {
            // Can't get meta data for this module.
            return '';
        }
        $content .= '';

        $warningclass = '';
        if ($meta->submitted) {
            $warningclass = ' ad-activity-date-submitted ';
        }

        $activitycontent = $this->submission_cta($mod, $meta);

        if (!(empty($activitycontent))) {
            if ( ($mod->modname == 'assign') && ($meta->submitted) ) {
                $content .= html_writer::start_tag('span', array('class' => 'ad-activity-due-date'.$warningclass));
                $content .= $activitycontent;
                $content .= html_writer::end_tag('span') . '<br>';
            } else {
                // Only display if this is really a student on the course (i.e. not anyone who can grade an assignment).
                if (!has_capability('mod/assign:grade', $mod->context)) {
                    $content .= html_writer::start_tag('div', array('class' => 'ad-activity-mod-engagement'.$warningclass));
                    $content .= $activitycontent;
                    $content .= html_writer::end_tag('div');
                }
            }
        }

        // Activity due date.
        if (!empty($meta->extension) || !empty($meta->timeclose)) {
            if (!empty($meta->extension)) {
                $field = 'extension';
            } else if (!empty($meta->timeclose)) {
                $field = 'timeclose';
            }

            // Create URL for due date.
            $url = new \moodle_url("/mod/{$mod->modname}/view.php", ['id' => $mod->id]);
            $dateformat = get_string('strftimedate', 'langconfig');
            $labeltext = get_string('due', 'theme_adaptable', userdate($meta->$field, $dateformat));
            $warningclass = '';

            // Display assignment status (due, nearly due, overdue), as long as it hasn't been submitted,
            // or submission not required.
            if ((!$meta->submitted) && (!$meta->submissionnotrequired)) {
                $warningclass = '';
                $labeltext = '';

                // If assignment due in 7 days or less, display in amber, if overdue, then in red, or if submitted, turn to green.

                // If assignment is 7 days before date due(nearly due).
                $time = time();
                $timedue = $meta->$field - (86400 * 7);
                if (($time > $timedue) &&  ($time <= $meta->$field)) {
                    if ($mod->modname == 'assign') {
                        $warningclass = ' ad-activity-date-nearly-due';
                    }
                } else if ($time > $meta->$field) { // If assignment is actually overdue.
                    if ($mod->modname == 'assign') {
                        $warningclass = ' ad-activity-date-overdue';
                    }
                    $labeltext .= $this->output->pix_icon('i/warning', get_string('warning', 'theme_adaptable'));
                }

                $labeltext .= get_string('due', 'theme_adaptable', userdate($meta->$field, $dateformat));

                $duedate = html_writer::start_tag('span', array('class' => 'ad-activity-due-date'.$warningclass));
                $duedate .= html_writer::link($url, $labeltext);
                $duedate .= html_writer::end_tag('span');
                $content .= html_writer::start_tag('div', array('class' => 'ad-activity-mod-engagement'));
                $content .= $duedate . html_writer::end_tag('div');
            }
        }

        if ($meta->isteacher) {
            // Teacher - useful teacher meta data.
            $engagementmeta = array();

            // Below, !== false means we get 0 out of x submissions.
            if (!$meta->submissionnotrequired && $meta->numparticipants !== false) {
                /* If numparticipants is 0 then the code cannot determine how many students could
                   take the activity.  Such as when the activity is hidden and would not be able
                   to tell if a student could when it was visible. */
                if ($meta->numparticipants == 0) {
                    $engagementmeta[] = get_string('x'.$meta->submitstrkey, 'theme_adaptable',
                        array(
                            'completed' => $meta->numsubmissions
                        )
                    );
                } else {
                    $engagementmeta[] = get_string('xofy'.$meta->submitstrkey, 'theme_adaptable',
                        array(
                            'completed' => $meta->numsubmissions,
                            'participants' => $meta->numparticipants
                        )
                    );
                }
            }

            if ($meta->numrequiregrading) {
                $engagementmeta[] = get_string('xungraded', 'theme_adaptable', $meta->numrequiregrading);
            }
            if (!empty($engagementmeta)) {
                $engagementstr = implode(', ', $engagementmeta);

                $params = array(
                    'action' => 'grading',
                    'id' => $mod->id,
                    'tsort' => 'timesubmitted',
                    'filter' => 'require_grading'
                );
                $url = new moodle_url("/mod/{$mod->modname}/view.php", $params);

                $icon = html_writer::tag('i', '&nbsp;', array('class' => 'fa fa-info-circle'));
                $content .= html_writer::start_tag('div', array('class' => 'ad-activity-mod-engagement'));
                $content .= html_writer::link($url, $icon.$engagementstr, array('class' => 'ad-activity-action'));
                $content .= html_writer::end_tag('div');
            }
        } else {
            // Feedback meta.
            if (!empty($meta->grade)) {
                $url = new \moodle_url('/grade/report/user/index.php', ['id' => $COURSE->id]);
                if (in_array($mod->modname, ['quiz', 'assign'])) {
                    $url = new \moodle_url('/mod/'.$mod->modname.'/view.php?id='.$mod->id);
                }
                $content .= html_writer::start_tag('span', array('class' => 'ad-activity-mod-feedback'));
                $feedbackavailable = html_writer::tag('i', '&nbsp;', array('class' => 'fa fa-commenting-o')) .
                    get_string('feedbackavailable', 'theme_adaptable');
                $content .= html_writer::link($url, $feedbackavailable);
                $content .= html_writer::end_tag('span');
            }
        }

        return $content;
    }

    /**
     * Submission call to action.
     *
     * @param cm_info $mod
     * @param activity_meta $meta
     * @return string
     * @throws coding_exception
     */
    public function submission_cta(cm_info $mod, \theme_adaptable\activity_meta $meta) {
        global $CFG;

        if (empty($meta->submissionnotrequired)) {

            $url = $CFG->wwwroot.'/mod/'.$mod->modname.'/view.php?id='.$mod->id;

            if ($meta->submitted) {
                if (empty($meta->timesubmitted)) {
                    $submittedonstr = '';
                } else {
                    $submittedonstr = ' '.userdate($meta->timesubmitted, get_string('strftimedate', 'langconfig'));
                }
                $message = $this->output->pix_icon('i/checked', get_string('checked', 'theme_adaptable')).
                    $meta->submittedstr.$submittedonstr;
            } else {
                if ($meta->expired) {
                    $warningstr = $meta->expiredstr;
                    $warningicon = 't/locked';
                } else if ($meta->reopened) {
                    $warningstr = $meta->reopenedstr;
                    $warningicon = 't/unlocked';
                } else if ($meta->draft) {
                    $warningstr = $meta->draftstr;
                    $warningicon = 'i/warning';
                } else if ($meta->notopen) {
                    $warningstr = $meta->notopenstr;
                    $warningicon = 'i/warning';
                } else if ($meta->notattempted) {
                    $warningstr = get_string('notattempted', 'theme_adaptable');
                    $warningicon = 'i/warning';
                } else {
                    $warningstr = $meta->notsubmittedstr;
                    $warningicon = 'i/warning';
                }

                $message = $this->output->pix_icon($warningicon, get_string('warning', 'theme_adaptable')).$warningstr;
            }

            return html_writer::link($url, $message, array('class' => 'ad-activity-action'));
        }
        return '';
    }

    /**
     * Renders the activity navigation.
     *
     * Defer to template.
     *
     * @param \core_course\output\activity_navigation $page
     * @return string html for the page
     */
    public function render_activity_navigation(\core_course\output\activity_navigation $page) {
        $data = $page->export_for_template($this->output);

        /* Add in extra data for our own overridden activity_navigation template.
           So manipulating the 'classes' and 'text' properties in 'action_link' and 'classes' in 'urlselect'. */
        if (!empty($data->prevlink)) {
            $data->prevlink->classes = 'previous_activity prevnext'; // Override the button!

            $icon = html_writer::tag('i', '', array('class' => 'fa fa-angle-double-left'));
            $previouslink = html_writer::tag('span', $icon, array('class' => 'nav_icon'));
            $activityname = html_writer::tag('span', get_string('previousactivity', 'theme_adaptable'),
                            array('class' => 'nav_guide')).'<br>';
            $activityname .= $data->prevlink->attributes[0]['value'];
            $previouslink .= html_writer::tag('span', $activityname, array('class' => 'text'));
            $data->prevlink->text = $previouslink;
        }

        if (!empty($data->nextlink)) {
            $data->nextlink->classes = 'next_activity prevnext'; // Override the button!

            $activityname = html_writer::tag('span', get_string('nextactivity', 'theme_adaptable'),
                            array('class' => 'nav_guide')).'<br>';
            $activityname .= $data->nextlink->attributes[0]['value'];
            $nextlink = html_writer::tag('span', $activityname, array('class' => 'text'));
            $icon = html_writer::tag('i', '', array('class' => 'fa fa-angle-double-right'));
            $nextlink .= html_writer::tag('span', $icon, array('class' => 'nav_icon'));
            $data->nextlink->text = $nextlink;
        }

        if (!empty($data->activitylist)) {
            $data->activitylist->classes = 'jumpmenu';
        }

        return $this->output->render_from_template('core_course/activity_navigation', $data);
    }
}
