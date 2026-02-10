<?php
/**
 * Custom Autoloader for HR Assistant
 * Pure PHP autoloader without external dependencies
 * 
 * This autoloader follows PSR-4-like conventions but adapted for a simpler structure:
 * - Controllers end with "Controller" suffix and are in app/controllers/
 * - Models are in app/models/ 
 * - Core classes are in app/core/
 * 
 * Usage: require_once 'autoload.php';
 */

class HRAutoloader
{
    /**
     * @var array Map of class name patterns to directories
     */
    private static $classMap = [
        'Controller' => 'app/controllers/',
        'Model' => 'app/models/',
        'Core' => 'app/core/'
    ];

    /**
     * @var array Map of known core classes (for classes that don't follow naming conventions)
     */
    private static $coreClasses = [
        'Router' => 'app/core/Router.php',
        'View' => 'app/core/View.php',
        'Database' => 'app/core/Database.php',
        'Icon' => 'app/core/Icon.php',
        'Provider' => 'app/core/Provider.php',
        'IProvider' => 'app/core/Provider.php',
        'AbstractProvider' => 'app/core/Provider.php',
        'HttpProvider' => 'app/core/HttpProvider.php',
        'HttpClient' => 'app/core/HttpClient.php',
        'HttpResponse' => 'app/core/HttpClient.php',
        'HttpResponseBody' => 'app/core/HttpClient.php',
        'Providers' => 'app/core/Providers.php',
        'ProviderFactory' => 'app/core/ProviderFactory.php',
        'ProviderSettings' => 'app/core/ProviderSettings.php',
        'ProviderType' => 'app/core/ProviderType.php',
        'AssetManager' => 'app/core/AssetManager.php',
        'ExcelStorage' => 'app/core/ExcelStorage.php'
    ];

    /**
     * @var array Map of known model classes
     */
    private static $modelClasses = [
        'User' => 'app/models/User.php',
        'Employee' => 'app/models/Employee.php',
        'Team' => 'app/models/Team.php',
        'Asset' => 'app/models/Asset.php',
        'Message' => 'app/models/Message.php',
        'Job' => 'app/models/Job.php',
        'ProviderInstance' => 'app/models/ProviderInstance.php',
        'Config' => 'app/models/Config.php',
        'Tenant' => 'app/models/Tenant.php'
    ];

    /**
     * @var array Map of known controller classes
     */
    private static $controllerClasses = [
        'AuthController' => 'app/controllers/AuthController.php',
        'DashboardController' => 'app/controllers/DashboardController.php',
        'EmployeeController' => 'app/controllers/EmployeeController.php',
        'TeamController' => 'app/controllers/TeamController.php',
        'MessageController' => 'app/controllers/MessageController.php',
        'AssetController' => 'app/controllers/AssetController.php',
        'JobController' => 'app/controllers/JobController.php',
        'SettingsController' => 'app/controllers/SettingsController.php',
        'SystemAdminController' => 'app/controllers/SystemAdminController.php'
    ];

    /**
     * @var string Base directory path for the application
     */
    private static $basePath;

    /**
     * Initialize the autoloader
     * 
     * @param string|null $basePath Base path for the application (defaults to dirname of this file)
     */
    public static function init($basePath = null)
    {
        self::$basePath = $basePath ?: dirname(__FILE__);
        spl_autoload_register([self::class, 'autoload'], true, true);
    }

    /**
     * Autoload function that gets called when a class needs to be loaded
     * 
     * @param string $className The name of the class to load
     * @return bool True if the class was loaded, false otherwise
     */
    public static function autoload($className)
    {
        // First, try the explicit class maps
        if (isset(self::$coreClasses[$className])) {
            return self::loadClass(self::$coreClasses[$className]);
        }

        if (isset(self::$modelClasses[$className])) {
            return self::loadClass(self::$modelClasses[$className]);
        }

        if (isset(self::$controllerClasses[$className])) {
            return self::loadClass(self::$controllerClasses[$className]);
        }

        // Try pattern-based loading for new classes
        return self::loadByPattern($className);
    }

    /**
     * Load a class by its file path
     * 
     * @param string $filePath Relative path to the class file
     * @return bool True if loaded successfully, false otherwise
     */
    private static function loadClass($filePath)
    {
        $fullPath = self::$basePath . DIRECTORY_SEPARATOR . $filePath;
        
        if (file_exists($fullPath)) {
            require_once $fullPath;
            return true;
        }

        return false;
    }

    /**
     * Load a class based on naming patterns
     * 
     * @param string $className The name of the class to load
     * @return bool True if loaded successfully, false otherwise
     */
    private static function loadByPattern($className)
    {
        // Try controllers (classes ending with "Controller")
        if (substr($className, -10) === 'Controller') {
            $filePath = 'app/controllers/' . $className . '.php';
            if (self::loadClass($filePath)) {
                return true;
            }
        }

        // Try models (check common model directory)
        $modelPath = 'app/models/' . $className . '.php';
        if (self::loadClass($modelPath)) {
            return true;
        }

        // Try core classes
        $corePath = 'app/core/' . $className . '.php';
        if (self::loadClass($corePath)) {
            return true;
        }

        // Try scanning directories for the class
        return self::scanDirectories($className);
    }

    /**
     * Scan all known directories for a class file
     * 
     * @param string $className The name of the class to find
     * @return bool True if found and loaded, false otherwise
     */
    private static function scanDirectories($className)
    {
        $directories = [
            'app/controllers/',
            'app/models/',
            'app/core/',
            'cli/'
        ];

        foreach ($directories as $dir) {
            $fullDir = self::$basePath . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($fullDir)) {
                $files = glob($fullDir . '*.php');
                foreach ($files as $file) {
                    $filename = basename($file, '.php');
                    if ($filename === $className) {
                        require_once $file;
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Add a new class to the autoloader mapping
     * 
     * @param string $className Name of the class
     * @param string $filePath Relative path to the class file
     */
    public static function addClass($className, $filePath)
    {
        // Determine the type based on the file path
        if (strpos($filePath, 'controllers/') !== false) {
            self::$controllerClasses[$className] = $filePath;
        } elseif (strpos($filePath, 'models/') !== false) {
            self::$modelClasses[$className] = $filePath;
        } elseif (strpos($filePath, 'core/') !== false) {
            self::$coreClasses[$className] = $filePath;
        }
    }

    /**
     * Get all registered classes
     * 
     * @return array Array of all registered classes with their file paths
     */
    public static function getAllClasses()
    {
        return array_merge(
            self::$coreClasses,
            self::$modelClasses,
            self::$controllerClasses
        );
    }

    /**
     * Check if a class is registered
     * 
     * @param string $className Name of the class to check
     * @return bool True if registered, false otherwise
     */
    public static function isClassRegistered($className)
    {
        return isset(self::$coreClasses[$className]) ||
               isset(self::$modelClasses[$className]) ||
               isset(self::$controllerClasses[$className]);
    }

    /**
     * Get debug information about the autoloader
     * 
     * @return array Debug information
     */
    public static function getDebugInfo()
    {
        return [
            'base_path' => self::$basePath,
            'core_classes' => count(self::$coreClasses),
            'model_classes' => count(self::$modelClasses),
            'controller_classes' => count(self::$controllerClasses),
            'total_classes' => count(self::getAllClasses())
        ];
    }
}

// Auto-initialize the autoloader when this file is included
HRAutoloader::init();