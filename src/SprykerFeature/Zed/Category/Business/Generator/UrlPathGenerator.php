<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Category\Business\Generator;

/**
 * Class UrlGenerator
 */
class UrlPathGenerator implements UrlPathGeneratorInterface
{
    const CATEGORY_NAME = 'name';

    /**
     * @param array $categoryPath
     *
     * @return string
     */
    public function generate(array $categoryPath)
    {
        $formattedPath = [];

        foreach ($categoryPath as $category) {
            $categoryName = trim($category[self::CATEGORY_NAME]);

            if ('' !== $categoryName) {
                $formattedPath[] = strtolower(str_replace(' ', '-', trim($categoryName)));
            }
        }

        return '/' . implode('/', $formattedPath);
    }

}
