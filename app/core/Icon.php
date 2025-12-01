<?php
/**
 * Icon Helper - Loads SVG icons from the icons directory
 */
class Icon
{
    private static string $iconPath = '';
    private static array $cache = [];

    public static function init(): void
    {
        self::$iconPath = __DIR__ . '/../../public/icons/';
    }

    /**
     * Get an SVG icon by name
     * 
     * @param string $name Icon name (without .svg extension)
     * @param int|null $width Override width
     * @param int|null $height Override height
     * @param string|null $style Additional inline style
     * @return string SVG markup
     */
    public static function get(string $name, ?int $width = null, ?int $height = null, ?string $style = null): string
    {
        $filePath = self::$iconPath . $name . '.svg';
        
        // Check cache
        if (isset(self::$cache[$filePath])) {
            $svg = self::$cache[$filePath];
        } else {
            if (!file_exists($filePath)) {
                return '<!-- Icon not found: ' . htmlspecialchars($name) . ' -->';
            }
            
            $svg = file_get_contents($filePath);
            self::$cache[$filePath] = $svg;
        }
        
        // Override dimensions if specified (use word boundary to avoid matching stroke-width)
        if ($width !== null) {
            $svg = preg_replace('/\bwidth="[^"]*"/', 'width="' . $width . '"', $svg, 1);
        }
        
        if ($height !== null) {
            $svg = preg_replace('/\bheight="[^"]*"/', 'height="' . $height . '"', $svg, 1);
        }
        
        // Add style if specified
        if ($style !== null) {
            $svg = preg_replace('/<svg/', '<svg style="' . htmlspecialchars($style) . '"', $svg);
        }
        
        return $svg;
    }

    /**
     * Echo an SVG icon by name (convenience method)
     */
    public static function render(string $name, ?int $width = null, ?int $height = null, ?string $style = null): void
    {
        echo self::get($name, $width, $height, $style);
    }
}

// Initialize icon path
Icon::init();
