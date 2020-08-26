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
 * Overridden Collapsed Topics Core Course Renderer for Adaptable theme
 *
 * @package    theme_adaptable
 * @copyright  2020 Gareth J Barnard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace theme_adaptable\output;

defined('MOODLE_INTERNAL') || die();

use cm_info;
use core_text;
use html_writer;

/**
 * Collapsed Topics Course renderer implementation.
 *
 * @package   theme_adaptable
 * @copyright  2020 Gareth J Barnard
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class topcoll_course_renderer extends \theme_adaptable\output\core\course_renderer {

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
        if (!$mod->uservisible && empty($mod->availableinfo)) {
            // Nothing to be displayed to the user.
            return '';
        }

        // If use adaptable icons is set to false, then just run CT version of the method.
        if (empty($this->page->theme->settings->coursesectionactivityuseadaptableicons)) {
            list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);
            $groupinglabel = $mod->get_grouping_label($textclasses);

            /* Render element that allows to edit activity name inline. It calls {@link course_section_cm_name_title()}
               to get the display title of the activity. */
            $tmpl = new \format_topcoll\output\course_module_name($mod, $this->page->user_is_editing(), $displayoptions);
            return $this->output->render_from_template('core/inplace_editable', $tmpl->export_for_template($this->output)).
                $groupinglabel;
        }

        $url = $mod->url;
        if (!$url) {
            return '';
        }

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

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        $groupinglabel = $mod->get_grouping_label($textclasses);

        // Display link itself.

        // Get icon url, but strip -24, -64, -256  etc from the end of filetype icons so we
        // only need to provide one SVG, see MDL-47082. (Used from snap theme).
        $imageurl = \preg_replace('/-\d\d\d?$/', '', $mod->get_icon_url());

        $activitylink = html_writer::empty_tag('img', array('src' => $imageurl,
                'class' => 'iconlarge activityicon', 'alt' => ' ', 'role' => 'presentation')) . $accesstext .
                html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));

        $outputlink = '';
        if ($mod->uservisible) {
            $outputlink .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick)) .
            $groupinglabel;
        } else {
            // We may be displaying this just in order to show information
            // about visibility, without the actual link ($mod->uservisible).
            $outputlink .= html_writer::tag('div', $activitylink, array('class' => $textclasses)) .
            $groupinglabel;
        }

        $tmpl = new \format_topcoll\output\course_module_name($mod, $this->page->user_is_editing(), $displayoptions);
        $templatedata = $tmpl->export_for_template($this->output);

        // Variable displayvalue element is purposely overriden below with link including custom icon created above.
        $templatedata['displayvalue'] = $outputlink;

        // Not sure about groupinglabel at end as same as CT but not Adaptable, need to see what happens.
        return $this->output->render_from_template('core/inplace_editable', $templatedata).$groupinglabel;
    }
}
