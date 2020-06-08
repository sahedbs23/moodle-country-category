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
$url = new moodle_url('/local/country_category/index.php');
require_capability('moodle/category:manage', context_system::instance());

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$title = "$SITE->shortname: " . get_string('managecountrycategory','local_country_category');
$fullname = $SITE->fullname;
$PAGE->set_title($title );
$PAGE->set_heading($fullname);


echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
$records = $DB->get_records('course_categories',['parent' => 0]);
?>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Languages</th>
            <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $langs = get_string_manager()->get_list_of_translations();
        foreach ($records as $record) :
            $languages = $DB->get_records('country_category_language',['course_category_id' => $record->id]);
            ?>
            <tr>
                <th scope="row"><?php echo $record->id; ?></th>
                <td><?php echo $record->name; ?></td>
                <td>
                    <div class="list-group">
                        <?php
                        foreach ($languages as $lang):?>
                        <a href="<?php echo new moodle_url('/local/country_category/manage.php',['lang' =>$lang->language_code ])?>"> <?php echo array_key_exists($lang->language_code,$langs) ? $langs[$lang->language_code] : '';?></a>
                        <?php
                        endforeach;
                        ?>
                    </div>

                </td>
                <td><a href="<?php echo new moodle_url('/local/country_category/index.php',['id' =>$record->id ]); ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php
echo $OUTPUT->footer();

