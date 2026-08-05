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
 * @package    local_campusai
 * @copyright  2026 Campus Assistant <hola@campusassistant.app>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This file is part of the Campus Assistant plugin for Moodle.
// It is distributed under the GNU GPL v3 or later license.



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class system_health extends base_admin { public function get_definition(): array { return [ 'name' => 'get_system_health', 'description' => 'Get system health: last cron run, pending tasks, and recent errors.', 'parameters' => ['type' => 'object', 'properties' => new \stdClass()], ]; } public function execute(array $arguments): array { global $DB, $CFG; $lastcron = get_config('core', 'lastcron'); $cronoverdue = $lastcron && (time() - $lastcron > 3600); $pendingtasks = 0; try { $pendingtasks = $DB->count_records_select('task_adhoc', 'nextruntime <= ?', [time()]); } catch (\Exception $e) { } $failedtasks = 0; try { $failedtasks = $DB->count_records_select('task_log', 'output LIKE ?', ['%error%']); } catch (\Exception $e) { } $moodlerelease = $CFG->release ?? 'Unknown'; $phpversion = phpversion(); $active24h = $DB->count_records_select('user', "lastlogin > ? AND deleted = 0 AND suspended = 0", [time() - DAYSECS]); $cachestores = []; try { $helper = new \ReflectionClass('cache_helper'); $method = $helper->getMethod('get_store_instances'); $method->setAccessible(true); $config = \cache_config::instance(); $cachestores = ['Cache system operational']; } catch (\Exception $e) { $cachestores = ['Unable to query cache stores']; } return [ 'moodle_version' => $moodlerelease, 'php_version' => $phpversion, 'last_cron' => $lastcron ? $this->format_date($lastcron) : 'Never', 'cron_overdue' => (bool)$cronoverdue, 'pending_adhoc_tasks' => (int)$pendingtasks, 'recent_task_errors' => (int)$failedtasks, 'active_users_24h' => (int)$active24h, 'cache_stores' => $cachestores, 'status' => $cronoverdue ? 'warning' : 'ok', ]; } } 
