<?php

namespace NotificationChannels\GoSms;

use Illuminate\Notifications\Notification;
use NotificationChannels\GoSms\Exceptions\CouldNotSendNotification;

class GoSmsChannel
{
    /** @var \NotificationChannels\GoSms\GoSmsApi */
    protected $gosms;

    public function __construct(GoSmsApi $gosms)
    {
        $this->gosms = $gosms;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     *
     * @throws  \NotificationChannels\GoSms\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        $to = $notifiable->routeNotificationFor('gosms');

        if (empty($to)) {
            throw CouldNotSendNotification::missingRecipient();
        }

        $message = $notification->toGoSms($notifiable);

        if (is_string($message)) {
            $message = new GoSmsMessage($message);
        }

        $this->sendMessage($to, $message);
    }

    protected function sendMessage($recipient, GoSmsMessage $message)
    {
        // if (mb_strlen($message->content) > 800) {
        //     throw CouldNotSendNotification::contentLengthLimitExceeded();
        // }
        $message->content = bin2hex(iconv('UTF-8', 'UTF-16BE', $message->content));

        $params = [
            'hp'  => $recipient,
            'mesg'     => $message->content,
            'sender'  => $message->from,
        ];

        if ($message->sendAt instanceof \DateTimeInterface) {
            $params['time'] = '0'.$message->sendAt->getTimestamp();
        }

        $this->gosms->send($params);
    }
}