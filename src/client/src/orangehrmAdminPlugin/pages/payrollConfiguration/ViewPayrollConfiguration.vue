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
      <div class="orangehrm-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title">
          {{ $t('admin.payroll_configuration') }}
        </oxd-text>
        <oxd-switch-input
          v-model="editable"
          :option-label="$t('general.edit')"
          label-position="left"
        />
      </div>
      <oxd-divider />

      <oxd-form :loading="isLoading" @submit-valid="onSave">
       
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="payrollconfiguration.pfEmployee"
                :label="$t('PF Employee')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="payrollconfiguration.pfEmployer"
                :label="$t('PF Employer')"                
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model.trim="payrollconfiguration.esicEmployee"
                :label="$t('Esic Employee')"                
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="payrollconfiguration.esicEmployer"
                :label="$t('Esic Employer')"               
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="payrollconfiguration.epsContri"
                :label="$t('Eps Contri')"                
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="payrollconfiguration.epsepfContri"
                :label="$t('Epsepf Contri')"                
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-divider />
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="payrollconfiguration.gratuity"
                :label="$t('Gratuity')"                
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="payrollconfiguration.medical"
                :label="$t('Medical')"                
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
            <oxd-divider />
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="payrollconfiguration.tdsDedu"
                :label="$t('Tds Deduction')"                
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>   

        <oxd-divider />

        <oxd-form-actions>
          <required-text />
          <submit-button v-if="editable" />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </div>
</template>

<script>
import {APIService} from '@ohrm/core/util/services/api.service';
import {
  required,
  shouldNotExceedCharLength,
  validEmailFormat,
  validPhoneNumberFormat,
} from '@ohrm/core/util/validation/rules';
import {OxdSwitchInput} from '@ohrm/oxd';

export default {
  components: {
    'oxd-switch-input': OxdSwitchInput,
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/admin/payrollconfiguration',
    );
    return {
      http,
    };
  },

  data() {
    return {
      editable: false,
      isLoading: false,
      payrollconfiguration: {
        pfEmployee: null,
        pfEmployer: null,
        esicEmployee: null,
        esicEmployer: null,
        epsContri: null,
        epsepfContri: null,
        gratuity: null,
        medical: null,
        tdsDedu: null,
      },
      errors: [],
    };
  },
  created() {
    this.isLoading = true;
    this.http.http
      .get('/api/v2/admin/payrollconfiguration')
      .then((response) => {
        console.log(response);
        const {data} = response.data;
        this.payrollconfiguration.pfEmployee = data.pfEmployee;
        this.payrollconfiguration.pfEmployer = data.pfEmployer;
        this.payrollconfiguration.esicEmployee = data.esicEmployee;
        this.payrollconfiguration.esicEmployer = data.esicEmployer;
        this.payrollconfiguration.epsContri = data.epsContri;
        this.payrollconfiguration.epsepfContri = data.epsepfContri;
        this.payrollconfiguration.gratuity = data.gratuity;
        this.payrollconfiguration.medical = data.medical;
        this.payrollconfiguration.tdsDedu = data.tdsDedu;
      })
      .finally(() => {
        this.isLoading = false;
      });
  },

  methods: {
    onSave() {
      this.isLoading = true;
      this.http.http
        .put('/api/v2/admin/payrollconfiguration', {
          pfEmployee: this.payrollconfiguration.pfEmployee,
          pfEmployer: this.payrollconfiguration.pfEmployer,
          esicEmployee: this.payrollconfiguration.esicEmployee,
          esicEmployer: this.payrollconfiguration.esicEmployer,
          epsContri: this.payrollconfiguration.epsContri,
          epsepfContri: this.payrollconfiguration.epsepfContri,
          gratuity: this.payrollconfiguration.gratuity,
          medical: this.payrollconfiguration.medical,
          tdsDedu: this.payrollconfiguration.tdsDedu,
        })
        .then(() => {
          return this.$toast.updateSuccess();
        })
        .then(() => {
          this.isLoading = false;
          this.editable = false;
        });
    },
  },
};
</script>

<style src="./payroll-configuration.scss" lang="scss" scoped></style>
