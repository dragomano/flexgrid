<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;

try {
    return RectorConfig::configure()
        ->withPaths([
            __DIR__ . '/src',
        ])
        ->withPhpSets()
        ->withTypeCoverageLevel(10)
        ->withDeadCodeLevel(10)
        ->withCodeQualityLevel(10)
        ->withCodingStyleLevel(10);
} catch (InvalidConfigurationException $e) {
    echo $e->getMessage();
}
