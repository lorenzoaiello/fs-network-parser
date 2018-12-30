<?php

namespace lorenzoaiello\FlightSim\NetworkParser;

use GuzzleHttp\Client;

class Ivao
{
    private $client = null;
    
    private $status_url        = 'https://www.ivao.aero/whazzup/status.txt';
    private $status_data_raw   = null;
    private $status_data_clean = null;
    
    private $data_url        = null;
    private $data_data_raw   = null;
    private $data_data_clean = null;
    
    private $sections = array(
        'GENERAL' => '_getGeneral',
        'CLIENTS' => '_getClients',
        'SERVERS' => '_getServers',
    );
    
    private $currentProcessor = '';
    
    public function __construct($client = null, $autoinit = true)
    {
        if ($client) {
            $this->client = $client;
        }
        
        if (! $client) {
            $this->client = new Client();
        }
        
        if ($autoinit) {
            $this->getStatus();
            $this->parseStatus();
            $this->getData();
            $this->parseData();
        }
    }
    
    public function getStatus()
    {
        if ($this->status_data_raw !== null) {
            return $this->status_data_raw;
        }
        $response = $this->client->request('GET', $this->status_url);
        $code = $response->getStatusCode();
        if ($code !== 200) {
            throw new \Exception('Error retrieving IVAO status page. Endpoint returned: ' . $code, $code);
        }
        
        $response_body = $response->getBody();
        
        if (strpos($response_body, 'url0=') == 0) {
            throw new \Exception('Malformed response returned. Missing url0.', 40000);
        }
        
        return $this->status_data_raw = $response_body;
    }
    
    public function parseStatus()
    {
        if ($this->status_data_raw == null) {
            throw new \Exception('No raw status data available. Did you run getStatus() first?', 40002);
        }
        
        if ($this->status_data_clean !== null) {
            return $this->status_data_clean;
        }
        
        return $this->status_data_clean = $this->my_parse_ini($this->status_data_raw);
    }
    
    public function getData()
    {
        if ($this->data_url == null) {
            $this->data_url = $this->status_data_clean['url0'];
        }
        
        $response = $this->client->request('GET', $this->status_url);
        $code = $response->getStatusCode();
        if ($code !== 200) {
            throw new \Exception('Error retrieving IVAO data page. Endpoint returned: ' . $code, $code);
        }
        
        $response_body = $response->getBody();
        
        if (strpos($response_body, '!CLIENTS') == 0) {
            throw new \Exception('Malformed response returned. Missing clients.', 40003);
        }
        
        return $this->data_data_raw = $response_body;
    }
    
    public function parseData()
    {
        if ($this->data_data_raw == null) {
            throw new \Exception('No raw data available. Did you run getData() first?', 40004);
        }
        
        if ($this->data_data_clean !== null) {
            return $this->data_data_clean;
        }
        
        $lines = explode("\n", $this->data_data_raw);
        foreach ($lines as $line) {
            $this->_processLine($line);
        }
        
        return $this->data_data_clean;
    }
    
    private function _processLine($line)
    {
        if (substr($line, 0, 1) == '!') {
            if (array_key_exists(substr($line, 1), $this->sections)) {
                $this->currentProcessor = $this->sections[substr($line, 1)];
                return;
            } else {
                return;
            }
        } else {
            if (isset($this->currentProcessor)) {
                if (method_exists($this, $this->currentProcessor)) {
                    $method = $this->currentProcessor;
                    $this->$method($line);
                }
            }
        }
    }
    
    private function _getGeneral($line)
    {
        $data = explode(' = ', $line);
        if (count($data) > 1) {
            list($k, $v) = $data;
            $this->data_data_clean['general'][$k] = $v;
        }
    }
    
    private function _getClients($line)
    {
        $data = explode(':', $line);
        if (count($data) == 49) {
            $client = $this->_parseClient($data);
            
            if ($data[3] == 'PILOT') {
                $this->data_data_clean['pilots'][] = $client;
            }
            if ($data[3] == 'ATC') {
                $this->data_data_clean['controllers'][] = $client;
            }
        }
    }
    
    private function _parseClient($data)
    {
        $client = array();
        
        if (count($data) == 49) {
            $client = array(
                'callsign' => $data[0],
                'vid' => $data[1],
                'name' => $data[2],
                'clienttype' => $data[3],
                'frequency' => $data[4],
                'frequency_cont' => $data[4],
                'latitude' => $data[5],
                'longitude' => $data[6],
                'altitude' => $data[7],
                'groundspeed' => $data[8],
                'planned_aircraft' => $data[9],
                'planned_tascruise' => $data[10],
                'planned_depairport' => $data[11],
                'planned_altitude' => $data[12],
                'planned_destairport' => $data[13],
                'server' => $data[14],
                'protrevision' => $data[15],
                'rating' => $data[16],
                'transponder' => $data[17],
                'facilitytype' => $data[18],
                'visualrange' => $data[19],
                'planned_revision' => $data[20],
                'planned_flighttype' => $data[21],
                'planned_deptime' => $data[22],
                'planned_actdeptime' => $data[23],
                'planned_hrsenroute' => $data[24],
                'planned_minenroute' => $data[25],
                'planned_hrsfuel' => $data[26],
                'planned_minfuel' => $data[27],
                'planned_altairport' => $data[28],
                'planned_remarks' => $data[29],
                'planned_route' => $data[30],
                'atis_message' => iconv("UTF-8", "UTF-8//IGNORE", $data[33]),
                'time_last_atis_received' => $data[34],
                'time_logon' => $data[35],
                'software_name' => $data[36],
                'software_version' => $data[37],
                'admin_version' => $data[38],
                'atcpilot_version' => $data[39],
                'planned_altairport2' => $data[40],
                'planned_type' => $data[41],
                'planned_pob' => $data[42],
                'heading' => $data[43],
                'onground' => $data[44],
                'simulator' => $data[45],
                'plane' => $data[46],
            );
        }
        
        return $client;
    }
    
    private function _getServers($line)
    {
        $data = explode(':', $line);
        if (count($data) >= 5) {
            $this->data_data_clean['servers'][$data[1]] = array(
                'ident' => $data[0],
                'hostname' => $data[1],
                'location' => $data[2],
                'name' => $data[3],
                'new_connections_allowed' => $data[4],
            );
        }
    }
    
    private function my_parse_ini($file)
    {
        $arr = array();
        $lines = explode("\n", $file);
        foreach ($lines as $line) {
            $parsed = parse_ini_string($line);
            if (empty($parsed)) {
                continue;
            }
            $key = key($parsed);
            if (isset($arr[$key])) {
                if (! is_array($arr[$key])) {
                    $tmp = $arr[$key];
                    $arr[$key] = array($tmp);
                }
                $arr[$key][] = $parsed[$key];
            } else {
                $arr[$key] = $parsed[$key];
            }
        }
        return $arr;
    }
}
