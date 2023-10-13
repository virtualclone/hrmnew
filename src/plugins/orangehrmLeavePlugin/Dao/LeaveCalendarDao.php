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

namespace OrangeHRM\Leave\Dao;


use Exception;
use OrangeHRM\Leave\Dto\LeaveCalendarSearchFilterParams;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\Leave;
use OrangeHRM\Entity\Employee;
use OrangeHRM\ORM\Paginator;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;


class LeaveCalendarDao extends BaseDao
{
    use DateTimeHelperTrait;

    /**
     * @param LeaveCalendarSearchFilterParams $leavecalendarSearchFilterParams
     * @return array
     * @throws DaoException
     */
    public function getLeaveCalendar(LeaveCalendarSearchFilterParams $leavecalendarSearchFilterParams): array
    {
        try {
           
            $paginator = $this->getLeaveCalendarPaginator($leavecalendarSearchFilterParams);   
                
            return [$paginator];   
            //return $paginator->getQuery()->execute();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param LeaveCalendarSearchFilterParams $leavecalendarSearchFilterParams
     * @return Paginator
     */
    public function getLeaveCalendarPaginator(LeaveCalendarSearchFilterParams $leavecalendarSearchFilterParams
    ) {

        $conn = $this->getEntityManager()->getConnection();
        
        $q = $this->createQueryBuilder(Leave::class, 'l')
            ->select('l.date')
            ->leftJoin('l.employee', 'employee');  
            $q->andWhere($q->expr()->between('l.date', ':fromDate', ':toDate'))
            ->setParameter('fromDate', $leavecalendarSearchFilterParams->getFromDate())
            ->setParameter('toDate', $leavecalendarSearchFilterParams->getToDate());          
           $q ->andwhere($q->expr()->in('l.status', ':ids'))
            ->setParameter('ids', [1,2]);
            $q->addGroupBy('l.date');
        $result = $q->getQuery()->getResult();
        
        $leavelist = [];
        
       foreach ($result as $dateItem) {
        $extractedDates= $dateItem['date']->format('Y-m-d');     
        
        $sql = "select (select GROUP_CONCAT(' ',emp_firstname,' ',emp_lastname) as Name FROM `ohrm_leave` OL 
        left join hs_hr_employee HE on HE.emp_number=OL.emp_number where date='".$extractedDates."' and status=1 
        and length_days='1.0000' group by date) as Fday,(select GROUP_CONCAT(' ',emp_firstname,' ',emp_lastname) as Name 
        FROM `ohrm_leave` OL left join hs_hr_employee HE on HE.emp_number=OL.emp_number where date='".$extractedDates."' 
        and status=1 and length_days='0.5000' group by date) as Hday from ohrm_leave where date='".$extractedDates."' 
        and status=1 group by date";
        
        $statement = $conn->prepare($sql);
        $Pending = $statement->executeQuery();

        if ($Pending->rowCount() > 0) {
            $result = $Pending->fetchAssociative();
            if($result['Fday']!="")
            {
                $PenFull="FD: ".$result['Fday'];
            }else{
                $PenFull="";
            }
    
            if($result['Hday']!="")
            {
                $Penhalf="HD: ".$result['Hday'];
            }else{
                $Penhalf="";
            }
        }else{
            $PenFull="";
            $Penhalf="";
        }


         $sql1 = "select (select GROUP_CONCAT(' ',emp_firstname,' ',emp_lastname) as Name FROM `ohrm_leave` OL 
        left join hs_hr_employee HE on HE.emp_number=OL.emp_number where date='".$extractedDates."' and status=2 
        and length_days='1.0000' group by date) as Fday,(select GROUP_CONCAT(' ',emp_firstname,' ',emp_lastname) as Name 
        FROM `ohrm_leave` OL left join hs_hr_employee HE on HE.emp_number=OL.emp_number where date='".$extractedDates."' 
        and status=2 and length_days='0.5000' group by date) as Hday from ohrm_leave where date='".$extractedDates."' 
        and status=2 group by date";
        
        $statement1 = $conn->prepare($sql1);
        $Approved = $statement1->executeQuery();

        if ($Approved->rowCount() > 0) {
            $result1 = $Approved->fetchAssociative();
            if($result1['Fday']!="")
            {
                $AppFull="FD: ".$result1['Fday'];
            }else{
                $AppFull="";
            }

            if($result1['Hday']!="")
            {
                $Apphalf="HD: ".$result1['Hday'];
            }else{
                $Apphalf="";
            }
        }else{
            $AppFull="";
            $Apphalf="";
        }
       
        $leavelist[] =['date'=>$extractedDates,'Pending'=>$PenFull.$Penhalf,'Approve'=>$AppFull.$Apphalf];
        
       }

      
        //$this->setSortingAndPaginationParams($q, $leavecalendarSearchFilterParams);
        return $leavelist;
    }

}