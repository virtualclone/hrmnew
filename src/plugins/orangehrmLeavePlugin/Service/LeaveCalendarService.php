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

 namespace OrangeHRM\Leave\Service;

 use DateTime;
 use OrangeHRM\Leave\Dao\LeaveCalendarDao;
 use OrangeHRM\Leave\Dto\LeaveCalendarSearchFilterParams;
 use InvalidArgumentException;
 use OrangeHRM\Leave\Service\Model\CompensatoryOffModel;
 use OrangeHRM\Core\Exception\DaoException;
 use OrangeHRM\Core\Traits\Service\NormalizerServiceTrait;
 use OrangeHRM\Entity\CompensatoryOff;
class LeaveCalendarService
{
    use NormalizerServiceTrait;

    /**
     * @var LeaveCalendarDao|null
     */
    private ?LeaveCalendarDao $leaveCalendarDao = null;

    /**
     * @return LeaveCalendarDao
     */
    public function getLeaveCalendarDao(): LeaveCalendarDao
    {
        if (!$this->leaveCalendarDao instanceof LeaveCalendarDao) {
            $this->leaveCalendarDao = new LeaveCalendarDao();
        }
        return $this->leaveCalendarDao;
    }

   
    /**
     * @param LeaveCalendarSearchFilterParams $leavecalendarSearchFilterParams
     * @return array
     * @throws DaoException
     */
    public function getLeaveCalendar(LeaveCalendarSearchFilterParams $leavecalendarSearchFilterParams): array
    {
        return $this->getLeaveCalendarDao()->getLeaveCalendar($leavecalendarSearchFilterParams);
    }

   
   
}
