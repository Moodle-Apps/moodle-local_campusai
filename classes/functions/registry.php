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

namespace local_campusai\functions;

/**
 * Registry of all assistant functions.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class registry {
    /**
     * Returns all available functions.
     *
     * @return base_function[]
     */
    public static function all(): array {
        $functions = [];
        $functions = array_merge($functions, self::load_classes(__DIR__, 'local_campusai\functions'));
        $functions = array_merge($functions, self::load_classes(__DIR__ . '/admin', 'local_campusai\functions\admin'));
        $functions = array_merge($functions, self::load_classes(__DIR__ . '/teacher', 'local_campusai\functions\teacher'));
        return $functions;
    }

    /**
     * Returns the functions visible to a specific user.
     *
     * @param int $userid User ID.
     * @return base_function[]
     */
    public static function for_user(int $userid): array {
        $functions = self::load_classes(__DIR__, 'local_campusai\functions');

        if (self::is_teacher($userid)) {
            $functions = array_merge($functions, self::load_classes(__DIR__ . '/teacher', 'local_campusai\functions\teacher'));
        }

        if (self::is_admin_mode($userid)) {
            $functions = array_merge($functions, self::load_classes(__DIR__ . '/admin', 'local_campusai\functions\admin'));
        }

        return $functions;
    }

    /**
     * Checks whether the user has the manager capability.
     *
     * @param int $userid User ID.
     * @return bool
     */
    public static function is_admin_mode(int $userid): bool {
        return has_capability('local/campusai:manage', \context_system::instance(), $userid);
    }

    /**
     * Returns the role type for the user.
     *
     * @param int $userid User ID.
     * @return string 'student', 'teacher' or 'admin'.
     */
    public static function get_role_type(int $userid): string {
        if (self::is_admin_mode($userid)) {
            return 'admin';
        }
        if (self::is_teacher($userid)) {
            return 'teacher';
        }
        return 'student';
    }

    /**
     * Returns example questions available to the given user.
     *
     * @param int $userid User ID.
     * @return string[]
     */
    public static function examples_for_user(int $userid): array {
        $examples = [];
        $dir = null;
        $namespace = null;

        if (self::is_admin_mode($userid)) {
            $dir = __DIR__ . '/admin';
            $namespace = 'local_campusai\functions\admin';
        } else if (self::is_teacher($userid)) {
            $dir = __DIR__ . '/teacher';
            $namespace = 'local_campusai\functions\teacher';
        } else {
            $dir = __DIR__;
            $namespace = 'local_campusai\functions';
        }

        foreach (self::load_classes($dir, $namespace) as $function) {
            $class = get_class($function);
            foreach ($class::examples() as $question) {
                $examples[] = $question;
            }
        }

        // Limit to avoid overwhelming the UI.
        return array_slice(array_unique($examples), 0, 12);
    }

    /**
     * Checks whether the user is a teacher in any course.
     *
     * @param int $userid User ID.
     * @return bool
     */
    private static function is_teacher(int $userid): bool {
        return has_capability('moodle/course:update', \context_system::instance(), $userid);
    }

    /**
     * Loads function instances from a directory.
     *
     * @param string $dir Directory path.
     * @param string $namespace Base namespace.
     * @return base_function[]
     */
    private static function load_classes(string $dir, string $namespace): array {
        $functions = [];
        if (!is_dir($dir)) {
            return $functions;
        }

        foreach (glob($dir . '/*.php') as $file) {
            $basename = basename($file, '.php');
            if (in_array($basename, ['base_function', 'base_admin', 'base_teacher', 'registry'])) {
                continue;
            }

            $class = $namespace . '\\' . $basename;
            if (!class_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract()) {
                continue;
            }

            $functions[] = new $class();
        }

        return $functions;
    }
}
