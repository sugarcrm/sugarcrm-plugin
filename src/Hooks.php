<?php

namespace DRI\SugarCRM\Plugin;

/**
 * @author Emil Kilhage
 */
class Hooks
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     *
     */
    public function preBuild()
    {
        $this->execute('pre_build');
    }

    /**
     *
     */
    public function postBuild()
    {
        $this->execute('post_build');
    }

    /**
     * @param string $hook
     */
    private function execute($hook)
    {
        if (isset($this->config['hooks'][$hook])) {
            $this->call($this->config['hooks'][$hook]);
        }
    }

    /**
     * @param mixed $input
     */
    private function call($input)
    {
        if (is_callable($input)) {
            $input();
        } elseif (is_array($input)) {
            array_map(array ($this, 'call'), $input);
        }
    }
}
