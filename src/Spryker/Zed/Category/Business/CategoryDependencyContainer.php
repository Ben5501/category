<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Category\Business;

use Spryker\Zed\Category\Business\Generator\UrlPathGenerator;
use Spryker\Zed\Category\Business\Tree\ClosureTableWriter;
use Spryker\Zed\Category\Business\Tree\NodeWriter;
use Spryker\Zed\Category\Business\Model\CategoryWriter;
use Spryker\Zed\Category\Business\Manager\NodeUrlManager;
use Spryker\Zed\Category\Business\Generator\UrlPathGeneratorInterface;
use Spryker\Zed\Category\Business\Model\CategoryWriterInterface;
use Spryker\Zed\Category\Business\Renderer\CategoryTreeRenderer;
use Spryker\Zed\Category\Business\Tree\CategoryTreeReader;
use Spryker\Zed\Category\Business\Tree\CategoryTreeWriter;
use Spryker\Zed\Category\Business\Tree\ClosureTableWriterInterface;
use Spryker\Zed\Category\Business\Tree\Formatter\CategoryTreeFormatter;
use Spryker\Zed\Category\Business\Tree\NodeWriterInterface;
use Spryker\Zed\Category\CategoryDependencyProvider;
use Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface;
use Spryker\Zed\Category\Dependency\Facade\CategoryToTouchInterface;
use Spryker\Zed\Category\Dependency\Facade\CategoryToUrlInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Category\Persistence\CategoryQueryContainer;

/**
 * @method CategoryQueryContainer getQueryContainer()
 */
class CategoryDependencyContainer extends AbstractBusinessFactory
{

    /**
     * @return CategoryTreeWriter
     */
    public function createCategoryTreeWriter()
    {
        return new CategoryTreeWriter(
            $this->createNodeWriter(),
            $this->createClosureTableWriter(),
            $this->createCategoryTreeReader(),
            $this->createNodeUrlManager(),
            $this->createTouchFacade(),
            $this->getProvidedDependency(CategoryDependencyProvider::PLUGIN_PROPEL_CONNECTION)
        );
    }

    /**
     * @param array $category
     *
     * @return CategoryTreeFormatter
     */
    public function createCategoryTreeStructure(array $category)
    {
        return new CategoryTreeFormatter($category);
    }

    /**
     * @return CategoryTreeReader
     */
    public function createCategoryTreeReader()
    {
        return new CategoryTreeReader(
            $this->getQueryContainer(),
            $this->createCategoryTreeFormatter()
        );
    }

    /**
     * @return CategoryTreeRenderer
     */
    public function createCategoryTreeRenderer()
    {
        $locale = $this->createLocaleFacade()->getCurrentLocale();

        return new CategoryTreeRenderer(
            $this->getQueryContainer(),
            $locale
        );
    }

    /**
     * @return CategoryWriterInterface
     */
    public function createCategoryWriter()
    {
        return new CategoryWriter(
            $this->getQueryContainer()
        );
    }

    /**
     * @return NodeWriterInterface
     */
    public function createNodeWriter()
    {
        return new NodeWriter(
            $this->getQueryContainer()
        );
    }

    /**
     * @return ClosureTableWriterInterface
     */
    protected function createClosureTableWriter()
    {
        return new ClosureTableWriter(
            $this->getQueryContainer()
        );
    }

    /**
     * @return NodeUrlManager
     */
    protected function createNodeUrlManager()
    {
        return new NodeUrlManager(
            $this->createCategoryTreeReader(),
            $this->createUrlPathGenerator(),
            $this->createUrlFacade()
        );
    }

    /**
     * @return UrlPathGeneratorInterface
     */
    public function createUrlPathGenerator()
    {
        return new UrlPathGenerator();
    }

    /**
     * @return CategoryToTouchInterface
     */
    protected function createTouchFacade()
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_TOUCH);
    }

    /**
     * @return CategoryToLocaleInterface
     */
    protected function createLocaleFacade()
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_LOCALE);
    }

    /**
     * @return CategoryToUrlInterface
     */
    protected function createUrlFacade()
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_URL);
    }

    /**
     * @return CategoryTreeFormatter
     */
    protected function createCategoryTreeFormatter()
    {
        return new CategoryTreeFormatter();
    }

    /**
     * @return TransferGeneratorInterface
     */
    public function createCategoryTransferGenerator()
    {
        return new TransferGenerator();
    }

}