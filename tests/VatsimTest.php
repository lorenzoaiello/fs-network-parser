<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class VatsimTest extends TestCase
{
    public function testGetStatusSuccessful()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-status.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
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
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
        $var->getStatus();
        unset($var);
    
        $this->expectException(Exception::class);
        $this->expectExceptionCode(40000);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
        $var->getStatus();
        unset($var);
    }
    
    public function testParseStatusSuccessful()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-status.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
    
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $this->assertCount(5, $var->parseStatus());
        $this->assertCount(3, $var->parseStatus()['url0']);
        $this->assertCount(3, $var->parseStatus()['url1']);
        unset($var);
    }
    
    public function testParseStatusExceptionHandling()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-status.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $this->expectException(Exception::class);
        $this->expectExceptionCode(40002);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
        $var->parseStatus();
        unset($var);
    }
    
    public function testGetDataSuccessful()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-status.txt')),
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-data.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $this->assertTrue(is_object($var->getData()));
        unset($var);
    }
    
    public function testGetDataExceptionHandling()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-status.txt')),
            new Response(404, [], '')
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
    
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
    
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $var->getData();
        unset($var);
    }
    
    public function testParseDataSuccessful()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-status.txt')),
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-data.txt'))
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $this->assertTrue(is_object($var->getData()));
        $this->assertTrue(is_array($var->parseData()));
        $this->assertCount(6, $var->parseData());
        
        $this->assertCount(5, $var->parseData()['general']);
        $this->assertEquals('8', $var->parseData()['general']['VERSION']);
        
        $this->assertCount(24, $var->parseData()['voice_servers']);
        $this->assertCount(5, $var->parseData()['voice_servers']['canada.voice.vatsim.net']);
        $this->assertEquals('Canada', $var->parseData()['voice_servers']['canada.voice.vatsim.net']['location']);
        
        $this->assertCount(15, $var->parseData()['servers']);
        $this->assertCount(5, $var->parseData()['servers']['69.42.61.44']);
        $this->assertEquals('Toronto, Canada', $var->parseData()['servers']['69.42.61.44']['location']);
        
        $this->assertCount(44, $var->parseData()['prefile']);
        $this->assertCount(41, $var->parseData()['prefile'][0]);
        $this->assertEquals('4XACD', $var->parseData()['prefile'][0]['callsign']);
        $this->assertEquals('DELN1A DELNA R650 NALSO/N0455F340 J10 ZFR T94 MZD J10 TOMAL DCT SOLIN ', $var->parseData()['prefile'][0]['planned_route']);
        
        $this->assertCount(891, $var->parseData()['pilots']);
        $this->assertCount(41, $var->parseData()['pilots'][0]);
        $this->assertEquals('2DEER', $var->parseData()['pilots'][0]['callsign']);
        $this->assertEquals('K0902S1220 SAS12D SASAN W161 VMB A593 VYK VYK09A', $var->parseData()['pilots'][0]['planned_route']);
        
        $this->assertCount(95, $var->parseData()['controllers']);
        $this->assertCount(41, $var->parseData()['controllers'][0]);
        $this->assertEquals('AH_OBS', $var->parseData()['controllers'][0]['callsign']);
        $this->assertEquals('', $var->parseData()['controllers'][0]['planned_route']);
        unset($var);
    }
    
    public function testParseDataExceptionHandling()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__.'/payloads/vatsim-status.txt')),
        ]);
        $handler = HandlerStack::create($mock);
        $client_options = ['handler' => $handler];
        $client = new Client($client_options);
        
        $this->expectException(Exception::class);
        $this->expectExceptionCode(40004);
        
        $var = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client, false);
        $this->assertTrue(is_object($var->getStatus()));
        $this->assertTrue(is_array($var->parseStatus()));
        $var->parseData();
        unset($var);
    }
}
