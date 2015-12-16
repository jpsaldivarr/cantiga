<?php
namespace Cantiga\LinksBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\LinksBundle\Entity\Link;
use Cantiga\LinksBundle\Form\LinkForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/links")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminLinksController extends AdminPageController
{
	const REPOSITORY_NAME = 'cantiga.links.repo.links';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaLinksBundle:Links:')
			->setItemNameProperty('name')
			->setPageTitle('Important links')
			->setPageSubtitle('Manage the links displayed on dashboards')
			->setIndexPage('admin_links_index')
			->setInfoPage('admin_links_info')
			->setInsertPage('admin_links_insert')
			->setEditPage('admin_links_edit')
			->setRemovePage('admin_links_remove')
			->setItemCreatedMessage('The link \'0\' has been created.')
			->setItemUpdatedMessage('The link \'0\' has been updated.')
			->setItemRemovedMessage('The link \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the link \'0\'?');
		
		$this->breadcrumbs()
			->workgroup('settings')
			->entryLink($this->trans('Important links', [], 'pages'), $this->crudInfo->getIndexPage());
	}
		
	/**
	 * @Route("/index", name="admin_links_index")
	 */
	public function indexAction(Request $request)
	{
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'ajaxListPage' => 'admin_links_ajax_list',
			'insertPage' => $this->crudInfo->getInsertPage()
		));
	}
	
	/**
	 * @Route("/ajax-list", name="admin_links_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id'])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id'])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id']);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable, $this->getTranslator())));
	}
	
	/**
	 * @Route("/{id}/info", name="admin_links_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id);
	}
	 
	/**
	 * @Route("/insert", name="admin_links_insert")
	 */
	public function insertAction(Request $request)
	{
		$entity = new Link();	
		$action = new InsertAction($this->crudInfo, $entity, new LinkForm(LinkForm::GENERAL));
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="admin_links_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new LinkForm(LinkForm::GENERAL));
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="admin_links_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
	}
}