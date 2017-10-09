<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\Utility;

use Cake\I18n\Time;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Date/Time/Timezone Utilities
 *
 * Excuse the naming, but all the good
 * ones are already taken.
 */
class DTZone
{
    /**
     * Get application timezone
     *
     * If application timezone is not configured,
     * fallback on UTC.
     *
     * @todo Move to Qobo/Utils
     * @return string Timezone string, like UTC
     */
    public static function getAppTimeZone()
    {
        $result = 'UTC';
        $appTimezone = Time::now()->format('e');
        if (!empty($appTimezone)) {
            $result = $appTimezone;
        }

        return $result;
    }

    /**
     * Convert a given value to DateTime instance
     *
     * @throws \InvalidArgumentException when cannot convert to \DateTime
     * @param mixed $value Value to convert (string, Time, DateTime, etc)
     * @param \DateTimeZone $dtz DateTimeZone instance
     * @return \DateTime
     */
    public static function toDateTime($value, DateTimeZone $dtz)
    {
        // TODO : Figure out where to move. Can vary for different source objects
        $format = 'Y-m-d H:i:s';

        if (is_string($value)) {
            $value = strtotime($value);
            $value = date($format, $value);

            return new DateTime($value, $dtz);
        }

        if ($value instanceof Time) {
            $value = $value->format($format);

            return new DateTime($value, $dtz);
        }

        if ($value instanceof DateTime) {
            return $value;
        }

        throw new InvalidArgumentException("Type [" . gettype($value) . "] is not supported for date/time");
    }

    /**
     * Offset DateTime value to UTC
     *
     * NOTE: This is a temporary work around until we fix our handling of
     *       the application timezones.  Database values should always be
     *       stored in UTC no matter what.  Otherwise, you will be riding
     *       a bike which is on fire, while you are on fire, and everything
     *       around you is on fire.  See Redmine ticket #4336 for details.
     *
     * @param \DateTime $value DateTime value to offset
     * @return \DateTime
     */
    public static function offsetToUtc(DateTime $value)
    {
        $result = $value;

        $dtz = $value->getTimezone();
        if ($dtz->getName() === 'UTC') {
            return $result;
        }

        $epoch = time();
        $transitions = $dtz->getTransitions($epoch, $epoch);

        $offset = $transitions[0]['offset'];
        $result = $result->modify("-$offset seconds");

        return $result;
    }
}
