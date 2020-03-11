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
 * @package    theme_adaptable
 * @copyright  2020 Gareth J Barnard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_adaptable\output;

defined('MOODLE_INTERNAL') || die();

/**
 * The mustache renderer.
 *
 * @copyright  &copy; 2020-onwards G J Barnard.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class mustachesource_renderer extends \renderer_base {

    /**
     * Gets the template source by name.
     *
     * @param string $templatename Template name.
     * @return Mustache Source string.
     */
    public function get_template($templatename) {
        $mustache = $this->get_mustache();

        return $mustache->getLoader()->load($templatename);
    }

}