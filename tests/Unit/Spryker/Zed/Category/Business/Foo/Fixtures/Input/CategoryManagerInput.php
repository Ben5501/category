<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Unit\Spryker\Zed\Category\Business\Foo\Fixtures\Input;

class CategoryManagerInput
{
    /**
     * @return array
     */
    public function getCategoryData()
    {
        return [
            'de_DE' => [
                'category_key' => 'CATEGORY_KEY',
                'is_active' => true,
                'is_in_menu' => true,
                'is_clickable' => true,
                'name' => 'Foo DE',
                'url' => 'MAKEME:/de/foo-de',
                'meta_title' => 'foo DE title',
                'meta_keywords' => 'foo DE meta',
                'category_image_name' => 'foo DE image',
            ],
            'en_US' => [
                'category_key' => 'CATEGORY_KEY',
                'is_active' => true,
                'is_in_menu' => true,
                'is_clickable' => true,
                'name' => 'Foo EN',
                'url' => 'MAKEME:/en/foo-en',
                'meta_title' => 'foo EN title',
                'meta_keywords' => 'foo EN meta',
                'category_image_name' => 'foo EN image',
            ]
        ];
    }

    /**
     * @return array
     */
    public function getCategoryData2()
    {
        return [
            'category_key' => 'CATEGORY_KEY',
            'is_active' => true,
            'is_in_menu' => true,
            'is_clickable' => true,
            'localized_attributes' => [
                'de_DE' => [
                    'name' => 'Foo DE',
                    'url' => 'MAKEME:/de/foo-de',
                    'meta_title' => 'foo DE title',
                    'meta_keywords' => 'foo DE meta',
                    'category_image_name' => 'foo DE image',
                ],
                'en_US' => [
                    'name' => 'Foo EN',
                    'url' => 'MAKEME:/en/foo-en',
                    'meta_title' => 'foo EN title',
                    'meta_keywords' => 'foo EN meta',
                    'category_image_name' => 'foo EN image',
                ]
            ]
        ];
    }

}