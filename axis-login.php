<?php

require_once 'vendor/autoload.php'; // Include Composer's autoloader

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;

// Define the data to be encoded
$dataToEncode = [
    'Data' => [
        'userName' => 'alwebuser',
        'password' => 'acid_qa',
    ],
    'Risks' => [],
];

$privateKey = JWK::createFromKeyFile('path/to/your/private-key.pem');

$jwe = (new JWEBuilder())
    ->create()
    ->withPayload(json_encode($dataToEncode))
    ->withSharedProtectedHeader(['alg' => 'RSA-OAEP-256'])
    ->withRecipientKey($privateKey)
    ->addRecipient();

$jws = (new JWSBuilder(new StandardConverter(), new AlgorithmManager([new RS256()])))
    ->create()
    ->withPayload($jwe->getToken())
    ->addSignature($privateKey, ['alg' => 'RS256']);

$encodedToken = $jws->build();

$url = 'https://sakshamuat.axisbank.co.in/gateway/api/v2/CRMNext/login';

$headers = [
    'Content-Type: application/json',
    // Add any other required headers here
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedToken);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo 'API Response: ' . $response;
}

curl_close($ch);
