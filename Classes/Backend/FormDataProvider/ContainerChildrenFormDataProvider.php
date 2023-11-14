<?php

namespace Team23\T23InlineContainer\Backend\FormDataProvider;

use B13\Container\Tca\Registry;
use Team23\T23InlineContainer\Helper\ColPosHelper;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ContainerChildrenFormDataProvider: customize container inline children
 *
 * @package Team23\T23InlineContainer\Backend\FormDataProvider
 */
class ContainerChildrenFormDataProvider implements FormDataProviderInterface
{

    /**
     * @param array $result
     * @return array
     */
    public function addData(array $result): array
    {
        // Only change the TCA if we edit or add an inline container child content element
        if (!empty($result['inlineParentUid']) && !empty($result['inlineParentFieldName'])
            && $result['inlineParentFieldName'] === 'tx_t23inlinecontainer_elements') {
            return $this->processContainerChildTca($result);
        }
        return $result;
    }

    /**
     * Process the TCA for container children: only show available colPos choices and allowed CType choices
     *
     * @param array $result
     * @return array
     */
    protected function processContainerChildTca(array $result): array
    {
        $containerId = (int)$result['inlineParentUid'];
        $parentRecord = BackendUtility::getRecord('tt_content', $containerId);

        if (!empty($parentRecord)) {
            $containerRegistry = GeneralUtility::makeInstance(Registry::class);
            $availableColumns = ColPosHelper::getAvailableColPos($containerId, (int)$result['vanillaUid']);
            if (!empty($availableColumns)) {
                // Determine allowed colPos values and column config for selected column (if not empty)
                $allowedColPosList = [];
                $selectedColPos = null;
                $columnConfig = $availableColumns[0];
                if ($result['command'] === 'new') {
                    $selectedColPos = (int) $columnConfig['colPos'];
                    // Set the default colPos value to the first allowed colPos choice
                    $result['pageTsConfig']['TCAdefaults.']['tt_content.']['colPos'] = $selectedColPos;
                } elseif ($result['command'] === 'edit' && !empty($result['databaseRow']['colPos'])) {
                    $selectedColPos = (int) $result['databaseRow']['colPos'];
                    foreach ($availableColumns as $tmpConfig) {
                        $allowedColPosList[] = (int)$tmpConfig['colPos'];
                    }
                    if (!in_array($selectedColPos, $allowedColPosList, true)) {
                        $selectedColPos = (int) current($allowedColPosList);
                        $result['databaseRow']['colPos'] = $selectedColPos;
                    }
                }

                $contentDefenderConfiguration = $containerRegistry->getContentDefenderConfiguration(
                    $parentRecord['CType'],
                    $selectedColPos
                );
                if (!empty($contentDefenderConfiguration)) {
                    $result = $this->processContentDefenderConfiguration($contentDefenderConfiguration, $result);
                }
            }
        }
        return $result;
    }

    /**
     * Process the TCA for container children: only show allowed CType/list_type items
     *
     * @param array $contentDefenderConfiguration
     * @param array $result
     * @return array
     */
    protected function processContentDefenderConfiguration(array $contentDefenderConfiguration, array $result): array
    {
        $allowedConfiguration = array_intersect_key($contentDefenderConfiguration['allowed.'] ?? [], $result['processedTca']['columns']);
        $disallowedConfiguration = array_intersect_key($contentDefenderConfiguration['disallowed.'] ?? [], $result['processedTca']['columns']);

        if (!empty($allowedConfiguration) || !empty($disallowedConfiguration)) {
            $typo3version = GeneralUtility::makeInstance(Typo3Version::class);
            $ctypeValueKey = ($typo3version->getBranch() >= 12) ? 'value' : 1;

            foreach ($allowedConfiguration as $field => $value) {
                $allowedValues = GeneralUtility::trimExplode(',', $value);
                $result['processedTca']['columns'][$field]['config']['items'] = array_filter(
                    $result['processedTca']['columns'][$field]['config']['items'],
                    static function ($item) use ($allowedValues, $ctypeValueKey) {
                        return in_array($item[$ctypeValueKey], $allowedValues, true);
                    }
                );
            }

            foreach ($disallowedConfiguration as $field => $value) {
                $disallowedValues = GeneralUtility::trimExplode(',', $value);
                $result['processedTca']['columns'][$field]['config']['items'] = array_filter(
                    $result['processedTca']['columns'][$field]['config']['items'],
                    static function ($item) use ($disallowedValues, $ctypeValueKey) {
                        return !in_array($item[$ctypeValueKey], $disallowedValues, true);
                    }
                );
            }
            $cTypeItemList = $result['processedTca']['columns']['CType']['config']['items'];
            // Remove the itemsProcFunc (set by EXT:t23_inline_container), because we have a fixed list of allowed CType items
            if (!empty($result['inlineParentConfig']['overrideChildTca']['columns']['CType']['config']['itemsProcFunc'])) {
                unset($result['inlineParentConfig']['overrideChildTca']['columns']['CType']['config']['itemsProcFunc']);
            }
            $availableCTypes = array_column($cTypeItemList, $ctypeValueKey);

            // Set the default CType value to the first allowed CType choice
            $result['pageTsConfig']['TCAdefaults.']['tt_content.']['CType'] = current($availableCTypes);
        }
        return $result;
    }
}