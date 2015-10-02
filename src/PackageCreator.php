<?php

namespace DRI\SugarCRM\Plugin;

use DRI\SugarCRM\Plugin\Builder\Package;

/**
 * @author Emil Kilhage
 */
class PackageCreator
{
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
     *
     */
    public function create()
    {
        $this->setupPackageDir();

        $this->writeManifest();

        $this->syncFiles();
        $this->copyFiles();

        $this->clean();

        $this->buildFiles();
        $this->cleanEmptyDirs();

        $this->createZipFile();
    }

    /**
     *
     */
    private function cleanEmptyDirs()
    {
        $this->exec("find {$this->config->getPackagePath()} -type d -empty -delete");
    }

    /**
     * @return string
     */
    private function getZipFileName()
    {
        $suffix = $this->config->get('suffix', '');

        if (!empty($suffix)) {
            $suffix = ".$suffix";
        }

        return sprintf(
            "%s.%s%s.zip",
            $this->config->get('prefix'),
            $this->config->get('manifest.version'),
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
    private function setupPackageDir()
    {
        $package = $this->config->getPackagePath();

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
        $this->config->set('published_date', $now->format("Y-m-d H:i:s"));
    }

    /**
     *
     */
    private function validateManifest()
    {
        if ($this->config->isEmpty('prefix')) {
            throw new \InvalidArgumentException('missing prefix in config');
        }

        if ($this->config->get('prefix') === 'dri_plugin_template') {
            throw new \InvalidArgumentException('Please change prefix to a unique name for your plugin');
        }

        if ($this->config->get('manifest.name') === 'DRI Plugin Template') {
            throw new \InvalidArgumentException('Please change manifest.name to a unique name for your plugin');
        }
    }

    /**
     *
     */
    private function fillManifest()
    {
        if ($this->config->isEmpty('manifest.key')) {
            $this->config->set('manifest.key', $this->config->get('prefix'));
        }

        if ($this->config->isEmpty('installdefs.id')) {
            $this->config->set('installdefs.id', $this->config->get('prefix'));
        }
    }

    /**
     *
     */
    private function writeManifest()
    {
        $this->setPublishedDate();
        $this->validateManifest();
        $this->fillManifest();

        $manifestArray = var_export($this->config->get('manifest'), true);
        $installdefsArray = var_export($this->config->get('installdefs'), true);

        $manifest_content = <<<PHP
<?php

\$manifest = $manifestArray;

\$installdefs = $installdefsArray;

PHP;

        file_put_contents($this->config->getPackageManifestFile(), $manifest_content);
    }

    /**
     *
     */
    private function clean()
    {
        $package = $this->config->getPackagePath();

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
        $package = $this->config->getPackagePath();
        $packages = $this->config->getPackagesPath();

        chdir($package);

        $zipFile = $this->getZipFileName();

        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        $this->exec("zip -r $zipFile *");

        if (!is_dir($packages)) {
            $this->exec("mkdir -p $packages");
        }

        $this->exec("mv $zipFile $packages/");
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

            $from = "{$this->config->getRootPath()}/$from";
            $to = "{$this->config->getPackagePath()}/$to";

            $to = dirname($to);

            if (!is_dir($to)) {
                $this->exec("mkdir -p $to");
            }

            $this->exec("rsync -r $from $to");
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

            $from = "{$this->config->getRootPath()}/$from";
            $to = "{$this->config->getPackagePath()}/$to";

            $toDir = dirname($to);

            if (!is_dir($toDir)) {
                $this->exec("mkdir -p $toDir");
            }

            $this->exec("cp $from $to");
        }
    }
}
