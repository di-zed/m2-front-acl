<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Acl\AclResource\ProviderInterface;

/**
 * Source for the customer roles.
 */
class Roles extends AbstractSource
{
    /**
     * @var string
     */
    public static $resourceId = 'FrontAcl_Role::index';

    /**
     * @var ProviderInterface
     */
    protected $aclResourceProvider;

    /**
     * Source constructor.
     *
     * @param ProviderInterface $aclResourceProvider
     */
    public function __construct(
        ProviderInterface $aclResourceProvider
    ) {
        $this->aclResourceProvider = $aclResourceProvider;
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        if (!$this->_options) {
            $this->_options = array_merge([
                [
                    'value' => '',
                    'label' => __('Not selected'),
                ],
            ], $this->toOptionArray());
        }

        return $this->_options;
    }

    /**
     * @inheritDoc
     */
    public function getOptionText($value): string
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->getAclResources() as $aclResource) {
            $options = array_merge($options, $this->prepareOption($aclResource));
        }

        // sorting by alphabet:
        usort($options, function ($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $options;
    }

    /**
     * Get list of all ACL resources declared in the system.
     *
     * @return array
     */
    public function getAclResources(): array
    {
        $resources = $this->aclResourceProvider->getAclResources();

        $configResource = array_filter($resources, function ($node) {
            return $node['id'] == $this::$resourceId;
        });
        $configResource = reset($configResource);

        return (!empty($configResource['children']) ? $configResource['children'] : []);
    }

    /**
     * Preparing option for array.
     *
     * @param array $aclResource
     * @param string $labelPrefix
     * @return array
     */
    protected function prepareOption(array $aclResource, string $labelPrefix = ''): array
    {
        $optionLabel = (($labelPrefix ? ($labelPrefix . ' -> ') : '') . $aclResource['title']);

        $options[] = [
            'value' => $aclResource['id'],
            'label' => $optionLabel,
        ];

        if (!empty($aclResource['children']) && is_array($aclResource['children'])) {
            foreach ($aclResource['children'] as $childResource) {
                $options = array_merge($options, $this->prepareOption($childResource, $optionLabel));
            }
        }

        return $options;
    }
}
