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
 *             based on local_custom_registration by mgerard@cblue.be, CBlue SPRL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add multilang tags to a string
 *
 * @param string $string
 * @param string $lang
 * @return string|void
 */
function local_customsignup_add_multilang($string, $lang) {
    if ('' != $string) {
        return '<span class="multilang" lang="'.$lang.'">'.trim($string).'</span>';
    }
}

/**
 * Return an array with the additional fields for the signup form.
 *
 * @return array[]
 */
function local_customsignup_get_additional_fields() {
    // Usage.
    global $CFG;
    require_once($CFG->dirroot.'/user/profile/lib.php');
    $regreasonlist = '';
    $fields = profile_get_user_fields_with_data(0);
    foreach ($fields as $field) {
        if ('regreason' == $field->field->shortname) {
            $regreasonlist = $field->options;
        }
    }

    // Install.
    if ('' == $regreasonlist) {
        $reasons = explode(",", get_string('regreasonlist', 'local_customsignup'));
        $reasons = array_merge(array(''), $reasons);
        $mlreasons = array();
        $languages = get_string_manager()->get_list_of_translations();
        foreach ($languages as $lang => $value) {
            $choose = array('');
            $tmp = explode(",", get_string_manager()->get_string('regreasonlist', 'local_customsignup', null, $lang));
            $tmp = array_merge($choose, $tmp);
            foreach ($tmp as $key => $value) {
                if (!isset($mlreasons[$key])) {
                    $mlreasons[$key] = local_customsignup_add_multilang($value, $lang);
                } else {
                    $mlreasons[$key] .= local_customsignup_add_multilang($value, $lang);
                }
            }
        }
        $regreasonlist = array_combine($reasons, $mlreasons);
    }

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
 * @param MoodleQuickForm $mform
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
                      $mform->addGroupRule($fieldname.'group', $field['required_feedback'], 'required', null, 0, 'client');
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
 * @param stdClass $datas
 * @return array
 */
function local_customsignup_validate_extend_signup_form($datas) {
    return [];
}

/**
 * Handle values of our custom signup fields.
 *
 * @param stdClass $datas
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
