/* eslint-disable */
<template>
  <oxd-input-field
    type="select"
    :label="$t('general.work_shift')"
    :options="options"
  />
</template>

<script>
import {ref, onBeforeMount} from 'vue';
import {APIService} from '@ohrm/core/util/services/api.service';
export default {
  name: 'WorkshiftDropdown',
  setup() {
    const options = ref([]);
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/admin/work-shifts',
    );
    onBeforeMount(() => {
      http.getAll().then(({data}) => {
        options.value = data.data.map((item) => {
          return {
            id: item.id,
            label: item.name + " ("+item.startTime+ " - "+ item.endTime + ")",
            _indent: item.level ? item.level + 1 : 1,
          };
        });
      });
    });
    return {
      options,
    };    
  },
};
</script>
