<?php

namespace DRI\SugarCRM\Plugin\Builder;

use DRI\SugarCRM\Plugin\Config;
use DRI\SugarCRM\Plugin\Exception\RemoveFileException;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Emil Kilhage
 */
class File
{
    const FLAV_TAG_REGEX = '/flav(\!?)=(\w+)/i';
    const BUILD_TAG_REGEX = '/\/\/\s*(BEGIN|END|FILE|ELSE)\s*SUGARCRM\s*(.*) ONLY/i';

    /**
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var resource
     */
    private $handle;

    /**
     * PackageBuilderFile constructor.
     *
     * @param Config $config
     * @param SplFileInfo $file
     */
    public function __construct(Config $config, SplFileInfo $file)
    {
        $this->config = $config;
        $this->file = $file;
    }

    /**
     *
     */
    public function build()
    {
        try {
            $this->parse();
            $this->write();
        } catch (RemoveFileException $e) {
            $this->remove();
        }
    }

    /**
     * @param string $line
     */
    private function parseLine($line)
    {
        $preg = self::BUILD_TAG_REGEX;

        $matches = array ();
        preg_match($preg, $line, $matches);

        if (preg_match($preg, $line, $matches) !== 0) {
            $this->parseComment($matches);
        } else {
            $this->writeLine($line);
        }
    }

    /**
     * @param array $matches
     *
     * @throws RemoveFileException
     */
    private function parseComment(array $matches)
    {
        switch (strtoupper($matches[1])) {
            case 'BEGIN':
                $this->enabled = $this->evalFlavoursTag($matches[2]);
                break;
            case 'ELSE':
                $this->enabled = !$this->enabled;
                break;
            case 'END':
                $this->enabled = true;
                break;
            case 'FILE':
                if (!$this->evalFlavoursTag($matches[2])) {
                    throw new RemoveFileException();
                }
                break;
        }
    }

    /**
     * @param string $flavoursTag
     *
     * @return bool
     */
    private function evalFlavoursTag($flavoursTag)
    {
        $flavoursTag = trim($flavoursTag);
        $flavoursTag = explode(' ', $flavoursTag);

        $check = false;

        foreach ($flavoursTag as $tag) {
            $matches = array ();
            preg_match(self::FLAV_TAG_REGEX, $tag, $matches);
            if (empty($matches[1])) {
                if ($this->config->isFlavourEnabled($matches[2])) {
                    $check = true;
                }
            } else {
                if (!$this->config->isFlavourEnabled($matches[2])) {
                    $check = true;
                }
            }
        }

        return $check;
    }

    /**
     * @param string $line
     */
    private function writeLine($line)
    {
        if ($this->enabled) {
            fputs($this->handle(), $line);
        }
    }

    /**
     * @return resource
     */
    private function handle()
    {
        if (!$this->handle) {
            $this->handle = fopen($this->getParsedFileName(), 'w');
        }

        return $this->handle;
    }

    /**
     * @return string
     */
    private function getParsedFileName()
    {
        return "{$this->file->getRealPath()}.parsed";
    }

    /**
     *
     */
    private function remove()
    {
        $parsedFileName = $this->getParsedFileName();

        unlink($this->file->getRealPath());

        if (is_resource($this->handle)) {
            fclose($this->handle());
        }

        if (file_exists($parsedFileName)) {
            unlink($parsedFileName);
        }
    }

    /**
     *
     */
    private function write()
    {
        fclose($this->handle());
        if (!rename($this->getParsedFileName(), $this->file->getRealPath())) {
            throw new \Exception('unable to move file');
        }
    }

    /**
     *
     */
    private function parse()
    {
        $handle = fopen($this->file->getRealPath(), 'r');

        while ($line = fgets($handle)) {
            $this->parseLine($line);
        }
    }
}
