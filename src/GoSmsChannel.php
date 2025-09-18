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
     * @throws \NotificationChannels\GoSms\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        // Resolve the recipient using the notifiable's route method with
        // backward compatibility for signatures without the notification param.
        $to = null;
        $method = 'routeNotificationForGoSms';

        if (method_exists($notifiable, $method)) {
            try {
                $ref = new \ReflectionMethod($notifiable, $method);
                $to = $ref->getNumberOfParameters() > 0
                    ? $notifiable->{$method}($notification)
                    : $notifiable->{$method}();
            } catch (\ReflectionException $e) {
                $to = $notifiable->routeNotificationFor('gosms', $notification);
            }
        } else {
            $to = $notifiable->routeNotificationFor('gosms', $notification);
        }

        if (empty($to)) {
            throw CouldNotSendNotification::missingRecipient();
        }

        $message = $notification->toGoSms($notifiable);

        if (is_string($message)) {
            $message = new GoSmsMessage($message);
        }

        $this->sendMessage($to, $message);
    }

    /**
     * @param $recipient
     * @param  GoSmsMessage  $message
     *
     * @throws CouldNotSendNotification
     */
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
        ];

        if ($message->sendAt instanceof \DateTimeInterface) {
            $params['time'] = '0'.$message->sendAt->getTimestamp();
        }

        $this->gosms->send($params);
    }
}
