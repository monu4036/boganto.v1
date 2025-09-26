-- Boganto Blog - Hero Banner Enhancement Migration Script
-- This script adds the required enhancements for hero banners and related books

USE boganto_blog;

-- ============================================
-- 1. Banner Images Table Enhancements
-- ============================================

-- Add blog_id column to banner_images table to link banners to specific blogs
ALTER TABLE banner_images 
ADD COLUMN IF NOT EXISTS blog_id INT NULL AFTER image_url;

-- Add foreign key constraint to link banners to blogs
ALTER TABLE banner_images 
ADD CONSTRAINT fk_banner_blog FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE SET NULL;

-- ============================================
-- 2. Related Books Table Enhancements
-- ============================================

-- Rename image_url to cover_image to support local file uploads
ALTER TABLE related_books 
CHANGE COLUMN image_url cover_image VARCHAR(255) NULL;

-- Add author column if it doesn't exist (for complete book information)
ALTER TABLE related_books 
ADD COLUMN IF NOT EXISTS author VARCHAR(255) NULL AFTER title;

-- ============================================
-- 3. Blogs Table Enhancements (Dual Featured Images)
-- ============================================

-- Add second featured image column for dual image support
ALTER TABLE blogs 
ADD COLUMN IF NOT EXISTS featured_image_2 VARCHAR(255) NULL AFTER featured_image;

-- ============================================
-- 4. Data Migration (Replace External URLs with Local Paths)
-- ============================================

-- Convert existing Unsplash URLs to local uploads path for banners
UPDATE banner_images 
SET image_url = CONCAT('/uploads/banner_', id, '.jpg') 
WHERE image_url LIKE 'https://images.unsplash.com%';

-- Convert existing Unsplash URLs to local uploads path for blog featured images
UPDATE blogs 
SET featured_image = CONCAT('/uploads/blog_', id, '.jpg') 
WHERE featured_image LIKE 'https://images.unsplash.com%';

-- ============================================
-- 5. Update Banner Data with Blog Links
-- ============================================

-- Link existing banners to blogs based on their link_url (if they match blog slugs)
UPDATE banner_images b
LEFT JOIN blogs bl ON b.link_url = CONCAT('/blog/', bl.slug)
SET b.blog_id = bl.id
WHERE bl.id IS NOT NULL;

-- ============================================
-- 6. Add Indexes for Better Performance
-- ============================================

-- Index for banner_images blog_id lookups
CREATE INDEX IF NOT EXISTS idx_banner_blog_id ON banner_images(blog_id);

-- Index for related_books with new structure
CREATE INDEX IF NOT EXISTS idx_related_books_blog_id ON related_books(blog_id);

-- ============================================
-- 7. Constraints and Limits
-- ============================================

-- Note: Banner limit of 4 will be enforced at application level
-- This provides better flexibility than database constraints

-- ============================================
-- 8. Sample Data Updates for Testing
-- ============================================

-- Update sample banner data to have proper blog links
-- Banner 1: Building Personal Library
UPDATE banner_images 
SET blog_id = (SELECT id FROM blogs WHERE slug = 'building-personal-library-complete-guide' LIMIT 1)
WHERE title = 'Building Your Personal Library';

-- Banner 2: Art of Storytelling  
UPDATE banner_images 
SET blog_id = (SELECT id FROM blogs WHERE slug = 'art-storytelling-modern-literature' LIMIT 1)
WHERE title = 'The Art of Storytelling';

-- Banner 3: Ancient Libraries
UPDATE banner_images 
SET blog_id = (SELECT id FROM blogs WHERE slug = 'ancient-libraries-guardians-knowledge' LIMIT 1)
WHERE title = 'Ancient Libraries';

-- Banner 4: Science Books
UPDATE banner_images 
SET blog_id = (SELECT id FROM blogs WHERE slug = 'books-changed-science-forever' LIMIT 1)
WHERE title = 'Science Books';

-- Banner 5: Modern Literature (keep as backup/test data)
UPDATE banner_images 
SET blog_id = (SELECT id FROM blogs WHERE slug = 'art-storytelling-modern-literature' LIMIT 1)
WHERE title = 'Modern Literature';

-- ============================================
-- 9. Verification Queries
-- ============================================

-- Check banner_images structure
-- SELECT * FROM banner_images LIMIT 5;

-- Check related_books structure  
-- SELECT * FROM related_books LIMIT 5;

-- Check blogs structure for dual images
-- SELECT id, title, featured_image, featured_image_2 FROM blogs LIMIT 5;

-- Check banner-blog relationships
-- SELECT b.id, b.title, b.blog_id, bl.title as blog_title, bl.slug 
-- FROM banner_images b 
-- LEFT JOIN blogs bl ON b.blog_id = bl.id;

COMMIT;