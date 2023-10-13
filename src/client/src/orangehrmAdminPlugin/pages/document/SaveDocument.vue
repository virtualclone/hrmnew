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
    <div class="orangehrm-card-container">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ $t('admin.add_document') }}
      </oxd-text>

      <oxd-divider />

      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-form-row>
          <oxd-input-field
            v-model="document.name"
            :label="$t('general.name')"
            :rules="rules.name"
            required
          />
          <oxd-input-field
          v-model="document.isrequire"
          type="checkbox"
          value="true"
          :label="$t('general.required')"
          :true-value="true"
          :false-value="false"
          /> 
          <oxd-input-field
          v-model="document.users"
          type="checkbox"
          value="true"
          :label="$t('general.visible_users')"
          :true-value="true"
          :false-value="false"
          />  
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-actions>
          <required-text />
          <oxd-button
            type="button"
            display-type="ghost"
            :label="$t('general.cancel')"
            @click="onCancel"
          />
          <submit-button />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </div>
</template>

<script>
import {navigate} from '@ohrm/core/util/helper/navigation';
import {APIService} from '@ohrm/core/util/services/api.service';
import {
  required,
  shouldNotExceedCharLength,
} from '@ohrm/core/util/validation/rules';

export default {
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/admin/documents',
    );
    return {
      http,
    };
  },
  data() {
    return {
      isLoading: false,
      document: {
        id: '',
        name: '',
        isrequire: false,
        users: false,
        deleted: false,
      },
      rules: {
        name: [required, shouldNotExceedCharLength(100)],
      },
    };
  },

  created() {
    this.isLoading = true;
    this.http
      .getAll({
        limit: 0,
      })
      .then((response) => {
        const {data} = response.data;
        this.rules.name.push((v) => {
          const index = data.findIndex(
            (item) =>
              String(item.name).toLowerCase() == String(v).toLowerCase(),
          );
          return index === -1 || this.$t('general.already_exists');
        });
      })
      .finally(() => {
        this.isLoading = false;
      });
  },

  methods: {
    onSave() {
      this.isLoading = true;
      this.http
        .create({
          name: this.document.name,
          isrequire: this.document.isrequire,
          users: this.document.users,
          deleted: false,
        })
        .then(() => {
          return this.$toast.saveSuccess();
        })
        .then(() => {
          this.onCancel();
        });
    },
    onCancel() {
      navigate('/admin/document');
    },
  },
};
</script>
