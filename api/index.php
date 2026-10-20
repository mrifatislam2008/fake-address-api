<?php
// ============================================================
// CXOFB Fake Address API
// Usage: api.php?country=United States
//        api.php?country=germany&results=5
//        api.php?country=bd (short code also works)
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ── Country name → randomuser.me nat code mapping ──────────
$countryMap = [
    // Full names (lowercase)
    'australia'      => 'AU',
    'brazil'         => 'BR',
    'canada'         => 'CA',
    'switzerland'    => 'CH',
    'germany'        => 'DE',
    'denmark'        => 'DK',
    'spain'          => 'ES',
    'finland'        => 'FI',
    'france'         => 'FR',
    'united kingdom' => 'GB',
    'uk'             => 'GB',
    'england'        => 'GB',
    'britain'        => 'GB',
    'ireland'        => 'IE',
    'india'          => 'IN',
    'iran'           => 'IR',
    'mexico'         => 'MX',
    'netherlands'    => 'NL',
    'holland'        => 'NL',
    'norway'         => 'NO',
    'new zealand'    => 'NZ',
    'serbia'         => 'RS',
    'turkey'         => 'TR',
    'ukraine'        => 'UA',
    'united states'  => 'US',
    'usa'            => 'US',
    'america'        => 'US',
    'us'             => 'US',

    // Short/ISO codes (already valid)
    'au' => 'AU',
    'br' => 'BR',
    'ca' => 'CA',
    'ch' => 'CH',
    'de' => 'DE',
    'dk' => 'DK',
    'es' => 'ES',
    'fi' => 'FI',
    'fr' => 'FR',
    'gb' => 'GB',
    'ie' => 'IE',
    'in' => 'IN',
    'ir' => 'IR',
    'mx' => 'MX',
    'nl' => 'NL',
    'no' => 'NO',
    'nz' => 'NZ',
    'rs' => 'RS',
    'tr' => 'TR',
    'ua' => 'UA',
];

// ── Helper: send JSON error ─────────────────────────────────
function sendError($msg, $code = 400, $supported = []) {
    http_response_code($code);
    $res = ['success' => false, 'error' => $msg];
    if (!empty($supported)) $res['supported_countries'] = $supported;
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Read & validate params ──────────────────────────────────
$countryInput = trim($_GET['country'] ?? '');
$results      = (int)($_GET['results'] ?? 1);
$gender       = strtolower(trim($_GET['gender'] ?? ''));
$format       = strtolower(trim($_GET['format'] ?? 'json'));

if ($countryInput === '') {
    sendError(
        'country parameter is required. Example: ?country=United States',
        400,
        array_keys($countryMap)
    );
}

$results = max(1, min($results, 50)); // clamp 1–50

$key = strtolower($countryInput);
if (!isset($countryMap[$key])) {
    sendError(
        "Unknown country: \"$countryInput\". Check supported_countries list.",
        404,
        array_keys($countryMap)
    );
}

$natCode = $countryMap[$key];

// ── Build randomuser.me URL ─────────────────────────────────
$params = [
    'nat'     => strtolower($natCode),
    'results' => $results,
    'inc'     => 'name,location,phone,cell,email,picture,nat,gender',
];
if (in_array($gender, ['male', 'female'])) {
    $params['gender'] = $gender;
}

$apiUrl = 'https://randomuser.me/api/?' . http_build_query($params);

// ── cURL call ───────────────────────────────────────────────
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_USERAGENT      => 'CXOFB-FakeAddress-API/1.0',
    CURLOPT_SSL_VERIFYPEER => false,
]);
$raw  = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $raw === false) {
    sendError('Failed to reach randomuser.me: ' . $err, 502);
}

$data = json_decode($raw, true);
if (!$data || !isset($data['results'])) {
    sendError('Invalid response from upstream API.', 502);
}

// ── Format & return ─────────────────────────────────────────
$users = [];
foreach ($data['results'] as $u) {
    $loc = $u['location'];
    $users[] = [
        'name'    => [
            'full'  => trim($u['name']['title'] . ' ' . $u['name']['first'] . ' ' . $u['name']['last']),
            'first' => $u['name']['first'],
            'last'  => $u['name']['last'],
        ],
        'gender'  => $u['gender'],
        'address' => [
            'street'   => $loc['street']['number'] . ' ' . $loc['street']['name'],
            'city'     => $loc['city'],
            'state'    => $loc['state'],
            'country'  => $loc['country'],
            'postcode' => $loc['postcode'],
            'coordinates' => [
                'lat' => $loc['coordinates']['latitude'],
                'lng' => $loc['coordinates']['longitude'],
            ],
        ],
        'contact' => [
            'email' => $u['email'],
            'phone' => $u['phone'],
            'cell'  => $u['cell'],
        ],
        'picture' => $u['picture']['large'],
    ];
}

echo json_encode([
    'success'         => true,
    'country_input'   => $countryInput,
    'country_code'    => $natCode,
    'results_count'   => count($users),
    'data'            => $users,
    'powered_by'      => ' Fake Address API',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
