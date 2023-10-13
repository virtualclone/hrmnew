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

namespace OrangeHRM\Time\Dto;

use OrangeHRM\Core\Dto\FilterParams;
use OrangeHRM\Core\Exception\SearchParamException;

class CustomerSearchFilterParams extends FilterParams
{
    public const ALLOWED_SORT_FIELDS = ['customer.name'];
    public const INCLUDE_CUSTOMER_ONLY_ACTIVE = 'Active';
    public const INCLUDE_CUSTOMER_ONLY_INACTIVE = 'InActive';
    public const INCLUDE_CUSTOMER_ALL = 'All';
 

    public const INCLUDE_CUSTOMER_MAP = [
        1 => self::INCLUDE_CUSTOMER_ONLY_ACTIVE,
        2 => self::INCLUDE_CUSTOMER_ALL,
        3 => self::INCLUDE_CUSTOMER_ONLY_INACTIVE,
    ];

    /**
     * @var string|null
     */
    protected ?string $name = null;

   /**
     * @var string|null
     */
     protected ?string $includestatus = self::INCLUDE_CUSTOMER_ONLY_ACTIVE;

    /**
     * @var int[]|null
     */
    protected ?array $customerIds = null;

    /**
     * @var int|null
     */
    protected ?int $customerId = null;


    public function __construct()
    {
        $this->setSortField('customer.name');
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int[]|null
     */
    public function getCustomerIds(): ?array
    {
        return $this->customerIds;
    }

    /**
     * @param int[]|null $customerIds
     */
    public function setCustomerIds(?array $customerIds): void
    {
        $this->customerIds = $customerIds;
    }

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    /**
     * @param int|null $customerId
     */
    public function setCustomerId(?int $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return string|null
     */
     public function getIncludeStatus(): ?string
     { 
         return $this->includestatus; 
     }
 
  
 
     /** 
      * @param string|null $includestatus 
      */
 
     public function setIncludeStatus(?string $includestatus): void 
     { 
         if (in_array($includestatus, array_keys(self::INCLUDE_CUSTOMER_MAP))) { 
             $includestatus = self::INCLUDE_CUSTOMER_MAP[$includestatus] ?? null; 
         }
          if (!is_null($includestatus) && !in_array($includestatus, array_values(self::INCLUDE_CUSTOMER_MAP))) { 
             throw new SearchParamException('Invalid parameter');
         } 
         $this->includestatus = $includestatus; 
     }
}
