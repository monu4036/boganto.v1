-- Update database schema to add featured_image_2 column for dual featured images functionality
-- This script is safe to run multiple times as it checks if the column exists first

USE boganto_blog;

-- Add featured_image_2 column to blogs table if it doesn't exist
SET @sql = (
    SELECT IF(
        (SELECT COUNT(*) FROM information_schema.COLUMNS 
         WHERE TABLE_SCHEMA = 'boganto_blog' 
         AND TABLE_NAME = 'blogs' 
         AND COLUMN_NAME = 'featured_image_2') > 0,
        'SELECT "Column featured_image_2 already exists" as message;',
        'ALTER TABLE blogs ADD COLUMN featured_image_2 VARCHAR(255) DEFAULT NULL AFTER featured_image;'
    )
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show the updated table structure
DESCRIBE blogs;