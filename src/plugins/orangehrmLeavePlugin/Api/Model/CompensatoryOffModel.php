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

namespace OrangeHRM\Leave\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\CompensatoryOff;

class CompensatoryOffModel implements Normalizable
{
    use ModelTrait;

    /**
     * @OA\Schema(
     *     schema="Compensatory-CompensatoryOffModel",
     *     type="object",
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="date", type="string"),
     *     @OA\Property(property="lengthHours", type="number"),
     *     @OA\Property(property="lengthDays", type="number"),
     *      @OA\Property(property="date", type="date"),
     *      @OA\Property(property="expireDate", type="date"),
     *     @OA\Property(
     *         property="status",
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *          ),
     *     @OA\Property(property="duration", type="string"),
     *      @OA\Property(property="comments", type="string"),
     *      @OA\Property(property="leaveType", type="integer"),
     *      @OA\Property(property="leaveTaken", type="integer"),
     *  
     * )
     * 
     * @param CompensatoryOff $compOff
     */
    public function __construct(CompensatoryOff $compOff)
    {
        $this->setEntity($compOff);
        $this->setFilters(
            [
                'id',
                'date',
                'expireDate',
                'lengthHours',
                'lengthDays',
                'status',
                'duration',
                'comments',
                'leaveType',
                'leaveTaken',
            ]
        );
        $this->setAttributeNames(
            [
                'id',
                'date',
                'expireDate',
                'lengthHours',
                'lengthDays',
                'status',
                'duration',
                'comments',
                'leaveType',
                'leaveTaken',
            ]
        );
    }
}
