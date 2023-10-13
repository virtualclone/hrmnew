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

namespace OrangeHRM\Admin\Api;

use Exception;
use OrangeHRM\Admin\Api\Model\PayrollConfigurationModel;
use OrangeHRM\Admin\Service\PayrollConfigurationService;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Entity\PayrollConfiguration;

class PayrollConfigurationAPI extends Endpoint implements CrudEndpoint
{
    public const PARAMETER_PFEMPLOYEE = 'pfEmployee';
    public const PARAMETER_PFEMPLOYER = 'pfEmployer';
    public const PARAMETER_ESICEMPLOYEE = 'esicEmployee';
    public const PARAMETER_ESICEMPLOYER = 'esicEmployer';
    public const PARAMETER_GRATUITY = 'gratuity';
    public const PARAMETER_MRDICAL = 'medical';
    public const PARAMETER_EPSCONTRI = 'epsContri';
    public const PARAMETER_EPSEPFCONTRI = 'epsepfContri';
    public const PARAMETER_TDSDEDU = 'tdsDedu';    

    
    /**
     * @var null|PayrollConfigurationService
     */
    protected ?PayrollConfigurationService $payrollconfigurationService = null;

    /**
     * @return PayrollConfigurationService
     */
    public function getPayrollConfigurationService(): PayrollConfigurationService
    {
        if (is_null($this->payrollconfigurationService)) {
            $this->payrollconfigurationService = new PayrollConfigurationService();
        }
        return $this->payrollconfigurationService;
    }

    /**
     * @param PayrollConfigurationService $payrollconfigurationService
     */
    public function setPayrollConfigurationService(PayrollConfigurationService $payrollconfigurationService): void
    {
        $this->payrollconfigurationService = $payrollconfigurationService;
    }

    /**
     * @OA\Get(
     *     path="/api/v2/admin/payrollconfiguration",
     *     tags={"Admin/PayrollConfiguration"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Admin-PayrollConfigurationModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     * @throws Exception
     */
    public function getOne(): EndpointResourceResult
    {
        $Payroll = $this->getPayrollConfigurationService()->getPayrollConfiguration();
        if (!$Payroll instanceof PayrollConfiguration) {
            $Payroll = new PayrollConfiguration();
            $Payroll->setPfEmployee(0);
            $Payroll->setPfEmployer(0);
            $Payroll->setEsicEmployee(0);
            $Payroll->setEsicEmployer(0);
            $Payroll->setGratuity(0);
            $Payroll->setMedical(0);
            $Payroll->setEpsContri(0);
            $Payroll->setEpsepfContri(0);
            $Payroll->setTdsDedu(0);            
        }

        return new EndpointResourceResult(PayrollConfigurationModel::class, $Payroll);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_ID
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function getAll(): EndpointCollectionResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResourceResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @OA\Put(
     *     path="/api/v2/admin/payrollconfiguration",
     *     tags={"Admin/PayrollConfiguration"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="pf_employee", type="float"),
            *     @OA\Property(property="pf_employer", type="float"),
            *     @OA\Property(property="esic_employee", type="float"),
            *     @OA\Property(property="esic_employer", type="float"),
            *     @OA\Property(property="gratuity", type="float"),
            *     @OA\Property(property="medical", type="float"),
            *     @OA\Property(property="eps_contri", type="float"),
            *     @OA\Property(property="epsepf_contri", type="float"),
            *     @OA\Property(property="tds_dedu", type="float"),
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Admin-PayrollConfigurationModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function update(): EndpointResourceResult
    {
        $Payroll = $this->savePayrollConfiguration();

        return new EndpointResourceResult(PayrollConfigurationModel::class, $Payroll);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_ID
            ),
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(
                    self::PARAMETER_PFEMPLOYEE,
                    //new Rule(Rules::FLOAT_TYPE),                    
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_PFEMPLOYER,
                    //new Rule(Rules::FLOAT_TYPE),                    
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ESICEMPLOYEE,
                    //new Rule(Rules::FLOAT_TYPE),                    
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_EPSCONTRI,
                    //new Rule(Rules::FLOAT_TYPE),                    
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_EPSEPFCONTRI,
                    //new Rule(Rules::FLOAT_TYPE),                    
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_TDSDEDU,
                    //new Rule(Rules::FLOAT_TYPE),                    
                ),
                true
            ),
            
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_MRDICAL,
                    //new Rule(Rules::FLOAT_TYPE),                                    
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ESICEMPLOYER,
                    //new Rule(Rules::FLOAT_TYPE),                                      
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_GRATUITY,
                    //new Rule(Rules::FLOAT_TYPE),                    
                ),
                true
            ),
        );
    }

    /**
     * @return PayrollConfiguration
     */
    public function savePayrollConfiguration(): PayrollConfiguration
    {
        $pfEmployee = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PFEMPLOYEE);
        $pfEmployer = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PFEMPLOYER);
        $esicEmployee = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY,self::PARAMETER_ESICEMPLOYEE
        );
        $esicEmployer = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ESICEMPLOYER);
        $gratuity = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_GRATUITY);
        $medical = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_MRDICAL);
        $epsContri = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_EPSCONTRI);
        $epsepfContri = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY,self::PARAMETER_EPSEPFCONTRI
        );
        $tdsDedu = $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_TDSDEDU);       
        

        $Payroll = $this->getPayrollConfigurationService()->getPayrollConfiguration();
        if ($Payroll == null) {
            $Payroll = new PayrollConfiguration();
        }
        $Payroll->setPfEmployee($pfEmployee);
        $Payroll->setPfEmployer($pfEmployer);
        $Payroll->setEsicEmployee($esicEmployee);
        $Payroll->setEsicEmployer($esicEmployer);
        $Payroll->setGratuity($gratuity);
        $Payroll->setMedical($medical);
        $Payroll->setEpsContri($epsContri);
        $Payroll->setEpsepfContri($epsepfContri);
        $Payroll->setTdsDedu($tdsDedu);        
        return $this->getPayrollConfigurationService()->savePayrollConfiguration($Payroll);
    }

    /**
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
        throw $this->getNotImplementedException();
    }
}
