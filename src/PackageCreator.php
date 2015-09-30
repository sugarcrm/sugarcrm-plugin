<?php

namespace DRI\SugarCRM\Plugin;

use DRI\SugarCRM\Plugin\Builder\Package;

/**
 * @author Emil Kilhage
 */
class PackageCreator
{
    /**
     * @var array
     */
    private $manifest;

    /**
     * @var array
     */
    private $installdefs;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Package
     */
    private $builder;

    /**
     * PackageCreator constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->builder = new Package($this->config);
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
        return $this->config->getPackagePath();
    }

    /**
     * @return string
     */
    public function getSourceManifestFile()
    {
        return $this->config->getSourceManifestFile();
    }

    /**
     * @return string
     */
    public function getPackageManifestFile()
    {
        return $this->config->getPackageManifestFile();
    }

    /**
     *
     */
    public function create()
    {
        $this->readManifest();

        $this->setupPackageDir();

        $this->writeManifest();

        $this->syncFiles();
        $this->copyFiles();

        $this->clean();

        $this->buildFiles();

        $this->createZipFile();
    }

    /**
     * @return string
     */
    public function getZipFileName()
    {
        $suffix = $this->config->get('suffix', '');

        if (!empty($suffix)) {
            $suffix = ".$suffix";
        }

        return sprintf(
            "%s.%s%s.zip",
            $this->config->get('prefix'),
            $this->manifest['version'],
            $suffix
        );
    }

    /**
     * @param $cmd
     */
    private function exec($cmd)
    {
        Cli::exec($cmd);
    }

    /**
     *
     */
    private function buildFiles()
    {
        $this->builder->build();
    }

    /**
     *
     */
    private function readManifest()
    {
        $manifest = null;
        $installdefs = null;

        require $this->getSourceManifestFile();

        $this->manifest = $manifest;
        $this->installdefs = $installdefs;
    }

    /**
     *
     */
    private function setupPackageDir()
    {
        $package = $this->getPackagePath();

        if (is_dir($package)) {
            $this->exec("rm -rf $package");
        }

        mkdir($package);
    }

    /**
     *
     */
    private function setPublishedDate()
    {
        $now = new \DateTime();
        $this->manifest['published_date'] = $now->format("Y-m-d H:i:s");
    }

    /**
     *
     */
    private function setManifestName()
    {
        $this->manifest['name'] = $this->config->get('name');
    }

    /**
     *
     */
    private function writeManifest()
    {
        $this->setPublishedDate();
        $this->setManifestName();

        $manifestArray = var_export($this->manifest, true);
        $installdefsArray = var_export($this->installdefs, true);

        $manifest_content = <<<PHP
<?php

\$manifest = $manifestArray;

\$installdefs = $installdefsArray;

PHP;

        file_put_contents($this->getPackageManifestFile(), $manifest_content);
    }

    /**
     *
     */
    private function clean()
    {
        $package = $this->getPackagePath();

        foreach ($this->config->get('globalClean') as $file) {
            $this->exec("find $package -name \"$file\" -exec rm '{}' \\;");
        }

        foreach ($this->config->get('clean') as $file) {
            $this->exec("rm -rf $package/$file");
        }
    }

    /**
     *
     */
    private function createZipFile()
    {
        $root = $this->getRootPath();
        $package = $this->getPackagePath();

        chdir($package);

        $zipFile = $this->getZipFileName();

        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        $this->exec("zip -r $zipFile *");

        $this->exec("mv $zipFile $root/");
    }

    /**
     *
     */
    private function syncFiles()
    {
        foreach ($this->config->get('sync') as $from => $to) {
            if (is_int($from)) {
                $from = $to;
            }

            $from = "{$this->getRootPath()}/$from";
            $toDir = "{$this->getPackagePath()}/$to";

            $toDir = dirname($toDir);

            if (!is_dir($toDir)) {
                $this->exec("mkdir -p $toDir");
            }

            $this->exec("rsync -r $from $toDir");
        }
    }

    /**
     *
     */
    private function copyFiles()
    {
        foreach ($this->config->get('copy') as $from => $to) {
            if (is_int($from)) {
                $from = $to;
            }

            $from = "{$this->getRootPath()}/$from";
            $to = "{$this->getPackagePath()}/$to";

            $toDir = dirname($to);

            if (!is_dir($toDir)) {
                $this->exec("mkdir -p $toDir");
            }

            $this->exec("cp $from $to");
        }
    }
}
