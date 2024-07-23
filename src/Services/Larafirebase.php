<?php

namespace Kutia\Larafirebase\Services;

use App\Library\GoogleTools;
use Kutia\Larafirebase\Exceptions\UnsupportedTokenFormat;

class Larafirebase
{
    const PRIORITY_NORMAL = 'normal';

    private $title;

    private $body;

    private $clickAction;

    private $image;

    private $icon;

    private $additionalData;

    private $sound;

    private $priority = self::PRIORITY_NORMAL;

    private $fromArray;

    private $authenticationKey;

    private $fromRaw;

    const API_URI = 'https://fcm.googleapis.com/v1/projects/ctinews-348407/messages:send';

    public function withTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function withBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function withClickAction($clickAction)
    {
        $this->clickAction = $clickAction;

        return $this;
    }

    public function withImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function withIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    public function withSound($sound)
    {
        $this->sound = $sound;

        return $this;
    }

    public function withPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function withAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    public function withAuthenticationKey($authenticationKey)
    {
        $this->authenticationKey = $authenticationKey;

        return $this;
    }

    public function fromArray($fromArray)
    {
        $this->fromArray = $fromArray;

        return $this;
    }

    public function fromRaw($fromRaw)
    {
        $this->fromRaw = $fromRaw;

        return $this;
    }

    public function sendNotification($tokens)
    {
        if (is_array($tokens) and count($tokens) !== 1) {
            return false;
        }

        $fields = [
            "validate_only" => true,
            "message" => [
                'data' => $this->additionalData,
                'notification' => ($this->fromArray) ? $this->fromArray : [
                    'title' => $this->title,
                    'body' => $this->body,
                    'image' => $this->image,
                ],
                'token' => $tokens[0],
            ],
        ];

        return $this->callApi($fields);
    }

    public function sendMessage($tokens)
    {
        $data = ($this->fromArray) ? $this->fromArray : [
            'title' => $this->title,
            'body' => $this->body,
        ];

        $data = $this->additionalData ? array_merge($data, $this->additionalData) : $data;

        $fields = array(
            'registration_ids' => $this->validateToken($tokens),
            'data' => $data,
        );

        return $this->callApi($fields);
    }

    public function send()
    {
        return $this->callApi($this->fromRaw);
    }

    private function callApi($fields)
    {

        echo "=> 發送資料: " . json_encode($fields) . PHP_EOL;

        $GoogleTools = new GoogleTools;
        $GoogleTools->initializeGoogleServiceAccount('main');
        $response = $GoogleTools->UseApiPointer("POST", self::API_URI, $fields);

        // $authenticationKey = isset($this->authenticationKey) ? $this->authenticationKey : config('larafirebase.authentication_key');

        // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . $authenticationKey,
        // ])->post(self::API_URI, $fields);

        return $response;
    }

    private function validateToken($tokens)
    {
        if (is_array($tokens)) {
            return $tokens;
        }

        if (is_string($tokens)) {
            return explode(',', $tokens);
        }

        throw new UnsupportedTokenFormat('Please pass tokens as array [token1, token2] or as string (use comma as separator if multiple passed).');
    }
}