<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Composer;

/**
 * Class InfoCommand calls composer info command
 */
class InfoCommand
{
    /**
     * Current version
     */
    const CURRENT_VERSION = 'current_version';

    const VERSIONS = 'versions';

    /**
     * Available versions
     */
    const AVAILABLE_VERSIONS = 'available_versions';


    /**
     * @var MagentoComposerApplication
     */
    protected $magentoComposerApplication;

    /**
     * Constructor
     *
     * @param MagentoComposerApplication $magentoComposerApplication
     */
    public function __construct(MagentoComposerApplication $magentoComposerApplication)
    {
        $this->magentoComposerApplication = $magentoComposerApplication;
    }

    /**
     * Runs composer info command
     *
     * @param string $package
     * @param bool $installed
     * @return array|bool
     */
    public function run($package, $installed = false)
    {
        $commandParameters = [
            'command' => 'info',
            'package' => $package,
            '-i' => $installed
        ];

        $result = [];

        try {
            $output = $this->magentoComposerApplication->runComposerCommand($commandParameters);
        } catch (\RuntimeException $e) {
            return false;
        }

        $rawLines = explode(PHP_EOL, $output);

        foreach ($rawLines as $line) {
            $chunk = explode(':', $line);
            if (count($chunk) === 2) {
                $result[trim($chunk[0])] = trim($chunk[1]);
            }
        }

        $result = $this->extractVersions($result);

        return $result;
    }

    /**
     * Extracts package versions info
     *
     * @param array $packageInfo
     * @return array
     */
    private function extractVersions($packageInfo)
    {
        $versions = explode(', ', $packageInfo[self::VERSIONS]);

        if (count($versions) === 1) {
            $packageInfo[self::CURRENT_VERSION] = str_replace('* ', '', $packageInfo[self::VERSIONS]);
            $packageInfo[self::AVAILABLE_VERSIONS] = [];
        } else {
            $currentVersion = array_values(preg_grep("/^\*.*/", $versions));
            if ($currentVersion) {
                $packageInfo[self::CURRENT_VERSION] = str_replace('* ', '', $currentVersion[0]);
            } else {
                $packageInfo[self::CURRENT_VERSION] = '';
            }

            $packageInfo[self::AVAILABLE_VERSIONS] = array_values(preg_grep("/^\*.*/", $versions, PREG_GREP_INVERT));
        }

        return $packageInfo;
    }
}
