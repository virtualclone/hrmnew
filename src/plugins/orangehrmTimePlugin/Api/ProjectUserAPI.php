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

namespace OrangeHRM\Time\Api;

use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Entity\ProjectUser;
use OrangeHRM\Time\Api\Model\ProjectUserModel;
use OrangeHRM\Time\Traits\Service\ProjectUserServiceTrait;
use OrangeHRM\Time\Dto\ProjectUserSearchFilterParams;
use OrangeHRM\Time\Exception\ProjectServiceException;

class ProjectUserAPI extends Endpoint implements CrudEndpoint
{
    use ProjectUserServiceTrait;


    public const FILTER__NAME = 'name';
    public const FILTER_PROJECT_ID = 'projectId';
    public const PARAMETER_USER_ID = 'userId';
    public const PARAMETER_PROJECT_ID = 'projectId';



    /**
     * 
     * 
     *  /**
     * @OA\Get(
     *     path="/api/v2/time/project/users",
     *     tags={"Time/Project"},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     * @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *    
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *     @OA\Parameter(ref="#/components/parameters/offset"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *             @OA\Property(property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $projectParamHolder = new ProjectUserSearchFilterParams();
        $this->setSortingAndPaginationParams($projectParamHolder);
        $projectParamHolder->setName(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER__NAME
            )
        );
        $projectParamHolder->setProjectId(
            $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_PROJECT_ID
            )
        );
        $projectId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::FILTER_PROJECT_ID
        );
        if ($projectId) {
            $projectParamHolder->setProjectId($projectId);
        }
        $projectUsers = $this->getProjectUserService()->searchProjectUsers($projectParamHolder);
        $projectUserCount = count($projectUsers);
        return new EndpointCollectionResult(
            ArrayModel::class,
            $projectUsers,
            new ParameterBag(
                [CommonParams::PARAMETER_TOTAL => $projectUserCount]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::FILTER_PROJECT_ID, new Rule(Rules::POSITIVE))
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER__NAME,
                    new Rule(Rules::STRING_TYPE),
                ),
            ),

            ...$this->getSortingAndPaginationParamsRules(ProjectUserSearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    /**
     * @return ParamRule
     */
    private function getProjectIdParamRule(): ParamRule
    {
        return new ParamRule(
            self::FILTER_PROJECT_ID,
            new Rule(Rules::POSITIVE)
        );
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResult
    {
        $projectUser = new ProjectUser();
        $projectId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_PROJECT_ID
        );
        $userId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_USER_ID
        );
        $projectUser->getDecorator()->setProjectByProjectId($projectId);
        $projectUser->getDecorator()->setEmployeeByEmpNumber($userId);

        $this->getProjectUserService()->getProjectUserDao()->saveProjectUser($projectUser);
        // print_r($projectUser);
        return new EndpointResourceResult(ProjectUserModel::class, $projectUser);
    }

    // /**
    //  * @param ProjectUser $projectUser
    //  * @return void
    //  */
    // private function setParamsToProjectUser(ProjectUser $projectUser): void
    // {
    //     list($projectId,$userId) = $this->getUrlAttributes();
    //     $projectUser->setProjectId($projectId);
    //     $projectUser->getDecorator()->setEmployeeByEmpNumber($userId);
    // }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            ...$this->getCommonBodyValidationRules(),
        );
    }


     /**
     * @return ParamRule[]
     */
     private function getCommonBodyValidationRules(): array
     { 
         return [ 
             $this->getValidationDecorator()->requiredParamRule( 
                 new ParamRule( 
                     self::PARAMETER_PROJECT_ID, 
                     new Rule(Rules::POSITIVE), 
                 ) 
                 ), 
                 $this->getValidationDecorator()->requiredParamRule( 
                     new ParamRule(
                         self::PARAMETER_USER_ID,
                         new Rule(Rules::POSITIVE),  
                     ) 
                 ) 
              ]; 
     }


    /**
     * @inheritDoc
     */
    public function delete(): EndpointResult
    {
        try {
            $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, CommonParams::PARAMETER_IDS);

            $this->getProjectUserService()->getProjectUserDao()->deleteProjectUsers($ids);
            return new EndpointResourceResult(ArrayModel::class, $ids);
        } catch (ProjectServiceException $projectServiceException) {
            throw $this->getBadRequestException($projectServiceException->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_IDS,
                new Rule(Rules::ARRAY_TYPE),
                new Rule(
                    Rules::EACH,
                    [new Rules\Composite\AllOf(new Rule(Rules::POSITIVE))]
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        // $projectId = $this->getRequestParams()->getInt(
        //     RequestParams::PARAM_TYPE_ATTRIBUTE,
        //     self::FILTER_PROJECT_ID
        // );
        // print_r($projectId);
        // list($projectId) = $this->getUrlAttributes();
        // $projectActivity = $this->getProjectUserService()
        //     ->getProjectUserDao()
        //     ->getProjectUserByProjectIdAndProjectUserId($projectId);
        // $this->throwRecordNotFoundExceptionIfNotExist($projectActivity, ProjectActivity::class);
        // return new EndpointResourceResult(ProjectActivityModel::class, $projectActivity);
        throw $this->getRecordNotFoundException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        // return new ParamRuleCollection(
        //     new ParamRule(
        //         CommonParams::PARAMETER_ID,
        //         new Rule(Rules::POSITIVE)
        //     ),
        //     $this->getProjectIdParamRule(),
        // );
        throw $this->getRecordNotFoundException();
    }

    /**
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        // list($projectId, $projectActivityId) = $this->getUrlAttributes();
        // $projectUser = $this->getProjectService()
        //     ->getProjectUserDao()
        //     ->getProjectUserByProjectIdAndProjectUserId($projectId, $projectActivityId);
        // $this->throwRecordNotFoundExceptionIfNotExist($projectUser, ProjectActivity::class);
        // $this->setParamsToProjectUser($projectUser);
        // $this->getProjectService()->getProjectUserDao()->saveProjectUser($projectUser);
        // return new EndpointResourceResult(ProjectActivityModel::class, $projectUser);
        throw $this->getRecordNotFoundException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        throw $this->getRecordNotFoundException();
    }

    //    /**
    //      * @return array
    //      */
    //     private function getUrlAttributes(): array
    //     {
    //         $projectId = $this->getRequestParams()->getInt(
    //             RequestParams::PARAM_TYPE_BODY,
    //             self::PARAMETER_PROJECT_ID
    //         );
    //         $userId = $this->getRequestParams()->getInt(
    //             RequestParams::PARAM_TYPE_BODY,
    //             self::PARAMETER_USER_ID
    //         );
    //         print_r($projectId,$userId);
    //         return [$projectId,$userId];
    //     }


}
