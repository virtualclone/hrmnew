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

namespace OrangeHRM\Leave\Service;
use DateTime;
use OrangeHRM\Leave\Dao\CompensatoryOffDao;
use InvalidArgumentException;
use OrangeHRM\Leave\Service\Model\CompensatoryOffModel;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Core\Traits\Service\NormalizerServiceTrait;
use OrangeHRM\Entity\CompensatoryOff;

class CompensatoryOffService
{
    use NormalizerServiceTrait;

    /**
     * @var CompensatoryOffDao|null
     */
    private ?CompensatoryOffDao $compOffDao = null;

     /**
     * @var array|null
     * array(
     *     2 => 'REJECTED',
     *     0 => 'CANCELLED',
     *     0 => 'PENDING APPROVAL',
     *     1 => 'APPROVED',
     *     3 => 'TAKEN',
     *     4 => 'WEEKEND',
     *     5 => 'HOLIDAY'
     * )
     */
    private ?array $compoffStatuses = null;

    /**
     * @return CompensatoryOffDao|null
     */
    public function getCompensatoryOffDao(): CompensatoryOffDao
    {
        if (!$this->compOffDao instanceof CompensatoryOffDao) {
            $this->compOffDao = new CompensatoryOffDao();
        }
        return $this->compOffDao;
    }

    /**
     * @param CompensatoryOffDao $compOffDao
     */
    public function setCompensatoryOffDao(CompensatoryOffDao $compOffDao): void
    {
        $this->compOffDao = $compOffDao;
    }

    // /**
    //  * @param DocumentSearchFilterParams $documentSearchParamHolder
    //  * @return array
    //  * @throws DaoException
    //  */
    // public function getDocumentList(DocumentSearchFilterParams $documentSearchParamHolder): array
    // {
    //     return $this->getDocumentDao()->getDocumentList($documentSearchParamHolder);
    // }

    

    /**
     * @param CompensatoryOff $compOff
     * @return CompensatoryOff
     * @throws DaoException
     */
    public function saveCompensatoryOff(CompensatoryOff $compOff): CompensatoryOff
    {
        return $this->getCompensatoryOffDao()->saveCompensatoryOff($compOff);
    }

    // /**
    //  * @param int $id
    //  * @return Document|null
    //  * @throws DaoException
    //  */
    // public function getDocumentById(int $id): ?Document
    // {
    //     return $this->getCompensatoryOffDao()->getDocumentById($id);
    // }

    // /**
    //  * @param string $name
    //  * @return Document|null
    //  * @throws DaoException
    //  */
    // public function getDocumentByName(string $name): ?Document
    // {
    //     return $this->getCompensatoryOffDao()->getDocumentByName($name);
    // }

    // /**
    //  * @param array $toDeleteIds
    //  * @return int
    //  * @throws DaoException
    //  */
    // public function deleteDocuments(array $toDeleteIds): int
    // {
    //     return $this->getCompensatoryOffDao()->deleteDocuments($toDeleteIds);
    // }

    /**
     * @param DateTime $comOffDate
     * @param int $comOffempNumber
     * @param string $durationLabel
     * @return CompensatoryOff|null
     * @throws DaoException
     */
    public function isExistingCompOff(DateTime $comOffDate,int $comOffempNumber, string $durationLabel): ?CompensatoryOff
    {
        
        return $this->getCompensatoryOffDao()->isExistingCompOff($comOffDate,$comOffempNumber,$durationLabel);
    }

      /**
     * @param int  $compoffRequestId
     * @param string actionType 
     * @return int
     * @throws DaoException
     */
    public function compoffStatusUpdate(int $compoffRequestId,string $actionType): int
    {        
      return $this->getCompensatoryOffDao()->compoffStatusUpdate($compoffRequestId,$actionType);
    }

    /**
     * @return array<int, string>
     */
    public function getAllCompoffStatusesAssoc(): ?array
    {
        if (is_null($this->compoffStatuses)) {
            foreach ($this->getCompensatoryOffDao()->getAllCompoffStatuses() as $status) {
                $this->compoffStatuses[$status->getStatus()] = $status->getName();
            }
        }
        return $this->compoffStatuses;
    }

    /**
     * @param string[] $names e.g. ['REJECTED', 'PENDING APPROVAL', 'TAKEN']
     * @return int[] e.g. [-1, 1, 3]
     */
    public function getLeaveStatusesByNames(array $names): array
    {
        $leaveStatuses = array_flip($this->getAllCompoffStatusesAssoc());
        return array_map(function (string $name) use ($leaveStatuses) {
            if (isset($leaveStatuses[$name])) {
                return $leaveStatuses[$name];
            }
            throw new InvalidArgumentException("Invalid status name $name");
        }, $names);
    }

    
    // /**
    //  * @return array
    //  * @throws DaoException
    //  */
    // public function getDocumentArray(): array
    // {
    //     $documentSearchParamHolder = new DocumentSearchFilterParams();
    //     $documentSearchParamHolder->setLimit(0);
    //     $documents = $this->getDocumentList($documentSearchParamHolder);
    //     return $this->getNormalizerService()->normalizeArray(DocumentModel::class, $documents);
    // }
}
