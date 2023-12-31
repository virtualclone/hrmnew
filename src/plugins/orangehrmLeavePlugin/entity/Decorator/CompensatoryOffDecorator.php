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

namespace OrangeHRM\Entity\Decorator;

use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\CompensatoryOff;

class CompensatoryOffDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;

    /**
     * @var CompensatoryOff
     */
    private CompensatoryOff $compOff;

    /**
     * @param CompensatoryOff $compOff
     */
    public function __construct(CompensatoryOff $compOff)
    {
        $this->compOff = $compOff;
    }

    /**
     * @return CompensatoryOff
     */
    protected function getCompOffRequest(): CompensatoryOff
    {
        return $this->compOff;
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getCompOffRequest()->setEmployee($employee);
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->getDateTimeHelper()->formatDate($this->getCompOffRequest()->getDate());
    }

    
}
