<?php
namespace App\Factory;

class UtmHandler
{
    public static array $utmKeys = ['referral', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
    
    public static function extractUtmData(): array
    {
        global $_GET;

        $getParams = $_GET;
        $utmData = [];
        foreach (self::$utmKeys as $key) {
            if (isset($getParams[$key])) {
                $utmData[$key] = htmlspecialchars($getParams[$key], ENT_QUOTES, 'UTF-8') ?? null;
            }
        }

        return $utmData;
    }

    public static function saveUtmToCookie(array $data): void
    {
        $utmData = self::getUtmFromCookie() ?? [];
        foreach (self::$utmKeys as $key) {
            if (isset($data[$key])) {
                $utm = htmlspecialchars($data[$key], ENT_QUOTES, 'UTF-8') ?? null;
                $utmData[$key] = $utm;
            }
        }

        if (!isset($data["referral"]) && array_key_exists("referral", $utmData)) {
            unset($utmData["referral"]);
        }

        setcookie('utm', json_encode($utmData), time() + (3 * 24 * 60 * 60), "/");
    }

    public static function getUtmFromCookie(): array
    {
        global $_COOKIE;
        return json_decode($_COOKIE['utm'] ?? '[]', true);
    }
}