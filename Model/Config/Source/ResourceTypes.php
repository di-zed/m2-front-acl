<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Source for the customer resource types.
 */
class ResourceTypes extends AbstractSource
{
    /**
     * Value Role.
     */
    const VALUE_ROLE = 'role';

    /**
     * Value Permission.
     */
    const VALUE_PERMISSION = 'permission';

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
        return [
            [
                'value' => self::VALUE_ROLE,
                'label' => __('Role'),
            ],
            [
                'value' => self::VALUE_PERMISSION,
                'label' => __('Permission'),
            ],
        ];
    }
}
