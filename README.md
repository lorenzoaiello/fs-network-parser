# FlightSim Network Parser

Description

## Installation

asdf

## Usage

```
$ composer require lorenzoaiello/fs-network-parser
```

```php
require 'vendor/autoload.php';

// VATSIM Only Data Retrieval
$data = new lorenzoaiello\FlightSim\NetworkParser\Vatsim($client);
$general = $data['general'];
$pilots = $data['pilots'];
$atc = $data['atc'];
$voice_servers = $data['voice_servers'];
$servers = $data['servers'];

// IVAO Only Data Retrieval
$data = new lorenzoaiello\FlightSim\NetworkParser\Ivao($client);
$general = $data['general'];
$pilots = $data['pilots'];
$atc = $data['atc'];
```

Sample Data Objects:
- [VATSIM](docs/vatsim-sample-object.txt)
- [IVAO](docs/ivao-sample-object.txt)

## Contributing

1. Fork it (https://github.com/lorenzoaiello/fs-network-parser/fork)
2. Create your feature branch (git checkout -b feature/fooBar)
3. Commit your changes (git commit -am 'Add some fooBar')
4. Push to the branch (git push origin feature/fooBar)
5. Create a new Pull Request
