<?php
require_once 'SearchService.php';
require_once 'SearchResult.php';
require_once 'AustraliaAdapter.php';

if (php_sapi_name() != "cli") {
    die('Only CLI allowed');
}

$search = new SearchService(new AustraliaAdapter());

try {
    echo $search->doSearch()->asJson();
} catch (Exception $e) {
    echo $e->getMessage();
}