// Centralized image utility functions for the blog application
const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';

export const DEFAULT_IMAGES = {
  BLOG_BANNER: '/uploads/1758873063_a-book-1760998_1280.jpg',
  HERO_BANNER: '/uploads/1758801057_a-book-759873_640.jpg',
  ARTICLE_THUMBNAIL: '/uploads/1758801057_book-419589_640.jpg',
  BUILDING_LIBRARY: '/uploads/1758779936_a-book-1760998_1280.jpg'
};

/**
 * Get the full URL for an image
 * @param {string} imagePath - The image path (can be relative or full URL)
 * @param {string} defaultImage - Default image to use if imagePath is invalid
 * @returns {string} Full image URL
 */
export const getImageUrl = (imagePath, defaultImage = DEFAULT_IMAGES.ARTICLE_THUMBNAIL) => {
  // Return default if no image path provided
  if (!imagePath) return defaultImage;
  
  // If it's already a full URL, return as-is
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath;
  }
  
  // If it starts with /assets/, it's a static frontend asset
  if (imagePath.startsWith('/assets/')) {
    return imagePath;
  }
  
  // If it starts with /uploads/, it should be served from backend
  if (imagePath.startsWith('/uploads/')) {
    return `${API_BASE_URL}${imagePath}`;
  }
  
  // If it's just a filename, assume it's in uploads directory
  if (imagePath && !imagePath.includes('/')) {
    return `${API_BASE_URL}/uploads/${imagePath}`;
  }
  
  // For any other relative paths, return as-is
  return imagePath;
};

/**
 * Get the optimized image URL with size parameters (for future use)
 * @param {string} imagePath - The image path
 * @param {Object} options - Size and optimization options
 * @returns {string} Optimized image URL
 */
export const getOptimizedImageUrl = (imagePath, options = {}) => {
  const { width, height, quality = 80 } = options;
  const baseUrl = getImageUrl(imagePath);
  
  // For now, just return the base URL
  // In the future, we can add image optimization parameters
  return baseUrl;
};

/**
 * Handle image load errors by providing fallback
 * @param {Event} event - The error event
 * @param {string} fallbackImage - Fallback image URL
 */
export const handleImageError = (event, fallbackImage = DEFAULT_IMAGES.ARTICLE_THUMBNAIL) => {
  event.target.src = fallbackImage;
  event.target.onerror = null; // Prevent infinite error loops
};

/**
 * Check if an image URL is valid and accessible
 * @param {string} imageUrl - The image URL to check
 * @returns {Promise<boolean>} Promise that resolves to true if image is accessible
 */
export const isImageAccessible = (imageUrl) => {
  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => resolve(true);
    img.onerror = () => resolve(false);
    img.src = imageUrl;
  });
};

/**
 * Get the best available image from multiple options
 * @param {Array<string>} imageUrls - Array of image URLs to try
 * @param {string} defaultImage - Default image if none are accessible
 * @returns {Promise<string>} Promise that resolves to the best available image URL
 */
export const getBestAvailableImage = async (imageUrls = [], defaultImage = DEFAULT_IMAGES.ARTICLE_THUMBNAIL) => {
  for (const imageUrl of imageUrls) {
    if (imageUrl && await isImageAccessible(getImageUrl(imageUrl))) {
      return getImageUrl(imageUrl);
    }
  }
  return defaultImage;
};