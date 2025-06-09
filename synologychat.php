<?php

/**
 * Synology Chat Transport
 *
 * Modified from Line Messaging API Transport for Synology Chat.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Synologychat extends Transport
{
    protected string $name = 'Synology Chat';

    /**
     * Deliver Alert
     *
     * @param  array<string, string>  $alert_data  Alert data
     * @return bool True if message sent successfully
     */
    public function deliverAlert($alert_data): bool
    {
        $base = rtrim($this->config['synology-chat-url'], '/');
        $token = $this->config['synology-chat-token'];
        $apiURL = "$base/webapi/entry.cgi?api=SYNO.Chat.External&method=incoming&version=2&token=$token";

        $data = [
            'payload' => json_encode(['text' => $alert_data['msg']]),
        ];

        $res = Http::client()->post($apiURL, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $data);
    }

    /**
     * Get config template
     *
     * @return array<string, mixed> config template
     */
    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Synology Chat URL',
                    'name' => 'synology-chat-url',
                    'descr' => 'Base URL of the Synology Chat server (e.g., https://host:5001)',
                    'type' => 'text',
                ],
                [
                    'title' => 'Webhook token',
                    'name' => 'synology-chat-token',
                    'descr' => 'Incoming webhook token',
                    'type' => 'password',
                ],
            ],
            'validation' => [
                'synology-chat-url' => 'required|string',
                'synology-chat-token' => 'required|string',
            ],
        ];
    }
}
