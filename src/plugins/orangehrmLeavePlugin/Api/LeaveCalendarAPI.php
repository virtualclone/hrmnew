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

namespace OrangeHRM\Leave\Api;


use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Leave\Dto\LeaveCalendarSearchFilterParams;
use Exception;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\Exception\RecordNotFoundException;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\CompensatoryOff;
use OrangeHRM\Leave\Service\LeaveCalendarService;

class LeaveCalendarAPI extends EndPoint implements CrudEndpoint
{

    /**
     * @var null|LeaveCalendarService
     */
    protected ?LeaveCalendarService $leaveCalenderService = null;

    /**
     * @return LeaveCalendarService
     */
    public function getLeaveCalendarService(): LeaveCalendarService
    {
        if (!$this->leaveCalenderService instanceof LeaveCalendarService) {
            $this->leaveCalenderService = new LeaveCalendarService();
        }
        
        return $this->leaveCalenderService;
    }

   

    /**
     *
     * @inheritDoc
     * @return RecordNotFoundException
     */
    public function getOne(): EndpointResourceResult
    {
        throw $this->getNotImplementedException();
    }


    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
        );
    }
    /**
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $leavecalendarSearchFilterParams = $this->getLeaveCalendarSearchFilterParams();        
        $compOffRequests = $this->getLeaveCalendarService()
            ->getLeaveCalendarDao()
            ->getLeaveCalendar($leavecalendarSearchFilterParams);
       // print_r($compOffRequests);
        // $total = $this->getLeaveCalendarService()
        //     ->getLeaveCalendarDao()
        //     ->getLeaveCalendar($leavecalendarSearchFilterParams);
        $total = null;
        return new EndpointCollectionResult(
           ArrayModel::class,
            $compOffRequests,
            new ParameterBag(
                [
                    CommonParams::PARAMETER_TOTAL => $total
                ]
            )
        );
    }

    /**
     * 
     * @return LeaveCalendarSearchFilterParams
     */
    protected function getLeaveCalendarSearchFilterParams(): LeaveCalendarSearchFilterParams
    {
        $leavecalendarSearchFilterParams = new LeaveCalendarSearchFilterParams();
        $this->setSortingAndPaginationParams($leavecalendarSearchFilterParams);

        // TODO leave period start date
        $leavecalendarSearchFilterParams->setFromDate(
            $this->getRequestParams()->getDateTimeOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                LeaveCommonParams::PARAMETER_FROM_DATE
            )
        );
        // TODO leave period end date
        $leavecalendarSearchFilterParams->setToDate(
            $this->getRequestParams()->getDateTimeOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                LeaveCommonParams::PARAMETER_TO_DATE
            )
        );

        return $leavecalendarSearchFilterParams;
    }
    
    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
           new ParamRule(
                LeaveCommonParams::PARAMETER_TO_DATE
            ),
            new ParamRule(
                LeaveCommonParams::PARAMETER_FROM_DATE
            ),
           
            ...$this->getSortingAndPaginationParamsRules(LeaveCalendarSearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    /**
    
     * @throws Exception
     */
    public function create(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }


    /**
     * @return CompensatoryOff
     * @throws DaoException
     * @throws RecordNotFoundException
     */
    public function saveCompensatoryOff(): CompensatoryOff
    {
        throw $this->getNotImplementedException();
    }



    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
           
        );
    }   

    /**
     *
     * @inheritDoc
     * @throws Exception
     */
    public function update(): EndpointResourceResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
           
        );
    }

    /**
     * @return ParamRuleCollection
     */
    public function getValidationRuleForSaveDocument(): ParamRuleCollection
    {
        return new ParamRuleCollection(
           
        );
    }

    /**
     *
     * @inheritDoc
     */
    public function delete(): EndpointResourceResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(CommonParams::PARAMETER_IDS),
        );
    }
}
