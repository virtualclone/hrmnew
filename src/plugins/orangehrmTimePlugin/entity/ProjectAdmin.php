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
use OrangeHRM\Entity\Decorator\DecoratorTrait;
use OrangeHRM\Entity\Decorator\ProjectDecorator;

/**
 * @method ProjectDecorator getDecorator()
 * @ORM\Table(name="ohrm_project_admin")
 * @ORM\Entity
 */
class ProjectAdmin
{
    use DecoratorTrait;

    /**
     * @var Project
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Project", cascade={"persist"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=false)
     */
    private Project $project;

    /**
     * @var Employee
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Employee", cascade={"persist"})
     * @ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number", nullable=false)
     */
    private Employee $employee;

    /**
     * @var int|null
     *
     * @ORM\Column(name="project_id", type="integer",  nullable=true)
     */
    private ?int $projectId;
    

    public function __construct()
    {
        
    }    


     /**
     * @return int|null
     */
    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    /**
     * @param  int|null  $projectId
     */
    public function setProjectId(?int $projectId): void
    {
        $this->projectId = $projectId;
    }

     /**
     * @return Employee
     */
    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    /**
     * @param Employee $employee
     */
    public function setEmployee(Employee $employee): void
    {
        $this->employee = $employee;
    }
}
