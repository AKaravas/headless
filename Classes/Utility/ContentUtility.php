<?php

/*
 * This file is part of the "headless" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 *
 * (c) 2021
 */

declare(strict_types=1);

namespace FriendsOfTYPO3\Headless\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * ContentUtility
 *
 * This class group elements by column position, for easier frontend rendering.
 */
class ContentUtility
{
    /**
     * @var HeadlessUserInt
     */
    private $headlessWrapper;

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    public function __construct(?HeadlessUserInt $headlessWrapper = null)
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class)->getContentObject();
        $this->headlessWrapper = $headlessWrapper ?? GeneralUtility::makeInstance(HeadlessUserInt::class);
    }

    /**
     * This method takes whole content as JSON string, breaks it per element, and pass to groupContentElementByColPos method to group content by colPos.
     *
     * @param $content
     * @param array $configuration
     * @return string|null
     */
    public function groupContent($content, array $configuration): string
    {
        $contents = $this->configurationManager->cObjGetSingle($configuration['10'], $configuration['10.']);
        $contentData = array_map('trim', (array_slice(explode('###BREAK###', $contents), 0, -1)));
        return json_encode($this->groupContentElementsByColPos($contentData));
    }

    /**
     * Groups content by colPos.
     *
     * @param array $contentElements
     * @return array
     */
    protected function groupContentElementsByColPos(array $contentElements): array
    {
        $data = [];

        foreach ($contentElements as $key => $element) {
            if (\strpos($element, '<!--INT_SCRIPT') !== false
                && \strpos($element, HeadlessUserInt::STANDARD) === false) {
                $element = $this->headlessWrapper->wrap($element);
            }

            $element = json_decode($element);
            if ($element->colPos >= 0) {
                $data['colPos' . $element->colPos][] = $element;
            }
        }

        return $data;
    }
}
