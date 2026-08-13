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

namespace local_campusai\provider;

/**
 * Campus Assistant managed proxy provider.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class proxy_provider implements provider_interface {
    /** @var string License key. */
    private string $licensekey;

    /** @var string JWT shared secret. */
    private string $jwtsecret;

    /**
     * Constructor.
     *
     * @param string $licensekey
     * @param string $jwtsecret
     */
    public function __construct(string $licensekey, string $jwtsecret) {
        $this->licensekey = $licensekey;
        $this->jwtsecret = $jwtsecret;
    }

    /**
     * Returns the provider identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'proxy';
    }

    /**
     * Sends the request to the managed proxy.
     *
     * @param string $systemprompt
     * @param array $messages
     * @param array $tools
     * @param string $model
     * @param int $maxtokens
     * @return array
     */
    public function chat(
        string $systemprompt,
        array $messages,
        array $tools,
        string $model,
        int $maxtokens
    ): array {
        global $USER;

        $url = 'https://campusassistant.app/app/api/chat.php';

        $payload = [
            'messages' => $messages,
            'tools'    => $tools,
            'system'   => $systemprompt,
            'model'    => $model,
        ];

        $curl = new \curl();
        $curl->setHeader('Authorization: Bearer ' . $this->licensekey);
        $curl->setHeader('Content-Type: application/json');
        $curl->setopt(['CURLOPT_TIMEOUT' => 30]);

        if (!empty($this->jwtsecret)) {
            $jwt = $this->sign_jwt((int) $USER->id);
            $curl->setHeader('X-Assistant-JWT: ' . $jwt);
        }

        $response = $curl->post($url, json_encode($payload));

        return provider_helper::parse_openai_like($curl, $response);
    }

    /**
     * Signs a HS256 JWT for the proxy.
     *
     * @param int $userid
     * @return string
     */
    private function sign_jwt(int $userid): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['sub' => $userid, 'exp' => time() + 3600]);

        $base64header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64header . '.' . $base64payload, $this->jwtsecret, true);
        $base64signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64header . '.' . $base64payload . '.' . $base64signature;
    }
}
