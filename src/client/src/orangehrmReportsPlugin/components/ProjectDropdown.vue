<template>
    <!-- <oxd-input-field type="select" v-bind="$attrs" :label="$t('time.project')" :options="options" /> -->
    <oxd-input-field type="select" :label="$t('time.project')" :options="options" />
</template>

<script>
import { ref, onBeforeMount, watch } from 'vue';
import { APIService } from '@ohrm/core/util/services/api.service';
export default {
    name: 'ProjectDropdown',
   // inheritAttrs: false,
    props: {
        customerId: {
            required: true,
            default: null,
        },
    },
    setup(props) {
        const options = ref([]);
        const http = new APIService(
            window.appGlobal.baseUrl,
            '/api/v2/time/projects',
        );

        watch(
            () => props.customerId,
            async (newCustomerId) => {
                if (newCustomerId !== null) {
                    http.getAll({
                        customerId: newCustomerId,
                        status: 1,
                    }).then(({ data }) => {
                        options.value = data.data.map((project) => ({
                            id: project.id,
                            label: project.name,
                        }));
                       // console.log(options);
                    });
                    // console.log("after if",this.options)
                }
            }
        );
        onBeforeMount(() => {
            http.getAll({ status: 1, limit: 0 }).then(({ data }) => {
                options.value = data.data.map((project) => {
                    return {
                        id: project.id,
                        label: project.name,
                    };
                });
            });
        });
        return {
            options
        }
    },

}
</script>