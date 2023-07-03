<?php

declare(strict_types=1);

/**
 * VodaPay Gateway
 *
 * Enabling ecommerce merchants to accept online payments from customers.
 *
 * The version of the OpenAPI document: v2.0
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.6.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace VodaPayGatewayClient\Model;

/**
 * Payment Page Process Theme
 *
 */
enum PaymentPageProcessTheme: string
{
    /**
     * Possible values of this enum
     */
    case LIGHT_THEME = 'LightTheme';

    case DARK_THEME = 'DarkTheme';

    case MINI_APP = 'MiniApp';

    /**
     * Gets allowable values of the enum
     *
     * @return list<string>
     */
    public static function getAllowableEnumValues(): array
    {
        return array_map(fn (self $enum): string => $enum->value, self::cases());
    }
}

