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

    <oxd-table-filter :filter-title="$t('Customers')">
      <oxd-form @submit-valid="filterItems" @reset="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <customer-autocomplete
                v-model="filters.customer"
                :rules="rules.customer"
              />
            </oxd-grid-item>
            
            <oxd-grid-item>
              <include-status-dropdown v-model="filters.active"></include-status-dropdown>
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-actions>
          <oxd-button
            type="reset"
            display-type="ghost"
            :label="$t('general.reset')"
          />
          <submit-button :label="$t('general.search')" />
        </oxd-form-actions>
      </oxd-form>
    </oxd-table-filter>
    <br />
    <div class="orangehrm-paper-container">
      <div class="orangehrm-header-container">
         <div>
          <oxd-button
            :label="$t('general.add')"
            icon-name="plus"
            display-type="secondary"
            @click="onClickAdd"
          />
        </div>
      </div>
      <table-header
        :selected="checkedItems.length"
        :total="total"
        :loading="isLoading"
        @delete="onClickDeleteSelected"
      ></table-header>
      <div class="orangehrm-container">
        <oxd-card-table
          v-model:selected="checkedItems"
          v-model:order="sortDefinition"
          :loading="isLoading"
          :headers="headers"
          :items="items?.data"
          :selectable="true"
          :clickable="false"
          row-decorator="oxd-table-decorator-card"
        />
      </div>
      <div class="orangehrm-bottom-container">
        <oxd-pagination
          v-if="showPaginator"
          v-model:current="currentPage"
          :length="pages"
        />
      </div>
    </div>

    <delete-confirmation ref="deleteDialog"></delete-confirmation>
  </div>
</template>

<script>
import {computed, ref} from 'vue';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog.vue';
import {navigate} from '@ohrm/core/util/helper/navigation';
import {APIService} from '@ohrm/core/util/services/api.service';
import useSort from '@ohrm/core/util/composable/useSort';
import {validSelection} from '@/core/util/validation/rules';
import CustomerAutocomplete from '@/orangehrmTimePlugin/components/CustomerAutocomplete.vue';
import IncludeStatusDropdown from '@/core/components/dropdown/IncludeStatusDropdown';


const defaultSortOrder = {
  'customer.name': 'ASC',
  'customer.activestatus': 'true',
};

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'customer-autocomplete': CustomerAutocomplete,
    'include-status-dropdown': IncludeStatusDropdown,
  },
  props: {
    unselectableIds: {
      type: Array,
      default: () => [],
    },
  },
  setup(props) {
    const customerNormalizer = (data) => {
      return data.map((item) => {
        const selectable = props.unselectableIds.findIndex(
          (id) => id == item.id,
        );
        let status='';
        if(item.active == 0)
          {
            status = 'No'
          }else{
            status = 'Yes'
          }
        
        return {
          id: item.id,
          name: item.name,
          description: item.description,
          clientemail: item.emailto,
          mhrslimit: item.mhrslimit,
          status: status,          
          isSelectable: selectable === -1,
        };
      });
    };

    const filters = ref({
      
      customer: null,
      active: {
        id: 1,
        param: 'Active',
        label: 'Active',
      },
    });
    const {sortDefinition, sortField, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });
    const serializedFilters = computed(() => {
      return {
        customerId: filters.value.customer?.id,
        status: filters.value.active?.param,
        sortField: sortField.value,
        sortOrder: sortOrder.value,
      };
    });
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/time/customers',
    );
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
      normalizer: customerNormalizer,
    });
    onSort(execQuery);
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
      sortDefinition,
    };
  },
  data() {
    return {
      customerStatuses: [
        {id: 1, label: this.$t('Active')},
        {id: 2, label: this.$t('InActive')},
        {id: 3, label: this.$t('All')},
      ],
      checkedItems: [],
      rules: {        
        customer: [validSelection],        
      },
    };
  },

  computed: { headers() {
      const headers = [
        {
          name: 'name',
          slot: 'title',
          title: this.$t('general.name'),
          sortField: 'customer.name',
          style: {flex: 2},
        },
        {
          name: 'description',
          title: this.$t('general.description'),
          style: {flex: 4},
        },
        {
          name: 'clientemail',
          title: this.$t('Client Email'),
          style: {flex: 3},
        },
        {
          name: 'mhrslimit',
          title: this.$t('Monthly Hrs'),
          style: {flex: 2},
        },
        {
          name: 'status',
          title: this.$t('Active'),
          style: {flex: 2},
        },
        {
        name: 'actions',
          title: this.$t('general.actions'),
          slot: 'action',
          style: {flex: 1},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            delete: {
              onClick: this.onClickDelete,
              component: 'oxd-icon-button',
              props: {
                name: 'trash',
              },
            },
            edit: {
              onClick: this.onClickEdit,
              props: {
                name: 'pencil-fill',
              },
            },
          },
        }
      ];
        
        return headers;
    },
  },
  methods: {
    onClickAdd() {
      navigate('/time/addCustomer');
    },
    onClickEdit(item) {
      navigate('/time/addCustomer/{id}', {id: item.id});
    },
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.items?.data[index].id;
      });
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(ids);
        }
      });
    },
    onClickDelete(item) {
      const isSelectable = this.unselectableIds.findIndex(
        (id) => id == item.id,
      );
      if (isSelectable > -1) {
        return this.$toast.error({
          title: this.$t('general.error'),
          message: this.$t(
            'time.not_allowed_to_delete_customer_who_have_time_logged_against',
          ),
        });
      }
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems([item.id]);
        }
      });
    },
    deleteItems(items) {
      if (items instanceof Array) {
        this.isLoading = true;
        this.http
          .deleteAll({
            ids: items,
          })
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            this.isLoading = false;
            this.resetDataTable();
          });
      }
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.execQuery();
    },
    async filterItems() {
      await this.execQuery();
    },
  },
};
</script>
