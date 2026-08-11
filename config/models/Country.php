<?php
namespace App\Models;

class Country {

    public static function countries(): array {
        return [
            ['ID_COUNTRY' => 7, 'COUNTRY_NAME' => 'Indonesia', 'COUNTRY_CODE' => 'ID']
        ];
    }

    public static function getByName($countryName): array|bool {
        return ['ID_COUNTRY' => 7, 'COUNTRY_NAME' => 'Indonesia', 'COUNTRY_CODE' => 'ID'];
    }

    public static function getByCountryCode($countryCode): array|bool {
        return ['ID_COUNTRY' => 7, 'COUNTRY_NAME' => 'Indonesia', 'COUNTRY_CODE' => 'ID'];
    }

}
