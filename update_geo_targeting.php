<?php
require_once 'includes/db.php';

try {
    // Update the homepage slide subtitle
    $stmt = $pdo->prepare("UPDATE slides SET subtitle = ? WHERE title LIKE '%Illuminate Your Senses%'");
    $stmt->execute(['Hand-poured luxury soy candles crafted with exquisite fragrances, proudly handmade in Calgary and delivering to Airdrie, Alberta.']);
    
    echo "<h1>Success!</h1>";
    echo "<p>The homepage sub-headline has been updated with geo-targeting for Calgary and Airdrie.</p>";
    echo "<p><a href='index.php'>Go back to Homepage</a></p>";
    
    // Auto-delete this script for security
    // unlink(__FILE__); 
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
