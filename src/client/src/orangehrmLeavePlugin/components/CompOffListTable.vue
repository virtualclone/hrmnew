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
    <slot :filters="filters" :rules="rules" :filter-items="filterItems" :on-reset="onReset"></slot>
    <br />
    <div class="orangehrm-paper-container">
      <div class="orangehrm-container">
        <oxd-card-table :headers="headers" :items="items?.data" :clickable="false" :loading="isLoading"
          row-decorator="oxd-table-decorator-card" />
      </div>
      <div class="orangehrm-bottom-container">
        <oxd-pagination v-if="showPaginator" v-model:current="currentPage" :length="pages" />
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
import { computed, ref } from 'vue';
import { APIService } from '@/core/util/services/api.service';
import { navigate } from '@ohrm/core/util/helper/navigation';
import { truncate } from '@ohrm/core/util/helper/truncate';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import useLeaveActions from '@/orangehrmLeavePlugin/util/composable/useLeaveActions';
import usei18n from '@/core/util/composable/usei18n';
import useDateFormat from '@/core/util/composable/useDateFormat';
import { formatDate, parseDate } from '@/core/util/helper/datefns';
import useLocale from '@/core/util/composable/useLocale';

const defaultFilters = {
  employee: null,
  fromDate: null,
  toDate: null,
  CompOffList: "Approve",
};

export default {
  name: 'CompOffListTable',



  props: {
    CompOffList: {
      type: String,      
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
  },

  setup(props) {
    const filters = ref({
      ...defaultFilters,
      ...(props.fromDate && { fromDate: props.fromDate }),
      ...(props.toDate && { toDate: props.toDate }),
      ...(props.employee && {
        employee: {
          id: props.employee.empNumber,
          label: `${props.employee.firstName} ${props.employee.middleName} ${props.employee.lastName}`,
          isPastEmployee: props.employee.terminationId,          
        },
        ...(props.CompOffList && { CompOffList: props.CompOffList }),
      }),
    });
    const { $t } = usei18n();
    const { locale } = useLocale();
    const { jsDateFormat, userDateFormat } = useDateFormat();

    const rules = {
      fromDate: [required, validDateFormat(userDateFormat)],
      toDate: [
        required,
        validDateFormat(userDateFormat),
        endDateShouldBeAfterStartDate(
          () => filters.value.fromDate,
          $t('general.to_date_should_be_after_from_date'),
          { allowSameDate: true },
        ),
      ],
      employee: [validSelection],
    };

    const serializedFilters = computed(() => {
      
      return {
        employeeNum: filters.value.employee?.id,
        fromDate: filters.value.fromDate,
        toDate: filters.value.toDate,
        CompOffList: filters.value.CompOffList,
        
      };
    });

    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/leave/compensatory-off`
    );

    const compOfflistNormalizer = (data) => {
      if (!data || !Array.isArray(data)) {
        // Handle the case where data is undefined or not an array
        return [];
      }

      return data.map((item) => {
        const apiDate = {
          date: item.date.date,
          timezone_type: item.date.timezone_type,
          timezone: item.date.timezone
        };
        const parts = apiDate.date.split(" ");
        const datePart = parts[0];
        const [year, month, day1] = datePart.split("-");

        // Create a new Date object with the parts

        const parsedDate = new Date(
          parseInt(year),
          parseInt(month) - 1, // Months are 0-based in JavaScript
          parseInt(day1),
        );

        // Convert to a formatted string in the Indian timezone without time
 

        const formattedDate = `${year}-${month}-${day1}`;  //parsedDate.toLocaleDateString("en-IN", options);
        let duration = '';
        if (item.duration == "half_day") {
          duration = 'Half Day'
        }
        else{
          duration = 'Full Day'
        }
        

        
        if (item.status == 0) {
          status = 'Pending'
        }
        else if (item.status == 1) {
          status = 'Approve'
        }
        else {
          status = 'Reject'
        }

        const weekday = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];

        
        const d = new Date(item.date.date);
        let day = weekday[d.getDay()];
             

        const empName = `${item.employee?.firstName} ${item.employee?.middleName} ${item.employee?.lastName}`;


        if (item.employee?.terminationId) {
          empName + ` (${$t('general.past_employee')})`;
        }
        if (item.leaveType?.deleted) {
          leaveTypeName + ` (${$t('general.deleted')})`;
        }

        return {
          id: item.id,
          empNumber: item.employee?.empNumber,
          date: formattedDate,
          employeeName: empName,
          lengthHours: parseFloat(item.lengthHours).toFixed(2),
          duration: duration,
          status: status,
          comment: item.comments,
          actions: item.id,
          day: day,
        };
      });
    };

    const {
      leaveActions,
      processLeaveRequestAction,
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
      normalizer: compOfflistNormalizer,
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

    };
  },

  data() {
    return {
      headers: [
        { name: 'date', title: this.$t('Working Date'), style: { flex: 1 } },
        {
          name: 'day',
          title: this.$t('Working Day'),
          style: { flex: 1 },
        },
        {
          name: 'employeeName',
          title: this.$t('Employee Name'),
          style: { flex: 1 },
        },
        {
          name: 'lengthHours',
          title: this.$t('No of Hours'),
          style: { flex: 1 },
        },        
        {
          name: 'duration',
          title: this.$t('Days'),
          style: { flex: 1 },
        },
        
        { name: 'status', title: this.$t('general.status'), style: { flex: 1 } },
        {
          name: 'comment',
          title: this.$t('general.comments'),
          style: { flex: '5%' },
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


  methods: {
    cellRenderer(...[, , , row]) {
      const cellConfig = {};
      const { approve, reject, cancel } = this.leaveActions;

      approve.props.label = this.$t('general.approve');
      approve.props.onClick = () => this.onLeaveAction(row.id, 'APPROVE');
      cellConfig.approve = approve;

      reject.props.label = this.$t('general.reject');
      reject.props.onClick = () => this.onLeaveAction(row.id, 'REJECT');
      cellConfig.reject = reject;
      return {
        props: {
          header: {
            cellConfig,
          },
        },
      };
    },

    onLeaveAction(compoffId, actionType) {
      this.isLoading = true;
      this.http
      .update(compoffId,{
        action:actionType,
      })
    .then(() => {
          this.$toast.updateSuccess();
        })
        .finally(this.resetDataTable);
    },

    async resetDataTable() {
      this.checkedItems = [];
      await this.execQuery();
    },
    async filterItems() {
      await this.execQuery();
    },
    onReset() {
      this.filters = { ...defaultFilters };
      this.resetDataTable();
    },
  },
};
</script>
  
<style lang="scss" scoped>
::v-deep(.card-footer-slot) {
  .oxd-table-cell-actions {
    justify-content: flex-end;
  }

  .oxd-table-cell-actions>* {
    margin: 0 !important;
  }
}
</style>
  