<?php

namespace App\Diff;

use App\Acl\ResourceUndefined;

use function Functional\flatten;

function parseScheme(array $from, array $to): array
{
    $intersect = array_intersect_key($from, $to);

    $delete = array_diff_key($from, $to);
    $save = array_intersect_assoc($from, $to);
    $add = array_diff_key($to, $from);
    $updateFrom = array_diff_key($intersect, $save);
    $updateTo = array_intersect_key($to, $updateFrom);
    return [
        'delete' => $delete,
        'add' => $add,
        'save' => $save,
        'updateFrom' => $updateFrom,
        'updateTo' => $updateTo
    ];
}

function formatStringByScheme(array $scheme): string
{
    $parsedArray = array_map(function ($keyGroup, $group) {
        $operator = ' ';

        if ($keyGroup === 'add' || $keyGroup === 'updateTo') {
            $operator = '+';
        } elseif ($keyGroup === 'delete' || $keyGroup === 'updateFrom') {
            $operator = '-';
        }

        return array_map(fn($key, $value) => "{$operator} {$key}: {$value}", array_keys($group), array_values($group));
    }, array_keys($scheme), array_values($scheme));

    $formatedString = flatten($parsedArray);
    usort($formatedString, function ($a, $b) {
        return strcmp($a[2], $b[2]);
    });

    $formatedStringLastElement = explode(': ', $formatedString[count($formatedString) - 1])[1];

    return array_reduce($formatedString, function ($acc, $field) use ($formatedStringLastElement) {
        $value = explode(': ', $field)[1];

        if ($value != $formatedStringLastElement) {
            return "{$acc}\t{$field}\n";
        }

        return "{$acc}\t{$field}\n}\n";
    }, "\n{\n");
}

function gendiff(string $from = null, string $to = null): string
{
    if (!(isset($from) && isset($to))) {
        throw new ResourceUndefined('No files passed');
    }
    $fromData = json_decode($from, true);
    $toData = json_decode($to, true);

    $scheme = parseScheme($fromData, $toData);
    $formatedString = formatStringByScheme($scheme);
    return $formatedString;
}

function printDiff(string $data): void
{
    print_r($data);
}
