<?php

namespace Symfony\WebpackEncoreBundle\Asset;

/**
 * Attempts to read the source of built files.
 *
 * @internal
 */
final class BuildFileLocator
{
    private $buildPaths;

    private $ensureFileExists = true;

    /**
     * @param string[] $buildPaths
     */
    public function __construct(array $buildPaths)
    {
        $this->buildPaths = $buildPaths;
    }

    public function findFile(string $path, string $buildName = '_default'): string
    {
        if (!isset($this->buildPaths[$buildName])) {
            throw new \InvalidArgumentException(sprintf('Invalid build name "%s"', $buildName));
        }

        // sanity / security check
        if (!$this->strEndsWith($path, '.css') && !$this->strEndsWith($path, '.js')) {
            throw new \InvalidArgumentException('Can only read files ending in .css and .js');
        }

        $buildPath = $this->buildPaths[$buildName];

        $targetPath = $this->combinePaths($buildPath, $path);

        if ($this->ensureFileExists && !file_exists($targetPath)) {
            throw new \LogicException(sprintf('Cannot determine how to locate the "%s" file by combining with the output_path "%s". Looked in "%s".', $path, $buildPath, $targetPath));
        }

        return $targetPath;
    }

    /**
     * This method tries to combine the build path and asset path to get a final path.
     *
     * It's really an "attempt" and will work in all normal cases, but not
     * in all cases. For example with this config:
     *
     *      output_path: %kernel.project_dir%/public/build
     *
     * If you pass an asset whose path is "build/file1.js", this would
     * remove the duplicated "build" on both and return a final path of:
     *
     *      %kernel.project_dir%/public/build/file1.js
     */
    private function combinePaths(string $buildPath, string $path): string
    {
        $pathParts = explode('/', ltrim($path, '/'));
        $buildPathInfo = new \SplFileInfo($buildPath);

        while (true) {
            // break if there are no directories left
            if (count($pathParts) == 1) {
                break;
            }

            // break if the beginning of the path and the "directory name" of the build path
            // don't intersect
            if ($pathParts[0] !== $buildPathInfo->getFilename()) {
                break;
            }

            // pop the first "directory" off of the path
            unset($pathParts[0]);
            $pathParts = array_values($pathParts);
        }

        return $buildPathInfo->getPathname().'/'.implode('/', $pathParts);
    }

    private function strEndsWith(string $haystack, string $needle): bool
    {
        return '' === $needle || $needle === \substr($haystack, -\strlen($needle));
    }

    /**
     * @internal
     */
    public function disableFileExistsCheck(): void
    {
        $this->ensureFileExists = false;
    }
}
