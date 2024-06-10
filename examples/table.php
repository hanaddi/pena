<?php
require_once '../vendor/autoload.php';
use Hanaddi\Pena;

$im     = imagecreatetruecolor(800, 800);
$black  = imagecolorallocate($im, 0, 0, 0);
$white  = imagecolorallocate($im, 255, 255, 255);
$orange = imagecolorallocate($im, 254, 193, 7);

$margin  = 40;
$padding = 10;
$pointy  = $margin;
$font_size = 10;
$font      = __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf';
$font_bold = __DIR__ . '/../assets/fonts/Roboto/Roboto-Black.ttf';
$font_thin = __DIR__ . '/../assets/fonts/Roboto/Roboto-Thin.ttf';

// Set the background to be white
imagefilledrectangle($im, 0, 0, imagesx($im), imagesy($im), $white);


$tablerow = [
    [
        'width' => 30,
        'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        'options' => [
            'padding' => 10,
            'color' => [255, 0, 255],
            'bgcolor' => [255, 255, 0],
        ],
    ],
    [
        'text' => 'TEXT BOTTOM',
        'width' => 12,
        'options' => [
            'bgcolor' => [0, 255, 0],
            'valign' => 'bottom',
        ],
    ],
    [
        'text' => 'TEXT TOP',
        'width' => 10,
        'options' => [
            'bgcolor' => [80, 25, 0, 100],
            'color' => [0, 25, 10, 0],
            'valign' => 'top',
            'font' => $font_thin,
        ],
    ],
    [
        'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
        'width' => 10,
        'options' => [
            'bgcolor' => [180, 125, 110],
            'align' => 'right',
            'fontsize' => 14,
        ],
    ],
    [
        'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        'width' => 10,
        'options' => [
            'fontsize' => 8,
            'align' => 'justify',
        ],
    ],
];

$options = [
    'align' => 'center',
    'padding' => $padding,
    'bordercolor' => [10, 10, 10],
    'bgcolor' => [254, 193, 7],
    'valign' => 'middle',
];
$height = Pena::writetablerow($im, $margin, $pointy, imagesx($im) - 2 * $margin, $font_size, $font_bold, $tablerow, $options);
$pointy += $height;

$height = Pena::writetablerow($im, $margin, $pointy, imagesx($im) - 2 * $margin, $font_size, $font_bold, array_slice($tablerow, 1), $options);
$pointy += $height;

$height = Pena::writetablerow($im, $margin, $pointy, imagesx($im) - 2 * $margin, $font_size, $font_bold, array_slice($tablerow, 2), $options);
$pointy += $height;

$height = Pena::writetablerow($im, $margin, $pointy, imagesx($im) - 2 * $margin, $font_size, $font_bold, array_slice($tablerow, 3), $options);
$pointy += $height;

$height = Pena::writetablerow($im, $margin, $pointy, imagesx($im) - 2 * $margin, $font_size, $font_bold, array_slice($tablerow, 4), $options);
$pointy += $height;

// Output as image
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
