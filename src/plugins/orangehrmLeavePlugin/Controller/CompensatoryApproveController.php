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

namespace OrangeHRM\Leave\Controller;

use OrangeHRM\Admin\Service\CompanyStructureService;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Controller\Common\NoRecordsFoundController;
use OrangeHRM\Core\Controller\Exception\RequestForwardableException;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Entity\Leave;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\I18N\Traits\Service\I18NHelperTrait;
use OrangeHRM\Leave\Traits\Service\LeaveRequestServiceTrait;
use OrangeHRM\Leave\Traits\Service\LeaveTypeServiceTrait;
use OrangeHRM\Pim\Traits\Service\EmployeeServiceTrait;

class CompensatoryApproveController extends AbstractVueController
{
    use LeaveRequestServiceTrait;
    use UserRoleManagerTrait;
    use EmployeeServiceTrait;
    use LeaveTypeServiceTrait;
    use I18NHelperTrait;

    public const LEAVE_STATUSES = [
        ['id' => Leave::LEAVE_STATUS_LEAVE_REJECTED, 'label' => 'Rejected'],
        ['id' => Leave::LEAVE_STATUS_LEAVE_CANCELLED, 'label' => 'Cancelled'],
        ['id' => Leave::LEAVE_STATUS_LEAVE_PENDING_APPROVAL, 'label' => 'Pending Approval'],
        ['id' => Leave::LEAVE_STATUS_LEAVE_APPROVED, 'label' => 'Scheduled'],
        ['id' => Leave::LEAVE_STATUS_LEAVE_TAKEN, 'label' => 'Taken'],
    ];

    protected ?CompanyStructureService $companyStructureService = null;

    /**
     * @return CompanyStructureService
     */
    protected function getCompanyStructureService(): CompanyStructureService
    {
        if (!$this->companyStructureService instanceof CompanyStructureService) {
            $this->companyStructureService = new CompanyStructureService();
        }
        return $this->companyStructureService;
    }

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('approve-compensatory');
        $empNumber = $request->query->get('empNumber');
        if (!is_null($empNumber)) {
            if (!$this->getUserRoleManagerHelper()->isEmployeeAccessible($empNumber)) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }

            $component->addProp(
                new Prop(
                    'employee',
                    Prop::TYPE_OBJECT,
                    $this->getEmployeeService()->getEmployeeAsArray($empNumber)
                )
            );
        }
        $this->addFromToDateProps($request, $component);

        $this->setComponent($component);
    }

    /**
     * @param Request $request
     * @param Component $component
     */
    protected function addFromToDateProps(Request $request, Component $component): void
    {
        $fromDate = $request->query->get('fromDate');
        $toDate = $request->query->get('toDate');
        if ($fromDate && $toDate) {
            $component->addProp(new Prop('from-date', Prop::TYPE_STRING, $fromDate));
            $component->addProp(new Prop('to-date', Prop::TYPE_STRING, $toDate));
        }
    }
}
