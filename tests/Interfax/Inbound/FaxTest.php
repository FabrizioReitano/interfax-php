<?php
/**
 * Interfax
 *
 * (C) InterFAX, 2016
 *
 * @package interfax/interfax
 * @author Interfax <dev@interfax.net>
 * @author Mike Smith <mike.smith@camc-ltd.co.uk>
 * @copyright Copyright (c) 2016, InterFAX
 * @license MIT
 */
namespace Interfax\Inbound;

use Interfax\BaseTest;
use GuzzleHttp\Psr7\Response;

class FaxTest extends BaseTest
{
    public function test_markRead()
    {
        $client = $this->getMockBuilder('Interfax\Client')
            ->disableOriginalConstructor()
            ->setMethods(array('post'))
            ->getMock();

        $client->expects($this->once())
            ->method('post')
            ->with('/inbound/faxes/854759652', ['query' => ['unread' => false]])
            ->will($this->returnValue(''));

        $fax = new Fax($client, 854759652);

        $this->assertTrue($fax->markRead());
    }

    public function test_markUnread()
    {
        $client = $this->getMockBuilder('Interfax\Client')
            ->disableOriginalConstructor()
            ->setMethods(array('post'))
            ->getMock();

        $client->expects($this->once())
            ->method('post')
            ->with('/inbound/faxes/854759652', ['query' => ['unread' => true]])
            ->will($this->returnValue(''));

        $fax = new Fax($client, 854759652);

        $this->assertTrue($fax->markUnread());
    }

    public function test_resend()
    {
        $client = $this->getMockBuilder('Interfax\Client')
            ->disableOriginalConstructor()
            ->setMethods(array('post'))
            ->getMock();

        $client->expects($this->once())
            ->method('post')
            ->with('/inbound/faxes/854759652/resend')
            ->will($this->returnValue(''));

        $fax = new Fax($client, 854759652);

        $this->assertTrue($fax->resend());
    }

    public function test_resend_with_email()
    {
        $client = $this->getMockBuilder('Interfax\Client')
            ->disableOriginalConstructor()
            ->setMethods(array('post'))
            ->getMock();

        $client->expects($this->once())
            ->method('post')
            ->with('/inbound/faxes/854759652/resend', ['query' => ['email' => 'foo@bar.com']])
            ->will($this->returnValue(''));

        $fax = new Fax($client, 854759652);

        $this->assertTrue($fax->resend('foo@bar.com'));
    }

    public function test_image()
    {
        $container = [];
        $resp_resource = fopen(__DIR__ .'/../test.pdf', 'r');
        $stream = \GuzzleHttp\Psr7\stream_for($resp_resource);
        $client = $this->getClientWithResponses([
            new Response(200, [], $stream)
        ], $container);

        $result_image = $this->getMockBuilder('Interfax\Image')->disableOriginalConstructor()->getMock();
        $factory = $this->getFactory([
//            $result_image
            [$result_image, [$stream]]
        ]);

        $fax = new Fax($client, 854759652, [], $factory);
        //$image = $fax->image();
        $this->assertEquals($result_image, $fax->image());
        $transaction = $container[0];
        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('/inbound/faxes/854759652/image', $transaction['request']->getUri()->getPath());

        fclose($resp_resource);
    }
}