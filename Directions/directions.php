<?php
/**
 * Created by PhpStorm.
 * User: alexgavrishev
 * Date: 1/12/16
 * Time: 10:34 AM
 */

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use \GuzzleHttp\MessageFormatter;

require_once 'vendor/autoload.php';


function createDirections(array $route) {

   $descr = [];
   foreach($route['legs'] AS $leg) {
      foreach ($leg['steps'] AS $step) {
         $travel_mode = $step['travel_mode'];
         $instructions = $step['html_instructions'];
         if ($travel_mode == 'TRANSIT') {
            if (isset($step['transit_details']['line']['vehicle'])) {
               $type = $step['transit_details']['line']['vehicle']['type'];
               if (stripos($type,'TRAIN') !== false) {
                  $instructions = 'Take '.$step['html_instructions'];
               } elseif (stripos($type,'BUS') !== false ) {
                  $short_name = isset($step['transit_details']['line']['short_name']) ? ' (#'.$step['transit_details']['line']['short_name'].')' : '';
                  $instructions = 'Take '.$step['html_instructions']. $short_name;
               }
            }
         }

         $descr[] = $instructions . ' for ' . $step['duration']['text'];
      }
   }
   return $descr;
}

$logger = new Monolog\Logger('directions');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout'));

$stack = HandlerStack::create();
$stack->push(Middleware::log($logger, new MessageFormatter(MessageFormatter::CLF)));

$client = new GuzzleHttp\Client([
   'handler' => $stack,
   'base_uri' => 'https://maps.googleapis.com'
]);

$origin = 'Park Plaza Victoria Amsterdam, Damrak 1-5, 1012 LG Amsterdam, Netherlands';
$destination = 'Schiphol Amsterdam Airport, Evert van de Beekstraat 202, 1118 CP Schiphol, Netherlands';

$response = $client->get('/maps/api/directions/json', [
   'query' => [
       'origin' => $origin,
       'destination' => $destination,
       'mode' => 'transit',
       'key' => 'AIzaSyDUDSK5jDoVKT2NdrE2FOKs4gbyjYzakZA',
      'language' => 'en'
   ]
]);

$decoded = json_decode($response->getBody(), true);

$directions = [];
foreach($decoded['routes'] AS $route) {
   $directions[] = createDirections($route);
}

echo "\n\n";
for($i = 0; $i < count($directions); $i++) {
   echo "Direction #".($i+1),"\n";
   echo implode('. ',$directions[$i]),"\n\n";
}
