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
 * 
 *
 */
enum ElectronicReceiptMethod: string
{
    /**
     * Possible values of this enum
     */
    case SMS = 'SMS';

    case EMAIL = 'Email';

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


