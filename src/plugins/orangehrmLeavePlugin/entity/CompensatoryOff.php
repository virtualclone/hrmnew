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

use DateTime;

use Doctrine\ORM\Mapping as ORM;
use OrangeHRM\Core\Traits\Service\NumberHelperTrait;
use OrangeHRM\Entity\Decorator\DecoratorTrait;
use OrangeHRM\Entity\Decorator\CompensatoryOffDecorator;

/**
 * @method CompensatoryOffDecorator getDecorator()
 * @ORM\Table(name="ohrm_compoff")
 * @ORM\Entity
 */
class CompensatoryOff
{
    use DecoratorTrait;
    use NumberHelperTrait;

    public const LEAVE_STATUS_COMPOFF_REJECTED = -2;
    public const LEAVE_STATUS_COMPOFF_PENDING_APPROVAL = 0;
    public const LEAVE_STATUS_COMPOFF_APPROVED = 1;
    public const LEAVE_STATUS_COMPOFF_TAKEN = 1;
    public const LEAVE_STATUS_COMPOFF_EXPIRED = 2;  

    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private DateTime $date;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expire_date", type="date", nullable=true)
     */
    private DateTime $expireDate;

    /**
     * @var float
     *
     * @ORM\Column(name="length_hrs", type="decimal", precision=6, scale=2, options={"unsigned" : true})
     */
    private float $lengthHours;

    /**
     * @var float
     *
     * @ORM\Column(name="length_days", type="decimal", precision=6, scale=4, options={"unsigned" : true})
     */
    private float $lengthDays;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", options={"default" : 0})
     */
    private int $status = 0;

    // /**
    //  * @var int
    //  *
    //  * @ORM\Column(name="employee_num", type="integer",)
    //  */
    // private int $empNumber;

    /**
     * @var Employee
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Employee")
     * @ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number")
     */
    private Employee $employee;


    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string")
     */
    private string $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="string")
     */
    private string $comments;


    /**
     * @var int|null
     *
     * @ORM\Column(name="leave_type", type="integer")
     */
    private ?int $leaveType = 4;

     /**
     * @var int|null
     *
     * @ORM\Column(name="leave_taken", type="integer")
     */
    private ?int $leaveTaken = 0;

    public function __construct()
    {
        $this->leaveType = 4;
        $this->leaveTaken = 0;
        $this->status = 0;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
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
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }
    

     /**
     * @return DateTime
     */
    public function getExpireDate(): DateTime
    {
        return $this->expireDate;
    }

    /**
     * @param DateTime $expireDate
     */
    public function setExpireDate(DateTime $expireDate): void
    {
        $this->expireDate = $expireDate;
    }

    /**
     * @return float
     */
    public function getLengthHours(): float
    {
        return $this->lengthHours;
    }

    /**
     * @param float $lengthHours
     */
    public function setLengthHours(float $lengthHours): void
    {
        $this->lengthHours = $this->getNumberHelper()->numberFormat($lengthHours, 2);
    }

    /**
     * @return float
     */
    public function getLengthDays(): float
    {
        return $this->lengthDays;
    }

    /**
     * @param float $lengthDays
     */
    public function setLengthDays(float $lengthDays): void
    {
        $this->lengthDays = $this->getNumberHelper()->numberFormat($lengthDays, 4);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    // /**
    //  * @return int
    //  */
    // public function getEmpNumber(): int
    // {
    //     return $this->empNumber;
    // }

    // /**
    //  * @param int $empNumber
    //  */
    // public function setEmpNumber(int $empNumber): void
    // {
    //     $this->empNumber = $empNumber;
    // }


    /**
     * @return string
     */
    public function getDuration(): string
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration(string $duration): void
    {
        $this->duration = $duration;
    }

     /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments(string $comments): void
    {
        $this->comments = $comments;
    }

    /**
     * @return Employee|null
     */
    public function getEmployee(): ?Employee
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

     /**
     * @return int|null
     */
    public function getLeaveTaken(): ?int
    {
        return $this->leaveTaken;
    }

    /**
     * @param int $leaveTaken
     */
    public function setLeaveTaken(int $leaveTaken): void
    {
        $this->leaveTaken = $leaveTaken;
    }


     /**
     * @return int|null
     */
    public function getLeaveType(): ?int
    {
        return $this->leaveType;
    }

    /**
     * @param int $leaveType
     */
    public function setLeaveType(int $leaveType): void
    {
        $this->leaveType = $leaveType;
    }
}
