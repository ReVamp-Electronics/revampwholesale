<?php
namespace Evdpl\Ourteam\Controller;

/**
 * Cms Controller Router
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Post factory
     *
     * @var \Evdpl\Ourteam\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Evdpl\Ourteam\Model\PostFactory $postFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Evdpl\Ourteam\Model\PostFactory $postFactory,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_pageFactory = $postFactory;
        $this->_response = $response;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $designation = trim($request->getPathInfo(), '/ourteam/');
        $designation = rtrim($designation, '/');

        /** @var \Evdpl\Ourteam\Model\Post $post */
        $post = $this->_pageFactory->create();
        $post_id = $post->checkUrlKey($designation);
        if (!$post_id) {
            return null;
        }

        $request->setModuleName('ourteam')->setControllerName('view')->setActionName('index')->setParam('post_id', $post_id);
        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $designation);

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }
}
