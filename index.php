<?php
/**
 * Request tipo:
 *   /proxmox-api/?r=/nodes
 *   /proxmox-api/?r=/nodes/ve1/status
 *   /proxmox-api/?n=pve1&r=/nodes/pve1/lxc/101/status/current
 */

// Antes de todo, si la peticion es OPTIONS, termina la solicitud
set_cors();



require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';
use Proxmox\Request;
$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');


$api_request = $_GET['r'] ?? null;
$nodo        = $_GET['n'] ?? null;

if (! $api_request) {
    header($protocol .' 410 Falta api request');
    echo "Falta api request";
    exit();
}

// Configs del nodo solicitado
if ($nodo && !empty($configure[$nodo])) {
    $configure = $configure[$nodo];
} else {
    $configure = $configure[0];
}


// Va en el config.php
// $configure = [
//     'hostname' => 'host.name',
//     'username' => 'root',
//     'realm'    => 'pve',
//     'password' => 'password'
// ];
Request::Login($configure); // Login ..

// Request($path, array $params = null, $method="GET")
$data = Request::Request($api_request, null, 'GET');

if ($data) {
    // Contenido retornado es JSON, requerido por algunos navegadores
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    header($protocol .' 405 Error');
    echo "Error, data vacio";
    exit();
}









/**
 * set_cors
 * Setea las cabeceras necesarias para permitir
 * acceso a xmlhttprequests desde otro dominio (CORS).
 *
 * @param  string  $domain   Si se especifica uno, se establecen cabeceras CORS
 *                           para permitir acceso a ese dominio.
 * @return void
 */
function set_cors()
{
        // Permitir el dominio de origen
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Ajustar aqui dominios para consumo permitidos
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache por 1 dia
        }

        // Access-Control headers recibidas durante la petición OPTIONS
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            die();
        }
}