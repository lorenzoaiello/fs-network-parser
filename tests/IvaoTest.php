<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class IvaoTest extends TestCase
{
    public function testGetStatusSuccessful()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-status.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertGreaterThan(0, strpos($var->getStatus(), '; TEST'));
        unset($var);
    }
    
    public function testGetStatusExceptionHandling()
    {
        $mock = new MockHandler([
            new Response(404, [], ''),
            new Response(200, [], 'url1='),
            new Response(200, [], 'url0=')
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
    
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $var->getStatus();
        unset($var);
    
        $this->expectException(Exception::class);
        $this->expectExceptionCode(40000);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $var->getStatus();
        unset($var);
    }
    
    public function testParseStatusSuccessful()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-status.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
    
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $this->assertCount(8, $var->parseStatus());
        $this->assertTrue(isset($var->parseStatus()['url0']));
        $this->assertTrue(isset($var->parseStatus()['url1']));
        unset($var);
    }
    
    public function testParseStatusExceptionHandling()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-status.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $this->expectException(Exception::class);
        $this->expectExceptionCode(40002);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $var->parseStatus();
        unset($var);
    }
    
    public function testGetDataSuccessful()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-status.txt')),
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-data.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $this->assertTrue(is_object($var->getData()));
        unset($var);
    }
    
    public function testGetDataExceptionHandling()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-status.txt')),
            new Response(404, [], '')
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
    
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
    
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $var->getData();
        unset($var);
    }
    
    public function testParseDataSuccessful()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-status.txt')),
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-data.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $this->assertTrue(is_object($var->getData()));
        $this->assertTrue(is_array($var->parseData()));
        $this->assertCount(4, $var->parseData());
        
        $this->assertCount(6, $var->parseData()['general']);
        $this->assertEquals('6', $var->parseData()['general']['VERSION']);
        
        $this->assertCount(9, $var->parseData()['servers']);
        $this->assertCount(5, $var->parseData()['servers']['185.34.216.31']);
        $this->assertEquals('Europe', $var->parseData()['servers']['185.34.216.31']['location']);
        
        $this->assertCount(1013, $var->parseData()['pilots']);
        $this->assertCount(46, $var->parseData()['pilots'][0]);
        $this->assertEquals('5BCEM', $var->parseData()['pilots'][0]['callsign']);
        $this->assertEquals('CIRCUITS', $var->parseData()['pilots'][0]['planned_route']);
        
        $this->assertCount(128, $var->parseData()['controllers']);
        $this->assertCount(46, $var->parseData()['controllers'][0]);
        $this->assertEquals('ARCH_OBS_1', $var->parseData()['controllers'][0]['callsign']);
        $this->assertEquals('', $var->parseData()['controllers'][0]['planned_route']);
        unset($var);
    }
    
    public function testParseDataExceptionHandling()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/ivao-status.txt')),
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $this->expectException(Exception::class);
        $this->expectExceptionCode(40004);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $var->parseData();
        unset($var);
    }
}
