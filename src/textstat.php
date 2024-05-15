<?php

declare(strict_types=1);

namespace Kck\TextStat;

require 'vendor/autoload.php';

use Jstewmc\Chunker\File;

if (!isset($argv[1])) {
    exit("Filename is not provided" . PHP_EOL);
}

try {
    $chunker = new File($argv[1]);
} catch (\Exception $exception) {
    exit($exception->getMessage() . PHP_EOL);
}

$countsArray = [];
$fileSize = 0;

while (true) {
    $chunk = $chunker->current();
    $chunker->next();

    if (!$chunk) {
        break;
    }

    $fileSize += strlen($chunk);

    foreach (mb_str_split($chunk) as $char) {
        if (!isset($countsArray[$char])) {
            $countsArray[$char] = 0;
        }

        $countsArray[$char]++;
    }

    if (!$chunker->hasNextChunk()) {
        break;
    }
}

ksort($countsArray);

foreach ($countsArray as $char => $count) {
    printf("%s - %s%%" . PHP_EOL, charToReadable(strval($char)), formatPercentage($count / $fileSize));
}

print(PHP_EOL);

function charToReadable(string $char): string
{
    return match(mb_ord($char)) {
        9 => '\t',
        10 => '\n',
        12 => '0x12',
        13 => '0x13',
        32 => '(space)',
        160 => '(non-breaking-space)',
        default => $char,
    };
}

function formatPercentage(float $number): string
{
    if ($number >= 0.01) {
        return strval(round($number, 2));
    } else {
        return strval(round($number, findPrecision($number)));
    }
}

function findPrecision(float $number): int
{
    $precision = 0;

    while ($number < 1) {
        $number *= 10;
        $precision++;
    }

    return $precision;
}
