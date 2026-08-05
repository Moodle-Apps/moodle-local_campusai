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



namespace local_campusai; defined('MOODLE_INTERNAL') || die(); class license_manager { const SERVER_URL = 'https://campusassistant.app/app/api/validate.php'; const CACHE_TTL = 86400; const GRACE_PERIOD = 604800; const JWT_SECRET = 'CaSt_JWT_S3cr3t_2026!Px9$mBz_v2'; public static function get_status(): array { $licensekey = trim(get_config('local_campusai', 'licensekey') ?? ''); if (empty($licensekey)) { return [ 'valid' => false, 'error' => 'No license key configured. Go to Settings to add your license key.', 'source' => 'no_key', ]; } $cached = self::get_cache(); if ($cached && $cached['expires_at'] > time()) { if (self::verify_jwt($cached['token'])) { return [ 'valid' => true, 'token' => $cached['token'], 'expires' => $cached['expires_at'], 'source' => 'cache', ]; } } $result = self::validate_remote($licensekey); if ($result['valid']) { self::set_cache($result['token'], $result['expires']); return [ 'valid' => true, 'token' => $result['token'], 'expires' => $result['expires'], 'source' => 'server', ]; } if (in_array($result['error'] ?? '', ['License key not found', 'License revoked', 'Subscription expired'])) { self::clear_cache(); return [ 'valid' => false, 'error' => $result['error'], 'source' => 'rejected', ]; } if ($cached && $cached['cached_at'] > time() - self::GRACE_PERIOD) { if (self::verify_jwt($cached['token'])) { return [ 'valid' => true, 'token' => $cached['token'], 'expires' => $cached['expires_at'], 'source' => 'grace', 'warning' => 'License server unreachable. Operating in grace period.', ]; } } return [ 'valid' => false, 'error' => $result['error'] ?? 'Unable to validate license. Please check your connection and try again.', 'source' => 'expired', ]; } protected static function validate_remote(string $licenseKey): array { global $CFG; $domain = parse_url($CFG->wwwroot, PHP_URL_HOST) ?? $_SERVER['HTTP_HOST'] ?? 'unknown'; $payload = json_encode([ 'license_key' => $licenseKey, 'domain' => $domain, ]); $ch = curl_init(self::SERVER_URL); curl_setopt_array($ch, [ CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => true, ]); $response = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch); if ($error || $code === 0) { return ['valid' => false, 'error' => 'Connection error: ' . $error]; } $data = json_decode($response, true); if (!$data) { return ['valid' => false, 'error' => 'Invalid server response']; } return $data; } protected static function get_cache(): ?array { $cache = get_config('local_campusai', 'license_cache'); if (empty($cache)) return null; $data = json_decode($cache, true); if (!$data || !isset($data['token'])) return null; return $data; } protected static function set_cache(string $token, int $expires): void { $data = json_encode([ 'token' => $token, 'expires_at' => $expires, 'cached_at' => time(), ]); set_config('license_cache', $data, 'local_campusai'); } public static function clear_cache(): void { set_config('license_cache', '', 'local_campusai'); } public static function verify_jwt(string $jwt): bool { $parts = explode('.', $jwt); if (count($parts) !== 3) return false; [$header, $payload, $signature] = $parts; $expected = self::base64url_encode(hash_hmac('sha256', "$header.$payload", self::JWT_SECRET, true)); if (!hash_equals($expected, $signature)) return false; $payloadData = json_decode(self::base64url_decode($payload), true); if (!$payloadData || !isset($payloadData['exp'])) return false; return $payloadData['exp'] > time(); } public static function get_token(): ?string { $status = self::get_status(); return $status['valid'] ? $status['token'] : null; } protected static function base64url_encode(string $data): string { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); } protected static function base64url_decode(string $data): string { return base64_decode(strtr($data, '-_', '+/')); } } 
