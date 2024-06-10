<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Hanaddi\Pena;

$im     = imagecreatetruecolor(800, 620);
$white  = imagecolorallocate($im, 255, 255, 255);

$margin  = 40;
$padding = 10;
$pointy  = $margin;
$font_size = 11;
$font      = __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf';
$font_bold = __DIR__ . '/../assets/fonts/Roboto/Roboto-Black.ttf';
$font_thin = __DIR__ . '/../assets/fonts/Roboto/Roboto-Thin.ttf';

// Set the background to be white
imagefilledrectangle($im, 0, 0, imagesx($im), imagesy($im), $white);

$text = 'Fermentum dui faucibus in ornare quam viverra orci sagittis eu.';

$table = Pena::table($im, [
        'font'      => $font,
        'fontsize'  => $font_size,
        'width'     => 780,
        'x'         => 10,
        'y'         => 10,
        'columns'   => 3,
        'cellwidth' => [1, 1, 1],

        'bordercolor'   => [0x11, 0x33, 0x55],
        'padding'   => 10,
        'valign'    => 'middle',
        'align'     => 'justify',
        'minheight' => 150,
    ])
    ->pushRow([
            [
                "text" => "[Top left] " . $text,
                "options" => [
                    "bgcolor" => [255, 50, 170],
                    "valign" => "top",
                ],
            ],
            [
                "text" => "[Middle left] " . $text,
                "options" => [
                    "bgcolor" => [255, 100, 170],
                    "valign" => "middle",
                ],
            ],
            [
                "text" => "[Bottom left] " . $text,
                "options" => [
                    "bgcolor" => [255, 150, 170],
                    "valign" => "bottom",
                ],
            ]
        ],
        [
            "align" => "left",
            "font"  => $font_bold,
        ]
    )
    ->pushRow([
            [
                "text" => "[Top center] " . $text,
                "options" => [
                    "bgcolor" => [255, 50, 120],
                    "valign" => "top",
                ],
            ],
            [
                "text" => "[Middle center] " . $text,
                "options" => [
                    "bgcolor" => [255, 100, 120],
                    "valign" => "middle",
                ],
            ],
            [
                "text" => "[Bottom center] " . $text,
                "options" => [
                    "bgcolor" => [255, 150, 120],
                    "valign" => "bottom",
                ],
            ],
        ],
        [
            "align" => "center",
            "font"  => $font_thin,
        ]
    )
    ->pushRow([
            [
                "text" => "[Top right] " . $text,
                "options" => [
                    "bgcolor" => [255, 50, 70],
                    "valign" => "top",
                ],
            ],
            [
                "text" => "[Middle right] " . $text,
                "options" => [
                    "bgcolor" => [255, 100, 70],
                    "valign" => "middle",
                ],
            ],
            [
                "text" => "[Bottom right] " . $text,
                "options" => [
                    "bgcolor" => [255, 150, 70],
                    "valign" => "bottom",
                ],
            ],
        ],
        ["align" => "right"]
    )
    ->pushRow([
            [
                "text" => "[Top justify] " . $text,
                "options" => [
                    "bgcolor" => [255, 50, 20],
                    "valign" => "top",
                ],
            ],
            [
                "text" => "[Middle justify] " . $text,
                "options" => [
                    "bgcolor" => [255, 100, 20],
                    "valign" => "middle",
                ],
            ],
            [
                "text" => "[Bottom justify] " . $text,
                "options" => [
                    "bgcolor" => [255, 150, 20],
                    "valign" => "bottom",
                ],
            ],
        ],
        ["align" => "justify"]
    )
    ->draw();

// Output as image
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
