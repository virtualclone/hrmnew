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
  <div class="orangehrm-paper-container">
    <div class="orangehrm-header-container">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ header }}
      </oxd-text>
    </div>
    <!-- <table-header :loading="false" :selected="0" :total="data.length"></table-header> -->
    <div class="orangehrm-container">
      <oxd-card-table :headers="headers" :items="items" :clickable="false" row-decorator="oxd-table-decorator-card" />
    </div>
    <div class="orangehrm-bottom-container"></div>
  </div>
  <br />
</template>
  
<script>
import useDateFormat from '@/core/util/composable/useDateFormat';
import { formatDate, parseDate } from '@/core/util/helper/datefns';
import useLocale from '@/core/util/composable/useLocale';

export default {
  name: 'CompensatoryConflict',
  props: {
    workshiftExceeded: {
      type: Boolean,
      default: false,
    },
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    const { jsDateFormat } = useDateFormat();
    const { locale } = useLocale();

    return {
      locale,
      jsDateFormat,
    };
  },
  data() {
    return {
      headers: [
        {
          name: 'date',
          title: this.$t('general.date'),
          style: { flex: 1 },
        },
        {
          name: 'lengthHours',
          title: this.$t('leave.no_of_hours'),
          style: { flex: 1 },
        },
        {
          name: 'duration',
          title: this.$t('Duration'),
          style: { flex: 1 },
        },
        {
          name: 'status',
          title: this.$t('general.status'),
          style: { flex: 1 },
        },
        {
          name: 'comments',
          title: this.$t('general.comments'),
          style: { flex: 1 },
        },
      ],
    };
  },

  computed: {
    header() {
      return this.workshiftExceeded
        ? this.$t(
          'leave.workshift_length_exceeded_due_to_the_following_leave_request',
        )
        : this.$t('Compensatory off is already applied. See the status below:');
    },

    items() {
      
        if (this.data.status == 0) {
          this.data.status = 'Pending';
        }
        if (this.data.status == 1) {
          this.data.status = 'Approved';
        }
        if (this.data.status == 2) {
          this.data.status = 'Reject';
        }

        const apiDate = {
          date: this.data.date.date,
          timezone_type: this.data.date.timezone_type,
          timezone: this.data.date.timezone
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
        const formattedDate = `${year}-${month}-${day1}`;
        // Convert to a formatted string in the Indian timezone without time
        const formattedItem = {
         // this.data.date.date ? new Date(this.data.date.date).toISOString().split('T')[0] : null,
          date: formattedDate,
          lengthHours: parseFloat(this.data.lengthHours).toFixed(2),
          duration: this.data.duration,
          status: this.data.status,
          comments: this.data.comments,
        };
        return [formattedItem];
     
    },

  },
};
</script>
  