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
 * Custom Signup form - lib file
 *
 * @package    local_customsignup
 * @copyright  2022 Melanie Treitinger, Ruhr-Universität Bochum <melanie.treitinger@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_customsignup_get_additional_fields() {
    if ('' != get_config('local_customsignup', 'regreasonlist')) {
        $reasons = get_config('local_customsignup', 'regreasonlist');
    }
    else {
        $reasons = explode(",", get_string('regreasonlist', 'local_customsignup'));
    }
    $reasons = array_map('trim', $reasons);
    $reasonkeys = array_combine($reasons, $reasons);

    $regreasonlist = array_merge(array('' => get_string('choose')), $reasonkeys);

    return [
        'confirmname' => [
            'element_type' => 'checkbox',
            'label' => get_string('confirmnamelabel', 'local_customsignup'),
            'required_feedback' => get_string('confirm')
        ],
        'regreason' => [
            'element_type' => 'select',
            'label' => get_string('regreasonlabel', 'local_customsignup'),
            'values' => $regreasonlist,
            'required_feedback' => get_string('missingreqreason')
        ]

    ];
}

/**
 * Extend the signup form with additional fields selected in the plugin configuration
 *
 * @param $mform
 * @throws coding_exception
 * @throws dml_exception
 */
function local_customsignup_extend_signup_form($mform) {

    $additionalfields = local_customsignup_get_additional_fields();

    foreach ($additionalfields as $fieldname => $field) {
        if (get_config('local_customsignup',  'enable_' . $fieldname)) {
            // For checkboxes.
            if ('checkbox' == $field['element_type']) {
                $cb = array($mform->createElement('checkbox', $fieldname, '', get_string($fieldname, 'local_customsignup')));
                $mform->addGroup($cb, $fieldname.'group', $field['label'], array(' '), false);
                if (get_config('local_customsignup', 'is_' . $fieldname . '_required')) {
                      $mform->addGroupRule($fieldname.'group', $field['required_feedback'], 'required', null, 'client');
                }
            } else {
                if (isset($field['values'])) {
                    // For select fields.
                    $mform->addElement($field['element_type'], $fieldname, get_string($fieldname, 'local_customsignup'),
                    $field['values']);
                } else {
                    $mform->addElement($field['element_type'], $fieldname, $field['label'],
                    get_string($fieldname, 'local_customsignup'));
                }
                $mform->setType($fieldname, PARAM_TEXT);

                if (get_config('local_customsignup', 'is_' . $fieldname . '_required')) {
                    $mform->addRule($fieldname, $field['required_feedback'], 'required', null, 'client');
                }
            }
        }
    }
}

/**
 * There is no data to validate.
 *
 * @param $datas
 * @return array
 */
function local_customsignup_validate_extend_signup_form($datas) {
    return [];
}

/**
 * Handle values of our custom signup fields.
 *
 * @param $datas
 */
function local_customsignup_post_signup_requests($datas) {
    $addedfields = array_keys(local_customsignup_get_additional_fields());

    foreach ($addedfields as $field) {
        $realfield = 'profile_field_'.$field;
        // Handle our added fields.
        if (isset($datas->$field)) {
            $datas->$realfield = $datas->$field;
            unset($datas->$field);
        }
    }
}
