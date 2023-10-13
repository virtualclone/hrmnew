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

use OrangeHRM\Admin\Dao\PayrollConfigurationDao;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\PayrollConfiguration;

class PayrollConfigurationService
{
    /**
     * @var PayrollConfigurationDao|null
     */
    private ?PayrollConfigurationDao $payrollconfigurationDao = null;

    /**
     * @return PayrollConfigurationDao
     */
    public function getPayrollConfigurationDao(): PayrollConfigurationDao
    {
        if (is_null($this->payrollconfigurationDao)) {
            $this->payrollconfigurationDao = new PayrollConfigurationDao();
        }
        return $this->payrollconfigurationDao;
    }

    /**
     * @param PayrollConfigurationDao $payrollconfigurationDao
     */
    public function setPayrollConfigurationDao(PayrollConfigurationDao $payrollconfigurationDao): void
    {
        $this->payrollconfigurationDao = $payrollconfigurationDao;
    }

    /**
     * Get payrollconfiguration general information
     *
     * @return PayrollConfiguration|null
     * @throws DaoException
     */
    public function getPayrollConfiguration(): ?PayrollConfiguration
    {
        return $this->getPayrollConfigurationDao()->getPayrollConfiguration();
    }

    /**
     * @param PayrollConfiguration $payrollconfiguration
     * @return PayrollConfiguration
     * @throws DaoException
     */
    public function savePayrollConfiguration(PayrollConfiguration $payrollconfiguration): PayrollConfiguration
    {
        return $this->getPayrollConfigurationDao()->savePayrollConfiguration($payrollconfiguration);
    }
}
