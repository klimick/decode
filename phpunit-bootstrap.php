<?php

$seed = abs(
    intval(getenv('ERIS_SEED') ?: (microtime(true) * 1000000))
);

putenv("ERIS_SEED={$seed}");

echo "Running with ERIS_SEED: {$seed}\n";
