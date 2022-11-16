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
 * Custom Signup form - Settings file
 *
 * @package    local_customsignup
 * @copyright  2022 Melanie Treitinger, Ruhr-Universität Bochum <melanie.treitinger@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/lib.php');

if ($hassiteconfig) {

    global $ADMIN;

    $settings = new admin_settingpage(
        'local_customsignup_settings',
        get_string('customsignup_form', 'local_customsignup'),
        'moodle/site:config'
    );

    if ($ADMIN->fulltree) {
        $additionalfields = local_customsignup_get_additional_fields();

        // Loop through defined fields.
        foreach ($additionalfields as $fieldname => $field) {
            $settings->add(new admin_setting_heading(
                'local_customsignup_' . $fieldname.'label',
                get_string($fieldname.'label', 'local_customsignup'),
                ''
            ));

            $settings->add(new admin_setting_configcheckbox(
                'local_customsignup/enable_' . $fieldname,
                get_string('enable_' . $fieldname, 'local_customsignup'),
                get_string('enable_' . $fieldname . '_desc', 'local_customsignup'),
                0
            ));

            $settings->add(new admin_setting_configcheckbox(
                'local_customsignup/is_' . $fieldname . '_required',
                get_string('is_' . $fieldname . '_required', 'local_customsignup'),
                get_string('is_' . $fieldname . '_required_desc', 'local_customsignup'),
                0
            ));

            // Add textarea for select field option list.
            if ('select' == $field['element_type']) {
                $optionlist = str_replace(", ", "\n", get_string($fieldname.'list', 'local_customsignup'));
                $settings->add(new admin_setting_configtextarea(
                    'local_customsignup/'.$fieldname.'list',
                    new lang_string($fieldname.'label', 'local_customsignup'),
                    new lang_string($fieldname.'listdesc', 'local_customsignup'),
                        $optionlist,
                    PARAM_TEXT,
                    '50',
                    '10'
                ));
            }
        }
    }

    $ADMIN->add('localplugins', $settings);

}
