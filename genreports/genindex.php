<?php
error_reporting(null);

if ($argc < 2) {
    echo "Usage: php genindex.php <path_to_directory>\n";
    exit(1);
}

$dir = __DIR__ . '/../' . $argv[1];
$files = scandir($dir);

echo "<ol>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $name = file_get_contents($file);
        if (!$name) {
            $name = $file;
        }
        $name = substr($name, 0, 50);
        echo "<li><a href='$file'>" . $name . "</a></li>\n";
    }
}
echo "</ol>";
