<?php
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Translation\TranslatorInterface;

class ProjectAreaRepository implements EntityTransformerInterface
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * Active project
	 * @var Project
	 */
	private $project;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	public function setActiveProject(Project $project)
	{
		$this->project = $project;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->column('territory', 't.name')
			->searchableColumn('groupName', 'i.groupName')
			->searchableColumn('status', 's.id')
			->column('memberNum', 'i.memberNum');
		return $dt;
	}
	
	public function listData(DataTable $dataTable, TranslatorInterface $translator)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('i.groupName', 'groupName')
			->field('s.id', 'status')
			->field('s.name', 'statusName')
			->field('s.label', 'statusLabel')
			->field('t.name', 'territory')
			->field('i.memberNum', 'memberNum')
			->from(CoreTables::AREA_TBL, 'i')
			->join(CoreTables::TERRITORY_TBL, 't', QueryClause::clause('i.territoryId = t.id'))
			->join(CoreTables::AREA_STATUS_TBL, 's', QueryClause::clause('i.statusId = s.id'))
			->where(QueryClause::clause('i.projectId = :projectId', ':projectId', $this->project->getId()));	
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);

		$qb->postprocess(function($row) use($translator) {
			$row['statusName'] = $translator->trans($row['statusName'], [], 'statuses');
			return $row;
		});
		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	/**
	 * @return Group
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		try {
			$item = Area::fetchByProject($this->conn, $id, $this->project);
			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			return $item;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function findMembers(Area $area)
	{
		$items = $this->conn->fetchAll('SELECT u.name, u.avatar, p.location, p.telephone, p.publicMail, p.privShowTelephone, p.privShowPublicMail, m.note '
			. 'FROM `'.CoreTables::USER_TBL.'` u '
			. 'INNER JOIN `'.CoreTables::USER_PROFILE_TBL.'` p ON p.`userId` = u.`id` '
			. 'INNER JOIN `'.CoreTables::AREA_MEMBER_TBL.'` m ON m.`userId` = u.`id` '
			. 'WHERE m.`areaId` = :areaId ORDER BY m.`role` DESC, u.`name`', [':areaId' => $area->getId()]);
		
		foreach ($items as &$item) {
			$item['publicMail'] = (User::evaluateUserPrivacy($item['privShowPublicMail'], $this->project) ? $item['publicMail'] : '');
			$item['telephone'] = (User::evaluateUserPrivacy($item['privShowTelephone'], $this->project) ? $item['telephone'] : '');
		}
		return $items;
	}
	
	public function insert(Area $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function update(Area $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->update($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function remove(Area $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->remove($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::AREA_TBL.'` ORDER BY `name`');
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[$row['id']] = $row['name'];
		}
		$stmt->closeCursor();
		return $result;
	}

	public function transformToEntity($key)
	{
		return $this->getItem($key);
	}

	public function transformToKey($entity)
	{
		return $entity->getId();
	}
}