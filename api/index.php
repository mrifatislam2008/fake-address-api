<?php
declare(strict_types=1);

/*
 * CXOFB Fake Address API
 * PHP 8.x compatible
 * Data source: randomuser.me
 * For testing and fictional profiles only.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function sendJson(array $data, int $status = 200): void
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

/*
 * Supported countries.
 * nat = RandomUser nationality code
 * bounds = approximate latitude/longitude range.
 *
 * These bounds are approximate and are not exact national borders.
 */
$countries = [
    'germany'        => ['name' => 'Germany',       'nat' => 'DE', 'bounds' => [47.2, 55.1, 5.8, 15.1]],
    'de'             => ['name' => 'Germany',       'nat' => 'DE', 'bounds' => [47.2, 55.1, 5.8, 15.1]],
    'united states'  => ['name' => 'United States', 'nat' => 'US', 'bounds' => [24.5, 49.4, -125.0, -66.9]],
    'united states of america' => ['name' => 'United States', 'nat' => 'US', 'bounds' => [24.5, 49.4, -125.0, -66.9]],
    'usa'            => ['name' => 'United States', 'nat' => 'US', 'bounds' => [24.5, 49.4, -125.0, -66.9]],
    'us'             => ['name' => 'United States', 'nat' => 'US', 'bounds' => [24.5, 49.4, -125.0, -66.9]],
    'united kingdom' => ['name' => 'United Kingdom', 'nat' => 'GB', 'bounds' => [50.0, 59.0, -8.2, 1.8]],
    'uk'             => ['name' => 'United Kingdom', 'nat' => 'GB', 'bounds' => [50.0, 59.0, -8.2, 1.8]],
    'gb'             => ['name' => 'United Kingdom', 'nat' => 'GB', 'bounds' => [50.0, 59.0, -8.2, 1.8]],
    'france'         => ['name' => 'France',        'nat' => 'FR', 'bounds' => [42.3, 51.1, -4.8, 8.2]],
    'fr'             => ['name' => 'France',        'nat' => 'FR', 'bounds' => [42.3, 51.1, -4.8, 8.2]],
    'canada'         => ['name' => 'Canada',        'nat' => 'CA', 'bounds' => [42.0, 60.0, -130.0, -60.0]],
    'ca'             => ['name' => 'Canada',        'nat' => 'CA', 'bounds' => [42.0, 60.0, -130.0, -60.0]],
    'australia'      => ['name' => 'Australia',     'nat' => 'AU', 'bounds' => [-43.7, -10.7, 113.0, 153.7]],
    'au'             => ['name' => 'Australia',     'nat' => 'AU', 'bounds' => [-43.7, -10.7, 113.0, 153.7]],
    'new zealand'    => ['name' => 'New Zealand',   'nat' => 'NZ', 'bounds' => [-47.3, -34.0, 166.0, 179.0]],
    'nz'             => ['name' => 'New Zealand',   'nat' => 'NZ', 'bounds' => [-47.3, -34.0, 166.0, 179.0]],
    'india'          => ['name' => 'India',         'nat' => 'IN', 'bounds' => [8.0, 35.5, 68.0, 97.5]],
    'in'             => ['name' => 'India',         'nat' => 'IN', 'bounds' => [8.0, 35.5, 68.0, 97.5]],
    'brazil'         => ['name' => 'Brazil',        'nat' => 'BR', 'bounds' => [-33.8, 5.3, -73.9, -34.8]],
    'br'             => ['name' => 'Brazil',        'nat' => 'BR', 'bounds' => [-33.8, 5.3, -73.9, -34.8]],
    'mexico'         => ['name' => 'Mexico',        'nat' => 'MX', 'bounds' => [14.5, 32.7, -118.4, -86.7]],
    'mx'             => ['name' => 'Mexico',        'nat' => 'MX', 'bounds' => [14.5, 32.7, -118.4, -86.7]],
    'spain'          => ['name' => 'Spain',         'nat' => 'ES', 'bounds' => [36.0, 43.8, -9.4, 4.4]],
    'es'             => ['name' => 'Spain',         'nat' => 'ES', 'bounds' => [36.0, 43.8, -9.4, 4.4]],
    'italy'          => ['name' => 'Italy',         'nat' => 'IT', 'bounds' => [36.6, 47.1, 6.6, 18.5]],
    'it'             => ['name' => 'Italy',         'nat' => 'IT', 'bounds' => [36.6, 47.1, 6.6, 18.5]],
    'netherlands'    => ['name' => 'Netherlands',   'nat' => 'NL', 'bounds' => [50.7, 53.6, 3.3, 7.3]],
    'nl'             => ['name' => 'Netherlands',   'nat' => 'NL', 'bounds' => [50.7, 53.6, 3.3, 7.3]],
    'switzerland'    => ['name' => 'Switzerland',   'nat' => 'CH', 'bounds' => [45.8, 47.9, 5.9, 10.5]],
    'ch'             => ['name' => 'Switzerland',   'nat' => 'CH', 'bounds' => [45.8, 47.9, 5.9, 10.5]],
    'sweden'         => ['name' => 'Sweden',        'nat' => 'SE', 'bounds' => [55.3, 69.1, 11.0, 24.2]],
    'se'             => ['name' => 'Sweden',        'nat' => 'SE', 'bounds' => [55.3, 69.1, 11.0, 24.2]],
    'norway'         => ['name' => 'Norway',        'nat' => 'NO', 'bounds' => [57.9, 71.2, 4.5, 31.2]],
    'no'             => ['name' => 'Norway',        'nat' => 'NO', 'bounds' => [57.9, 71.2, 4.5, 31.2]],
    'denmark'        => ['name' => 'Denmark',       'nat' => 'DK', 'bounds' => [54.5, 57.8, 8.0, 15.2]],
    'dk'             => ['name' => 'Denmark',       'nat' => 'DK', 'bounds' => [54.5, 57.8, 8.0, 15.2]],
    'ireland'        => ['name' => 'Ireland',       'nat' => 'IE', 'bounds' => [51.4, 55.5, -10.7, -5.9]],
    'ie'             => ['name' => 'Ireland',       'nat' => 'IE', 'bounds' => [51.4, 55.5, -10.7, -5.9]],
    'finland'        => ['name' => 'Finland',       'nat' => 'FI', 'bounds' => [59.8, 70.1, 20.5, 31.6]],
    'fi'             => ['name' => 'Finland',       'nat' => 'FI', 'bounds' => [59.8, 70.1, 20.5, 31.6]],
    'belgium'        => ['name' => 'Belgium',       'nat' => 'BE', 'bounds' => [49.5, 51.5, 2.5, 6.4]],
    'be'             => ['name' => 'Belgium',       'nat' => 'BE', 'bounds' => [49.5, 51.5, 2.5, 6.4]],
    'turkey'         => ['name' => 'Turkey',        'nat' => 'TR', 'bounds' => [36.0, 42.1, 26.0, 44.8]],
    'tr'             => ['name' => 'Turkey',        'nat' => 'TR', 'bounds' => [36.0, 42.1, 26.0, 44.8]],
    'ukraine'        => ['name' => 'Ukraine',       'nat' => 'UA', 'bounds' => [44.3, 52.4, 22.1, 40.2]],
    'ua'             => ['name' => 'Ukraine',       'nat' => 'UA', 'bounds' => [44.3, 52.4, 22.1, 40.2]],
    'iran'           => ['name' => 'Iran',          'nat' => 'IR', 'bounds' => [25.0, 39.8, 44.0, 63.3]],
    'ir'             => ['name' => 'Iran',          'nat' => 'IR', 'bounds' => [25.0, 39.8, 44.0, 63.3]],
    'serbia'         => ['name' => 'Serbia',        'nat' => 'RS', 'bounds' => [42.2, 46.2, 18.8, 23.0]],
    'rs'             => ['name' => 'Serbia',        'nat' => 'RS', 'bounds' => [42.2, 46.2, 18.8, 23.0]],
];

/* Read and validate query parameters. */
$countryInput = trim((string)($_GET['country'] ?? ''));

if ($countryInput === '') {
    sendJson([
        'success' => false,
        'error' => 'country parameter is required.',
        'example' => '?country=Germany&results=5'
    ], 400);
}

$countryKey = strtolower($countryInput);

if (!isset($countries[$countryKey])) {
    sendJson([
        'success' => false,
        'error' => 'Unsupported country.',
        'supported_countries' => array_values(array_unique(
            array_column($countries, 'name')
        ))
    ], 400);
}

$country = $countries[$countryKey];

/* Results: minimum 1, maximum 50. */
$results = filter_var(
    $_GET['results'] ?? 1,
    FILTER_VALIDATE_INT
);

if ($results === false) {
    sendJson([
        'success' => false,
        'error' => 'results must be an integer between 1 and 50.'
    ], 400);
}

$results = max(1, min($results, 50));

/* Validate optional gender parameter. */
$gender = strtolower(trim((string)($_GET['gender'] ?? '')));

if ($gender !== '' && !in_array($gender, ['male', 'female'], true)) {
    sendJson([
        'success' => false,
        'error' => 'gender must be male or female.'
    ], 400);
}

/* Build RandomUser API URL. */
$params = [
    'nat' => $country['nat'],
    'results' => $results,
    'inc' => 'name,location,phone,cell,email,picture,nat,gender'
];

if ($gender !== '') {
    $params['gender'] = $gender;
}

$apiUrl = 'https://randomuser.me/api/?' . http_build_query($params);

/* cURL request with TLS verification enabled. */
$ch = curl_init($apiUrl);

if ($ch === false) {
    sendJson([
        'success' => false,
        'error' => 'Could not initialize API request.'
    ], 500);
}

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json'
    ],
    CURLOPT_USERAGENT => 'CXOFB-Fake-Address-API/1.0'
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpStatus = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

/*
 * Do not call curl_close().
 * It is unnecessary in modern PHP and deprecated in PHP 8.5.
 */

if ($response === false) {
    error_log('RandomUser API cURL error: ' . $curlError);

    sendJson([
        'success' => false,
        'error' => 'The profile provider could not be reached.'
    ], 502);
}

if ($httpStatus < 200 || $httpStatus >= 300) {
    error_log('RandomUser API returned HTTP status: ' . $httpStatus);

    sendJson([
        'success' => false,
        'error' => 'The profile provider returned an unsuccessful response.'
    ], 502);
}

$decoded = json_decode($response, true);

if (!is_array($decoded) || !isset($decoded['results']) || !is_array($decoded['results'])) {
    sendJson([
        'success' => false,
        'error' => 'The profile provider returned invalid JSON.'
    ], 502);
}

/*
 * Generate approximate coordinates within the selected country's
 * bounding box whenever the provider's coordinates are outside it.
 *
 * Important: these are approximate test coordinates, not verified
 * locations of the generated streets or people.
 */
[$minLat, $maxLat, $minLng, $maxLng] = $country['bounds'];

function coordinateIsInBounds(
    float $lat,
    float $lng,
    float $minLat,
    float $maxLat,
    float $minLng,
    float $maxLng
): bool {
    return $lat >= $minLat
        && $lat <= $maxLat
        && $lng >= $minLng
        && $lng <= $maxLng;
}

$data = [];

foreach ($decoded['results'] as $person) {
    $name = $person['name'] ?? [];
    $location = $person['location'] ?? [];
    $coordinates = $location['coordinates'] ?? [];

    $lat = isset($coordinates['latitude'])
        ? (float)$coordinates['latitude']
        : NAN;

    $lng = isset($coordinates['longitude'])
        ? (float)$coordinates['longitude']
        : NAN;

    if (
        !is_finite($lat)
        || !is_finite($lng)
        || !coordinateIsInBounds(
            $lat,
            $lng,
            $minLat,
            $maxLat,
            $minLng,
            $maxLng
        )
    ) {
        /*
         * Use approximate coordinates inside the country's bounding box.
         * These do not guarantee a point inside the exact national border.
         */
        $lat = random_int((int)round($minLat * 10000), (int)round($maxLat * 10000)) / 10000;
        $lng = random_int((int)round($minLng * 10000), (int)round($maxLng * 10000)) / 10000;
    }

    $street = $location['street'] ?? [];
    $firstName = (string)($name['first'] ?? '');
    $lastName = (string)($name['last'] ?? '');

    $data[] = [
        'name' => [
            'full' => trim(
                (string)($name['title'] ?? '') . ' '
                . $firstName . ' ' . $lastName
            ),
            'first' => $firstName,
            'last' => $lastName
        ],
        'gender' => $person['gender'] ?? null,
        'address' => [
            'street' => trim(
                (string)($street['number'] ?? '') . ' '
                . (string)($street['name'] ?? '')
            ),
            'city' => $location['city'] ?? null,
            'state' => $location['state'] ?? null,
            'country' => $country['name'],
            'postcode' => $location['postcode'] ?? null,
            'coordinates' => [
                'lat' => number_format($lat, 4, '.', ''),
                'lng' => number_format($lng, 4, '.', '')
            ]
        ],
        'contact' => [
            'email' => $person['email'] ?? null,
            'phone' => $person['phone'] ?? null,
            'cell' => $person['cell'] ?? null
        ],
        'picture' => $person['picture']['large']
            ?? $person['picture']['medium']
            ?? $person['picture']['thumbnail']
            ?? null
    ];
}

sendJson([
    'success' => true,
    'country_input' => $countryInput,
    'country_code' => $country['nat'],
    'results_count' => count($data),
    'data' => $data,
    'powered_by' => 'Molla Mohammad Rifat Osama',
    'Telegram' => '@Molla_Mohammad_Rifat_Osama',
    'notice' => 'Profiles are fictional. Coordinates are approximate and are not verified street locations.'
]);
