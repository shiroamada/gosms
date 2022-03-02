<?php

namespace NotificationChannels\GoSms\Test;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery as M;
use NotificationChannels\GoSms\Exceptions\CouldNotSendNotification;
use NotificationChannels\GoSms\GoSmsApi;
use NotificationChannels\GoSms\GoSmsChannel;
use NotificationChannels\GoSms\GoSmsMessage;
use PHPUnit\Framework\TestCase;

class GoSmsChannelTest extends TestCase
{
    /**
     * @var GoSmsApi
     */
    private $gosms;

    /**
     * @var GoSmsChannel
     */
    private $channel;

    /**
     * @var \DateTime
     */
    public static $sendAt;

    public function setUp(): void
    {
        parent::setUp();

        $config = [
            'company'   => 'company',
            'username'  => 'test',
            'password'  => 'test',
            'gateway'   => 'L',
            'mode'      => 'BUK',
            'type'      => 'TX',
            'charge'    => '0',
            'maskid'    => '1',
            'url'       => '0',
            'verifypwd' => '0',
        ];

        $this->gosms = M::mock(GoSmsApi::class, $config);
        $this->channel = new GoSmsChannel($this->gosms);
        $this->message = M::mock(GoSmsMessage::class);
    }

    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    /** @test */
    public function it_can_send_a_notification(): void
    {
        $this->gosms->shouldReceive('send')->once()
            ->with(
                [
                    'hp' => '60123456789',
                    'mesg' => 'hello',
                ]
            );

        $this->channel->send(new TestNotifiable(), new TestNotification());
    }

    /** @test */
    public function it_can_send_a_deferred_notification(): void
    {
        self::$sendAt = new \DateTime();

        $this->gosms->shouldReceive('send')->once()
            ->with(
                [
                    'hp' => '60123456789',
                    'mesg' => 'hello',
                    'time' => '0'.self::$sendAt->getTimestamp(),
                ]
            );

        $this->channel->send(new TestNotifiable(), new TestNotificationWithSendAt());
    }

    /** @test */
    public function it_does_not_send_a_message_when_to_missed()
    {
        $this->expectException(CouldNotSendNotification::class);

        $this->channel->send(
            new TestNotifiableWithoutRouteNotificationForGoSms(), new TestNotification()
        );
    }
}

class TestNotifiable
{
    use Notifiable;

    // Laravel v5.6+ passes the notification instance here
    // So we need to add `Notification $notification` argument to check it when this project stops supporting < 5.6
    public function routeNotificationForGoSms()
    {
        return '0123456789';
    }
}

class TestNotifiableWithoutRouteNotificationForGoSms extends TestNotifiable
{
    public function routeNotificationForGoSms()
    {
        return false;
    }
}

class TestNotification extends Notification
{
    public function toGoSms()
    {
        return GoSmsMessage::create('hello');
    }
}

class TestNotificationWithSendAt extends Notification
{
    public function toGoSms()
    {
        return GoSmsMessage::create('hello')
            ->sendAt(GoSmsChannelTest::$sendAt);
    }
}
