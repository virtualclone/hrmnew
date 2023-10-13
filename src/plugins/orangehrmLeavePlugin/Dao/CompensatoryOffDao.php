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

use DateTime;
use Exception;
use OrangeHRM\Leave\Dto\CompOffSearchFilterParams;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\CompensatoryOff;
use OrangeHRM\Entity\ReportTo;
use OrangeHRM\ORM\Paginator;
use OrangeHRM\ORM\ListSorter;
use Doctrine\ORM\QueryBuilder;
use OrangeHRM\Entity\LeaveEntitlement;
use Doctrine\DBAL\Connection;
use OrangeHRM\Entity\Employee;
use Respect\Validation\Rules\DateTime as RulesDateTime;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;

use PDO;


class CompensatoryOffDao extends BaseDao
{
    private Connection $connection;
    use DateTimeHelperTrait;
    
    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->connection;
    }
    
    /**
     * @param CompensatoryOff $compOff
     * @return CompensatoryOff
     * @throws DaoException
     */
    public function saveCompensatoryOff(CompensatoryOff $compOff): CompensatoryOff
    {
        try {
            
            $comOffDate = $this->getDateTimeHelper()->formatDateTimeToYmd($compOff->getDate());
            $comOffempNumber= $_SESSION['_sf2_attributes']['user.user_employee_number'];
            $this->persist($compOff);

            $q = $this->createQueryBuilder(CompensatoryOff::class, 'c');
            $q->select('c')
                ->where('c.date = :date')
                ->andWhere('c.employee = :empNumber')->setParameter('date', $comOffDate)
                ->setParameter('empNumber', $comOffempNumber);

                $results = $q->getQuery()->getArrayResult();                
                $firstItem = $results[0];

            $duration = $firstItem["duration"];       


            $headers = "From: OrangeHRM<hr@virtualclone.in>" . "\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $txtmsghr = "<html><body>";

            $Repo = $this->createQueryBuilder(ReportTo::class, 'rt');
            $Repo->leftJoin('rt.supervisor', 'supervisor')
                ->andWhere('rt.subordinate = :subordinateId')
                ->setParameter('subordinateId', $comOffempNumber);  
            $resultRepo = $Repo->getQuery()->getArrayResult();
            $firstRepo = $resultRepo[0];
            $reportingMangereId = $firstRepo["erep_sup_emp_number"];

            $empIn = $this->createQueryBuilder(Employee::class, 'e');
            $empIn->select(
                'CONCAT(e.firstName, \' \', e.lastName) AS fullName');
            $empIn->andWhere('e.empNumber = :empNumber')
                ->setParameter('empNumber', $comOffempNumber);  
            $resultempIn = $empIn->getQuery()->getArrayResult();
            $firstempIn = $resultempIn[0];
            $empName = $firstempIn["fullName"];

            $Manager = $this->createQueryBuilder(Employee::class, 'e');
            $Manager->select('e.workEmail');
            $Manager->andWhere('e.empNumber = :empNumber')
                ->setParameter('empNumber', $reportingMangereId);  
            $resultManager = $Manager->getQuery()->getArrayResult();
            $firstmanager = $resultManager[0];
            $Manageremail = $firstmanager["workEmail"];

            $whoIsApply=$empName. " has applied for ".str_replace('_',' ',$duration)." compensatory off for ".$comOffDate.".";
            $to = "vidhi.nayak@virtualclone.in";
            //$to = "hr@virtualclone.in, ".$Manageremail;
            $subject = "Compensatory Off Applied";
            $messageBody= $whoIsApply;
            $sendmail= mail($to ,$subject,$messageBody,$headers);


            return $compOff;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }



    /**
     * @return LeaveStatus[]
     */
    public function getAllCompoffStatuses(): array
    {
        return $this->getRepository(LeaveStatus::class)->findAll();
    }

    

    /**
     * @param CompOffSearchFilterParams $compOffSearchFilterParams
     * @param int $userRoleId 
     * @param int $empNumber
     * @param string $CompOffList
     * @return CompensatoryOff[]
     */
    public function getCompOffRequests(CompOffSearchFilterParams $compOffSearchFilterParams, $userRoleId, $empNumber,$CompOffList): array
    {

        if (!is_null($compOffSearchFilterParams->getFromDate()) && !is_null($userRoleId) && !is_null($empNumber) && $CompOffList=="Approve") {
           
            return $this->getCompOffRequestsPaginator($compOffSearchFilterParams)->getQuery()->execute();
        }

        if(is_null($compOffSearchFilterParams->getFromDate()) && !is_null($userRoleId) && !is_null($empNumber)  && $CompOffList=="Approve")
        {
            $q = $this->createQueryBuilder(ReportTo::class, 'rt');
            $q->leftJoin('rt.supervisor', 'supervisor')
                ->andWhere('rt.supervisor = :supervisorId')
                ->setParameter('supervisorId', $empNumber);   

        $count = $this->count($q);        

         if($count!=0 || $userRoleId == 1) {

            return $this->getCompOffRequestsPaginator($compOffSearchFilterParams)->getQuery()->execute();
        }else{
           
            return [];
        }
            
        }
        if (is_null($compOffSearchFilterParams->getFromDate()) && !is_null($userRoleId) && !is_null($empNumber) && $CompOffList=="MY") {
           
            return $this->getMyCompOffRequestsPaginator($empNumber)->getQuery()->execute();
        }
        //return $this->getCompOffRequestsPaginator($compOffSearchFilterParams)->getQuery()->execute();
    }

    /**
     * @param CompOffSearchFilterParams $compOffSearchFilterParams
     * @return int
     */
    public function getCompOffRequestsCount(CompOffSearchFilterParams $compOffSearchFilterParams): int
    {

        //$this->_markApprovedLeaveAsTaken();
        return $this->getCompOffRequestsPaginator($compOffSearchFilterParams)->count();
    }


    // paginator code
    /**
     * @param CompOffSearchFilterParams $compOffSearchFilterParams
     * @return Paginator
     */
    private function getCompOffRequestsPaginator(
        CompOffSearchFilterParams $compOffSearchFilterParams
    ): Paginator {
        $q = $this->createQueryBuilder(CompensatoryOff::class, 'compoffRequest')
            ->leftJoin('compoffRequest.employee', 'employee');
        $this->setSortingAndPaginationParams($q, $compOffSearchFilterParams);
        $q->addOrderBy('employee.lastName', ListSorter::ASCENDING)
            ->addOrderBy('employee.firstName', ListSorter::ASCENDING);

        if (!is_null($compOffSearchFilterParams->getEmpNumber())) {
            $q->andWhere('compoffRequest.employee = :empNumber')
                ->setParameter('empNumber', $compOffSearchFilterParams->getEmpNumber());
        }

        if (!is_null($compOffSearchFilterParams->getFromDate())) {
            $q->andWhere($q->expr()->gte('compoffRequest.date', ':fromDate'))
                ->setParameter('fromDate', $compOffSearchFilterParams->getFromDate());
        }

        if (!is_null($compOffSearchFilterParams->getToDate())) {
            $q->andWhere($q->expr()->lte('compoffRequest.date', ':toDate'))
                ->setParameter('toDate', $compOffSearchFilterParams->getToDate());
        }

        $q->andWhere($q->expr()->in('compoffRequest.status', 0));

        $q->addGroupBy('compoffRequest.id');

        $q->andWhere($q->expr()->isNull('employee.purgedAt'));

        return $this->getPaginator($q);
    }

    // paginator code
    /**
     * @param int $empNumber
     * @return Paginator
     */
    private function getMyCompOffRequestsPaginator($empNumber): Paginator {
        $q = $this->createQueryBuilder(CompensatoryOff::class, 'compoffRequest')
            ->leftJoin('compoffRequest.employee', 'employee');
        $q->addOrderBy('employee.lastName', ListSorter::ASCENDING)
            ->addOrderBy('employee.firstName', ListSorter::ASCENDING);        
            $q->andWhere('compoffRequest.employee = :empNumber')
                ->setParameter('empNumber', $empNumber);
              
       
        $q->addGroupBy('compoffRequest.id');
        $q->andWhere($q->expr()->isNull('employee.purgedAt'));

        return $this->getPaginator($q);
    }

    /**
     * @param array $toDeleteIds
     * @return int
     * @throws DaoException
     */
    public function deleteDocuments(array $toDeleteIds): int
    {
        $deleted = 1;
        try {
            $q = $this->createQueryBuilder(Document::class, 'd');
            $q->update()
                ->set('d.deleted', ':deleted')
                ->setParameter('deleted', $deleted)
                ->where($q->expr()->in('d.id', ':ids'))
                ->setParameter('ids', $toDeleteIds);
            return $q->getQuery()->execute();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param int  $compoffRequestId
     * @param string actionType
     * @return int
     * @throws DaoException
     */
    public function compoffStatusUpdate(int $compoffRequestId, string $actionType): int
    {
        try {
            $this->beginTransaction();
            $q = $this->createQueryBuilder(CompensatoryOff::class, 'cp');

            // Check the actionType and set the status accordingly
            $headers = "From: OrangeHRM<hr@virtualclone.in>" . "\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $txtmsghr = "<html><body>";

            $currentyear=date("Y");
            $fromDate=$currentyear."-01-01 00:00:00";
            $ToDate=$currentyear."-12-31 00:00:00";

            $CF = $this->createQueryBuilder(CompensatoryOff::class, 'cp');            
            $CF->select('cp','employee')
                ->leftJoin('cp.employee', 'employee')
                ->Where($CF->expr()->eq('cp.id', ':compensatoryId'))
                ->setParameter('compensatoryId', $compoffRequestId);
            $results = $CF->getQuery()->getArrayResult();
            
            $firstItem = $results[0];

            $id = $firstItem["id"];
            $date = explode(' ',$firstItem["date"]->format('Y-m-d H:i:s')); // Format the DateTime object
            $expireDate = explode(' ',$firstItem["expireDate"]->format('Y-m-d H:i:s'));
            $lengthHours = $firstItem["lengthHours"];
            $lengthDays = $firstItem["lengthDays"];
            $status = $firstItem["status"];
            $duration = $firstItem["duration"];
            $comments = $firstItem["comments"];
            $leaveType = $firstItem["leaveType"];
            $leaveTaken = $firstItem["leaveTaken"];
            $empEmail = $firstItem["employee"]["workEmail"];
            $empnumber = $firstItem["employee"]["empNumber"];

            
            $AppDate=$date[0];
            $expDate =$expireDate[0];


            $LoginUser= $_SESSION['_sf2_attributes']['user.user_employee_number'];

            $Lu = $this->createQueryBuilder(Employee::class, 'e');        
            $Lu->select('e.firstName')                
                ->Where($Lu->expr()->eq('e.empNumber', ':empNumber'))
                ->setParameter('empNumber', $LoginUser);
            $resultLU = $Lu->getQuery()->getArrayResult();
            $firstLU = $resultLU[0];
            $LUserName = $firstLU["firstName"];


           
            $LE = $this->createQueryBuilder(LeaveEntitlement::class, 'entitlement');
            $LE->select('entitlement.noOfDays','count(entitlement.id) as CT')
                ->andWhere($q->expr()->in('entitlement.employee', ':empNumbers'))
                ->setParameter('empNumbers', $empnumber);
            $LE->andWhere('entitlement.deleted = :deleted')
                ->setParameter('deleted', false);
            $LE->andWhere('entitlement.leaveType = :leaveType')
                ->setParameter('leaveType', 4);  
            $LE->andWhere('entitlement.fromDate = :fromDate')
                ->setParameter('fromDate', $fromDate);
            $LE->andWhere('entitlement.toDate = :toDate')
                ->setParameter('toDate', $ToDate);
            $resultLE = $LE->getQuery()->getArrayResult();
            $LEItem = $resultLE[0];
            

            $noOfDays = $LEItem["noOfDays"];
            $CT = $LEItem["CT"];         
            


            if ($actionType === 'APPROVE') {
                $q->update()
                    ->set('cp.status', 1);


                    $pdo = new PDO('mysql:host=localhost;dbname=hrm', 'root', '');
                    if($CT==0)
                    {
       
                       $currentdate=date('Y-m-d H:i:s');
                       
                       // Disable foreign key checks
                       $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                       
                       $pdo->exec("INSERT INTO ohrm_leave_entitlement 
                       (emp_number,no_of_days,leave_type_id,from_date,to_date,created_by_id,entitlement_type,created_by_name,credited_date) 
                       VALUES ('".$empnumber."','".$lengthDays."',4,'".$fromDate."', '".$ToDate."', '".$LoginUser."',1,'".$LUserName."',now())");
                       
                       $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
       
                       
                    }else{
       
                       $no_of_days=$noOfDays+$lengthDays;
       
                       $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
       
                       $pdo->exec("UPDATE ohrm_leave_entitlement SET no_of_days='".$no_of_days."',created_by_id='".$LoginUser."',
                       created_by_name='".$LUserName."' where from_date='".$fromDate."' and to_date='".$ToDate."' and emp_number='".$empnumber."' and leave_type_id=4 and deleted=0");
       
                       $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
       
                    }    

                    $status="Approved";
                    $whoIsApply="Your compensatory off request has been ".$status." for  date: ".$AppDate.".You have to use it before ".$expDate.".";    
            } elseif ($actionType === 'REJECT') {
                $q->update()
                    ->set('cp.status', 2);

               $status="Rejected";
               $whoIsApply="Your compensatory off request has been ".$status." for  date: ".$AppDate.".";
            }
             $to = "vidhi.nayak@virtualclone.in,".$empEmail;

            $subject = "Compensatory Off ".$status;
            $messageBody= $whoIsApply;
            $sendmail= mail($to ,$subject,$messageBody,$headers);

            // // Add a condition to select data using id
            $q->andWhere($q->expr()->eq('cp.id', ':compensatoryId'))
                ->setParameter('compensatoryId', $compoffRequestId);

            return $q->getQuery()->execute();
            
            $this->commitTransaction();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param DateTime $comOffDate
     * @param int $comOffempNumber
     * @param string $durationLabel
     * @return CompensatoryOff|null
     * @throws DaoException
     */
    public function isExistingCompOff(DateTime $comOffDate, int $comOffempNumber, string $durationLabel): ?CompensatoryOff
    {

        try {
            $q = $this->createQueryBuilder(CompensatoryOff::class, 'c');
            $q->select('c')
                ->where('c.date = :date')
                ->andWhere('c.employee = :empNumber')->setParameter('date', $comOffDate)
                ->setParameter('empNumber', $comOffempNumber);
                

                if($durationLabel=='full_day')
                {
                    $lengthHours = 8.0000;
                    $lengthDays = 1.0000;
                    $duration = $durationLabel;
                }else{
                    $lengthHours = 4.0000;
                    $lengthDays = 0.5000;
                    $duration = $durationLabel;
                }            
            
            $count = $this->count($q);
           
            if($count == 0) {
               
                return null;
            } else{   

                $results = $q->getQuery()->getArrayResult();  
                $firstItem = $results[0];
                $id = $firstItem["id"];                      
                $status = $firstItem["status"];                
                
                
                if($id!="" && $status==2)
                {
                    $cfUpdate = $this->createQueryBuilder(CompensatoryOff::class, 'cp');
                    $cfUpdate->update()
                        ->set('cp.lengthHours', ':lengthHours')
                        ->setParameter('lengthHours', $lengthHours)
                        ->set('cp.lengthDays', ':lengthDays')
                        ->setParameter('lengthDays', $lengthDays)
                        ->set('cp.duration', ':duration')
                        ->setParameter('duration', $duration)
                        ->set('cp.status', 0);
                    $cfUpdate->andWhere($cfUpdate->expr()->eq('cp.id', ':compensatoryId'))
                        ->setParameter('compensatoryId', $id);
                    $cfUpdate->getQuery()->execute();        

                 
                }
                return $q->getQuery()->getOneOrNullResult();
            }
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
