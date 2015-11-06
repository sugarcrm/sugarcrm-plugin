<?php

namespace DRI\SugarCRM\Plugin;

use Dflydev\DotAccessData\Data;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Emil Kilhage
 */
class Config implements \ArrayAccess
{
    /**
     *
     */
    const DEFAULT_TYPE = 'full';

    /**
     * @param null $path
     *
     * @return Config
     */
    public static function factory($path = null)
    {
        $path = $path ?: self::getConfigFilePath();

        $params = require $path;

        if (isset($params['class'])) {
            $config = new $params['class']();
        } else {
            $config = new static();
        }

        if (isset($params['flavours'])) {
            $config->setFlavours($params['flavours']);
            unset($params['flavours']);
        }

        $config->merge($params);

        return $config;
    }

    /**
     * @return string
     */
    protected static function getConfigFilePath()
    {
        return Path::getRootPath()."/config.php";
    }

    /**
     * @var array
     */
    protected $config = array (
        'class' => null,
        'prefix' => null,
        'suffix' => '',
        'manifest' => array (
            'readme' => '',
            'key' => '',
            'description' => '',
            'icon' => '',
            'is_uninstallable' => true,
            'name' => null,
            'published_date' => null,
            'type' => 'module',
            'version' => null,
            'remove_tables' => 'prompt',
        ),
        'installdefs' => array (
            'id' => '',
            'post_execute' => array (
                '<basepath>/actions/post_install_actions.php',
            ),
            'post_uninstall' => array (
                '<basepath>/actions/post_uninstall_actions.php',
            ),
            'copy' => array (
                array (
                    'from' => '<basepath>/src/custom',
                    'to' => 'custom',
                ),
            ),
            'beans'=> array (),
        ),
        'clean' => array (
            'src/custom/application',
            'src/custom/blowfish',
            'src/custom/history',
            'src/custom/script',
            'src/custom/scripts',
            'src/custom/modules/*/Ext',
            'src/custom/include/language',
            'src/custom/modules/Connectors/metadata',
            'src/custom/modules/unified_search_modules_display.php',
        ),
        'dev' => array (
            'src/custom' => 'custom',
        ),
        'globalClean' => array (
            ".DS_Store",
            ".gitkeep",
            ".git",
        ),
        'copy' => array (
            'doc/README.txt' => 'README.txt',
            'doc/LICENCE.txt' => 'LICENCE.txt',
        ),
        'sync' => array (
            'src/custom',
            'actions',
        ),
    );

    /**
     * @var array
     */
    protected $flavours = array ();

    /**
     * @var string[]
     */
    protected $currentFlavours = array ();

    /**
     * @param array $flavours
     */
    public function setFlavours($flavours)
    {
        $this->flavours = $flavours;
    }

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->scanModules();
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return Path::getRootPath();
    }

    /**
     * @return string
     */
    public function getPackagePath()
    {
        return "{$this->getRootPath()}/package";
    }

    /**
     * @return string
     */
    public function getPackagesPath()
    {
        return "{$this->getRootPath()}/packages";
    }

    /**
     * @return string
     */
    public function getSrcPath()
    {
        return "{$this->getRootPath()}/src";
    }

    /**
     * @return string
     */
    public function getPackageManifestFile()
    {
        return "{$this->getPackagePath()}/manifest.php";
    }

    /**
     *
     */
    private function scanModules()
    {
        $finder = new Finder();
        $finder->directories()
            ->in("{$this->getSrcPath()}/modules")
            ->depth(0);

        foreach ($finder as $file) {
            /** @var SplFileInfo $file */
            $path = "src/modules/{$file->getRelativePathname()}";
            $this->config['sync'][] = $path;
            $this->config['dev'][$path] = "modules/{$file->getRelativePathname()}";
        }

        if ($finder->count() > 0) {
            $this->config['installdefs']['copy'][] = array (
                'from' => '<basepath>/src/modules',
                'to' => 'modules',
            );

            $this->scanBeans();
        }
    }

    /**
     *
     */
    private function scanBeans()
    {
        $finder = new Finder();
        $finder->files()
            ->in("{$this->getSrcPath()}/custom/Extension/application/Ext/Include")
            ->name('/\.php/');

        foreach ($finder as $file) {
            $this->scanBeanFile($file);
        }
    }

    /**
     * @param SplFileInfo $file
     */
    private function scanBeanFile(SplFileInfo $file)
    {
        $beanList = array ();
        $beanFiles = array ();

        require $file->getRealPath();

        foreach ($beanList as $moduleName => $beanName) {
            $beanFile = $beanFiles[$beanName];
            $this->config['installdefs']['beans'][] = array (
                'module' => $moduleName,
                'class' => $beanName,
                'path' => $beanFile,
                'tab' => false,
            );
        }
    }

    /**
     * @param string $name
     * @param null|string $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (strpos($name, '.') !== false) {
            $data = new Data($this->config);
            $value = $data->get($name);

            if (isset($value)) {
                return $value;
            }
        } else if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        if (isset($default)) {
            return $default;
        }

        throw new \InvalidArgumentException('missing config parameter: '.$name);
    }

    /**
     * @return array
     */
    public function getAvailableFlavours()
    {
        return !empty($this->flavours) ? array_keys($this->flavours) : array (self::DEFAULT_TYPE);
    }

    /**
     * @return string[]
     */
    public function getCurrentFlavours()
    {
        return $this->currentFlavours;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        if (strpos($name, '.') !== false) {
            $data = new Data($this->config);
            $value = $data->get($name);
            return $value !== false;
        } else if (isset($this->config[$name])) {
            return isset($this->config[$name]);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isEmpty($name)
    {
        $value = $this->get($name);
        return empty($value);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function set($name, $value)
    {
        if (strpos($name, '.') !== false) {
            $data = new Data($this->config);
            $data->set($name, $value);
            $this->config = $data->export();
        } else {
            $this->config[$name] = $value;
        }
    }

    /**
     * @param array $config
     */
    public function merge(array $config)
    {
        if (isset($config['suffix']) && !empty($this->config['suffix'])) {
            $this->config['suffix'] .= ".";
            $this->config['suffix'] .= $config['suffix'];
            unset($config['suffix']);
        }

        $this->config = ArrayUtils::mergeRecursive($this->config, $config);
    }

    /**
     * @param string $flavour
     */
    public function mergeFlavour($flavour)
    {
        if ($flavour !== Config::DEFAULT_TYPE && !isset($this->flavours[$flavour])) {
            throw new \InvalidArgumentException($flavour.' flavour of plugin is not supported');
        }

        $this->currentFlavours[] = $flavour;

        if ($flavour !== Config::DEFAULT_TYPE) {
            $this->merge($this->flavours[$flavour]);
        }
    }

    /**
     * @param string $flavour
     *
     * @return bool
     */
    public function isFlavourEnabled($flavour)
    {
        return in_array($flavour, $this->getCurrentFlavours());
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->config;
    }

    /**
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
    }
}
