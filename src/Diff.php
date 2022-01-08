<?php

namespace App\Diff;

use App\Acl\ResourceUndefined;

use function Functional\flatten;

function schemeConstruct(array $schemeGroups): array
{
    $delete = [array_keys($schemeGroups['delete']), array_values($schemeGroups['delete'])];
    $add = [array_keys($schemeGroups['add']), array_values($schemeGroups['add'])];
    $save = [array_keys($schemeGroups['save']), array_values($schemeGroups['save'])];
    $updateFrom = [array_keys($schemeGroups['updateFrom']), array_values($schemeGroups['updateFrom'])];
    $updateTo = [array_keys($schemeGroups['updateTo']), array_values($schemeGroups['updateTo'])];

    return array_merge(
        array_map(fn($key, $value) => ['group' => 'delete', 'operator' => '-', $key => $value], $delete[0], $delete[1]),
        array_map(fn($key, $value) => ['group' => 'add', 'operator' => '+', $key => $value], $add[0], $add[1]),
        array_map(fn($key, $value) => ['group' => 'save', 'operator' => ' ', $key => $value], $save[0], $save[1]),
        array_map(
            fn($key, $value) => ['group' => 'updateFrom', 'operator' => '-', $key => $value],
            $updateFrom[0],
            $updateFrom[1]
        ),
        array_map(
            fn($key, $value) => ['group' => 'updateTo', 'operator' => '+', $key => $value],
            $updateTo[0],
            $updateTo[1]
        )
    );
}

function parseScheme(array $from, array $to): array
{
    $intersect = array_intersect_key($from, $to);
    $delete = array_diff_key($from, $to);
    $add = array_diff_key($to, $from);
    $save = array_intersect_assoc($from, $to);
    $updateFrom = array_diff_key($intersect, $save);
    $updateTo = array_intersect_key($to, $updateFrom);

    $schemeGroups = [
        'delete' => $delete,
        'add' => $add,
        'save' => $save,
        'updateFrom' => $updateFrom,
        'updateTo' => $updateTo
    ];

    $scheme = schemeConstruct($schemeGroups);

    usort($scheme, function (array $a, array $b) {
        return strcmp(array_keys($a)[2], array_keys($b)[2]);
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
