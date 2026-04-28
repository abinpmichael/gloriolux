<?php
require_once 'includes/db.php';

$products = [
    [
        'name' => 'Midnight Velvet Amber',
        'category_id' => 1,
        'price' => 45.00,
        'description' => 'A sultry blend of deep amber, smoked vanilla, and midnight jasmine. Hand-poured in a matte black glass vessel for a sophisticated burn.',
        'image_url' => 'assets/img/product1.png',
        'stock' => 20
    ],
    [
        'name' => 'Bergamot & White Tea',
        'category_id' => 1,
        'price' => 38.00,
        'description' => 'An uplifting, clean scent featuring crisp bergamot layered with delicate white tea leaves and a hint of fresh linen.',
        'image_url' => 'assets/img/product1.png',
        'stock' => 15
    ],
    [
        'name' => 'Oud Wood Retreat',
        'category_id' => 1,
        'price' => 52.00,
        'description' => 'Earthy, rich, and deeply grounding. Features rare oud wood, cedar, and vetiver, bringing the serenity of an ancient forest indoors.',
        'image_url' => 'assets/img/product1.png',
        'stock' => 10
    ],
    [
        'name' => 'The Signature Trio Set',
        'category_id' => 2,
        'price' => 110.00,
        'description' => 'The perfect luxury gift. Contains three of our best-selling 8oz soy candles, beautifully packaged in an elegant black box with gold ribbon.',
        'image_url' => 'assets/img/gifting.png',
        'stock' => 5
    ],
    [
        'name' => 'Himalayan Sea Salt & Orchid',
        'category_id' => 1,
        'price' => 42.00,
        'description' => 'A smooth and elegant blend of soft floral notes with salty highlights. Creates a calming spa-like atmosphere in any room.',
        'image_url' => 'assets/img/product1.png',
        'stock' => 25
    ]
];

try {
    $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, description, image_url, stock) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($products as $p) {
        $stmt->execute([
            $p['name'],
            $p['category_id'],
            $p['price'],
            $p['description'],
            $p['image_url'],
            $p['stock']
        ]);
    }
    
    echo "Successfully seeded 5 new products into the database.\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
