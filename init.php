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
 * Public configuration endpoint for the Campus Assistant widget.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require(__DIR__ . '/../../config.php');
require_login();

header('Content-Type: application/json; charset=utf-8');

$response = [
    'enabled'     => false,
    'color'       => '#0066CC',
    'position'    => 'bottom-right',
    'title'       => get_string('widget_title_fallback', 'local_campusai'),
    'welcome'     => '',
    'defaultLang' => 'en',
    'iconUrl'     => '',
    'isAdmin'     => false,
    'userRole'    => 'student',
];

if (!isloggedin() || isguestuser()) {
    echo json_encode($response);
    exit;
}

$context = context_system::instance();
if (!has_capability('local/campusai:use', $context)) {
    echo json_encode($response);
    exit;
}

$provider = get_config('local_campusai', 'provider');
$licensekey = get_config('local_campusai', 'licensekey');
if ($provider === 'proxy' && empty($licensekey)) {
    echo json_encode($response);
    exit;
}

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'local_campusai', 'fabicon', 0, 'sortorder', false);
$iconurl = '';
foreach ($files as $file) {
    if ($file->is_valid_image()) {
        $iconurl = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
        break;
    }
}

$response['enabled']     = (bool) get_config('local_campusai', 'enabled');
$response['color']       = get_config('local_campusai', 'color');
$response['position']    = get_config('local_campusai', 'position');
$response['title']       = get_config('local_campusai', 'title');
$response['welcome']     = get_config('local_campusai', 'welcome');
$response['defaultLang'] = get_config('local_campusai', 'language');
$response['iconUrl']     = $iconurl;
$response['isAdmin']     = \local_campusai\functions\registry::is_admin_mode($USER->id);
$response['userRole']    = \local_campusai\functions\registry::get_role_type($USER->id);
$response['examples']    = \local_campusai\functions\registry::examples_for_user($USER->id);

echo json_encode($response);
