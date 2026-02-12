<?php
/**
 * Advanced plugin manager.
 * Scans src/Plugins/ for plugin.json manifests, builds a dependency graph,
 * runs topological sort (Kahn's algorithm) with cycle detection that throws
 * a descriptive error listing the cycle path.
 */

declare(strict_types=1);

namespace Src\Core;

final class PluginManager
{
    private array $loaded = [];
    private string $pluginsDir;

    public function __construct(
        private readonly Database $db,
        private readonly Router $router,
        private readonly Tenant $tenant,
    ) {
        $this->pluginsDir = dirname(__DIR__) . '/Plugins';
    }

    public function loadAll(): void
    {
        $manifests = $this->scanManifests();
        if (empty($manifests)) {
            return;
        }

        $sorted = $this->topologicalSort($manifests);

        foreach ($sorted as $name) {
            $pluginFile = $this->pluginsDir . '/' . $name . '/Plugin.php';
            if (!is_file($pluginFile)) {
                continue;
            }
            require_once $pluginFile;
            $className = 'Src\\Plugins\\' . $name . '\\Plugin';
            if (!class_exists($className)) {
                continue;
            }
            $instance = new $className();
            if (!$instance instanceof PluginInterface) {
                continue;
            }
            $instance->register($this->router, $this->tenant, $this->db);
            $this->loaded[$name] = $instance;
        }
    }

    public function getPlugin(string $name): ?PluginInterface
    {
        return $this->loaded[$name] ?? null;
    }

    public function allLoaded(): array
    {
        return $this->loaded;
    }

    public function sidebarItems(): array
    {
        $items = [];
        foreach ($this->loaded as $plugin) {
            $item = $plugin->sidebarItem();
            if ($item !== null) {
                $items[] = $item;
            }
        }
        return $items;
    }

    private function scanManifests(): array
    {
        $manifests = [];
        if (!is_dir($this->pluginsDir)) {
            return $manifests;
        }
        $dirs = glob($this->pluginsDir . '/*/plugin.json');
        foreach ($dirs as $jsonFile) {
            $raw = file_get_contents($jsonFile);
            $data = json_decode($raw, true);
            if (!is_array($data) || !isset($data['name'])) {
                continue;
            }
            $manifests[$data['name']] = $data;
        }
        return $manifests;
    }

    private function topologicalSort(array $manifests): array
    {
        $inDegree = [];
        $adjacency = [];
        $names = array_keys($manifests);

        foreach ($names as $name) {
            $inDegree[$name] = 0;
            $adjacency[$name] = [];
        }

        foreach ($manifests as $name => $manifest) {
            $requires = $manifest['requires'] ?? [];
            foreach ($requires as $dep) {
                if ($dep === 'Core') {
                    continue;
                }
                if (!isset($inDegree[$dep])) {
                    continue;
                }
                $adjacency[$dep][] = $name;
                $inDegree[$name]++;
            }
        }

        $queue = [];
        foreach ($inDegree as $name => $deg) {
            if ($deg === 0) {
                $queue[] = $name;
            }
        }

        $sorted = [];
        while (!empty($queue)) {
            $node = array_shift($queue);
            $sorted[] = $node;
            foreach ($adjacency[$node] as $neighbor) {
                $inDegree[$neighbor]--;
                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }

        if (count($sorted) !== count($names)) {
            $remaining = array_diff($names, $sorted);
            $cyclePath = $this->describeCycle($remaining, $manifests);
            throw new \RuntimeException(
                'Circular dependency detected: ' . $cyclePath
            );
        }

        return $sorted;
    }

    private function describeCycle(array $nodes, array $manifests): string
    {
        $visited = [];
        $start = reset($nodes);
        $current = $start;
        $path = [];

        for ($i = 0; $i < count($nodes) + 1; $i++) {
            if (in_array($current, $visited, true)) {
                $cycleStart = array_search($current, $path, true);
                $cycle = array_slice($path, $cycleStart);
                $cycle[] = $current;
                return implode(' → ', $cycle);
            }
            $visited[] = $current;
            $path[] = $current;
            $requires = $manifests[$current]['requires'] ?? [];
            $nextFound = false;
            foreach ($requires as $dep) {
                if ($dep !== 'Core' && in_array($dep, $nodes, true)) {
                    $current = $dep;
                    $nextFound = true;
                    break;
                }
            }
            if (!$nextFound) {
                break;
            }
        }

        return implode(', ', $nodes);
    }
}
