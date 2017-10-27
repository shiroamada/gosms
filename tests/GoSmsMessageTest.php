<?php

namespace NotificationChannels\GoSms\Test;

use NotificationChannels\GoSms\GoSmsMessage;

class GoSmsMessageTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_accept_a_content_when_constructing_a_message()
    {
        $message = new GoSmsMessage('hello');

        $this->assertEquals('hello', $message->content);
    }

    /** @test */
    public function it_can_accept_a_content_when_creating_a_message()
    {
        $message = GoSmsMessage::create('hello');

        $this->assertEquals('hello', $message->content);
    }

    /** @test */
    public function it_can_set_the_content()
    {
        $message = (new GoSmsMessage())->content('hello');

        $this->assertEquals('hello', $message->content);
    }

    /** @test */
    public function it_can_set_the_from()
    {
        $message = (new GoSmsMessage())->from('John_Doe');

        $this->assertEquals('John_Doe', $message->from);
    }

    /** @test */
    public function it_can_set_the_send_at()
    {
        $sendAt = date_create();
        $message = (new GoSmsMessage())->sendAt($sendAt);

        $this->assertEquals($sendAt, $message->sendAt);
    }
}
