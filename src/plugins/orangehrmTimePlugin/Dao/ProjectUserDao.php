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

namespace OrangeHRM\Time\Dao;

use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\Project;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\ProjectUser;
use OrangeHRM\Entity\TimesheetItem;
use OrangeHRM\ORM\Paginator;
use OrangeHRM\Time\Dto\ProjectUserSearchFilterParams;
use OrangeHRM\Time\Exception\ProjectServiceException;
use OrangeHRM\ORM\QueryBuilderWrapper;

class ProjectUserDao extends BaseDao
{
    /**
     * @param ProjectUserSearchFilterParams $projectUserSearchFilterParams
     * @return array
     */
    public function getProjectUserListByName(ProjectUserSearchFilterParams $projectUserSearchFilterParams): array
    {
        $qb = $this->getProjectUserQueryBuilderWrapper($projectUserSearchFilterParams)->getQueryBuilder();
        return $qb->getQuery()->execute();
    }

    /**
     * @param ProjectUserSearchFilterParams $projectUserSearchFilterParams
     * @return QueryBuilderWrapper
     */
    protected function getProjectUserQueryBuilderWrapper(
        ProjectUserSearchFilterParams $projectUserSearchFilterParams
    ): QueryBuilderWrapper {

        if (!is_null($projectUserSearchFilterParams->getProjectId())) {
            $q = $this->createQueryBuilder(ProjectUser::class, 'projectu');
            $q->select('employee.empNumber', 'employee.firstName', 'employee.middleName', 'employee.lastName', 'projectu.id');
            $q->innerJoin('projectu.employee', 'employee');
            $q->andWhere('projectu.projectId =:projectId');
            $q->andWhere($q->expr()->isNull('employee.employeeTerminationRecord'));
            $q->setParameter('projectId', $projectUserSearchFilterParams->getProjectId());
        } else {

            $q = $this->createQueryBuilder(Employee::class, 'employee');
            $q->select('employee.empNumber', 'employee.firstName', 'employee.middleName', 'employee.lastName', 'IDENTITY(employee.employeeTerminationRecord) AS terminationId');
            $q->innerJoin('employee.users', 'u');
            $q->where($q->expr()->in('u.userRole', [2, 3]));

            if (!is_null($projectUserSearchFilterParams->getName())) {
                $q->andWhere(
                    $q->expr()->orX(
                        $q->expr()->like('employee.firstName', ':name'),
                        $q->expr()->like('employee.lastName', ':name'),
                        $q->expr()->like('employee.middleName', ':name'),
                        $q->expr()->like(
                            $q->expr()->concat(
                                'employee.firstName',
                                $q->expr()->literal(' '),
                                'employee.lastName',
                            ),
                            ':name'
                        ),
                        $q->expr()->like(
                            $q->expr()->concat(
                                'employee.firstName',
                                $q->expr()->literal(' '),
                                'employee.middleName',
                                $q->expr()->literal(' '),
                                'employee.lastName',
                            ),
                            ':name'
                        ),
                    )

                );
                $q->setParameter('name', '%' . $projectUserSearchFilterParams->getName() . '%');
            }
        }
        return $this->getQueryBuilderWrapper($q);
    }

    /**
     * @param ProjectUserSearchFilterParams $projectUserSearchFilterParams
     * @return int
     */
    public function getProjectUserCount(
        ProjectUserSearchFilterParams $projectUserSearchFilterParams
    ): int {
        $qb = $this->getProjectUserQueryBuilderWrapper($projectUserSearchFilterParams)->getQueryBuilder();
        return $this->getPaginator($qb)->count();
    }

    /**
     * @param ProjectUser $projectUser
     * @return ProjectUser
     */
    public function saveProjectUser(ProjectUser $projectUser): ProjectUser
    {   
        $this->persist($projectUser);
        return $projectUser;
    }

    /**
     * @param int $projectId
     * @return Project|null
     */
    public function getProjectById(int $projectId): ?Project
    {
        $project = $this->getRepository(Project::class)->find($projectId);
        return ($project instanceof Project) ? $project : null;
    }

    /**
     * @param int $projectId
     * @param int $projectUseryId
     * @return ProjectUser|null
     */
    public function getProjectUserByProjectIdAndProjectUserId(
        int $projectId,
        int $projectUserId
    ): ?ProjectUser {
        $project = $this->getProjectById($projectId);
        $projectUser = $this->getRepository(ProjectUser::class)->findOneBy(
            ['id' => $projectUserId, 'project' => $project]
        );
        return ($projectUser instanceof ProjectUser) ? $projectUser : null;
    }

    /**
     * @param int $activityId
     * @return bool
     */
    public function hasActivityGotTimesheetItems(int $activityId): bool
    {
        $q = $this->createQueryBuilder(TimesheetItem::class, 'timesheetItem');
        $q->andWhere('timesheetItem.projectActivity = :projectActivityId');
        $q->setParameter('projectActivityId', $activityId);
        $count = $this->getPaginator($q)->count();
        return ($count > 0);
    }

    /**
     * @param int[] $toBeDeletedUserIds
     * @return int
     * @throws ProjectServiceException
     */
    public function deleteProjectUsers(array $toBeDeletedUserIds): int
    { {
            foreach ($toBeDeletedUserIds as $toBeDeletedUserId) {

                $conn = $this->getEntityManager()->getConnection();
                $sql = "SELECT * FROM `ohrm_project_employee` where pro_emp_id=" . $toBeDeletedUserId;
                $statement = $conn->prepare($sql);
                $Pending = $statement->executeQuery();
                $result = $Pending->fetchAssociative();
                $ProjectId = $result['project_id'];
                $UserId = $result['emp_number'];

                $sql1 = "SELECT distinct(timesheet_id),employee_id FROM ohrm_timesheet_item WHERE project_id='" . $ProjectId . "' AND employee_id='" . $UserId . "' ";
                $statement1 = $conn->prepare($sql1);
                $Timesheet = $statement1->executeQuery()->fetchAllAssociative();

                $isDelete = 0;
                $timeid = '';
                $rval = 0;
                foreach ($Timesheet as $row) {
                    // Access individual columns in the current row

                    $timeid .= $row['timesheet_id'] . ",";
                    $timesheetid = rtrim($timeid, ',');
                }

                $count = $statement1->executeQuery()->rowCount();
                if ($count != 0) {
                    $sql2 = "SELECT state FROM ohrm_timesheet WHERE timesheet_id in($timesheetid) AND employee_id='" . $UserId . "' ";
                    $statement2 = $conn->prepare($sql2);
                    $Timesheetstate = $statement2->executeQuery()->fetchAllAssociative();
                    foreach ($Timesheetstate as $row1) {
                        // Access individual columns in the current row

                        if ($row1['state'] != 'APPROVED') {
                            $isDelete = 1;
                            $rval = 0;
                            break;
                        }
                    }
                }

                if ($isDelete == 0) {
                    $q = $this->createQueryBuilder(ProjectUser::class, 'ProjectUser');
                    $q->delete()
                        ->where($q->expr()->in('ProjectUser.id', ':ids'))
                        ->setParameter('ids', $toBeDeletedUserId);
                    $q->getQuery()->execute();
                    $rval = 1;
                }
            }
            if ($rval == 0) {
                throw ProjectServiceException::projectUserExist();
            }
            return $rval;
        }
    }

    /**
     **this function for validating the project activity name availability. ( true -> project activity name already exist, false - project activity name is not exist )
     * @param int $projectId
     * @param string $projectActivityName
     * @param int|null $projectActivityId
     * @return bool
     */
    public function isProjectActivityNameTaken(
        int $projectId,
        string $projectActivityName,
        ?int $projectActivityId = null
    ): bool {
        $q = $this->createQueryBuilder(ProjectActivity::class, 'projectActivity');
        $q->andWhere('projectActivity.name = :projectActivityName');
        $q->andWhere('projectActivity.project = :projectId');
        $q->setParameter('projectActivityName', $projectActivityName);
        $q->setParameter('projectId', $projectId);
        if (!is_null($projectActivityId)) {
            // we need to skip the current project activity Name on update, otherwise count always return 1
            $q->andWhere('projectActivity.id != :projectActivityId');
            $q->setParameter('projectActivityId', $projectActivityId);
        }
        return $this->getPaginator($q)->count() > 0;
    }

    /**
     * @param int $fromProjectId
     * @param int $toProjectId
     * @return ProjectActivity[]
     */
    public function getDuplicatedActivities(int $fromProjectId, int $toProjectId): array
    {
        $q = $this->createQueryBuilder(ProjectActivity::class, 'activity');
        $q->andWhere(
            $q->expr()->orX(
                $q->expr()->eq('activity.project', ':fromProjectId'),
                $q->expr()->eq('activity.project', ':toProjectId')
            )
        )
            ->setParameter('fromProjectId', $fromProjectId)
            ->setParameter('toProjectId', $toProjectId)
            ->andWhere('activity.deleted = :deleted')
            ->setParameter('deleted', false);
        $q->groupBy('activity.name')
            ->having('counter >= 2')
            ->select('activity, COUNT(activity.id) AS HIDDEN counter');
        return $q->getQuery()->execute();
    }

    /**
     * @param int[] $projectActivityIds
     * @return ProjectActivity[]
     */
    public function getProjectActivitiesByActivityIds(array $projectActivityIds): array
    {
        // this will get all activities which belongs to $fromProjectActivityIds
        $q = $this->createQueryBuilder(ProjectActivity::class, 'activity');
        $q->andWhere($q->expr()->in('activity.id', ':projectActivityIds'))
            ->setParameter('projectActivityIds', $projectActivityIds);

        return $q->getQuery()->execute();
    }
}
