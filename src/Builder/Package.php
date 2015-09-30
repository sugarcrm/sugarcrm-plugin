<?php

namespace DRI\SugarCRM\Plugin\Builder;

use DRI\SugarCRM\Plugin\Config;
use DRI\SugarCRM\Plugin\StringUtils;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Emil Kilhage
 */
class Package
{
    /**
     * @var Config
     */
    private $config;

    /**
     * PackageCreator constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     *
     */
    public function build()
    {
        foreach ($this->getPaths() as $path) {
            $finder = new Finder();
            $finder->files()
                ->in("{$this->config->getPackagePath()}/$path")
                ->contains(File::BUILD_TAG_REGEX)
                ->name('/\.php|js|less|yml|css|hbs|html$/');

            foreach ($finder as $file) {
                $this->buildFile($file);
            }
        }
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        $paths = $this->config->get('sync');

        foreach ($paths as $i => $path) {
            if (StringUtils::isWildcardPath($path)) {
                $paths[$i] = dirname($path);
            }
        }

        return $paths;
    }

    /**
     * @param SplFileInfo $file
     */
    public function buildFile(SplFileInfo $file)
    {
        $fileBuilder = new File($this->config, $file);
        $fileBuilder->build();
    }
}
