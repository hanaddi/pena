<?php
$dir = getcwd();
$files = scandir($dir);

echo "<ol>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..' && preg_match('/\.php$/', $file)) {
        echo "<li><a href='$file'>" . $file . "</a><br/></li>\n";
    }
}
echo "</ol>";
