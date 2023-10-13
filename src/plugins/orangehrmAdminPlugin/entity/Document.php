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

namespace OrangeHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ohrm_document_type")
 * @ORM\Entity
 */
class Document
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private string $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private string $deleted;

    /** 
     * @var bool 
     * 
     * @ORM\Column(name="isrequire", type="boolean") 
     */ 
    private string $isrequire;  

    /** 
     * @var bool
     * 
     * @ORM\Column(name="users", type="boolean") 
     */ 
    private string $users;



    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */

     public function getDeleted(): bool
     { 
         return $this->deleted; 
     }  
 
     /** 
      * @param bool $deleted 
      */
 
     public function setDeleted(bool $deleted): void 
     { 
         $this->deleted = $deleted; 
     }  
 
      /** 
      * @return bool 
      */
 
     public function getIsRequire(): bool 
     { 
         return $this->isrequire; 
     }  
 
     /** 
      * @param bool $isrequire 
      */
 
     public function setIsRequire(bool $isrequire): void 
     { 
         $this->isrequire = $isrequire; 
     } 
 
      /** 
      * @return bool 
      */ 
     public function getUsers(): bool 
     { 
         return $this->users; 
     }  
 
     /** 
      * @param bool $users 
      */
 
     public function setUsers(bool $users): void 
     {
         $this->users = $users; 
     }
}
