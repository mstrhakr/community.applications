<?php
/**
 * Quick test to see if we can include CA files through the stream wrapper
 */
require __DIR__ . '/bootstrap-poc.php';

echo PHP_EOL . "Testing include..." . PHP_EOL;

// Try to include paths.php which defines $caPaths
require '/usr/local/emhttp/plugins/community.applications/include/paths.php';

echo "paths.php loaded!" . PHP_EOL;
echo "caPaths keys: " . implode(', ', array_slice(array_keys($caPaths), 0, 5)) . "..." . PHP_EOL;
