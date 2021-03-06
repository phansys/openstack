<?php

$rootDir = dirname(dirname(__DIR__));

require_once $rootDir . '/vendor/autoload.php';

$sampleDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'samples';

$runner = new \OpenCloud\Integration\Runner($sampleDir, __DIR__, 'OpenStack\\Integration');
$runner->runServices();
