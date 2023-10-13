<template>
    <!-- <oxd-input-field type="select" v-bind="$attrs" :label="$t('time.project')" :options="options" /> -->
    <oxd-input-field type="select" :label="$t('Supervisor')" :options="options" />
</template>

<script>
import { ref, onBeforeMount, watch } from 'vue';
import { APIService } from '@ohrm/core/util/services/api.service';
export default {
    name: 'SupervisorDropdown',
    // inheritAttrs: false,
    props: {
        subunitId: {
            required: true,
            default: null,
        },
    },
    setup(props) {
        const options = ref([]);
        const http = new APIService(
            window.appGlobal.baseUrl,
            '/api/v2/pim/employees',
        );
        watch(
            () => props.subunitId,
            async (newsubunitId) => {
                if (newsubunitId !== null) {
                    http.getAll({
                        subunitId: newsubunitId,
                    }).then(({ data }) => {
                        options.value = data.data.map((employee) => {
                            return {
                                id: employee.empNumber,
                                label: `${employee.firstName} ${employee.middleName} ${employee.lastName}`,
                            };
                        });
                    });
                } 
            }
        );
        onBeforeMount(() => {
            http.getAll({ limit: 0 }).then(({ data }) => {
                options.value = data.data.map((employee) => {
                    return {
                        id: employee.empNumber,
                        label: `${employee.firstName} ${employee.middleName} ${employee.lastName}`,
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