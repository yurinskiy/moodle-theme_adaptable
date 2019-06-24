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
 * Login page settings
 *
 * @package    theme_adaptable
 * @copyright  2015-2019 Jeremy Hopkins (Coventry University)
 * @copyright  2015-2019 Fernando Acedo (3-bits.com)
 * @copyright  2017-2019 Manoj Solanki (Coventry University)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */


defined('MOODLE_INTERNAL') || die;

    // Login page heading.
    $temp = new admin_settingpage('theme_adaptable_login', get_string('loginsettings', 'theme_adaptable'));
    $temp->add(new admin_setting_heading('theme_adaptable_login', get_string('loginsettingsheading', 'theme_adaptable'),
                format_text(get_string('logindesc', 'theme_adaptable'), FORMAT_MARKDOWN)));

    // Page background image.
    $name = 'theme_adaptable/loginbgimage';
    $title = get_string('loginbgimage', 'theme_adaptable');
    $description = get_string('loginbgimagedesc', 'theme_adaptable');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbgimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Top text.
    $name = 'theme_adaptable/logintextboxtop';
    $title = get_string('logintextboxtop', 'theme_adaptable');
    $description = get_string('logintextboxtopdesc', 'theme_adaptable');
    $default = '';
    $setting = new adaptable_setting_confightmleditor($name, $title, $description, $default);
    $temp->add($setting);

    // Bottom text.
    $name = 'theme_adaptable/logintextboxbottom';
    $title = get_string('logintextboxbottom', 'theme_adaptable');
    $description = get_string('logintextboxbottomdesc', 'theme_adaptable');
    $default = '';
    $setting = new adaptable_setting_confightmleditor($name, $title, $description, $default);
    $temp->add($setting);

    // Remove moodle default text.
    $name = 'theme_adaptable/loginmoodletext';
    $title = get_string('loginmoodletext', 'theme_adaptable');
    $description = get_string('loginmoodletextdesc', 'theme_adaptable');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $ADMIN->add('theme_adaptable', $temp);
