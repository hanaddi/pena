# hanaddi/pena

![GitHub Release](https://img.shields.io/github/v/release/hanaddi/pena?include_prereleases&display_name=tag)
![GitHub License](https://img.shields.io/github/license/hanaddi/pena)
![PHP Version](https://img.shields.io/packagist/dependency-v/hanaddi/pena/php)


Writing document as an image using PHP GD.


## Installation

You can add this library to your project using [Composer](https://getcomposer.org/):

```bash
composer require hanaddi/pena
```

## Example

### Writing simple paragraphs\:

```php
use Hanaddi\Pena;

$doc = new Pena(
    [400, 300],
    [
        'margin' => 10,
    ]
);

$text = "eget nulla facilisi etiam dignissim diam quis enim lobortis scelerisque fermentum dui faucibus in ornare "
      . "quam viverra orci sagittis eu volutpat odio facilisis mauris sit amet massa vitae tortor condimentum";

$doc->write($text, ["align" => "center", "lspace" => 1.5])
    ->lineSpace()
    ->write($text, ["color" => [255, 0, 0], "lspace" => 1.5])
    ->lineSpace()
    ->write($text, ["align" => "justify", "lspace" => 1.5, "bgcolor" => [0, 255, 255]]);

// Output as image
header('Content-Type: image/png');
imagepng($doc->document);
imagedestroy($doc->document);
```

Result\:

<p align="left">
    <img alt="Example table" width="400" src="https://raw.githubusercontent.com/hanaddi/pena/main/examples/images/paragraph-sample.png">
</p>

### Writing a table\:

```php
use Hanaddi\Pena;

$image  = imagecreatetruecolor(300, 150);
$white  = imagecolorallocate($image, 255, 255, 255);

// Set the background to be white
imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $white);

// Write a table
$table = Pena::table(
        $image,
        [
            'x'         => 10,
            'y'         => 10,
            'width'     => 280,
            'columns'   => 2,
            'cellwidth' => [1, 3],
            'padding'   => 10,
        ]
    )
    ->pushRow(
        [['text' => 'No.'], ['text' => 'Name']],
        ['bgcolor' => [250, 200, 0], 'align' => 'center']
    )
    ->pushRow([['text' => '1.'], ['text' => 'Alpha']])
    ->pushRow([['text' => '2.'], ['text' => 'Beta']])
    ->pushRow([['text' => '3.'], ['text' => 'Charlie']])
    ->draw();

// Output as image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
```

Result\:

<p align="left">
    <img alt="Example table" width="300" src="https://raw.githubusercontent.com/hanaddi/pena/main/examples/images/table-simple.png">
</p>