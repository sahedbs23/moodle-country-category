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
 * Local plugin "country_category" - Settings
 *
 * @package   local_country_category
 * @copyright 2020 Brainn station 23 ltd. <brainstation-23.comm>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$capabilities = array(
    'moodle/category:manage',
    'moodle/course:create',
);
$systemcontext = context_system::instance();

if ($hassiteconfig or has_any_capability($capabilities, $systemcontext)) {
    $ADMIN->add('courses',
        new admin_externalpage('addcountrycategory', new lang_string('pluginname', 'local_country_category'),
            new moodle_url('/local/country_category/index.php',['parent' =>0]),
            array('moodle/category:manage')
        )
    );
    $ADMIN->add('courses',
        new admin_externalpage('managecountrycategory', new lang_string('managecountrycategory', 'local_country_category'),
            new moodle_url('/local/country_category/manage.php',['parent' =>0]),
            array('moodle/category:manage')
        )
    );
}
