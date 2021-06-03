<?php

namespace CedricZiel\TwigLoaderFlysystem;

use League\Flysystem\Filesystem;
use Twig\Loader\LoaderInterface;
use Twig\Error\LoaderError;
use Twig\Source;

/**
 * Provides a template loader for twig that allows to use flysystem
 * instances to load templates.
 *
 * @package CedricZiel\TwigLoaderFlysystem
 */
class FlysystemLoader implements LoaderInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $templatePath;

    /**
     * FlysystemLoader constructor.
     *
     * @param Filesystem $filesystem
     * @param string $templatePath
     */
    public function __construct(Filesystem $filesystem, $templatePath = '')
    {
        $this->filesystem = $filesystem;
        $this->templatePath = $templatePath;
    }

    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name The name of the template to load
     *
     * @return Source The template source code
     *
     * @throws LoaderError When $name is not found
     */
    public function getSourceContext($name)
    {
        $this->getFileOrFail($name);

        $code = $this->filesystem->read($this->resolveTemplateName($name));

        if (!$code) {
            throw new LoaderError('Template could not be found on the given filesystem');
        }

        return new Source($code, $name);
    }

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @param string $name The name of the template to check if we can load
     *
     * @return bool If the template source code is handled by this loader or not
     */
    public function exists($name)
    {
        return $this->filesystem->has($this->resolveTemplateName($name));
    }

    /**
     * Checks if the underlying flysystem contains a file of the given name.
     *
     * @param string $name
     *
     * @return \League\Flysystem\File|\League\Flysystem\Handler
     * @throws LoaderError
     */
    protected function getFileOrFail($name)
    {
        if (!$this->filesystem->has($this->resolveTemplateName($name))) {
            throw new LoaderError('Template could not be found on the given filesystem');
        }

        $fileObject = $this->filesystem->get($this->resolveTemplateName($name));
        if ($fileObject->isDir()) {
            throw new LoaderError('Cannot use directory as template');
        }

        return $fileObject;
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     *
     * @return string The cache key
     *
     * @throws LoaderError When $name is not found
     */
    public function getCacheKey($name)
    {
        $this->getFileOrFail($name);

        return $name;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string $name The template name
     * @param int    $time Timestamp of the last modification time of the
     *                     cached template
     *
     * @return bool true if the template is fresh, false otherwise
     *
     * @throws LoaderError When $name is not found
     */
    public function isFresh($name, $time)
    {
        $object = $this->getFileOrFail($name);

        return (int)$time >= (int)$object->getTimestamp();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function resolveTemplateName($name)
    {
        $prefix = $this->templatePath;
        if ($this->templatePath !== null && $this->templatePath !== '') {
            $prefix = rtrim($prefix, '/').'/';
        }

        return $prefix.$name;
    }
}
