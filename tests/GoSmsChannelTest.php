<?php

namespace NotificationChannel\GoSms\Tests;

use Mockery as M;
use Illuminate\Notifications\Notification;
use NotificationChannels\GoSms\GoSmsApi;
use NotificationChannels\GoSms\GoSmsChannel;
use NotificationChannels\GoSms\GoSmsMessage;
use NotificationChannels\GoSms\Exceptions\CouldNotSendNotification;

class GoSmsChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GoSmsApi
     */
    private $gosms;

    /**
     * @var GoSmsMessage
     */
    private $message;

    /**
     * @var GoSmsChannel
     */
    private $channel;

    /**
     * @var \DateTime
     */
    public static $sendAt;

    public function setUp()
    {
        parent::setUp();

        $this->gosms = M::mock(GoSmsApi::class, ['company', 'test', 'test', 'John_Doe']);
        $this->channel = new GoSmsChannel($this->gosms);
        $this->message = M::mock(GoSmsMessage::class);
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    /** @test */
    public function it_can_send_a_notification()
    {
        $this->gosms->shouldReceive('send')->once()
            ->with(
                [
                    'hp'  => '60123456789',
                    'mesg'     => 'hello',
                    'sender'  => 'John_Doe',
                ]
            );

        $this->channel->send(new TestNotifiable(), new TestNotification());
    }

    /** @test */
    public function it_can_send_a_deferred_notification()
    {
        self::$sendAt = new \DateTime();

        $this->gosms->shouldReceive('send')->once()
            ->with(
                [
                    'hp'  => '60123456789',
                    'mesg'     => 'hello',
                    'sender'  => 'John_Doe',
                    'time'    => '0'.self::$sendAt->getTimestamp(),
                ]
            );

        $this->channel->send(new TestNotifiable(), new TestNotificationWithSendAt());
    }

    /** @test */
    public function it_does_not_send_a_message_when_to_missed()
    {
        $this->expectException(CouldNotSendNotification::class);

        $this->channel->send(
            new TestNotifiableWithoutRouteNotificationForSmscru(), new TestNotification()
        );
    }
}

class TestNotifiable
{
    public function routeNotificationFor()
    {
        return '0123456789';
    }
}

class TestNotifiableWithoutRouteNotificationForSmscru extends TestNotifiable
{
    public function routeNotificationFor()
    {
        return false;
    }
}

class TestNotification extends Notification
{
    public function toGoSms()
    {
        return GoSmsMessage::create('hello')->from('John_Doe');
    }
}

class TestNotificationWithSendAt extends Notification
{
    public function toGoSms()
    {
        return GoSmsMessage::create('hello')
            ->from('John_Doe')
            ->sendAt(GoSmsChannelTest::$sendAt);
    }
}
