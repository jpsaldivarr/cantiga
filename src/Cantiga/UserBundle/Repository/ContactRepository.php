<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\UserBundle\Repository;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\Metamodel\Transaction;
use Cantiga\UserBundle\Entity\ContactData;
use Doctrine\DBAL\Connection;

/**
 * Methods for managing the profile of the user that is logged in.
 */
class ContactRepository
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	/**
	 * Finds the contact data for the given project. Empty entity is returned, if the user has not configured
	 * any contact data for this project yet.
	 * 
	 * @param \Cantiga\Components\Hierarchy\HierarchicalInterface $project
	 * @param \Cantiga\UserBundle\Repository\CantigaUserRefInterface $user
	 * @return Contact data entity
	 */
	public function findContactData(HierarchicalInterface $project, CantigaUserRefInterface $user): ContactData
	{
		$this->transaction->requestTransaction();
		try {
			return ContactData::findContactData($this->conn, $project, $user);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
		}
	}
	
	public function persistContactData(ContactData $contactData)
	{
		$this->transaction->requestTransaction();
		try {
			$contactData->update($this->conn);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
		}
	}
}
