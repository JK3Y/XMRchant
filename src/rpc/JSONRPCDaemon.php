<?php

namespace JK3Y\Xmrchant\RPC;

// JSON RPC backend for Monero Daemon

use function JK3Y\Xmrchant\Backend\from_atomic;
use JK3Y\Xmrchant\Backend\Transaction;

class JSONRPCDaemon {

    protected $url = null;
    protected $curl_options = array(
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 8
    );
    private $httpErrors = array(
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        408 => '408 Request Timeout',
        500 => '500 Internal Server Error',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable'
    );

    public function __construct($protocol = 'http', $host = '127.0.0.1', $port = 18081, $path = '/json_rpc', $user = null, $pass = null)
    {
        $this->url = $protocol . '://' . $host . ':' . $port;
    }

    public function info()
    {
        return $this->raw_jsonrpc_request('get_info');
    }

    public function send_transaction($blob, $relay = true)
    {
        $data = array(
            'tx_as_hex' => $blob,
            'do_not_relay' => !$relay
        );
        return $this->raw_request('/sendrawtransaction', $data);
    }

    public function mempool()
    {
        $txs = array();
        $result = $this->raw_request('/get_transaction_pool');
        foreach ($result['transactions'] as $tx) {
            array_push($txs, new Transaction([
                'hash' => $tx['id_hash'],
                'fee' => from_atomic($tx['fee']),
                'timestamp' => date_parse($tx['receive_time'])
            ]));
        }
        return $txs;
    }

    public function raw_request($path, $data = [])
    {
        $response = $this->makeRequest($path, $data);
        return $this->checkResponse($response, $data);
    }

    public function raw_jsonrpc_request($method, $params = null)
    {
        static $requestId = 0;
        $requestId++;

        $this->validate(false === is_scalar($method), 'Method name has no scalar value.');

        $data = array(
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $requestId
        );

        $response = $this->makeRequest('/json_rpc', $data);
        return $this->checkResponse($response, $data);
    }

    public function makeRequest($path, $data)
    {
        $data = json_encode($data);
        $ch = curl_init();

        if (!$ch) throw new RuntimeException("Couldn't initialize a cURL session.");

        curl_setopt($ch, CURLOPT_URL, $this->url . $path);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (!curl_setopt_array($ch, $this->curl_options)) throw new RuntimeException('Error while getting cURL options.');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (isset($this->httpErrors[$httpCode])) throw new RuntimeException('Response HTTP Error - ' . $this->httpErrors[$httpCode]);
        if (0 < curl_errno($ch)) throw new RuntimeException('Unable to connect to ' . $this->url . '. Error: ' . curl_error($ch));

        curl_close($ch);
        return $response;
    }

    public function checkResponse($response, $data)
    {
        $requestId = $data['id'];
        $decoded_response = json_decode($response, true);
        $json_error_msg = $this->JSONLastErrorMsg();

        $this->validate(!is_null($json_error_msg), $json_error_msg . ': ' . $response);
        $this->validate(empty($decoded_response['id']), 'Invalid response data structure: ' . $response);
        $this->validate($decoded_response['id'] != $requestId,
            'Request ID: ' . $requestId . ' is different from Response ID: ' . $decoded_response['id']);

        if (isset($decoded_response['error'])) {
            $errorMessage = 'Request have return error: ' . $decoded_response['error']['message'] . '; ' . "\n" .
                'Request: ' . json_encode($data) . '; ';

            if (isset($decoded_response['error']['data'])) {
                $errorMessage .= "\n" . 'Error Data: ' . $decoded_response['error']['data'];
            }
            $this->validate(!is_null($decoded_response['error']), $errorMessage);
        }
        return $decoded_response['result'];
    }



    public function validate($failed, $err)
    {
        if ($failed) throw new RuntimeException($err);
    }

    function JSONLastErrorMsg()
    {
        static $errors = array(
            JSON_ERROR_NONE           => 'No error',
            JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
            JSON_ERROR_SYNTAX         => 'Syntax error',
            JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        $error = json_last_error();
        return array_key_exists($error, $errors) ? $errors[$error] : 'Unknown error (' . $error . ')';
    }
}