<?php

namespace App\Diff;

use App\Acl\ResourceUndefined;

use function Functional\flatten;

function parseScheme(array $from, array $to): array
{
    $intersect = array_intersect_key($from, $to);

    $delete = array_diff_key($from, $to);
    $add = array_diff_key($to, $from);
    $save = array_intersect_assoc($from, $to);
    $updateFrom = array_diff_key($intersect, $save);
    $updateTo = array_intersect_key($to, $updateFrom);

    $scheme = array_merge(
        array_map(
            fn($key, $value) => ['group' => 'delete', 'operator' => '-', $key => $value],
            array_keys($delete),
            array_values($delete)
        ),
        array_map(
            fn($key, $value) => ['group' => 'add', 'operator' => '+', $key => $value],
            array_keys($add),
            array_values($add)
        ),
        array_map(
            fn($key, $value) => ['group' => 'save', 'operator' => ' ', $key => $value],
            array_keys($save),
            array_values($save)
        ),
        array_map(
            fn($key, $value) => ['group' => 'updateFrom', 'operator' => '-', $key => $value],
            array_keys($updateFrom),
            array_values($updateFrom)
        ),
        array_map(
            fn($key, $value) => ['group' => 'updateTo', 'operator' => '+', $key => $value],
            array_keys($updateTo),
            array_values($updateTo)
        )
    );

    usort($scheme, function ($a, $b) {
        $a = array_keys($a)[2];
        $b = array_keys($b)[2];
        return strcmp($a, $b);
    });

    return $scheme;
}

function format(array $scheme): string
{
    $newScheme = array_map(function ($field) {
        $key = array_keys($field)[2];
        $value = array_values($field)[2];

        if (gettype($value) === 'boolean') {
            $value = $value ? 'true' : 'false';
        }

        return "{$field['operator']} {$key}: {$value}";
    }, $scheme);
 
    return array_reduce($newScheme, function ($initial, $field) {
        return "{$initial}\t{$field}\n";
    }, "{\n") . "}\n";
    return '';
}

function gendiff(string $from = null, string $to = null): string
{
    if (!(isset($from) && isset($to))) {
        throw new ResourceUndefined('No files passed');
    }
    $fromData = json_decode($from, true);
    $toData = json_decode($to, true);

    $scheme = parseScheme($fromData, $toData);
    $string = format($scheme);

    return $string;
}

function printDiff(string $data): void
{
    print_r($data);
}
