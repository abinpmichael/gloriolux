<?php
// fix_paths.php
// WARNING: Only run this on your live server!
// This script will search through all PHP files in the current directory
// and replace '/Gloriolux/' with '/'

$dir = __DIR__;
$files_updated = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        // Skip this script itself
        if ($file->getFilename() === 'fix_paths.php') continue;

        $content = file_get_contents($file->getPathname());
        
        if (strpos($content, '/Gloriolux/') !== false) {
            $new_content = str_replace('/Gloriolux/', '/', $content);
            file_put_contents($file->getPathname(), $new_content);
            $files_updated++;
            echo "Updated: " . $file->getFilename() . "<br>";
        }
    }
}

echo "<br><strong>Done! Successfully updated paths in $files_updated files.</strong>";
echo "<br>Please delete this script (fix_paths.php) from your server now for security.";
?>
