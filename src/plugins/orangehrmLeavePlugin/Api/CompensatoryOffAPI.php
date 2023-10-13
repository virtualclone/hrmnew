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
use OrangeHRM\Leave\Api\Model\CompensatoryOffDetailedModel;
use OrangeHRM\Leave\Dto\CompOffSearchFilterParams;
use Exception;
use OrangeHRM\Leave\Api\Model\CompensatoryOffModel;
use OrangeHRM\Leave\Service\CompensatoryOffService;
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
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\Service\NormalizerServiceTrait;
use DateTime;

class CompensatoryOffAPI extends EndPoint implements CrudEndpoint
{


    use AuthUserTrait;
    use NormalizerServiceTrait;
    public const PARAMETER_DATE = 'applyDate';
    public const PARAMETER_DURATION = 'duration';
    public const PARAMETER_LENGTH_HOURS = 'lengthHours';
    public const PARAMETER_LENGTH_DAYS = 'lengthDays';
    public const PARAMETER_COMMENT = 'comment';
    public const PARAMETER_EXPIRE_DATE = 'expireDate';
    public const PARAMETER_EMP_NUMBER = 'employeeNum';
    public const FILTER_INCLUDE_EMPLOYEES = 'includeEmployees';
    public const PARAMETER_COMPOFF_REQUEST_ID = 'id';
    public const PARAMETER_ACTION_TYPE = 'action';
    public const META_PARAMETER_IS_EXIST = '';
    public const PARAMETER_COMPOFF_LIST = 'CompOffList';
    public const PARAMETERE_DURATION_LABEL = 'durationLabel';

    /**
     * @var null|CompensatoryOffService
     */
    protected ?CompensatoryOffService $compOffService = null;

    /**
     * @return CompensatoryOffService
     */
    public function getCompensatoryService(): CompensatoryOffService
    {
        if (!$this->compOffService instanceof CompensatoryOffService) {
            $this->compOffService = new CompensatoryOffService();
        }
        return $this->compOffService;
    }

    /**
     *
     * @inheritDoc
     * @return RecordNotFoundException
     */
    public function getOne(): EndpointResourceResult
    {
        $empNumber = $this->getAuthUser()->getEmpNumber();
        
        $applyDate = $this->getRequestParams()->getDateTimeOrNull(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_DATE
        );
        $durationLabel = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETERE_DURATION_LABEL
        );
        $compOffReturn = $this->getCompensatoryService()->isExistingCompOff($applyDate, $empNumber,$durationLabel);
        if ($compOffReturn != null) {
            return new EndpointResourceResult(CompensatoryOffModel::class, $compOffReturn);
        } else {
            $defaultValues = [];
            return new EndpointResourceResult(ArrayModel::class, $defaultValues);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getDateParam(): ?DateTime
    {
        return $this->getRequestParams()->getDateTimeOrNull(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            LeaveCommonParams::PARAMETER_DATE
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_DATE,
            ),
            new ParamRule(
                self::PARAMETERE_DURATION_LABEL,
            ),
        );
    }
    /**
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {

        $empNumber = $this->getAuthUser()->getEmpNumber();
        $userRoleId = $this->getAuthUser()->getUserRoleId();
        $CompOffList = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::PARAMETER_COMPOFF_LIST
        );
             
        $employeeNum = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::PARAMETER_EMP_NUMBER
        );
        $compOffSearchFilterParams = $this->getCompOffSearchFilterParams($employeeNum);
        $compOffRequests = $this->getCompensatoryService()
            ->getCompensatoryOffDao()
            ->getCompOffRequests($compOffSearchFilterParams,$userRoleId,$empNumber,$CompOffList);
        $total = $this->getCompensatoryService()
            ->getCompensatoryOffDao()
            ->getCompOffRequestsCount($compOffSearchFilterParams);

        return new EndpointCollectionResult(
            CompensatoryOffDetailedModel::class,
            $compOffRequests,
            new ParameterBag(
                [
                    CommonParams::PARAMETER_EMP_NUMBER => $empNumber,
                    CommonParams::PARAMETER_TOTAL => $total
                ]
            )
        );
    }

    /**
     * @param int|null $employeeNum
     * @return CompOffSearchFilterParams
     */
    protected function getCompOffSearchFilterParams(?int $employeeNum = null): CompOffSearchFilterParams
    {
        $compOffSearchFilterParams = new CompOffSearchFilterParams();
        $compOffSearchFilterParams->setEmpNumber($employeeNum);
        $this->setSortingAndPaginationParams($compOffSearchFilterParams);

        // TODO leave period start date
        $compOffSearchFilterParams->setFromDate(
            $this->getRequestParams()->getDateTimeOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                LeaveCommonParams::PARAMETER_FROM_DATE
            )
        );
        // TODO leave period end date
        $compOffSearchFilterParams->setToDate(
            $this->getRequestParams()->getDateTimeOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                LeaveCommonParams::PARAMETER_TO_DATE
            )
        );
        $compOffSearchFilterParams->setIncludeEmployees(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_INCLUDE_EMPLOYEES,
                $this->getDefaultIncludeEmployees()
            )
        );

        return $compOffSearchFilterParams;
    }



    /**
     * @return string
     */
    protected function getDefaultIncludeEmployees(): string
    {
        return CompOffSearchFilterParams::INCLUDE_EMPLOYEES_ONLY_CURRENT;
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_INCLUDE_EMPLOYEES,
                    new Rule(Rules::IN, [CompOffSearchFilterParams::INCLUDE_EMPLOYEES])
                )
            ),
            new ParamRule(
                LeaveCommonParams::PARAMETER_TO_DATE
            ),
            new ParamRule(
                LeaveCommonParams::PARAMETER_FROM_DATE
            ),
            new ParamRule(
                self::PARAMETER_COMPOFF_LIST
            ),
            
            new ParamRule(
                self::PARAMETER_EMP_NUMBER,
            ),
            ...$this->getSortingAndPaginationParamsRules(CompOffSearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    /**
    
     * @throws Exception
     */
    public function create(): EndpointResourceResult
    {
        $compOff = $this->saveCompensatoryOff();
        return new EndpointResourceResult(CompensatoryOffModel::class, $compOff);
    }


    /**
     * @return CompensatoryOff
     * @throws DaoException
     * @throws RecordNotFoundException
     */
    public function saveCompensatoryOff(): CompensatoryOff
    {
        $empNumber = $this->getAuthUser()->getEmpNumber();
        $applyDate = $this->getRequestParams()->getDateTimeOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_DATE);
        $lengthHours = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_LENGTH_HOURS);
        $lengthDays = $this->getRequestParams()->getFloatOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_LENGTH_DAYS);
        $duration = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_DURATION);
        $comment = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COMMENT);
        $expireDate = $this->getRequestParams()->getDateTimeOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_EXPIRE_DATE);

        $compOff = new CompensatoryOff();

        $compOff->setDate($applyDate);
        $compOff->setLengthHours($lengthHours);
        $compOff->setLengthDays($lengthDays);
        $compOff->setDuration($duration);
        $compOff->setComments($comment);
        $compOff->setExpireDate($expireDate);
        $compOff->getDecorator()->setEmployeeByEmpNumber($empNumber);

        return $this->getCompensatoryService()->saveCompensatoryOff($compOff);
    }



    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_DATE,
            ),
            new ParamRule(
                self::PARAMETER_DURATION,

            ),
            new ParamRule(
                self::PARAMETER_LENGTH_DAYS,

            ),
            new ParamRule(
                self::PARAMETER_LENGTH_HOURS,

            ),
            new ParamRule(
                self::PARAMETER_COMMENT,

            ),
            new ParamRule(
                self::PARAMETER_EXPIRE_DATE,

            ),
        );
    }

    /**
     *
     * @inheritDoc
     * @throws Exception
     */
    public function update(): EndpointResourceResult
    {
        $compoffRequestId = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, CommonParams::PARAMETER_ID);
        $actionType = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_ACTION_TYPE
        );
        $result = $this->getCompensatoryService()->compoffStatusUpdate($compoffRequestId, $actionType);
        if ($result == 1) {
            $result = [];
        }
        return new EndpointResourceResult(ArrayModel::class, $result);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_DATE,
            ),
            new ParamRule(
                self::PARAMETER_DURATION,

            ),
            new ParamRule(
                self::PARAMETER_LENGTH_DAYS,

            ),
            new ParamRule(
                self::PARAMETER_LENGTH_HOURS,

            ),
            new ParamRule(
                self::PARAMETER_COMMENT,

            ),
            new ParamRule(
                self::PARAMETER_EXPIRE_DATE,

            ),
            new ParamRule(
                self::PARAMETER_COMPOFF_REQUEST_ID,

            ),
            new ParamRule(
                self::PARAMETER_ACTION_TYPE,

            ),
        );
    }

    /**
     * @return ParamRuleCollection
     */
    public function getValidationRuleForSaveDocument(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_DATE,
            ),
            new ParamRule(
                self::PARAMETER_DURATION,

            ),
            new ParamRule(
                self::PARAMETER_LENGTH_DAYS,

            ),
            new ParamRule(
                self::PARAMETER_LENGTH_HOURS,

            ),
            new ParamRule(
                self::PARAMETER_COMMENT,

            ),
            new ParamRule(
                self::PARAMETER_EXPIRE_DATE,

            ),
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
