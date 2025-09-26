// Re-export image utilities for backward compatibility
export { DEFAULT_IMAGES, getImageUrl, getOptimizedImageUrl, handleImageError } from './imageUtils';

export const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';