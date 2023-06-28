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
enum FrequencyCodes2: string
{
    /**
     * Possible values of this enum
     */
    case AD_HOC = 'AdHoc';

    case DAILY = 'Daily';

    case BI_WEEKLY = 'BiWeekly';

    case WEEKLY = 'Weekly';

    case FORTNIGHTLY = 'Fortnightly';

    case MONTHLY = 'Monthly';

    case QUARTERLY = 'Quarterly';

    case TWICE_ANNUALLY = 'TwiceAnnually';

    case ANNUALLY = 'Annually';

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


