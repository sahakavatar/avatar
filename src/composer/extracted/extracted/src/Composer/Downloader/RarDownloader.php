<?php


namespace Composer\Downloader;

use Composer\Cache;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\IOInterface;
use Composer\Util\IniHelper;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;
use Composer\Util\RemoteFilesystem;
use RarArchive;


class RarDownloader extends ArchiveDownloader
{
    protected $process;

    public function __construct(IOInterface $io, Config $config, EventDispatcher $eventDispatcher = null, Cache $cache = null, ProcessExecutor $process = null, RemoteFilesystem $rfs = null)
    {
        $this->process = $process ?: new ProcessExecutor($io);
        parent::__construct($io, $config, $eventDispatcher, $cache, $rfs);
    }

    protected function extract($file, $path)
    {
        $processError = null;


        if (!Platform::isWindows()) {
            $command = 'unrar x ' . ProcessExecutor::escape($file) . ' ' . ProcessExecutor::escape($path) . ' >/dev/null && chmod -R u+w ' . ProcessExecutor::escape($path);

            if (0 === $this->process->execute($command, $ignoredOutput)) {
                return;
            }

            $processError = 'Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput();
        }

        if (!class_exists('RarArchive')) {

            $iniMessage = IniHelper::getMessage();

            $error = "Could not decompress the archive, enable the PHP rar extension or install unrar.\n"
                . $iniMessage . "\n" . $processError;

            if (!Platform::isWindows()) {
                $error = "Could not decompress the archive, enable the PHP rar extension.\n" . $iniMessage;
            }

            throw new \RuntimeException($error);
        }

        $rarArchive = RarArchive::open($file);

        if (false === $rarArchive) {
            throw new \UnexpectedValueException('Could not open RAR archive: ' . $file);
        }

        $entries = $rarArchive->getEntries();

        if (false === $entries) {
            throw new \RuntimeException('Could not retrieve RAR archive entries');
        }

        foreach ($entries as $entry) {
            if (false === $entry->extract($path)) {
                throw new \RuntimeException('Could not extract entry');
            }
        }

        $rarArchive->close();
    }
}
