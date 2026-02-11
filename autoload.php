<?php
/**
 * Dynamic PSR-4 Autoloader for HR Assistant
 * Fully automatic class loading with namespace support
 * 
 * Features:
 * - PSR-4 compliant namespace mapping
 * - Automatic file discovery
 * - Support for legacy non-namespaced classes
 * - Case-insensitive file system support
 * - Development-friendly error reporting
 */

class HRAutoloader
{
    /**
     * @var array PSR-4 namespace mappings
     */
    private static $namespaces = [
        'App\\Controllers\\' => 'app/controllers/',
        'App\\Models\\' => 'app/models/',
        'App\\Core\\' => 'app/core/',
        'App\\' => 'app/',
        'HRAssistant\\Controllers\\' => 'app/controllers/',
        'HRAssistant\\Models\\' => 'app/models/',
        'HRAssistant\\Core\\' => 'app/core/',
        'HRAssistant\\' => 'app/',
    ];

    /**
     * @var array Legacy class mappings for backward compatibility
     */
    private static $legacyClasses = [];

    /**
     * @var string Base directory path for the application
     */
    private static $basePath;

    /**
     * @var bool Enable development mode for verbose error reporting
     */
    private static $devMode = true;

    /**
     * @var array Cache for discovered files to improve performance
     */
    private static $fileCache = [];

    /**
     * Initialize the autoloader
     * 
     * @param string|null $basePath Base path for the application
     * @param bool $devMode Enable development mode for verbose errors
     */
    public static function init($basePath = null, $devMode = true)
    {
        self::$basePath = $basePath ?: dirname(__FILE__);
        self::$devMode = $devMode;
        
        // Discover existing files for legacy support
        self::discoverLegacyClasses();
        
        // Register autoloader with highest priority
        spl_autoload_register([self::class, 'autoload'], true, true);
        
        if (self::$devMode) {
            self::validateDirectories();
        }
    }

    /**
     * PSR-4 compliant autoloader
     * 
     * @param string $className Fully qualified class name
     * @return bool True if class was loaded successfully
     */
    public static function autoload($className)
    {
        // First try PSR-4 namespace mapping
        if (self::loadPsr4Class($className)) {
            return true;
        }
        
        // Try to map bare class name to namespaced equivalent
        if (strpos($className, '\\') === false) {
            if (self::loadBareClassName($className)) {
                return true;
            }
        }
        
        // Fallback to legacy class mapping
        if (self::loadLegacyClass($className)) {
            return true;
        }
        
        // Try file discovery as last resort
        if (self::discoverAndLoadClass($className)) {
            return true;
        }
        
        if (self::$devMode) {
            error_log("HRAutoloader: Failed to load class '$className'");
        }
        
        return false;
    }
    
    /**
     * Create backward compatibility alias for a namespaced class
     */
    private static function createAlias($namespacedClass, $alias)
    {
        if (class_exists($namespacedClass) && !class_exists($alias)) {
            class_alias($namespacedClass, $alias);
        }
    }
    
    /**
     * Create backward compatibility alias for specific namespaced classes
     */
    private static function createBackwardCompatibilityAlias($className)
    {
        // Define mapping from namespaced classes to their backward compatible aliases
        $aliases = [
            // Core class aliases (App namespace)
            'App\\Core\\Database' => 'Database',
            'App\\Core\\View' => 'View',
            'App\\Core\\Router' => 'Router',
            'App\\Core\\Icon' => 'Icon',
            'App\\Core\\HttpClient' => 'HttpClient',
            'App\\Core\\HttpProvider' => 'HttpProvider',
            'App\\Core\\Provider' => 'Provider',
            'App\\Core\\ProviderFactory' => 'ProviderFactory',
            'App\\Core\\ProviderSettings' => 'ProviderSettings',
            'App\\Core\\Providers' => 'Providers',
            'App\\Core\\ProviderType' => 'ProviderType',
            'App\\Core\\EmailProvider' => 'EmailProvider',
            'App\\Core\\GitProvider' => 'GitProvider',
            'App\\Core\\MessengerProvider' => 'MessengerProvider',
            'App\\Core\\IamProvider' => 'IamProvider',
            'App\\Core\\MailcowProvider' => 'MailcowProvider',
            'App\\Core\\GitLabProvider' => 'GitLabProvider',
            'App\\Core\\TelegramProvider' => 'TelegramProvider',
            'App\\Core\\KeycloakProvider' => 'KeycloakProvider',
            'App\\Core\\AssetManager' => 'AssetManager',
            'App\\Core\\ExcelStorage' => 'ExcelStorage',
            
            // Model class aliases (App namespace)
            'App\\Models\\User' => 'User',
            'App\\Models\\Employee' => 'Employee',
            'App\\Models\\Tenant' => 'Tenant',
            'App\\Models\\Team' => 'Team',
            'App\\Models\\Asset' => 'Asset',
            'App\\Models\\Job' => 'Job',
            'App\\Models\\Message' => 'Message',
            'App\\Models\\Config' => 'Config',
            'App\\Models\\ProviderInstance' => 'ProviderInstance',
            
            // Controller class aliases (App namespace)
            'App\\Controllers\\AuthController' => 'AuthController',
            'App\\Controllers\\DashboardController' => 'DashboardController',
            'App\\Controllers\\EmployeeController' => 'EmployeeController',
            'App\\Controllers\\TeamController' => 'TeamController',
            'App\\Controllers\\AssetController' => 'AssetController',
            'App\\Controllers\\JobController' => 'JobController',
            'App\\Controllers\\MessageController' => 'MessageController',
            'App\\Controllers\\SettingsController' => 'SettingsController',
            'App\\Controllers\\SystemAdminController' => 'SystemAdminController',
            'App\\Controllers\\NotificationController' => 'NotificationController',
            'App\\Controllers\\ReportsController' => 'ReportsController',
            'App\\Controllers\\AuditController' => 'AuditController',
            'App\\Controllers\\ApiController' => 'ApiController',
            
            // Legacy HRAssistant namespace mappings for backward compatibility
            // Core class aliases
            'HRAssistant\\Core\\Database' => 'Database',
            'HRAssistant\\Core\\View' => 'View',
            'HRAssistant\\Core\\Router' => 'Router',
            'HRAssistant\\Core\\Icon' => 'Icon',
            'HRAssistant\\Core\\HttpClient' => 'HttpClient',
            'HRAssistant\\Core\\HttpProvider' => 'HttpProvider',
            'HRAssistant\\Core\\IProvider' => 'IProvider',
            'HRAssistant\\Core\\AbstractProvider' => 'AbstractProvider',
            'HRAssistant\\Core\\ProviderFactory' => 'ProviderFactory',
            'HRAssistant\\Core\\ProviderSettings' => 'ProviderSettings',
            'HRAssistant\\Core\\EmailProvider' => 'EmailProvider',
            'HRAssistant\\Core\\GitProvider' => 'GitProvider',
            'HRAssistant\\Core\\MessengerProvider' => 'MessengerProvider',
            'HRAssistant\\Core\\IamProvider' => 'IamProvider',
            'HRAssistant\\Core\\ProviderType' => 'ProviderType',
            'HRAssistant\\Core\\AssetManager' => 'AssetManager',
            'HRAssistant\\Core\\MailcowProvider' => 'MailcowProvider',
            
            // Model class aliases
            'HRAssistant\\Models\\User' => 'User',
            'HRAssistant\\Models\\Employee' => 'Employee',
            'HRAssistant\\Models\\Tenant' => 'Tenant',
            'HRAssistant\\Models\\Team' => 'Team',
            'HRAssistant\\Models\\Asset' => 'Asset',
            'HRAssistant\\Models\\Job' => 'Job',
            'HRAssistant\\Models\\Message' => 'Message',
            'HRAssistant\\Models\\Config' => 'Config',
            'HRAssistant\\Models\\ProviderInstance' => 'ProviderInstance',
            
            // Controller class aliases
            'HRAssistant\\Controllers\\AuthController' => 'AuthController',
            'HRAssistant\\Controllers\\DashboardController' => 'DashboardController',
            'HRAssistant\\Controllers\\EmployeeController' => 'EmployeeController',
            'HRAssistant\\Controllers\\TeamController' => 'TeamController',
            'HRAssistant\\Controllers\\AssetController' => 'AssetController',
            'HRAssistant\\Controllers\\JobController' => 'JobController',
            'HRAssistant\\Controllers\\MessageController' => 'MessageController',
            'HRAssistant\\Controllers\\SettingsController' => 'SettingsController',
            'HRAssistant\\Controllers\\SystemAdminController' => 'SystemAdminController',
            'HRAssistant\\Controllers\\NotificationController' => 'NotificationController',
            'HRAssistant\\Controllers\\ReportsController' => 'ReportsController',
            'HRAssistant\\Controllers\\AuditController' => 'AuditController',
            'HRAssistant\\Controllers\\ApiController' => 'ApiController',
        ];
        
        if (isset($aliases[$className])) {
            self::createAlias($className, $aliases[$className]);
        }
    }

    /**
     * Load class using PSR-4 namespace mapping
     * 
     * @param string $className
     * @return bool
     */
    private static function loadPsr4Class($className)
    {
        foreach (self::$namespaces as $namespace => $baseDir) {
            if (strpos($className, $namespace) === 0) {
                $relativeClass = substr($className, strlen($namespace));
                $file = self::$basePath . '/' . $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                
                if (self::loadFile($file)) {
                    // Create backward compatibility alias after successful load
                    self::createBackwardCompatibilityAlias($className);
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Try to load a bare class name by mapping it to its full namespace
     * 
     * @param string $className
     * @return bool
     */
    private static function loadBareClassName($className)
    {
        // Define mappings for bare class names to their full namespace equivalents
        $possibleMappings = [];
        
        // Try controllers
        if (preg_match('/Controller$/', $className)) {
            $possibleMappings[] = "App\\Controllers\\{$className}";
            $possibleMappings[] = "HRAssistant\\Controllers\\{$className}";
        }
        
        // Try models
        if (preg_match('/^[A-Z][a-z]+$/', $className) && !preg_match('/Controller$/', $className)) {
            $possibleMappings[] = "App\\Models\\{$className}";
            $possibleMappings[] = "HRAssistant\\Models\\{$className}";
        }
        
        // Try core classes
        $coreClasses = ['Router', 'View', 'Database', 'Icon', 'HttpClient', 'AssetManager', 'Provider', 'ProviderFactory'];
        if (in_array($className, $coreClasses)) {
            $possibleMappings[] = "App\\Core\\{$className}";
            $possibleMappings[] = "HRAssistant\\Core\\{$className}";
        }
        
        // Try to load each possible mapping
        foreach ($possibleMappings as $fullClassName) {
            if (self::loadPsr4Class($fullClassName)) {
                // Create an alias for the bare class name
                if (!class_exists($className, false)) {
                    class_alias($fullClassName, $className);
                }
                return true;
            }
        }
        
        return false;
    }

    /**
     * Load class using legacy mapping
     * 
     * @param string $className
     * @return bool
     */
    private static function loadLegacyClass($className)
    {
        if (isset(self::$legacyClasses[$className])) {
            return self::loadFile(self::$basePath . '/' . self::$legacyClasses[$className]);
        }
        
        return false;
    }

    /**
     * Discover and load class by searching directories
     * 
     * @param string $className
     * @return bool
     */
    private static function discoverAndLoadClass($className)
    {
        // Check cache first
        if (isset(self::$fileCache[$className])) {
            return self::loadFile(self::$fileCache[$className]);
        }
        
        // Search in common directories
        $searchPaths = [
            'app/controllers/' . $className . '.php',
            'app/models/' . $className . '.php',
            'app/core/' . $className . '.php',
        ];
        
        // Try case-insensitive search for controllers and models
        if (preg_match('/Controller$/', $className)) {
            $searchPaths[] = 'app/controllers/' . $className . '.php';
        }
        
        if (preg_match('/^[A-Z][a-z]+$/', $className)) {
            $searchPaths[] = 'app/models/' . $className . '.php';
        }
        
        foreach ($searchPaths as $path) {
            $fullPath = self::$basePath . '/' . $path;
            if (file_exists($fullPath)) {
                self::$fileCache[$className] = $fullPath;
                return self::loadFile($fullPath);
            }
        }
        
        return false;
    }

    /**
     * Load a PHP file
     * 
     * @param string $file
     * @return bool
     */
    private static function loadFile($file)
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        
        return false;
    }

    /**
     * Discover existing classes for legacy support
     */
    private static function discoverLegacyClasses()
    {
        $directories = [
            'app/controllers/',
            'app/models/',
            'app/core/'
        ];
        
        foreach ($directories as $dir) {
            $fullDir = self::$basePath . '/' . $dir;
            if (is_dir($fullDir)) {
                $files = glob($fullDir . '*.php');
                foreach ($files as $file) {
                    $className = basename($file, '.php');
                    self::$legacyClasses[$className] = str_replace(self::$basePath . '/', '', $file);
                }
            }
        }
    }

    /**
     * Validate that required directories exist
     */
    private static function validateDirectories()
    {
        foreach (self::$namespaces as $namespace => $dir) {
            $fullPath = self::$basePath . '/' . $dir;
            if (!is_dir($fullPath)) {
                error_log("HRAutoloader: Directory '$fullPath' does not exist for namespace '$namespace'");
            }
        }
    }

    /**
     * Register a new namespace
     * 
     * @param string $namespace
     * @param string $baseDir
     */
    public static function registerNamespace($namespace, $baseDir)
    {
        self::$namespaces[$namespace] = $baseDir;
    }

    /**
     * Get registered namespaces
     * 
     * @return array
     */
    public static function getNamespaces()
    {
        return self::$namespaces;
    }

    /**
     * Get discovered legacy classes
     * 
     * @return array
     */
    public static function getLegacyClasses()
    {
        return self::$legacyClasses;
    }

    /**
     * Clear file cache (useful for development)
     */
    public static function clearCache()
    {
        self::$fileCache = [];
    }

    /**
     * Get autoloader statistics
     * 
     * @return array
     */
    public static function getStats()
    {
        return [
            'namespaces' => count(self::$namespaces),
            'legacy_classes' => count(self::$legacyClasses),
            'cached_files' => count(self::$fileCache),
            'dev_mode' => self::$devMode
        ];
    }
}

// Initialize autoloader
HRAutoloader::init();

// Alias for backward compatibility
class_alias('HRAutoloader', 'Autoloader');