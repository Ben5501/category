<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Category\Communication\Controller;

use Spryker\Zed\Application\Communication\Controller\AbstractController;
use Spryker\Zed\Category\Business\CategoryFacade;
use Spryker\Zed\Category\Communication\CategoryDependencyContainer;
use Spryker\Zed\Category\Communication\Table\CategoryAttributeTable;
use Spryker\Zed\Category\Persistence\CategoryQueryContainer;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @method CategoryFacade getFacade()
 * @method CategoryDependencyContainer getCommunicationFactory()
 * @method CategoryQueryContainer getQueryContainer()
 */
class IndexController extends AbstractController
{

    const PARAM_ID_CATEGORY_NODE = 'id-category-node';

    /**
     * @return array
     */
    public function indexAction()
    {
        $rootCategories = $this->getCommunicationFactory()
            ->createRootNodeTable();

        return $this->viewResponse([
            'rootCategories' => $rootCategories->render(),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function rootNodeTableAction()
    {
        $table = $this->getCommunicationFactory()
            ->createRootNodeTable();

        return $this->jsonResponse(
            $table->fetchData()
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function nodeAction(Request $request)
    {
        $idCategoryNode = $request->get(self::PARAM_ID_CATEGORY_NODE);

        $categories = $this->getCategoryChildrenByIdCategory($idCategoryNode);

        return $this->viewResponse([
            'code' => Response::HTTP_OK,
            'categories' => $categories,
            'idCategoryNode' => $idCategoryNode,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function nodeByNameAction(Request $request)
    {
        $categoryName = $request->request->get('category-name');

        $idCategory = $this->getFacade()->getCategoryNodeIdentifier(
            trim($categoryName),
            $this->getCommunicationFactory()->createCurrentLocale()
        );

        $children = $this->getCategoryChildrenByIdCategory($idCategory);

        return $this->jsonResponse([
            'code' => Response::HTTP_OK,
            'data' => $children,
            'idCategoryNode' => $idCategory,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function attributesAction(Request $request)
    {
        $idCategory = $request->get(self::PARAM_ID_CATEGORY_NODE);

        /** @var CategoryAttributeTable $table */
        $table = $this->getCommunicationFactory()
            ->createCategoryAttributeTable($idCategory);

        $tableData = $this->getTableArrayFormat($table);

        return $this->viewResponse($tableData);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function urlsAction(Request $request)
    {
        $idCategory = $request->get(self::PARAM_ID_CATEGORY_NODE);

        $table = $this->getCommunicationFactory()
            ->createUrlTable($idCategory);

        $tableData = $this->getTableArrayFormat($table);

        return $this->viewResponse($tableData);
    }

    /**
     * @param Request $request
     *
     * @return void
     */
    public function rebuildClosureTableAction(Request $request)
    {
        $this->getFacade()
            ->rebuildClosureTable();

        exit('<br/>Done');
    }

    /**
     * @param AbstractTable $table
     *
     * @return array
     */
    protected function getTableArrayFormat(AbstractTable $table)
    {
        $tableData = [
            'table' => $table->fetchData(),
        ];
        $tableData['table']['header'] = $table->getConfiguration()->getHeader();

        return $tableData;
    }

    /**
     * @param int $idCategory
     *
     * @return array
     */
    protected function getCategoryChildrenByIdCategory($idCategory)
    {
        return $this->getFacade()
            ->getTreeNodeChildrenByIdCategoryAndLocale(
                $idCategory,
                $this->getCommunicationFactory()->createCurrentLocale()
            );
    }

}