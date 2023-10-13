<!--
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
 -->

 <template>
    <div class="orangehrm-background-container">
      <slot
        :filters="filters"
        :rules="rules"
        :filter-items="filterItems"
        :on-reset="onReset"
      ></slot>
      <br />
      <div class="orangehrm-paper-container">
        <div class="orangehrm-container">
          <oxd-card-table
            :headers="headers"
            :items="items?.data"
            :clickable="false"
            :loading="isLoading"
            row-decorator="oxd-table-decorator-card"
          />
        </div>
        <div class="orangehrm-bottom-container">
          <oxd-pagination
            v-if="showPaginator"
            v-model:current="currentPage"
            :length="pages"
          />
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import {
    required,
    validSelection,
    validDateFormat,
    endDateShouldBeAfterStartDate,
  } from '@/core/util/validation/rules';
  import {computed, ref} from 'vue';
  import {APIService} from '@/core/util/services/api.service';
  import {navigate} from '@ohrm/core/util/helper/navigation';
  import {truncate} from '@ohrm/core/util/helper/truncate';
  import usePaginate from '@ohrm/core/util/composable/usePaginate';
  import useLeaveActions from '@/orangehrmLeavePlugin/util/composable/useLeaveActions';
  import usei18n from '@/core/util/composable/usei18n';
  import useDateFormat from '@/core/util/composable/useDateFormat';
  import {formatDate, parseDate} from '@/core/util/helper/datefns';
  import useLocale from '@/core/util/composable/useLocale';
  
  const defaultFilters = {
    employee: null,
    fromDate: null,
    toDate: null,
  };
  
  export default {
    name: 'CompOffListTable',
  
   
  
    props: {
      myLeaveList: {
        type: Boolean,
        default: false,
      },
      leaveStatuses: {
        type: Array,
        default: () => [],
      },
      employee: {
        type: Object,
        required: false,
        default: () => null,
      },
      leaveType: {
        type: Object,
        required: false,
        default: () => null,
      },
      fromDate: {
        type: String,
        required: false,
        default: null,
      },
      toDate: {
        type: String,
        required: false,
        default: null,
      },
      leaveStatus: {
        type: Object,
        required: false,
        default: () => null,
      },
    },
  
    setup(props) {
      const filters = ref({
        ...defaultFilters,
        ...(props.fromDate && {fromDate: props.fromDate}),
        ...(props.toDate && {toDate: props.toDate}),
        ...(props.leaveStatus && {statuses: [props.leaveStatus]}),
        ...(props.employee && {
          employee: {
            id: props.employee.empNumber,
            label: `${props.employee.firstName} ${props.employee.middleName} ${props.employee.lastName}`,
            isPastEmployee: props.employee.terminationId,
          },
        }),
      });
      const {$t} = usei18n();
      const {locale} = useLocale();
      const {jsDateFormat, userDateFormat} = useDateFormat();
  
      const rules = {
        fromDate: [required, validDateFormat(userDateFormat)],
        toDate: [
          required,
          validDateFormat(userDateFormat),
          endDateShouldBeAfterStartDate(
            () => filters.value.fromDate,
            $t('general.to_date_should_be_after_from_date'),
            {allowSameDate: true},
          ),
        ],
        statuses: [required],
        employee: [validSelection],
      };
  
      const serializedFilters = computed(() => {
        const statuses = Array.isArray(filters.value.statuses)
          ? filters.value.statuses
          : [];
  
        return {
          empNumber: filters.value.employee?.id,
          fromDate: filters.value.fromDate,
          toDate: filters.value.toDate,
          subunitId: filters.value.subunit?.id,
        //   includeEmployees: filters.value.includePastEmps
        //     ? 'currentAndPast'
        //     : 'onlyCurrent',
          statuses: statuses.map((item) => item.id),
          leaveTypeId: filters.value.leaveType?.id,
        };
      });
      console.log(serializedFilters);
      const http = new APIService(
        window.appGlobal.baseUrl,
        `/api/v2/leave/compensatory-off`
      );
  
      const leavelistNormalizer = (data) => {
        return data.map((item) => {
          let leaveDatePeriod,
            leaveStatuses,
            leaveBalances = '';
          const duration = item.dates.durationType?.type;
  
          if (item.dates.fromDate) {
            leaveDatePeriod = formatDate(
              parseDate(item.dates.fromDate),
              jsDateFormat,
              {locale},
            );
          }
          if (item.dates.toDate) {
            leaveDatePeriod += ` to ${formatDate(
              parseDate(item.dates.toDate),
              jsDateFormat,
              {locale},
            )}`;
          }
          if (item.dates.startTime && item.dates.endTime) {
            leaveDatePeriod += ` (${item.dates.startTime} - ${item.dates.endTime})`;
          }
          if (
            duration === 'half_day_morning' ||
            duration === 'half_day_afternoon'
          ) {
            leaveDatePeriod += ` ${$t('leave.half_day')}`;
          }
          if (Array.isArray(item.leaveBreakdown)) {
            leaveStatuses = item.leaveBreakdown
              .map(
                (status) =>
                  `${status.name} (${parseFloat(status.lengthDays).toFixed(2)})`,
              )
              .join(', ');
          }
          if (Array.isArray(item.leaveBalances)) {
            if (item.leaveBalances.length > 1) {
              leaveBalances = item.leaveBalances
                .map(({period, balance}) => {
                  const _balance = parseFloat(balance.balance).toFixed(2);
                  const startDate = formatDate(
                    parseDate(period.startDate),
                    jsDateFormat,
                    {locale},
                  );
                  const endDate = formatDate(
                    parseDate(period.endDate),
                    jsDateFormat,
                    {locale},
                  );
                  return `${_balance} (${startDate} - ${endDate})`;
                })
                .join(', ');
            } else {
              const balance = item.leaveBalances[0]?.balance.balance;
              leaveBalances = balance ? parseFloat(balance).toFixed(2) : '0.00';
            }
          }
  
          const empName = `${item.employee?.firstName} ${item.employee?.middleName} ${item.employee?.lastName}`;
          const leaveTypeName = item.leaveType?.name;
  
          if (item.employee?.terminationId) {
            empName + ` (${$t('general.past_employee')})`;
          }
          if (item.leaveType?.deleted) {
            leaveTypeName + ` (${$t('general.deleted')})`;
          }
  
          return {
            id: item.id,
            empNumber: item.employee?.empNumber,
            date: leaveDatePeriod,
            employeeName: empName,
            leaveType: leaveTypeName,
            leaveBalance: leaveBalances,
            days: parseFloat(item.noOfDays).toFixed(2),
            status: leaveStatuses,
            comment: truncate(item.lastComment?.comment),
            actions: item.allowedActions,
          };
        });
      };
  
      const {
        leaveActions,
        processLeaveRequestAction,
        processLeaveRequestBulkAction,
      } = useLeaveActions(http);
  
      const {
        showPaginator,
        currentPage,
        total,
        pages,
        pageSize,
        response,
        isLoading,
        execQuery,
      } = usePaginate(http, {
        query: serializedFilters,
        //normalizer: leavelistNormalizer,
      });

  
      return {
        http,
        showPaginator,
        currentPage,
        isLoading,
        total,
        pages,
        pageSize,
        execQuery,
        items: response,
        rules,
        filters,
        leaveActions,
        processLeaveRequestAction,
        processLeaveRequestBulkAction,
      };
    },
  
    data() {
      return {
        headers: [
          {name: 'date', title: this.$t('general.date'), style: {flex: 1}},
          {
            name: 'employeeName',
            title: this.$t('general.employee_name'),
            style: {flex: 1},
          },
          {
            name: 'leaveType',
            title: this.$t('leave.leave_type'),
            style: {flex: 1},
          },
          {
            name: 'leaveBalance',
            title: this.$t('leave.leave_balance_days'),
            style: {flex: 1},
          },
          {
            name: 'days',
            title: this.$t('leave.number_of_days'),
            style: {flex: 1},
          },
          {name: 'status', title: this.$t('general.status'), style: {flex: 1}},
          {
            name: 'comment',
            title: this.$t('general.comments'),
            style: {flex: '5%'},
          },
          {
            name: 'action',
            slot: 'footer',
            title: this.$t('general.actions'),
            cellType: 'oxd-table-cell-actions',
            cellRenderer: this.cellRenderer,
            style: {
              flex: this.myLeaveList ? '10%' : '20%',
            },
          },
        ],
      };
    },
  
    beforeMount() {
      this.isLoading = true;
      if (this.filters.statuses.length === 0) {
        this.filters.statuses = this.myLeaveList
          ? this.leaveStatuses
          : this.leaveStatuses.filter((status) => status.id === 1);
      }
      this.http
        .request({method: 'GET', url: '/api/v2/leave/leave-periods'})
        .then((response) => {
            console.log(response);
          const {data, meta} = response.data;
          if (meta.leavePeriodDefined) {
            this.filters.fromDate =
              this.filters.fromDate ?? meta?.currentLeavePeriod.startDate;
            this.filters.toDate =
              this.filters.toDate ?? meta?.currentLeavePeriod.endDate;
          } else {
            this.filters.fromDate = this.filters.fromDate ?? data[0]?.startDate;
            this.filters.toDate = this.filters.toDate ?? data[0]?.endDate;
          }
        })
        .finally(() => {
          this.isLoading = false;
          Object.assign(defaultFilters, this.filters);
        });
    },
  
    methods: {
      cellRenderer(...[, , , row]) {
        const cellConfig = {};
        const {approve, reject, cancel, more} = this.leaveActions;
        const dropdownActions = [
          {label: this.$t('general.add_comment'), context: 'add_comment'},
          {label: this.$t('leave.view_leave_details'), context: 'leave_details'},
          {label: this.$t('leave.view_pim_info'), context: 'pim_details'},
        ];
  
        row.actions.map((item) => {
          if (item.action === 'APPROVE') {
            approve.props.label = this.$t('general.approve');
            approve.props.onClick = () => this.onLeaveAction(row.id, 'APPROVE');
            cellConfig.approve = approve;
          }
          if (item.action === 'REJECT') {
            reject.props.label = this.$t('general.reject');
            reject.props.onClick = () => this.onLeaveAction(row.id, 'REJECT');
            cellConfig.reject = reject;
          }
          if (item.action === 'CANCEL') {
            if (this.myLeaveList) {
              cancel.props.label = this.$t('general.cancel');
              cancel.props.onClick = () => this.onLeaveAction(row.id, 'CANCEL');
              cellConfig.reject = cancel;
            } else {
              dropdownActions.push({
                label: this.$t('leave.cancel_leave'),
                context: 'cancel_leave',
              });
            }
          }
        });
  
        more.props.options = dropdownActions;
        more.props.onClick = ($event) => this.onLeaveDropdownAction($event, row);
        cellConfig.more = more;
  
        return {
          props: {
            header: {
              cellConfig,
            },
          },
        };
      },
      onLeaveDropdownAction(event, item) {
        switch (event.context) {
          case 'add_comment':
            this.commentModalState = item.id;
            this.showCommentModal = true;
            break;
          case 'cancel_leave':
            this.onLeaveAction(item.id, 'CANCEL');
            break;
          case 'pim_details':
            navigate('/pim/viewPersonalDetails/empNumber/{id}', {
              id: item.empNumber,
            });
            break;
          default:
            navigate(
              '/leave/viewLeaveRequest/{id}',
              {id: item.id},
              this.myLeaveList && {mode: 'my-leave'},
            );
        }
      },

      
    },
  };
  </script>
  
  <style lang="scss" scoped>
  ::v-deep(.card-footer-slot) {
    .oxd-table-cell-actions {
      justify-content: flex-end;
    }
    .oxd-table-cell-actions > * {
      margin: 0 !important;
    }
  }
  </style>
  