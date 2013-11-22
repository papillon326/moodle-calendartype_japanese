<?php
// This file is part of the Japanese calendar type plugin for Moodle
//
// The Japanese calendar type plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// The Japanese calendar type plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// For a copy of the GNU General Public License, see <http://www.gnu.org/licenses/>.

/**
* Link to japanese calendar type settings.
*
* @package calendartype_japanese
* @copyright 2013 Adrian Greeve
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configselect('calendartype_japanese/japaneseyeartype',
        new lang_string('japaneseyeartype', 'calendartype_japanese'),
        new lang_string('configjapaneseyeartype', 'calendartype_japanese'), 0,
        array(new lang_string('gregorian', 'calendartype_japanese'),
              new lang_string('emperor', 'calendartype_japanese'))));
