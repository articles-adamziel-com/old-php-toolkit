<?php

$baseComposerFile = __DIR__ . '/../composer.base.json';
$componentsDir = __DIR__ . '/../components';

// Load base composer.json
$composerData = json_decode(file_get_contents($baseComposerFile), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error parsing composer.base.json\n");
}

// Initialize arrays if they don't exist
$composerData['require'] = $composerData['require'] ?? [];
$composerData['autoload'] = $composerData['autoload'] ?? [];

// Iterate through components
foreach (new DirectoryIterator($componentsDir) as $component) {
    if ($component->isDot() || !$component->isDir()) continue;

    $componentComposerFile = $component->getPathname() . '/composer.json';
    if (!file_exists($componentComposerFile)) continue;

    $componentData = json_decode(file_get_contents($componentComposerFile), true);
    if (json_last_error() !== JSON_ERROR_NONE) continue;
    // Merge require dependencies
    if (!empty($componentData['require'])) {
        foreach ($componentData['require'] as $package => $version) {
            if (isset($composerData['require'][$package]) && $composerData['require'][$package] !== $version) {
                echo "Warning: Package {$package} has conflicting versions: {$composerData['require'][$package]} vs {$version}\n";
            }
        }
        $composerData['require'] = array_merge(
            $composerData['require'],
            $componentData['require']
        );
    }

    // Merge require-dev dependencies
    if (!empty($componentData['require-dev'])) {
        if (!isset($composerData['require-dev'])) {
            $composerData['require-dev'] = [];
        }
        foreach ($componentData['require-dev'] as $package => $version) {
            if (isset($composerData['require-dev'][$package]) && $composerData['require-dev'][$package] !== $version) {
                echo "Warning: Package {$package} has conflicting versions in require-dev: {$composerData['require-dev'][$package]} vs {$version}\n";
            }
        }
        $composerData['require-dev'] = array_merge(
            $composerData['require-dev'],
            $componentData['require-dev']
        );
    }
    // Merge autoload configurations
    if (!empty($componentData['autoload'])) {
        foreach ($componentData['autoload'] as $type => $mappings) {
            if (!isset($composerData['autoload'][$type])) {
                $composerData['autoload'][$type] = [];
            }
            
            // Adjust paths for different autoload types
            switch ($type) {
                case 'psr-4':
                case 'psr-0':
                    $adjustedMappings = [];
                    foreach ($mappings as $namespace => $paths) {
                        $paths = (array)$paths;
                        $adjustedPaths = array_map(function($path) use ($component) {
                            return 'components/' . $component->getBasename() . '/' . ltrim($path, '/');
                        }, $paths);
                        $adjustedMappings[$namespace] = count($adjustedPaths) === 1 ? $adjustedPaths[0] : $adjustedPaths;
                    }
                    $mappings = $adjustedMappings;
                    break;

                case 'files':
                    $mappings = array_map(function($path) use ($component) {
                        return 'components/' . $component->getBasename() . '/' . ltrim($path, '/');
                    }, $mappings);
                    // Remove duplicates while preserving order
                    $mappings = array_values(array_unique($mappings));
                    break;

                case 'classmap':
                    $mappings = array_map(function($path) use ($component) {
                        return 'components/' . $component->getBasename() . '/' . ltrim($path, '/');
                    }, $mappings);
                    // Remove duplicates while preserving order
                    $mappings = array_values(array_unique($mappings));
                    break;
            }
            
            if ($type === 'files' || $type === 'classmap' || $type === 'exclude-from-classmap') {
                // Merge arrays and remove duplicates while preserving order
                $composerData['autoload'][$type] = array_values(array_unique(
                    array_merge($composerData['autoload'][$type], $mappings)
                ));
            } else {
                $composerData['autoload'][$type] = array_merge(
                    $composerData['autoload'][$type],
                    $mappings
                );
            }
        }
    }
}

// Write merged composer.json
file_put_contents(__DIR__ . '/../composer.json', json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "composer.json has been generated.\n";