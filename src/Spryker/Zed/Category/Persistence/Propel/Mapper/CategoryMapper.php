<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryTemplateTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeCollectionTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Category\Persistence\SpyCategory;
use Orm\Zed\Category\Persistence\SpyCategoryNode;
use Orm\Zed\Category\Persistence\SpyCategoryTemplate;
use Propel\Runtime\Collection\ObjectCollection;

class CategoryMapper implements CategoryMapperInterface
{
    /**
     * @var \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryNodeMapper
     */
    protected $categoryNodeMapper;

    /**
     * @var \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryStoreRelationMapper
     */
    protected $categoryStoreRelationMapper;

    /**
     * @var \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryLocalizedAttributesUrlMapper
     */
    protected $categoryLocalizedAttributesUrlMapper;

    /**
     * @param \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryNodeMapper $categoryNodeMapper
     * @param \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryStoreRelationMapper $categoryStoreRelationMapper
     * @param \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryLocalizedAttributesUrlMapper $categoryLocalizedAttributesUrlMapper
     */
    public function __construct(
        CategoryNodeMapper $categoryNodeMapper,
        CategoryStoreRelationMapper $categoryStoreRelationMapper,
        CategoryLocalizedAttributesUrlMapper $categoryLocalizedAttributesUrlMapper
    ) {
        $this->categoryNodeMapper = $categoryNodeMapper;
        $this->categoryStoreRelationMapper = $categoryStoreRelationMapper;
        $this->categoryLocalizedAttributesUrlMapper = $categoryLocalizedAttributesUrlMapper;
    }

    /**
     * @param \Orm\Zed\Category\Persistence\SpyCategory $spyCategory
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    public function mapCategory(SpyCategory $spyCategory, CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        return $categoryTransfer->fromArray($spyCategory->toArray(), true);
    }

    /**
     * @param \Orm\Zed\Category\Persistence\SpyCategory $spyCategory
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    public function mapCategoryWithRelations(SpyCategory $spyCategory, CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        $categoryTransfer = $this->mapCategory($spyCategory, $categoryTransfer);
        $categoryTransfer = $this->mapParentCategoryNodes($spyCategory, $categoryTransfer);
        $categoryTransfer = $this->mapLocalizedAttributes($spyCategory->getAttributes(), $categoryTransfer);
        $categoryTransfer->setCategoryTemplate($this->mapCategoryTemplateEntityToCategoryTemplateTransfer(
            $spyCategory->getCategoryTemplate(),
            new CategoryTemplateTransfer(),
        ));
        $categoryTransfer = $this->categoryNodeMapper->mapCategoryNodes($spyCategory, $categoryTransfer);
        $storeRelationTransfer = $this->categoryStoreRelationMapper->mapCategoryStoreEntitiesToStoreRelationTransfer(
            $spyCategory->getSpyCategoryStores(),
            (new StoreRelationTransfer())->setIdEntity($spyCategory->getIdCategory()),
        );
        $categoryTransfer->setStoreRelation($storeRelationTransfer);

        return $categoryTransfer;
    }

    /**
     * @param array<\Orm\Zed\Category\Persistence\SpyCategoryNode> $categoryNodeEntities
     * @param \Generated\Shared\Transfer\NodeCollectionTransfer $nodeCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\NodeCollectionTransfer
     */
    public function mapCategoryNodeEntitiesToNodeCollectionTransfer(
        array $categoryNodeEntities,
        NodeCollectionTransfer $nodeCollectionTransfer
    ): NodeCollectionTransfer {
        foreach ($categoryNodeEntities as $categoryNodeEntity) {
            $nodeTransfer = $this->mapCategoryNodeEntityToNodeTransferWithCategoryRelation(
                $categoryNodeEntity,
                new NodeTransfer(),
            );

            $nodeCollectionTransfer->addNode($nodeTransfer);
        }

        return $nodeCollectionTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\Category\Persistence\SpyCategoryNode[] $nodeEntities
     * @param \Generated\Shared\Transfer\NodeCollectionTransfer $nodeCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\NodeCollectionTransfer
     */
    public function mapCategoryNodeEntitiesToNodeCollectionTransferWithCategoryRelation(
        ObjectCollection $nodeEntities,
        NodeCollectionTransfer $nodeCollectionTransfer
    ): NodeCollectionTransfer {
        foreach ($nodeEntities as $nodeEntity) {
            $nodeCollectionTransfer->addNode($this->mapCategoryNodeEntityToNodeTransferWithCategoryRelation($nodeEntity, new NodeTransfer()));
        }

        return $nodeCollectionTransfer;
    }

    /**
     * @param \Orm\Zed\Category\Persistence\SpyCategoryNode $nodeEntity
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     *
     * @return \Generated\Shared\Transfer\NodeTransfer
     */
    public function mapCategoryNodeEntityToNodeTransferWithCategoryRelation(SpyCategoryNode $nodeEntity, NodeTransfer $nodeTransfer): NodeTransfer
    {
        $nodeTransfer = $this->categoryNodeMapper->mapCategoryNode($nodeEntity, $nodeTransfer);
        $categoryEntity = $nodeEntity->getCategory();

        $categoryTransfer = $this->mapCategory($categoryEntity, new CategoryTransfer());
        $categoryTransfer = $this->mapLocalizedAttributes($categoryEntity->getAttributesJoinLocale(), $categoryTransfer, $nodeEntity->getSpyUrls());
        $storeRelationTransfer = $this->categoryStoreRelationMapper->mapCategoryStoreEntitiesToStoreRelationTransfer(
            $categoryEntity->getSpyCategoryStores(),
            (new StoreRelationTransfer())->setIdEntity($categoryEntity->getIdCategory()),
        );
        $categoryTransfer->setStoreRelation($storeRelationTransfer);

        $categoryTemplateTransfer = $this->mapCategoryTemplateEntityToCategoryTemplateTransfer(
            $categoryEntity->getCategoryTemplate(),
            new CategoryTemplateTransfer(),
        );
        $categoryTransfer->setCategoryTemplate($categoryTemplateTransfer);

        return $nodeTransfer->setCategory($categoryTransfer);
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\Category\Persistence\SpyCategory[] $categoryEntities
     * @param \Generated\Shared\Transfer\CategoryCollectionTransfer $categoryCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function mapCategoryCollection(
        ObjectCollection $categoryEntities,
        CategoryCollectionTransfer $categoryCollectionTransfer
    ): CategoryCollectionTransfer {
        foreach ($categoryEntities as $categoryEntity) {
            $categoryTransfer = $this->mapCategory($categoryEntity, new CategoryTransfer());
            $categoryTransfer = $this->mapLocalizedAttributes($categoryEntity->getAttributes(), $categoryTransfer);

            foreach ($categoryTransfer->getLocalizedAttributes() as $localizedAttribute) {
                $categoryTransfer->fromArray($localizedAttribute->toArray(), true);
            }

            $nodeCollectionTransfer = $this->categoryNodeMapper->mapNodeCollection(
                $categoryEntity->getNodes(),
                new NodeCollectionTransfer(),
            );
            $categoryTransfer->setNodeCollection($nodeCollectionTransfer);

            $storeRelationTransfer = $this->categoryStoreRelationMapper->mapCategoryStoreEntitiesToStoreRelationTransfer(
                $categoryEntity->getSpyCategoryStores(),
                (new StoreRelationTransfer())->setIdEntity($categoryEntity->getIdCategory()),
            );
            $categoryTransfer->setStoreRelation($storeRelationTransfer);

            $categoryCollectionTransfer->addCategory($categoryTransfer);
        }

        return $categoryCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Orm\Zed\Category\Persistence\SpyCategory $categoryEntity
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategory
     */
    public function mapCategoryTransferToCategoryEntity(CategoryTransfer $categoryTransfer, SpyCategory $categoryEntity): SpyCategory
    {
        $categoryEntity->fromArray($categoryTransfer->modifiedToArray());

        return $categoryEntity;
    }

    /**
     * @param \Orm\Zed\Category\Persistence\SpyCategory $categoryEntity
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    protected function mapParentCategoryNodes(SpyCategory $categoryEntity, CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        foreach ($categoryEntity->getNodes() as $categoryNodeEntity) {
            $parentCategoryNodeEntity = $categoryNodeEntity->getParentCategoryNode();

            if ($parentCategoryNodeEntity === null) {
                continue;
            }

            if ($categoryNodeEntity->isMain()) {
                $categoryTransfer->setParentCategoryNode($this->categoryNodeMapper->mapCategoryNode($parentCategoryNodeEntity, new NodeTransfer()));

                continue;
            }

            $categoryTransfer->addExtraParent($this->categoryNodeMapper->mapCategoryNode($parentCategoryNodeEntity, new NodeTransfer()));
        }

        return $categoryTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\Category\Persistence\SpyCategoryAttribute[] $attributeCollection
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\Url\Persistence\SpyUrl[]|null $urlEntities
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    protected function mapLocalizedAttributes(
        ObjectCollection $attributeCollection,
        CategoryTransfer $categoryTransfer,
        ?ObjectCollection $urlEntities = null
    ): CategoryTransfer {
        foreach ($attributeCollection as $attribute) {
            $localeTransfer = new LocaleTransfer();
            $localeTransfer->fromArray($attribute->getLocale()->toArray(), true);

            $categoryLocalizedAttributesTransfer = new CategoryLocalizedAttributesTransfer();
            $categoryLocalizedAttributesTransfer->fromArray($attribute->toArray(), true);
            $categoryLocalizedAttributesTransfer->setLocale($localeTransfer);

            if ($urlEntities) {
                $categoryLocalizedAttributesTransfer = $this->categoryLocalizedAttributesUrlMapper->mapUrlEntitiesToCategoryLocalizedAttributesTransfer(
                    $urlEntities,
                    $categoryLocalizedAttributesTransfer,
                );
            }

            $categoryTransfer->addLocalizedAttributes($categoryLocalizedAttributesTransfer);
        }

        return $categoryTransfer;
    }

    /**
     * @param \Orm\Zed\Category\Persistence\SpyCategoryTemplate $categoryTemplateEntity
     * @param \Generated\Shared\Transfer\CategoryTemplateTransfer $categoryTemplateTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryTemplateTransfer
     */
    protected function mapCategoryTemplateEntityToCategoryTemplateTransfer(
        SpyCategoryTemplate $categoryTemplateEntity,
        CategoryTemplateTransfer $categoryTemplateTransfer
    ): CategoryTemplateTransfer {
        return $categoryTemplateTransfer->fromArray($categoryTemplateEntity->toArray(), true);
    }
}
