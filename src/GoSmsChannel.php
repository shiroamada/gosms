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
        $message->content = html_entity_decode($message->content, ENT_QUOTES, 'utf-8');
        $message->content = urlencode($message->content);

        //the sms format must start with 6
        $valid_mobile = '';

        if ($recipient[0] == '6') {
            $valid_mobile = $recipient;
        }

        if ($recipient[0] == '0') {
            $valid_mobile = '6'.$recipient;
        }

        if ($recipient[0] == '+') {
            $valid_mobile = substr($recipient, 1);
        }

        $params = [
            'hp'        => $valid_mobile,
            'mesg'      => $message->content,
            'sender'    => $message->from,
        ];

        if ($message->sendAt instanceof \DateTimeInterface) {
            $params['time'] = '0'.$message->sendAt->getTimestamp();
        }

        $this->gosms->send($params);
    }
}
