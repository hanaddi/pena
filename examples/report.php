<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Hanaddi\Pena;

$im     = imagecreatetruecolor(800, 840);
$white  = imagecolorallocate($im, 255, 255, 255);

$margin  = 40;
$padding = 10;
$font_size = 12;
$font      = __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf';
$font_bold = __DIR__ . '/../assets/fonts/Roboto/Roboto-Black.ttf';
$font_thin = __DIR__ . '/../assets/fonts/Roboto/Roboto-Thin.ttf';
$width  = imagesx($im) - 2*$margin;
$pointy = $margin;

// Set the background to be white
imagefilledrectangle($im, 0, 0, imagesx($im), imagesy($im), $white);

$pointy += Pena::writeinlinebox($im, $margin, $pointy, $width, $font_size*1.2|0, $font_bold, "Student Learning Report");
$pointy += $font_size * 2;

$options = [
    'padding' => 5,
    'bordercolor' => [0, 0, 0, 127],
];
$height = Pena::writetablerow(
    $im, $margin - 5, $pointy, $width, $font_size, $font,
    [
        [
            'text' => 'Student Name',
            'width' => 7,
            'options' => [
                'font' => $font_bold,
            ],
        ],
        [
            'text' => ': John Doe',
            'width' => 33,
        ],
    ],
    $options
);
$pointy += $height;

$height = Pena::writetablerow(
    $im, $margin - 5, $pointy, $width, $font_size, $font,
    [
        [
            'text' => 'Grade',
            'width' => 7,
            'options' => [
                'font' => $font_bold,
            ],
        ],
        [
            'text' => ': 10',
            'width' => 33,
        ],
    ],
    $options
);
$pointy += $height;

$height = Pena::writetablerow(
    $im, $margin - 5, $pointy, $width, $font_size, $font,
    [
        [
            'text' => 'Semester',
            'width' => 7,
            'options' => [
                'font' => $font_bold,
            ],
        ],
        [
            'text' => ': Fall 2023',
            'width' => 33,
        ],
    ],
    $options
);
$pointy += $height;
$pointy += $font_size * 2;

$options = [
    'padding' => 10,
    'align' => 'center',
    'bgcolor' => [0xff, 0xee, 0xbb],
];
$height = Pena::writetablerow(
    $im, $margin, $pointy, $width, $font_size, $font_bold,
    [
        [
            'text' => 'Subject',
            'width' => 6,
        ],
        [
            'text' => 'Grade',
            'width' => 3,
        ],
        [
            'text' => 'Teacher',
            'width' => 5,
        ],
        [
            'text' => 'Comments',
            'width' => 14,
        ],
    ],
    $options
);
$pointy += $height;


$options = [
    'padding' => 10,
    'align' => 'left',
    'valign' => 'middle',
];
$height = Pena::writetablerow(
    $im, $margin, $pointy, $width, $font_size, $font,
    [
        [
            'text' => 'English Language Arts (ELA)',
            'width' => 6,
        ],
        [
            'text' => 'A-',
            'width' => 3,
        ],
        [
            'text' => 'Mrs. Smith',
            'width' => 5,
        ],
        [
            'text' => 'John has made excellent progress in ELA. His reading comprehension skills have improved markedly, and he has become more confident in class discussions. His essays are well-structured, demonstrating a clear understanding of the topics. Continued focus on expanding his vocabulary will further enhance his writing.',
            'width' => 14,
            'options' => [
                'align' => 'justify',
            ],
        ],
    ],
    $options
);
$pointy += $height;
$height = Pena::writetablerow(
    $im, $margin, $pointy, $width, $font_size, $font,
    [
        [
            'text' => 'Mathematics',
            'width' => 6,
        ],
        [
            'text' => 'B+',
            'width' => 3,
        ],
        [
            'text' => 'Mr. Johnson',
            'width' => 5,
        ],
        [
            'text' => 'John has shown a strong grasp of algebraic concepts. He has improved his problem-solving skills and participates actively in class. He should continue to work on his speed and accuracy in solving equations. Extra practice with complex problems can help him achieve even better results.',
            'width' => 14,
            'options' => [
                'align' => 'justify',
            ],
        ],
    ],
    $options
);
$pointy += $height;
$height = Pena::writetablerow(
    $im, $margin, $pointy, $width, $font_size, $font,
    [
        [
            'text' => 'Science',
            'width' => 6,
        ],
        [
            'text' => 'A',
            'width' => 3,
        ],
        [
            'text' => 'Ms. Lee',
            'width' => 5,
        ],
        [
            'text' => 'John has excelled in Biology this semester. His lab work is thorough, and he collaborates well with classmates. His understanding of scientific concepts is solid, and he often asks insightful questions. John\'s project on ecosystems was particularly noteworthy.',
            'width' => 14,
            'options' => [
                'align' => 'justify',
            ],
        ],
    ],
    $options
);
$pointy += $height;

$pointy += $font_size * 2;
$pointy += Pena::writeinlinebox(
    $im, $margin, $pointy, $width, $font_size, $font,
    "John has shown tremendous growth this semester. His positive attitude and willingness to learn are commendable. With continued effort and focus on his areas for improvement, I am confident he will achieve even greater success in the next semester.",
    [
        'align' => 'justify',
    ]
);

$pointy += $font_size * 3;
$pointy += Pena::writeinlinebox(
    $im, $margin, $pointy, $width, $font_size, $font,
    "January 20, 2024",
    [
        'align' => 'right',
    ]
);

$pointy += $font_size * 4;
$pointy += Pena::writeinlinebox(
    $im, $margin, $pointy, $width, $font_size, $font,
    "Mrs. Smith",
    [
        'align' => 'right',
    ]
);


// Output as image
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);

// ob_start();
// imagepng($im);
// $image_data = ob_get_clean();
// echo '<img src="data:image/png;base64, ' . base64_encode($image_data) . '">';
// imagedestroy($im);