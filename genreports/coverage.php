<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Hanaddi\Pena;

if ($argc < 2) {
    echo "Usage: php coverage.php <output.png>\n";
    exit(1);
}

$file_coverage = __DIR__ . '/../' . $argv[1];
if (!file_exists($file_coverage)) {
    echo "File not found: $file_coverage\n";
    exit(1);
}

$coverage_contents = file_get_contents($file_coverage);

preg_match('/Methods\:\s+([0-9]+(\.[0-9]+){0,1}\%)/', $coverage_contents, $matches);

if (isset($matches[1])) {
    $coverage = $matches[1];
} else {
    $coverage = 'N/A';
}

$doc = new Pena([150, 30], ['margin' => 0]);
$doc->tableNew( [
        'columns'     => 2,
        'fontsze'     => 12,
        'padding'     => 5,
        'bordercolor' => [0, 0, 0, 127],
        'bgcolor'     => [250, 231, 157],
        'minheight'   => 30,
        'valign'      => 'middle',
    ])
    ->tableRow([['text' => 'Coverage'], ['text' => $coverage, 'options'=>['bgcolor'=>[57, 231, 250], 'align'=>'center']]])
    ->tableDraw();

// Output as image
imagepng($doc->document, 'coverage.png');
imagedestroy($doc->document);
