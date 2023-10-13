<template>
    <!-- <oxd-input-field type="select" v-bind="$attrs" :label="$t('time.project')" :options="options" /> -->
    <oxd-input-field type="select" :label="$t('general.employee')" :options="options" />
</template>

<script>
import { ref, onBeforeMount, watch } from 'vue';
import { APIService } from '@ohrm/core/util/services/api.service';
export default {
    name: 'EmployeeDropdown',
    // inheritAttrs: false,
    props: {
        projectId: {
            required: true,
            default: null,
        },
    },
    setup(props) {
        const options = ref([]);
        const http = new APIService(
            window.appGlobal.baseUrl, '/api/v2/pim/employees',);
        // '/api/v2/time/project-admins'
        const myArray = [8, 301, 42, 13, 4];
        watch(
            () => props.projectId,
            async (newProjectId) => {
                if (newProjectId !== null) {
                    http
                        .request({
                            method: 'GET',
                            url: `/api/v2/time/project-admins/${newProjectId}`,
                            params: { projectId: newProjectId },
                        }).then(({ data }) => {
                            console.log(data, 'received data');
                            // options.value = data.data.map((employee) => ({
                            //     id: employee.empNumber,
                            //     label: `${employee.firstName} ${employee.middleName} ${employee.lastName}`,
                            // }));
                            // console.log(options);
                        });
                    // console.log("after if",this.options)
                }
            }
        );
        onBeforeMount(() => {
            // Assuming myArray is an array of empNumbers like [8, 15, 23]
            const requests = myArray.map((empNumber) => {
                return http.getAll({ empNumber, limit: 0 }).then(({ data }) => {
                    return data.data.map((employee) => ({
                        id: employee.empNumber,
                        label: `${employee.firstName} ${employee.middleName} ${employee.lastName}`,
                    }));
                });
            });

            // Use Promise.all to wait for all requests to complete
            Promise.all(requests)
                .then((results) => {
                    // Merge the results from all requests into a single options array
                    options.value = [].concat(...results);
                })
                .catch((error) => {
                    // Handle errors here
                    console.error(error);
                });
        });
        // onBeforeMount(() => {
        //     http.getAll({ empNumber:myArray, limit: 0 }).then(({ data }) => {
        //         options.value = data.data.map((employee) => {
        //             return {
        //                 id: employee.empNumber,
        //                 label: `${employee.firstName} ${employee.middleName} ${employee.lastName}`,
        //             };
        //         });
        //     });
        // });
        return {
            options
        }
    },

}
</script>