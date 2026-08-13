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
 * Admin settings for Campus Assistant.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_campusai',
        get_string('pluginname', 'local_campusai')
    );
    $ADMIN->add('localplugins', $settings);

    // General.
    $settings->add(new admin_setting_heading(
        'local_campusai/general',
        get_string('admin', 'local_campusai'),
        ''
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_campusai/enabled',
        get_string('settings_enabled', 'local_campusai'),
        get_string('settings_enabled_desc', 'local_campusai'),
        1
    ));

    // Provider.
    $settings->add(new admin_setting_heading(
        'local_campusai/provider',
        get_string('settings_provider', 'local_campusai'),
        ''
    ));

    $settings->add(new admin_setting_configselect(
        'local_campusai/provider',
        get_string('settings_provider', 'local_campusai'),
        get_string('settings_provider_desc', 'local_campusai'),
        'proxy',
        [
            'proxy'    => get_string('provider_proxy', 'local_campusai'),
            'openai'   => get_string('provider_openai', 'local_campusai'),
            'gemini'   => get_string('provider_gemini', 'local_campusai'),
            'claude'   => get_string('provider_claude', 'local_campusai'),
            'deepseek' => get_string('provider_deepseek', 'local_campusai'),
        ]
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_campusai/apikey',
        get_string('settings_apikey', 'local_campusai'),
        get_string('settings_apikey_desc', 'local_campusai'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_campusai/model',
        get_string('settings_model', 'local_campusai'),
        get_string('settings_model_desc', 'local_campusai'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_campusai/systemprompt',
        get_string('settings_systemprompt', 'local_campusai'),
        get_string('settings_systemprompt_desc', 'local_campusai'),
        get_string('default_systemprompt', 'local_campusai'),
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtext(
        'local_campusai/licensekey',
        get_string('settings_licensekey', 'local_campusai'),
        get_string('settings_licensekey_desc', 'local_campusai'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_campusai/jwtsecret',
        get_string('settings_jwtsecret', 'local_campusai'),
        get_string('settings_jwtsecret_desc', 'local_campusai'),
        ''
    ));

    // Appearance.
    $settings->add(new admin_setting_heading(
        'local_campusai/appearance',
        get_string('settings_title', 'local_campusai'),
        ''
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'local_campusai/color',
        get_string('settings_color', 'local_campusai'),
        get_string('settings_color_desc', 'local_campusai'),
        '#0066CC'
    ));

    $settings->add(new admin_setting_configselect(
        'local_campusai/position',
        get_string('settings_position', 'local_campusai'),
        get_string('settings_position_desc', 'local_campusai'),
        'bottom-right',
        [
            'bottom-right' => get_string('position_bottom_right', 'local_campusai'),
            'bottom-left'  => get_string('position_bottom_left', 'local_campusai'),
        ]
    ));

    $settings->add(new admin_setting_configtext(
        'local_campusai/title',
        get_string('settings_title', 'local_campusai'),
        get_string('settings_title_desc', 'local_campusai'),
        get_string('widget_title_fallback', 'local_campusai'),
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_campusai/welcome',
        get_string('settings_welcome', 'local_campusai'),
        get_string('settings_welcome_desc', 'local_campusai'),
        get_string('default_welcome', 'local_campusai'),
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configselect(
        'local_campusai/language',
        get_string('settings_language', 'local_campusai'),
        get_string('settings_language_desc', 'local_campusai'),
        'en',
        [
            'en' => get_string('lang_english', 'local_campusai'),
            'es' => get_string('lang_spanish', 'local_campusai'),
            'fr' => get_string('lang_french', 'local_campusai'),
            'de' => get_string('lang_german', 'local_campusai'),
            'it' => get_string('lang_italian', 'local_campusai'),
            'pt' => get_string('lang_portuguese', 'local_campusai'),
        ]
    ));

    $settings->add(new admin_setting_configstoredfile(
        'local_campusai/fabicon',
        get_string('settings_fabicon', 'local_campusai'),
        get_string('settings_fabicon_desc', 'local_campusai'),
        'fabicon',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['.png', '.jpg', '.gif', '.svg']]
    ));

    // Privacy.
    $settings->add(new admin_setting_heading(
        'local_campusai/privacy',
        get_string('settings_auditlog', 'local_campusai'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_campusai/ratelimit',
        get_string('settings_ratelimit', 'local_campusai'),
        get_string('settings_ratelimit_desc', 'local_campusai'),
        '30',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_campusai/window',
        get_string('settings_window', 'local_campusai'),
        get_string('settings_window_desc', 'local_campusai'),
        '600',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_campusai/auditlog',
        get_string('settings_auditlog', 'local_campusai'),
        get_string('settings_auditlog_desc', 'local_campusai'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_campusai/logretention',
        get_string('settings_logretention', 'local_campusai'),
        get_string('settings_logretention_desc', 'local_campusai'),
        '90',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_campusai/hideroles',
        get_string('settings_hideroles', 'local_campusai'),
        get_string('settings_hideroles_desc', 'local_campusai'),
        '',
        PARAM_RAW
    ));
}
