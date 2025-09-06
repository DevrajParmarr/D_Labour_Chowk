<?php
/**
 * Performance Optimization Helper Class
 * Handles caching, image optimization, and performance enhancements
 */

class PerformanceOptimizer {
    private static $instance = null;
    private $cache_dir;
    private $cache_ttl;
    
    private function __construct() {
        $this->cache_dir = __DIR__ . '/../cache/';
        $this->cache_ttl = 3600; // 1 hour default
        
        // Create cache directory if it doesn't exist
        if (!file_exists($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Cache a value with a key
     */
    public function cache($key, $data, $ttl = null) {
        $ttl = $ttl ?: $this->cache_ttl;
        $cache_file = $this->cache_dir . md5($key) . '.cache';
        
        $cache_data = [
            'timestamp' => time(),
            'ttl' => $ttl,
            'data' => $data
        ];
        
        file_put_contents($cache_file, serialize($cache_data));
        return $data;
    }
    
    /**
     * Get cached value by key
     */
    public function getCache($key) {
        $cache_file = $this->cache_dir . md5($key) . '.cache';
        
        if (!file_exists($cache_file)) {
            return false;
        }
        
        $cache_data = unserialize(file_get_contents($cache_file));
        
        // Check if cache has expired
        if (time() - $cache_data['timestamp'] > $cache_data['ttl']) {
            unlink($cache_file);
            return false;
        }
        
        return $cache_data['data'];
    }
    
    /**
     * Clear cache by key or all cache
     */
    public function clearCache($key = null) {
        if ($key) {
            $cache_file = $this->cache_dir . md5($key) . '.cache';
            if (file_exists($cache_file)) {
                unlink($cache_file);
            }
        } else {
            $files = glob($this->cache_dir . '*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Optimize and resize image
     */
    public function optimizeImage($source_path, $max_width = 800, $max_height = 600, $quality = 85) {
        if (!file_exists($source_path)) {
            return false;
        }
        
        $image_info = getimagesize($source_path);
        if (!$image_info) {
            return false;
        }
        
        list($orig_width, $orig_height, $image_type) = $image_info;
        
        // Calculate new dimensions
        $ratio = min($max_width / $orig_width, $max_height / $orig_height);
        $new_width = round($orig_width * $ratio);
        $new_height = round($orig_height * $ratio);
        
        // Create image resource based on type
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source_image = imagecreatefrompng($source_path);
                break;
            case IMAGETYPE_GIF:
                $source_image = imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }
        
        // Create new image
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG and GIF
        if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefill($new_image, 0, 0, $transparent);
        }
        
        // Resize image
        imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, 
                          $new_width, $new_height, $orig_width, $orig_height);
        
        // Generate optimized filename
        $path_info = pathinfo($source_path);
        $optimized_path = $path_info['dirname'] . '/' . $path_info['filename'] . '_optimized.jpg';
        
        // Save optimized image as JPEG
        imagejpeg($new_image, $optimized_path, $quality);
        
        // Clean up memory
        imagedestroy($source_image);
        imagedestroy($new_image);
        
        return $optimized_path;
    }
    
    /**
     * Generate responsive image srcset
     */
    public function generateResponsiveImages($source_path, $sizes = [400, 800, 1200]) {
        $responsive_images = [];
        
        foreach ($sizes as $size) {
            $optimized = $this->optimizeImage($source_path, $size, $size);
            if ($optimized) {
                $responsive_images[] = [
                    'path' => $optimized,
                    'width' => $size,
                    'url' => $this->pathToUrl($optimized)
                ];
            }
        }
        
        return $responsive_images;
    }
    
    /**
     * Convert file path to URL
     */
    private function pathToUrl($path) {
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        return str_replace($doc_root, '', $path);
    }
    
    /**
     * Minify CSS
     */
    public function minifyCSS($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove unnecessary whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ', '], [';', '{', '{', '}', '}', ':', ','], $css);
        
        return trim($css);
    }
    
    /**
     * Minify JavaScript
     */
    public function minifyJS($js) {
        // Remove single-line comments (but preserve URLs)
        $js = preg_replace('/(?<!:)\/\/.*$/m', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        
        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        $js = str_replace(['; ', ' {', '{ ', ' }', '} ', ' (', '( ', ' )', ') ', ' =', '= ', ' +', '+ ', ' -', '- '], 
                         [';', '{', '{', '}', '}', '(', '(', ')', ')', '=', '=', '+', '+', '-', '-'], $js);
        
        return trim($js);
    }
    
    /**
     * Enable output compression
     */
    public function enableCompression() {
        if (!ob_get_level()) {
            if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
                ob_start('ob_gzhandler');
            } else {
                ob_start();
            }
        }
    }
    
    /**
     * Set caching headers
     */
    public function setCacheHeaders($max_age = 3600) {
        $expires = gmdate('D, d M Y H:i:s', time() + $max_age) . ' GMT';
        
        header("Cache-Control: max-age={$max_age}, public");
        header("Expires: {$expires}");
        header("Last-Modified: " . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');
        
        // Generate ETag
        $etag = md5(filemtime(__FILE__) . filesize(__FILE__));
        header("ETag: \"{$etag}\"");
        
        // Check if client has cached version
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === "\"{$etag}\"") {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }
    
    /**
     * Lazy load images implementation
     */
    public function generateLazyLoadHTML($src, $alt = '', $class = '', $placeholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="1" height="1"%3E%3C/svg%3E') {
        return '<img src="' . $placeholder . '" data-src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" class="lazy ' . htmlspecialchars($class) . '" loading="lazy">';
    }
    
    /**
     * Database query caching
     */
    public function cacheQuery($sql, $params = [], $ttl = null) {
        $cache_key = 'query_' . md5($sql . serialize($params));
        
        // Check cache first
        $cached = $this->getCache($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        
        // Execute query
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare($sql);
            
            if (!empty($params)) {
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            
            // Cache the result
            $this->cache($cache_key, $data, $ttl);
            
            return $data;
        } catch (Exception $e) {
            error_log('Cache Query Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate critical CSS
     */
    public function generateCriticalCSS($css, $above_fold_selectors = []) {
        $critical_css = '';
        
        // Default above-the-fold selectors
        $default_selectors = [
            'body', 'html', '.navbar', '.hero', '.header', '.banner', 
            '.container', '.row', '.col', 'h1', 'h2', 'p', 'a'
        ];
        
        $selectors = array_merge($default_selectors, $above_fold_selectors);
        
        foreach ($selectors as $selector) {
            $pattern = '/(' . preg_quote($selector, '/') . '\\s*{[^}]*})/i';
            preg_match_all($pattern, $css, $matches);
            
            foreach ($matches[1] as $match) {
                $critical_css .= $match;
            }
        }
        
        return $this->minifyCSS($critical_css);
    }
    
    /**
     * Preload critical resources
     */
    public function preloadResources($resources = []) {
        foreach ($resources as $resource) {
            $rel = isset($resource['rel']) ? $resource['rel'] : 'preload';
            $href = $resource['href'];
            $as = isset($resource['as']) ? $resource['as'] : '';
            $type = isset($resource['type']) ? $resource['type'] : '';
            
            $preload_html = "<link rel=\"{$rel}\" href=\"{$href}\"";
            
            if ($as) {
                $preload_html .= " as=\"{$as}\"";
            }
            
            if ($type) {
                $preload_html .= " type=\"{$type}\"";
            }
            
            $preload_html .= ">";
            
            echo $preload_html . "\n";
        }
    }
    
    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics() {
        $metrics = [];
        
        // Memory usage
        $metrics['memory_usage'] = memory_get_usage(true);
        $metrics['memory_peak'] = memory_get_peak_usage(true);
        $metrics['memory_limit'] = ini_get('memory_limit');
        
        // Execution time
        if (defined('APP_START_TIME')) {
            $metrics['execution_time'] = microtime(true) - APP_START_TIME;
        }
        
        // Cache statistics
        $cache_files = glob($this->cache_dir . '*.cache');
        $metrics['cache_files'] = count($cache_files);
        $metrics['cache_size'] = 0;
        
        foreach ($cache_files as $file) {
            $metrics['cache_size'] += filesize($file);
        }
        
        return $metrics;
    }
}

// Utility function to format file size
function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}

// Helper function for lazy loading JavaScript
function getLazyLoadScript() {
    return "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if ('IntersectionObserver' in window) {
            const lazyImages = document.querySelectorAll('img.lazy');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                        
                        // Add fade-in effect
                        img.style.opacity = '0';
                        img.onload = () => {
                            img.style.transition = 'opacity 0.3s';
                            img.style.opacity = '1';
                        };
                    }
                });
            });
            
            lazyImages.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            const lazyImages = document.querySelectorAll('img.lazy');
            lazyImages.forEach(img => {
                img.src = img.dataset.src;
                img.classList.remove('lazy');
            });
        }
    });
    </script>";
}

// Start performance monitoring
define('APP_START_TIME', microtime(true));
?>
