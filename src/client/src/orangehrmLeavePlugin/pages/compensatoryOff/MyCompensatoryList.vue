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
import { computed, ref } from 'vue';
import { APIService } from '@/core/util/services/api.service';
import { navigate } from '@ohrm/core/util/helper/navigation';
import { truncate } from '@ohrm/core/util/helper/truncate';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import usei18n from '@/core/util/composable/usei18n';
import useDateFormat from '@/core/util/composable/useDateFormat';
import { formatDate, parseDate } from '@/core/util/helper/datefns';
import useLocale from '@/core/util/composable/useLocale';

const defaultFilters = {
 CompOffList: "MY",
};

export default {
  name: 'MyCompOffListTable',

  props: {
    CompOffList: {
      type: String,      
    },
           
  },

  setup(props) {
    const filters = ref({
      ...defaultFilters,
      ...(props.CompOffList && { CompOffList: props.CompOffList }),    
      
    });
    const { $t } = usei18n();
    const { locale } = useLocale();
    const { jsDateFormat, userDateFormat } = useDateFormat();    

    const serializedFilters = computed(() => {
      
      return {
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

        const apiExpDate = {
          date: item.expireDate.date,
          timezone_type: item.date.timezone_type,
          timezone: item.date.timezone
        };
        const partsExp = apiExpDate.date.split(" ");
        const datePartExp = partsExp[0];
        const [yearExp, monthExp, day1Exp] = datePartExp.split("-");

        // Create a new Date object with the parts

        const parsedDateExp = new Date(
          parseInt(yearExp),
          parseInt(monthExp) - 1, // Months are 0-based in JavaScript
          parseInt(day1Exp),
        );

        // Convert to a formatted string in the Indian timezone without time
 

        const formattedDate = `${year}-${month}-${day1}`;  //parsedDate.toLocaleDateString("en-IN", options);
        const formattedDateExp = `${yearExp}-${monthExp}-${day1Exp}`;  //parsedDate.toLocaleDateString("en-IN", options);


        const date = formattedDate
        const Expdate = formattedDateExp
        let duration = '';
        let leaveTaken='';
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

        if (item.leaveTaken == 0) {
          leaveTaken = 'No'
        }
        else {
          leaveTaken = 'Yes'
        }
        

        return {
          id: item.id,
          Expdate: Expdate,
          date: date,
          lengthHours: parseFloat(item.lengthHours).toFixed(2),
          duration: duration,
          status: status,
          comment: item.comments,
          leaveTaken: leaveTaken, 
        };
      });
    };

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
      filters,
     };
  },

  data() {
    return {
      headers: [
        { name: 'date', title: this.$t('Date'), style: { flex: 1 } },
        {
          name: 'Expdate',
          title: this.$t('Expiry Date'),
          style: { flex: 1 },
        },
        {
          name: 'lengthHours',
          title: this.$t('No of Hours'),
          style: { flex: 1 },
        },        
        {
          name: 'duration',
          title: this.$t('Duration'),
          style: { flex: 1 },
        },
        
        { name: 'status', title: this.$t('general.status'), style: { flex: 1 } },
        {
          name: 'comment',
          title: this.$t('general.comments'),
          style: { flex: '5%' },
        },
        {
          name: 'leaveTaken',
          title: this.$t('Availed'),
          style: { flex: '5%' },
        },
      
      ],
    };
  },


  methods: {
   
    async filterItems() {
      await this.execQuery();
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
  