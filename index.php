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
 * Local plugin "country_category" - Enrolment page
 *Page for creating or editing course category name/parent/description.
 *
 * When called with an id parameter, edits the category with that id.
 * Otherwise it creates a new category with default parent from the parent
 * parameter, which may be 0.
 *
 * @package   local_country_category
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use local_country_category\country_category_form;

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/course/lib.php');

global $PAGE, $OUTPUT, $SESSION, $DB;
require_login();



$id = optional_param('id', 0, PARAM_INT);
$lang_table = 'country_category_language';
$langs = null;
$url = new moodle_url('/local/country_category/index.php');
if ($id) {
    $coursecat = core_course_category::get($id, MUST_EXIST, true);
    $category = $coursecat->get_db_record();

    $langs = $DB->get_records($lang_table,['course_category_id' =>$category->id ]);
    if (count($langs) > 0){
        foreach ($langs as $lang):
            $arr[] = $lang->language_code;
        endforeach;
        $category->country = isset($arr) ? $arr : [];
    }else{
        $category->country = ['en'];
    }

    $context = context_coursecat::instance($id);

    $url->param('id', $id);
    $strtitle = new lang_string('edit_country_category_settings','local_country_category');
    $itemid = 0; // Initialise itemid, as all files in category description has item id 0.
    $title = $strtitle;
    $fullname = $coursecat->get_formatted_name();

} else {
    $parent = required_param('parent', PARAM_INT);
    $url->param('parent', $parent);
    if ($parent) {
        $DB->record_exists('course_categories', array('id' => $parent), '*', MUST_EXIST);
        $context = context_coursecat::instance($parent);
    } else {
        $context = context_system::instance();
    }
    navigation_node::override_active_url(new moodle_url('/local/country_category/index.php', array('parent' => $parent)));

    $category = new stdClass();
    $category->country = ['en'];
    $category->id = 0;
    $category->parent = $parent;
    $strtitle = new lang_string("addnewcountrycategory",'local_country_category');
    $itemid = null; // Set this explicitly, so files for parent category should not get loaded in draft area.
    $title = "$SITE->shortname: " . get_string('addnewcountrycategory','local_country_category');
    $fullname = $SITE->fullname;
}

require_capability('moodle/category:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($title);
$PAGE->set_heading($fullname);

$mform = new country_category_form(null, array(
    'categoryid' => $id,
    'parent' => $category->parent,
    'context' => $context,
    'itemid' => $itemid
));

$mform->set_data(file_prepare_standard_editor(
    $category,
    'description',
    $mform->get_description_editor_options(),
    $context,
    'coursecat',
    'description',
    $itemid
));

$manageurl = new moodle_url('/local/country_category/manage.php');
if ($mform->is_cancelled()) {
    redirect($manageurl);
} else if ($data = $mform->get_data()) {
        if (isset($coursecat)) {
            if ((int)$data->parent !== (int)$coursecat->parent && !$coursecat->can_change_parent($data->parent)) {
                print_error('cannotmovecategory');
            }
            $coursecat->update($data, $mform->get_description_editor_options());
        } else {
            $category = core_course_category::create($data, $mform->get_description_editor_options());
        }
        $course_category_id =isset($coursecat) ? $coursecat->id : $category->id;

        if ($langs){
            foreach ($langs as $lang) :
                if (!in_array($lang->language_code,$data->country)){
                    $DB->delete_records($lang_table,['course_category_id' => $course_category_id,'language_code' =>$lang->language_code]);
                }
            endforeach;
        }

        foreach ($data->country as $country) {
            $recordexist = $DB->record_exists($lang_table, ['course_category_id' => $course_category_id, 'language_code' => $country]);
            if (!$recordexist){
                $cat_countr_lang = new stdClass();
                $cat_countr_lang->course_category_id = $course_category_id;
                $cat_countr_lang->language_code = $country;
                $DB->insert_record($lang_table, $cat_countr_lang);
            }
        }
        redirect($manageurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
$mform->display();
echo $OUTPUT->footer();

