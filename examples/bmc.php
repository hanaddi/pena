<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Hanaddi\Pena;

$doc = new Pena([800, 380], ['margin' => 10]);
$doc->tableNew( [
        'columns'     => 6,
        'cellwidth'   => [2, 2, 1, 1, 2, 2],
        'padding'     => 10,
        'minheight'   => 120,
        'align'       => 'center',
        'bgcolor'     => [230, 180, 250],
        'color'       => [10, 20, 100],
        'bordercolor' => [10, 20, 100],
    ])
    ->tableRow(
        [
            ['text' => 'Key Partners', 'options'=>['rowspan'=>2]],
            ['text' => 'Key Activities'],
            ['text' => 'Value Propositions', 'options'=>['rowspan'=>2, 'colspan'=>2, 'bgcolor' => [250, 210, 100], 'valign'=>'top']],
            ['text' => 'Customer Relationship'],
            ['text' => 'Market Segments', 'options'=>['rowspan'=>2]],
        ]
    )
    ->tableRow(
        [
            ['text' => 'Key Resources'],
            ['text' => 'Channels'],
        ]
    )
    ->tableRow(
        [
            ['text' => 'Cost Structures', 'options'=>['colspan'=>3]],
            ['text' => 'Revenue Stream', 'options'=>['colspan'=>3]],
        ],
        ['colspan'=>3, 'bgcolor' => [100, 210, 250]]
        
    )
    ->tableDraw();

// Output as image
header('Content-Type: image/png');
imagepng($doc->document);
imagedestroy($doc->document);
