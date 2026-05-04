<?php
$dir = 'uploads';
if (!file_exists($dir)) {
    if (mkdir($dir, 0777, true)) {
        echo "Created directory: $dir\n";
    } else {
        echo "Failed to create directory: $dir\n";
    }
} else {
    echo "Directory exists: $dir\n";
}

if (is_writable($dir)) {
    echo "Directory is writable: $dir\n";
    $test_file = $dir . '/test_write.txt';
    if (file_put_contents($test_file, 'test')) {
        echo "Successfully wrote to test file: $test_file\n";
        unlink($test_file);
    } else {
        echo "Failed to write to test file: $test_file\n";
    }
} else {
    echo "Directory is NOT writable: $dir\n";
}
?>
