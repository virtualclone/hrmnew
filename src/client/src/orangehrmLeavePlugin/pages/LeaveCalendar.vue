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
  <slot :filters="filters" :rules="rules" :filter-items="filterItems" :on-reset="onReset"></slot>
  <oxd-table-filter :filter-title="$t('leave.leave_list')">
    <oxd-form @submit-valid="filterItems" @reset="onReset">
      <oxd-form-row>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <date-input v-model="filters.fromDate" :label="$t('general.from_date')" :rules="rules.fromDate" />
          </oxd-grid-item>
          <oxd-grid-item>
            <date-input v-model="filters.toDate" :label="$t('general.to_date')" :rules="rules.toDate" />
          </oxd-grid-item>

        </oxd-grid>
      </oxd-form-row>
      <oxd-divider />

      <oxd-form-actions>
        <required-text />
        <oxd-button display-type="ghost" :label="$t('general.reset')" type="reset" />
        <oxd-button class="orangehrm-left-space" display-type="secondary" :label="$t('general.search')" type="submit" />
      </oxd-form-actions>
    </oxd-form>
  </oxd-table-filter>
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
</template>
    
  
<script>
import {
  required,
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
  fromDate: null,
  toDate: null,
};

export default {
  name: 'CompOffListTable',



  props: {

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

    };

    const serializedFilters = computed(() => {

      return {
        fromDate: filters.value.fromDate,
        toDate: filters.value.toDate,

      };
    });

    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/leave/leave-calendar`
    );

    const compOfflistNormalizer = (data) => {
      if (!data || !Array.isArray(data)) {
        // Handle the case where data is undefined or not an array
        return [];
      }

      return data[0].map((item) => {

        let arr;
        arr = item.Approve.split('HD:');
        if(arr[1] != null){
          arr[1] = "HD: "+arr[1];
        }

        let pen;
        pen = item.Pending.split('HD:');
        if(pen[1] != null){
          pen[1] = "HD: "+pen[1];
        }
        const weekday = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];


        const d = new Date(item.date);
        let day = weekday[d.getDay()];


        return {
          date: item.date,
          day: day,
          pendingHalfday: pen[1],
          approveHalfday: arr[1],
          pendingFullday: pen[0],
          approveFullday: arr[0],

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
          name: 'pendingHalfday',
          title: this.$t('Pending HalfDay'),
          style: { flex: 2 },
        },
        {
          name: 'approveHalfday',
          title: this.$t('Approval HalfDay'),
          style: { flex: 2 },
        },
        {
          name: 'pendingFullday',
          title: this.$t('Pending FullDay'),
          style: { flex: 2 },
        },
        {
          name: 'approveFullday',
          title: this.$t('Approval FullDay'),
          style: { flex: 2 },
        },

      ],
    };
  },


  methods: {


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
.orangehrm-leave-filter {
  display: flex;
  align-items: center;
  white-space: nowrap;

  &-text {
    font-size: $oxd-input-control-font-size;
    margin-right: 1rem;
  }
}
</style>
  