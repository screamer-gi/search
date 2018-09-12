<?php
/**
 * Usage: php s.php search phrase
 */

if (php_sapi_name() != "cli") {
    die('Only CLI allowed');
}

if (empty($_SERVER['argv'][1])) {
    throw new Exception('Search word is required');
}
$word = implode(' ', array_map('trim', array_slice($_SERVER['argv'], 1)));
$isWordPhrase = strpos($word, ' ') !== false;

$cookiePath = __DIR__ . '/cookie';
$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36';
$results = [];
$page = 0;
$fetchResultCount = true;


touch($cookiePath);
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://search.ipaustralia.gov.au/trademarks/search/advanced',
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => false,
    CURLOPT_USERAGENT => $userAgent,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_COOKIEFILE => $cookiePath,
    CURLOPT_COOKIEJAR => $cookiePath,
]);

$data = curl_exec($ch);
if ($data === false) {
    var_dump(curl_error($ch));
    die;
} elseif (($httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 200) {
    var_dump($httpCode);
    var_dump($data);
    die;
}

if (preg_match('|<meta name="_csrf" content="(.*?)"/>|', $data, $matches)) {
    $csrf = $matches[1];
} else {
    curl_close($ch);
    die('no csrf');
}

sleep(3);

if ($fetchResultCount) {
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://search.ipaustralia.gov.au/trademarks/search/count?_csrf=92275217-09c7-4036-aeef-9aba1363fde5&_sw=on&ct=A&dateType=LODGEMENT_DATE&ieOp%5B0%5D=AND&ieOp%5B1%5D=AND&irOp=AND&it%5B0%5D=PART&it%5B1%5D=PART&it%5B2%5D=PART&it%5B3%5D=PART&nameField%5B0%5D=OWNER&undefined=false&weOp%5B0%5D=AND&weOp%5B1%5D=AND&wp=asti+martini&wps=false&wrOp=AND&wt%5B0%5D=PART&wt%5B1%5D=PART&wt%5B2%5D=PART&wt%5B3%5D=PART" . ($isWordPhrase ? 'wp=' : 'wv%5B0%5D=') . rawurlencode($word),
        CURLOPT_REFERER => 'https://search.ipaustralia.gov.au/trademarks/search/advanced',
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => false,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_COOKIEFILE => $cookiePath,
        CURLOPT_COOKIEJAR => $cookiePath,
        CURLOPT_HTTPHEADER => [
            'Accept' => 'application/json, text/plain, */*',
        ],
    ]);

    $data = curl_exec($ch);
    if ($data === false) {
        var_dump(curl_error($ch));
        curl_close($ch);
        die;
    } elseif (($httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 200) {
        var_dump($httpCode);
        var_dump($data);
        curl_close($ch);
        die;
    }

    $resultCount = json_decode($data, true)['count'] ?? 0;

    if (!$resultCount) {
        curl_close($ch);
        echo json_encode([]);
        exit;
    }

    sleep (1);
}

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://search.ipaustralia.gov.au/trademarks/search/doSearch',
    CURLOPT_REFERER => 'https://search.ipaustralia.gov.au/trademarks/search/advanced',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        '_csrf'           => $csrf,
        'wv[0]'           => !$isWordPhrase ? $word : '',
        'wt[0]'           => 'PART',
        'weOp[0]'         => 'AND',
        'wv[1]'           => '',
        'wt[1]'           => 'PART',
        'wrOp'            => 'AND',
        'wv[2]'           => '',
        'wt[2]'           => 'PART',
        'weOp[1]'         => 'AND',
        'wv[3]'           => '',
        'wt[3]'           => 'PART',
        'iv[0]'           => '',
        'it[0]'           => 'PART',
        'ieOp[0]'         => 'AND',
        'iv[1]'           => '',
        'it[1]'           => 'PART',
        'irOp'            => 'AND',
        'iv[2]'           => '',
        'it[2]'           => 'PART',
        'ieOp[1]'         => 'AND',
        'iv[3]'           => '',
        'it[3]'           => 'PART',
        'wp'              => $isWordPhrase ? $word : '',
        '_sw'             => 'on',
        'classList'       => '',
        'ct'              => 'A',
        'status'          => '',
        'dateType'        => 'LODGEMENT_DATE',
        'fromDate'        => '',
        'toDate'          => '',
        'ia'              => '',
        'gsd'             => '',
        'endo'            => '',
        'nameField[0]'    => 'OWNER',
        'name[0]'         => '',
        'attorney'        => '',
        'oAcn'            => '',
        'idList'          => '',
        'ir'              => '',
        'i'               => '',
        'c'               => '',
        'originalSegment' => '',
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type' => 'application/x-www-form-urlencoded',
    ],
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => false,
    CURLOPT_USERAGENT => $userAgent,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_COOKIEFILE => $cookiePath,
    CURLOPT_COOKIEJAR => $cookiePath,
]);

$data = curl_exec($ch);
if ($data === false) {
    var_dump(curl_error($ch));
    curl_close($ch);
    die;
} elseif (($httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 302) {
    var_dump($httpCode);
    var_dump($data);
    curl_close($ch);
    die;
}

$resultUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
if (strpos($resultUrl, 'view') !== false) {
    $resultUrl = preg_replace('|view/\d+|', 'result', $resultUrl);
}
$referer = 'https://search.ipaustralia.gov.au/trademarks/search/doSearch';

do {

    $paginatedUrl = $resultUrl . ($page ? "&p=$page" : '');
    curl_setopt_array($ch, [
        CURLOPT_URL => $paginatedUrl,
        CURLOPT_HTTPGET => true,
        CURLOPT_REFERER => $referer,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => false,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_COOKIEFILE => $cookiePath,
        CURLOPT_COOKIEJAR => $cookiePath,
    ]);

    $data = curl_exec($ch);
    if ($data === false) {
        var_dump(curl_error($ch));
        curl_close($ch);
        die;
    } elseif (($httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 200) {
        var_dump($httpCode);
        var_dump($data);
        curl_close($ch);
        die;
    }

    $startResultsTable = strpos($data, '<table id="resultsTable');
    $endResultsTable   = strpos($data, '</table>', $startResultsTable);
    $resultsTable      = substr($data, $startResultsTable, $endResultsTable - $startResultsTable);
    preg_match_all('|<tbody .*?>(.*?)</tbody>|su', $resultsTable, $matches);

    $results = array_reduce($matches[1], function($result, $row) {
        preg_match('|/view/(\d+)|u', $row, $number);
        $hasImage = preg_match('|src="(.*?)"|u', $row, $image);
        preg_match('|<td class="trademark words".*?>(.*?)</td>|su', $row, $name);
        preg_match('|<td class="classes ">(.*?)</td>|su', $row, $classes);
        preg_match('|<td class="status ">\s*<i[^<]*</i>(.*?)<p class="lowerStatus">(.*?)</p>|su', $row, $status);
        $result[] = [
            'number'           => $number[1],
            'logo_url'         => $hasImage ? $image[1] : false,
            'name'             => trim($name[1]),
            'classes'          => trim(str_replace("\n", ' ', $classes[1])),
            'status1'          => trim($status[1]),
            'status2'          => trim($status[2]),
            'details_page_url' => 'https://search.ipaustralia.gov.au/trademarks/search/view/' . $number[1],
        ];
        return $result;
    }, $results);

    $referer = $paginatedUrl;
    $hasNextPage = strpos($data, 'data-gotopage="' . (++$page) . '"') !== false;
    usleep(rand(2000000, 4000000));

} while ($hasNextPage);

echo json_encode($results);
echo "\nTotal: " . count($results);

curl_close($ch);
