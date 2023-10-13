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

namespace OrangeHRM\Time\Service;


use OrangeHRM\Time\Dao\ProjectUserDao;
use OrangeHRM\Entity\ProjectUser;
use OrangeHRM\Time\Dto\ProjectUserSearchFilterParams;
use OrangeHRM\Time\Exception\ProjectServiceException;

class ProjectUserService
{
    /**
     * @var ProjectUserDao|null
     */
    private ?ProjectUserDao $projectUserDao = null;

    /**
     * @return ProjectUserDao
     */
    public function getProjectUserDao(): ProjectUserDao
    {
        if (is_null($this->projectUserDao)) {
            $this->projectUserDao = new ProjectUserDao();
        }
        return $this->projectUserDao;
    }

     /**
     * @param ProjectUserDao $projectUserDao
     */
    public function setProjectUserDao(ProjectUserDao $projectUserDao): void
    {
        $this->projectUserDao = $projectUserDao;
    }

     /**
     * @param ProjectUserSearchFilterParams $projectUserSearchFilterParams
     * @return array[]
     */
    public function searchProjectUsers(ProjectUserSearchFilterParams $projectUserSearchFilterParams): array
    {
        return $this->getProjectUserDao()->getProjectUserListByName($projectUserSearchFilterParams);
    }
}
