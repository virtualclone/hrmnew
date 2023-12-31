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

namespace OrangeHRM\Leave\Dto;

use InvalidArgumentException;
use OrangeHRM\Entity\CompensatoryOff;
use OrangeHRM\ORM\ListSorter;

class CompOffSearchFilterParams extends DateRangeSearchFilterParams
{
    

    public const ALLOWED_SORT_FIELDS = ['compoffRequest.date'];

    public const INCLUDE_EMPLOYEES_ONLY_CURRENT = 'onlyCurrent';
    public const INCLUDE_EMPLOYEES_ONLY_PAST = 'onlyPast';
    public const INCLUDE_EMPLOYEES_CURRENT_AND_PAST = 'currentAndPast';

    public const INCLUDE_EMPLOYEES = [
        self::INCLUDE_EMPLOYEES_ONLY_CURRENT,
        self::INCLUDE_EMPLOYEES_ONLY_PAST,
        self::INCLUDE_EMPLOYEES_CURRENT_AND_PAST,
    ];

    public const COMPOFF_STATUSES = [
        CompensatoryOff::LEAVE_STATUS_COMPOFF_REJECTED,
        CompensatoryOff::LEAVE_STATUS_COMPOFF_PENDING_APPROVAL,
        CompensatoryOff::LEAVE_STATUS_COMPOFF_APPROVED,
        CompensatoryOff::LEAVE_STATUS_COMPOFF_TAKEN,
        CompensatoryOff::LEAVE_STATUS_COMPOFF_EXPIRED,
    ];

    /**
     * @var int|null
     */
    private ?int $empNumber = null;

    // /**
    //  * @var int[]|null
    //  */
    // protected ?array $empNumbers = null;

    /**
     * @var string|null
     */
    private ?string $includeEmployees = self::INCLUDE_EMPLOYEES_ONLY_CURRENT;

    public function __construct()
    {
        $this->setSortField('compoffRequest.date');
        $this->setSortOrder(ListSorter::DESCENDING);
    }

    /**
     * @return int|null
     */
    public function getEmpNumber(): ?int
    {
        return $this->empNumber;
    }

    /**
     * @param int|null $empNumber
     */
    public function setEmpNumber(?int $empNumber): void
    {
        $this->empNumber = $empNumber;
    }

    // /**
    //  * @return int[]|null
    //  */
    // public function getEmpNumbers(): ?array
    // {
    //     return $this->empNumbers;
    // }

    // /**
    //  * @param int[]|null $empNumbers
    //  */
    // public function setEmpNumbers(?array $empNumbers): void
    // {
    //     $this->empNumbers = $empNumbers;
    // }


    /**
     * @return string|null
     */
    public function getIncludeEmployees(): ?string
    {
        return $this->includeEmployees;
    }

    /**
     * @param string|null $includeEmployees
     */
    public function setIncludeEmployees(?string $includeEmployees): void
    {
        $this->includeEmployees = $includeEmployees;
    }
}
