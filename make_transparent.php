<?php
$source_image = 'assets/img/logo.jpeg';
$output_image = 'assets/img/logo_transparent.png';

$img = imagecreatefromjpeg($source_image);

// Get image dimensions
$width = imagesx($img);
$height = imagesy($img);

// Create a new true color image with alpha channel
$transparent_img = imagecreatetruecolor($width, $height);

// Enable alpha blending and save full alpha channel
imagealphablending($transparent_img, false);
imagesavealpha($transparent_img, true);

// Allocate a transparent color
$transparent_color = imagecolorallocatealpha($transparent_img, 0, 0, 0, 127);
imagefill($transparent_img, 0, 0, $transparent_color);

// Tolerance for "white"
$tolerance = 30;
$target_r = 255;
$target_g = 255;
$target_b = 255;

for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
        $rgb = imagecolorat($img, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        
        // If the color is close to white, don't copy it (leave it transparent)
        if (abs($r - $target_r) <= $tolerance && 
            abs($g - $target_g) <= $tolerance && 
            abs($b - $target_b) <= $tolerance) {
            // It's background, skip
        } else {
            // It's foreground, copy the pixel
            $color = imagecolorallocate($transparent_img, $r, $g, $b);
            imagesetpixel($transparent_img, $x, $y, $color);
        }
    }
}

imagepng($transparent_img, $output_image);
imagedestroy($img);
imagedestroy($transparent_img);

echo "Transparent logo created successfully.";
?>
