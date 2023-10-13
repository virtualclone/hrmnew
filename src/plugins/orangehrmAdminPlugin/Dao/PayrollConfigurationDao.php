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
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\PayrollConfiguration;
use OrangeHRM\ORM\Exception\TransactionException;

class PayrollConfigurationDao extends BaseDao
{
    /**
     * @return PayrollConfiguration|null
     * @throws DaoException
     */
    public function getPayrollConfiguration(): ?PayrollConfiguration
    {
        try {
            $orgInfo = $this->getRepository(PayrollConfiguration::class)->find(1);
            if ($orgInfo instanceof PayrollConfiguration) {
                return $orgInfo;
            }
            return null;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param PayrollConfiguration $payrollconfiguration
     * @return PayrollConfiguration
     * @throws TransactionException
     */
    public function savePayrollConfiguration(PayrollConfiguration $payrollconfiguration): PayrollConfiguration
    {
        $this->beginTransaction();
        try {
            $this->persist($payrollconfiguration);
            $this->updatePayrollConfiguration($payrollconfiguration);
            $this->commitTransaction();
            return $payrollconfiguration;
        } catch (Exception $exception) {
            $this->rollBackTransaction();
            throw new TransactionException($exception);
        }
    }

    /**
     * @param PayrollConfiguration $payrollconfiguration
     * @return void
     */
    private function updatePayrollConfiguration(PayrollConfiguration $payrollconfiguration): void
    {
        //$baseUnit = $this->getRepository(Subunit::class)->findOneBy(['level' => 0]);
        /** @var Subunit $baseUnit */
        //$baseUnit->setName($payrollconfiguration->getName());
        //$this->persist($baseUnit);
    }
}
