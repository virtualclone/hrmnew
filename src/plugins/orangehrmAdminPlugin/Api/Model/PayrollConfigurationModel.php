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

namespace OrangeHRM\Admin\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\PayrollConfiguration;

/**
 * @OA\Schema(
 *     schema="Admin-PayrollConfigurationModel",
 *     type="object",
 *     @OA\Property(property="pf_employee", type="integer"),
 *     @OA\Property(property="pf_employer", type="integer"),
 *     @OA\Property(property="esic_employee", type="float"),
 *     @OA\Property(property="esic_employer", type="float"),
 *     @OA\Property(property="gratuity", type="float"),
 *     @OA\Property(property="medical", type="float"),
 *     @OA\Property(property="eps_contri", type="float"),
 *     @OA\Property(property="epsepf_contri", type="float"),
 *     @OA\Property(property="tds_dedu", type="float"), *     
 * )
 */
class PayrollConfigurationModel implements Normalizable
{
    use ModelTrait;

    public function __construct(PayrollConfiguration $payrollConfiguration)
    {
        $this->setEntity($payrollConfiguration);
        $this->setFilters(
            [
                'pfEmployee',
                'pfEmployer',
                'esicEmployee',
                'esicEmployer',
                'gratuity',
                'medical',
                'epsContri',
                'epsepfContri',
                'tdsDedu',        
            ]
        );
    }
}
