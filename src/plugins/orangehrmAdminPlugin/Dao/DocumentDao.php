<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Admin\Dao;

use Exception;
use OrangeHRM\Admin\Dto\DocumentSearchFilterParams;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\Document;
use OrangeHRM\ORM\Paginator;

class DocumentDao extends BaseDao
{
    /**
     * @param Document $document
     * @return Document
     * @throws DaoException
     */
    public function saveDocument(Document $document): Document
    {
        try {
            $this->persist($document);
            return $document;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param DocumentSearchFilterParams $documentSearchFilterParams
     * @return array
     * @throws DaoException
     */
    public function getDocumentList(DocumentSearchFilterParams $documentSearchFilterParams): array
    {
        try {
            $paginator = $this->getDocumentListPaginator($documentSearchFilterParams);
            return $paginator->getQuery()->execute();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param DocumentSearchFilterParams $documentSearchFilterParams
     * @return Paginator
     */
    public function getDocumentListPaginator(
        DocumentSearchFilterParams $documentSearchFilterParams
    ): Paginator {
        $q = $this->createQueryBuilder(Document::class, 'd');
        $q->where('d.deleted = 0');
        $this->setSortingAndPaginationParams($q, $documentSearchFilterParams);
        return new Paginator($q);
    }

    /**
     * @param DocumentSearchFilterParams $documentSearchFilterParams
     * @return int
     * @throws DaoException
     */
    public function getDocumentCount(DocumentSearchFilterParams $documentSearchFilterParams): int
    {
        try {
            $paginator = $this->getDocumentListPaginator($documentSearchFilterParams);
            return $paginator->count();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param int $id
     * @return Document|null
     * @throws DaoException
     */
    public function getDocumentById(int $id): ?Document
    {
        try {
            $document = $this->getRepository(Document::class)->find($id);
            if ($document instanceof Document) {
                return $document;
            }
            return null;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $name
     * @return Document|null
     * @throws DaoException
     */
    public function getDocumentByName(string $name): ?Document
    {
        try {
            $query = $this->createQueryBuilder(Document::class, 'd');
            $trimmed = trim($name, ' ');
            $query->andWhere('d.name = :name');
            $query->setParameter('name', $trimmed);
            return $query->getQuery()->getOneOrNullResult();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array $toDeleteIds
     * @return int
     * @throws DaoException
     */
    public function deleteDocuments(array $toDeleteIds): int
    {
        $deleted=1;
        try {
            $q = $this->createQueryBuilder(Document::class, 'd');
            $q->update()
            ->set('d.deleted', ':deleted')
            ->setParameter('deleted',$deleted)
            ->where($q->expr()->in('d.id', ':ids'))
            ->setParameter('ids', $toDeleteIds);
        return $q->getQuery()->execute();
            
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $documentName
     * @return bool
     * @throws DaoException
     */
    public function isExistingDocumentName(string $documentName): bool
    {
        try {
            $q = $this->createQueryBuilder(Document::class, 'd');
            $trimmed = trim($documentName, ' ');
            $q->where('d.name = :name');
            $q->setParameter('name', $trimmed);
            $count = $this->count($q);
            if ($count > 0) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
