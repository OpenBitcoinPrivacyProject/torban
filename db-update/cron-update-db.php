<?php

/* Kristov Atlas 2014 */

include_once('IntersectionListFetcher.php');
include_once('../common/TorBanDB.php');

$fetcher = new IntersectionListFetcher();
$matches = $fetcher->getIntersection();

$db = new TorBanDB();
$db->open();
$db->storeNextIntersectionList($matches);
$db->close();

?>