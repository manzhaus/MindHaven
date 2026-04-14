<?php
require __DIR__ . '/vendor/autoload.php';

$ABLY_API_KEY = 'Hy3ikg.H4dR0g:lQ3A03GgC2DL_gAEFc1D1e66qt94QdcA1k1v8veEqSc'; // Replace this with your Ably API key

$clientOptions = new Ably\AblyRest([
    'key' => $ABLY_API_KEY
]);

$tokenParams = [];
$tokenDetails = $clientOptions->auth->requestToken($tokenParams);

header('Content-Type: application/json');
echo json_encode($tokenDetails);
