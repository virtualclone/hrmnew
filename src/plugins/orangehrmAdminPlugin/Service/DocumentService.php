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

namespace OrangeHRM\Admin\Service;

use OrangeHRM\Admin\Dao\DocumentDao;
use OrangeHRM\Admin\Dto\DocumentSearchFilterParams;
use OrangeHRM\Admin\Service\Model\DocumentModel;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Core\Traits\Service\NormalizerServiceTrait;
use OrangeHRM\Entity\Document;

class DocumentService
{
    use NormalizerServiceTrait;

    /**
     * @var DocumentDao|null
     */
    private ?DocumentDao $documentDao = null;

    /**
     * @return DocumentDao
     */
    public function getDocumentDao(): DocumentDao
    {
        if (!$this->documentDao instanceof DocumentDao) {
            $this->documentDao = new DocumentDao();
        }
        return $this->documentDao;
    }

    /**
     * @param DocumentDao $documentDao
     */
    public function setDocumentDao(DocumentDao $documentDao): void
    {
        $this->documentDao = $documentDao;
    }

    /**
     * @param DocumentSearchFilterParams $documentSearchParamHolder
     * @return array
     * @throws DaoException
     */
    public function getDocumentList(DocumentSearchFilterParams $documentSearchParamHolder): array
    {
        return $this->getDocumentDao()->getDocumentList($documentSearchParamHolder);
    }

    /**
     * @param DocumentSearchFilterParams $documentSearchParamHolder
     * @return int
     * @throws DaoException
     */
    public function getDocumentCount(DocumentSearchFilterParams $documentSearchParamHolder): int
    {
        return $this->getDocumentDao()->getDocumentCount($documentSearchParamHolder);
    }

    /**
     * @param Document $document
     * @return Document
     * @throws DaoException
     */
    public function saveDocument(Document $document): Document
    {
        return $this->getDocumentDao()->saveDocument($document);
    }

    /**
     * @param int $id
     * @return Document|null
     * @throws DaoException
     */
    public function getDocumentById(int $id): ?Document
    {
        return $this->getDocumentDao()->getDocumentById($id);
    }

    /**
     * @param string $name
     * @return Document|null
     * @throws DaoException
     */
    public function getDocumentByName(string $name): ?Document
    {
        return $this->getDocumentDao()->getDocumentByName($name);
    }

    /**
     * @param array $toDeleteIds
     * @return int
     * @throws DaoException
     */
    public function deleteDocuments(array $toDeleteIds): int
    {
        return $this->getDocumentDao()->deleteDocuments($toDeleteIds);
    }

    /**
     * @param string $documentName
     * @return bool
     * @throws DaoException
     */
    public function isExistingDocumentName(string $documentName): bool
    {
        return $this->getDocumentDao()->isExistingDocumentName($documentName);
    }

    /**
     * @return array
     * @throws DaoException
     */
    public function getDocumentArray(): array
    {
        $documentSearchParamHolder = new DocumentSearchFilterParams();
        $documentSearchParamHolder->setLimit(0);
        $documents = $this->getDocumentList($documentSearchParamHolder);
        return $this->getNormalizerService()->normalizeArray(DocumentModel::class, $documents);
    }
}
