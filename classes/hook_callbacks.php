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

namespace local_campusai;

use moodle_url;

/**
 * Hook callbacks for Campus Assistant.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Injects the chat widget assets and configuration on every page.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     * @return void
     */
    public static function before_standard_head($hook): void {
        global $PAGE, $USER;

        if (!get_config('local_campusai', 'enabled')) {
            return;
        }

        if (!isloggedin() || isguestuser()) {
            return;
        }

        $context = \context_system::instance();
        if (!has_capability('local/campusai:use', $context)) {
            return;
        }

        $provider = get_config('local_campusai', 'provider');
        $licensekey = get_config('local_campusai', 'licensekey');
        if ($provider === 'proxy' && empty($licensekey)) {
            return;
        }

        $ajaxurl = new moodle_url('/local/campusai/ajax.php');
        $iconurl = self::get_fab_icon_url();

        $config = [
            'enabled'     => true,
            'ajaxUrl'     => $ajaxurl->out(false),
            'sesskey'     => sesskey(),
            'color'       => get_config('local_campusai', 'color'),
            'position'    => get_config('local_campusai', 'position'),
            'title'       => get_config('local_campusai', 'title'),
            'welcome'     => get_config('local_campusai', 'welcome'),
            'defaultLang' => get_config('local_campusai', 'language'),
            'iconUrl'     => $iconurl ? $iconurl->out(false) : '',
            'isAdmin'     => functions\registry::is_admin_mode($USER->id),
            'userRole'    => functions\registry::get_role_type($USER->id),
            'examples'    => functions\registry::examples_for_user($USER->id),
        ];

        $strings = [
            'pluginname',
            'error_generic',
            'error_ratelimit',
            'placeholder',
            'default_welcome',
            'widget_online',
            'widget_close',
            'widget_send',
            'widget_title_fallback',
            'widget_admin_suffix',
            'widget_help',
            'widget_help_title',
            'widget_examples_student',
            'widget_examples_teacher',
            'widget_examples_admin',
            'quick_courses_label',
            'quick_courses_text',
            'quick_exams_label',
            'quick_exams_text',
            'quick_tasks_label',
            'quick_tasks_text',
            'quick_teaching_courses_label',
            'quick_teaching_courses_text',
            'quick_overdue_label',
            'quick_overdue_text',
            'quick_needing_grading_label',
            'quick_needing_grading_text',
            'quick_campus_stats_label',
            'quick_campus_stats_text',
            'quick_course_list_admin_label',
            'quick_course_list_admin_text',
            'quick_inactive_users_label',
            'quick_inactive_users_text',
            'quick_help_label',
        ];
        $stringdata = [];
        foreach ($strings as $key) {
            $stringdata[$key] = get_string($key, 'local_campusai');
        }

        $json = json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $stringsjson = json_encode($stringdata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $hook->add_html("<script>window.campusaiConfig = {$json}; window.campusaiStrings = {$stringsjson};</script>");

        $PAGE->requires->js_call_amd('local_campusai/campusai', 'init');
    }

    /**
     * Returns the URL of the uploaded FAB icon, or null for the default.
     *
     * @return moodle_url|null
     */
    private static function get_fab_icon_url(): ?moodle_url {
        global $CFG;

        $context = \context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_campusai', 'fabicon', 0, 'sortorder', false);

        foreach ($files as $file) {
            if ($file->is_valid_image()) {
                return moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
            }
        }

        return null;
    }
}
