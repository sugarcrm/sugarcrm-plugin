<?php

namespace DRI\SugarCRM\Plugin;

/**
 * @author Emil Kilhage
 */
class Config implements \ArrayAccess
{
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
        'name' => null,
        'suffix' => '',
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
        ),
        'copy' => array (
            'doc/README.md' => 'README.md',
            'doc/LICENCE.md' => 'LICENCE.md',
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
    public function getSourceManifestFile()
    {
        return "{$this->getRootPath()}/manifest.php";
    }

    /**
     * @return string
     */
    public function getPackageManifestFile()
    {
        return "{$this->getPackagePath()}/manifest.php";
    }

    /**
     * @param string $name
     * @param null|string $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        if (isset($default)) {
            return $default;
        }

        print_r($this->config);

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
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->config[$name]);
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
        return in_array($flavour, $this->getAvailableFlavours());
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
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
    }
}
