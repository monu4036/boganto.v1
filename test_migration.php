<?php
// Simple test script to run the migration and check database structure
require_once 'backend/config.php';

echo "=== Boganto Hero Banner Migration Test ===\n\n";

$database = new DatabaseConfig();
$db = $database->getConnection();

if (!$db) {
    die("❌ Database connection failed\n");
}

echo "✅ Database connection successful\n\n";

// Read and execute migration script
echo "📋 Reading migration script...\n";
$migrationSQL = file_get_contents('backend/migration_hero_banners.sql');

if (!$migrationSQL) {
    die("❌ Failed to read migration script\n");
}

// Execute migration
echo "🔄 Executing migration...\n";
try {
    // Split SQL into individual statements
    $statements = explode(';', $migrationSQL);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $db->exec($statement);
        }
    }
    
    echo "✅ Migration executed successfully\n\n";
} catch (PDOException $e) {
    echo "⚠️  Migration warning: " . $e->getMessage() . "\n\n";
}

// Verify table structures
echo "🔍 Verifying table structures...\n\n";

// Check banner_images table
try {
    $stmt = $db->prepare("DESCRIBE banner_images");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "📋 banner_images table structure:\n";
    foreach ($columns as $column) {
        echo "   - {$column['Field']}: {$column['Type']}\n";
    }
    
    // Check if blog_id column exists
    $blogIdExists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'blog_id') {
            $blogIdExists = true;
            break;
        }
    }
    
    echo $blogIdExists ? "✅ blog_id column found\n" : "❌ blog_id column missing\n";
    echo "\n";
    
} catch (PDOException $e) {
    echo "❌ Error checking banner_images table: " . $e->getMessage() . "\n\n";
}

// Check related_books table
try {
    $stmt = $db->prepare("DESCRIBE related_books");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "📋 related_books table structure:\n";
    foreach ($columns as $column) {
        echo "   - {$column['Field']}: {$column['Type']}\n";
    }
    
    // Check if cover_image column exists
    $coverImageExists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'cover_image') {
            $coverImageExists = true;
            break;
        }
    }
    
    echo $coverImageExists ? "✅ cover_image column found\n" : "❌ cover_image column missing\n";
    echo "\n";
    
} catch (PDOException $e) {
    echo "❌ Error checking related_books table: " . $e->getMessage() . "\n\n";
}

// Check blogs table for dual images
try {
    $stmt = $db->prepare("DESCRIBE blogs");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "📋 blogs table structure (checking for dual images):\n";
    
    $featuredImage1 = false;
    $featuredImage2 = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'featured_image') {
            echo "   ✅ featured_image: {$column['Type']}\n";
            $featuredImage1 = true;
        }
        if ($column['Field'] === 'featured_image_2') {
            echo "   ✅ featured_image_2: {$column['Type']}\n";
            $featuredImage2 = true;
        }
    }
    
    echo $featuredImage2 ? "✅ Dual image support enabled\n" : "❌ featured_image_2 column missing\n";
    echo "\n";
    
} catch (PDOException $e) {
    echo "❌ Error checking blogs table: " . $e->getMessage() . "\n\n";
}

// Test banner-blog relationships
echo "🔗 Testing banner-blog relationships...\n";
try {
    $stmt = $db->prepare("
        SELECT b.id, b.title, b.blog_id, bl.title as blog_title, bl.slug 
        FROM banner_images b 
        LEFT JOIN blogs bl ON b.blog_id = bl.id 
        LIMIT 3
    ");
    $stmt->execute();
    $banners = $stmt->fetchAll();
    
    if (count($banners) > 0) {
        foreach ($banners as $banner) {
            echo "   Banner: {$banner['title']}\n";
            echo "     -> Blog: " . ($banner['blog_title'] ?: 'No blog linked') . "\n";
            echo "     -> Slug: " . ($banner['slug'] ?: 'N/A') . "\n\n";
        }
    } else {
        echo "   No banners found in database\n\n";
    }
} catch (PDOException $e) {
    echo "❌ Error testing relationships: " . $e->getMessage() . "\n\n";
}

echo "=== Migration Test Complete ===\n";
?>