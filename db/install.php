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
 * Custom Signup form - Version file
 *
 * @package    local_customsignup
 * @copyright  2022 Melanie Treitinger, Ruhr-Universität Bochum <melanie.treitinger@ruhr-uni-bochum.de>
 *             based on local_custom_registration by mgerard@cblue.be, CBlue SPRL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Set the initial order for the feedback comments plugin (top)
 * @return bool
 */
function xmldb_local_customsignup_install() {
    global $CFG, $DB;
    $DB->set_debug(true);

    require_once($CFG->dirroot . '/local/customsignup/lib.php');
    require_once($CFG->dirroot . '/user/profile/definelib.php');

    $addedfields = local_customsignup_get_additional_fields();

    foreach ($addedfields as $name => $field) {
        $datatype = $field['element_type'];
        $datatype = ('select' == $datatype ? 'menu' : $datatype);
        $return = (in_array($datatype, array('checkbox', 'datetime', 'menu', 'text', 'textarea')) ? true : false);

        if (!$custom = $DB->get_record('user_info_field', array('shortname' => $name))) {

            $custom = new stdClass();
            $custom->shortname = $name;
            $custom->name = $field['label'];
            $custom->datatype = $datatype;
            $custom->description = '';
            $custom->descriptionformat = FORMAT_HTML;
            $custom->categoryid = 1;
            $custom->sortorder = 1;
            $custom->required = 0;
            $custom->locked = 1;
            $custom->visible = 1;
            $custom->forceunique = 0;
            $custom->signup = 0;
            $custom->defaultdata = '';
            $custom->defaultdataformat = FORMAT_HTML;

            switch($datatype){
                case 'text':
                    $custom->param1 = '30'; // Display size.
                    $custom->param2 = '2048'; // Maximum length.
                    $custom->param3 = 0; // Is this a password field?
                    $custom->param4 = ''; // Link.
                    $custom->param5 = ''; // Link target.
                break;
                case 'menu':
                    $custom->param1 = implode("\n", $field['values']); // Options.
                    $custom->param2 = '';
                    $custom->param3 = '';
                    $custom->param4 = '';
                    $custom->param5 = '';
                break;
                default:
                    $custom->param1 = '';
                    $custom->param2 = '';
                    $custom->param3 = '';
                    $custom->param4 = '';
                    $custom->param5 = '';
                break;
            }

        }

        if ($return) {
            require_once($CFG->dirroot.'/user/profile/field/'.$datatype.'/define.class.php');
            $newfield = 'profile_define_'.$datatype;
            $formfield = new $newfield();

            $formfield->define_save($custom);
            profile_reorder_fields();
            profile_reorder_categories();
        }
    }

    return $return;
}
