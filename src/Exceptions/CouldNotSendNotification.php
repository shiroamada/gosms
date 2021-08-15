<?php

namespace NotificationChannels\GoSms\Exceptions;

use DomainException;
use Exception;

class CouldNotSendNotification extends Exception
{
    protected static $errorDescriptions = [
        'E01' => 'Invalid Company ID Error',
        'E02' => 'Invalid User ID',
        'E03' => 'Invalid Password',
        'E04' => 'Invalid encrypted code',
        'E05' => 'Invalid Gateway code',
        'E06' => 'Invalid Message mode',
        'E07' => 'Invalid Message type',
        'E08' => 'Invalid hp number',
        'E09' => 'Invalid group id',
        'E10' => 'Invalid message text or invalid message length',
        'E11' => 'Invalid timer',
        'E12' => 'Invalid mask id',
        'E13' => 'Invalid shortcode',
        'E14' => 'Invalid charge code',
        'E15' => 'Invalid mesg_id',
        'E16' => 'Insufficient credit',
        'E17' => 'HP list exceeds limit or HP not supported',
        'E18' => 'Company module not match',
        'E19' => 'Message ID duplicated',
        'E20' => 'SMS EAPI Internal Error',
        'E21' => 'Invalid URL',
        'E22' => 'Sender IP not allowed',
        'E23' => '3Series shortcode promo message is not allowed for MODE=BUK',
        'E24' => 'Symbol not supported: `~[]{}',
        'E99' => 'Internal Error'
    ];

    /**
     * Thrown when recipient's phone number is missing.
     *
     * @return static
     */
    public static function missingRecipient()
    {
        return new static('Notification was not sent. Phone number is missing.');
    }

    /**
     * Thrown when content length is greater than 800 characters.
     *
     * @return static
     */
    public static function contentLengthLimitExceeded()
    {
        return new static(
            'Notification was not sent. Content length may not be greater than 800 characters.'
        );
    }

    /**
     * Thrown when we're unable to communicate with smsc.ru.
     *
     * @param  DomainException  $exception
     *
     * @return static
     */
    public static function exceptionGoSmsRespondedWithAnError($errorCode)
    {
        return new static(
            "gosms responded with an error '{$errorCode} : " . self::$errorDescriptions[$errorCode] . "'"
        );
    }

    /**
     * Thrown when we're unable to communicate with smsc.ru.
     *
     * @param  Exception  $exception
     *
     * @return static
     */
    public static function couldNotCommunicateWithGoSms(Exception $exception, $request)
    {
        return new static("The communication with gosms.com.my failed. Reason: {$exception->getMessage()}, Request: {$request}");
    }
}
