/* eslint-disable */
<template>
  <oxd-input-field
    type="select"
    :label="$t('general.location')"
    :options="options"
  />
</template>

<script>
import {ref, onBeforeMount} from 'vue';
import {APIService} from '@ohrm/core/util/services/api.service';
export default {
  name: 'LocationDropdown',
  setup() {
    const options = ref([]);
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/admin/locations',
    );
    onBeforeMount(() => {
      http.getAll().then(({data}) => {
        options.value = data.data.map((item) => {
          return {
            id: item.id,
            label: item.name,
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
