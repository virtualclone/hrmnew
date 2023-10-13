 <template>
  <oxd-input-field type="select" v-bind="$attrs" :label="$t('time.project')" :options="options" />
</template>

<script>
import { ref, onBeforeMount, computed,watch } from 'vue';
import { APIService } from '@ohrm/core/util/services/api.service';
export default {
  name: 'ProjectDropdown',
  inheritAttrs: false,
  props: {
      customerId: {
          required: true,
          default: null,
      }
  },

  setup(props) {
     // const customerId = ref([]);
      const options = ref([]);
      const http = new APIService(
          window.appGlobal.baseUrl,
          '/api/v2/time/projects',
      );
      
      watch(
      () => props.customerId,
      async (newCustomerId) => {
        if (newCustomerId !== null) {
          try {
            const response = await http.getAll({
              customerId: newCustomerId,
              // Other query parameters as needed
            });
            console.log(response);
          //   options.value = data.data.map((item) => {
          //         return {
          //             id: item.id,
          //             label: item.name,
          //         };
          //     }); // Update the projects array
          }
          catch (error) {
            console.error('Error fetching projects:', error);
          }
        }
      }
    );
    
      onBeforeMount(() => {
          http.getAll().then(({ data }) => {
              options.value = data.data.map((item) => {
                  return {
                      id: item.id,
                      label: item.name,
                  };
              });
          });
      });

  
             
  },

};
</script>
