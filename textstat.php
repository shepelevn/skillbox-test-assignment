<?php

define("CHUNK_LENGTH", 10 * 1024 * 1024);

if (!isset($argv[1])) {
    exit("Filename is not provided" . PHP_EOL);
}

$file = fopen($argv[1], "r");

if (!$file) {
    exit("File not found" . PHP_EOL);
}

$countsArray = [];
$fileSize = 0;

while (!feof($file)) {
    $chunk = fgets($file, CHUNK_LENGTH);

    if ($chunk === false) {
        break;
    }

    $fileSize += strlen($chunk);

    foreach (mb_str_split($chunk) as $char) {
        if (!isset($countsArray[$char])) {
            $countsArray[$char] = 0;
        }

        $countsArray[$char]++;
    }
}

ksort($countsArray);

foreach ($countsArray as $char => $count) {
    printf("%s - %s%%" . PHP_EOL, charToReadable($char), formatPercentage($count / $fileSize));
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
