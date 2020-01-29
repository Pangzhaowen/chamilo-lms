<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\MigrationMoodle\Traits;

/**
 * Class FileFinderTrait.
 *
 * @package Chamilo\PluginBundle\MigrationMoodle\Traits\MapTrait
 */
trait FileFinderTrait
{
    /**
     * @param $contentHash
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function findFilePath($contentHash)
    {
        $d1 = substr($contentHash, 0, 2);
        $d2 = substr($contentHash, 2, 2);

        $moodleDataPath = '/var/www/moodle/moodledata';
        $moodleDataPath = rtrim($moodleDataPath, ' /');

        $filePath = "$moodleDataPath/filedir/$d1/$d2/$contentHash";

        if (!file_exists($filePath)) {
            throw new \Exception("File $contentHash not found in $moodleDataPath/filedir");
        }

        return $filePath;
    }
}
