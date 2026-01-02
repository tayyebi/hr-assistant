<?php
/**
 * DEPRECATED: ExcelStorage shim
 *
 * The legacy Excel-based storage has been moved to `cli/legacy/ExcelStorage.php`
 * and should only be used by the migration tool `cli/import_excel.php`.
 *
 * If you see this error in runtime, it means some code still depends on
 * the legacy storage; remove such dependencies and migrate data to MySQL.
 */

class ExcelStorage
{
    public static function __callStatic($name, $args)
    {
        throw new \Exception("ExcelStorage is deprecated. Use cli/import_excel.php and cli/legacy/ExcelStorage.php for migration.");
    }
}
