<template>

  <div class="orangehrm-background-container">
    <compensatory-conflict v-if="showCompensatoryConflict" :workshift-exceeded="isWorkShiftExceeded"
      :data="compConflictData"></compensatory-conflict>
    <div class="orangehrm-card-container">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ $t('Apply Compensatory') }}
      </oxd-text>

      <oxd-divider />

      <oxd-form ref="formRef" :loading="isLoading" @submit-valid="onSave">
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
        <oxd-form-row>
          <date-input
          v-model="compensatory.applyDate"
          :label="$t('Work Date')" 
          :rules="rules.applyDate" required
           />
        </oxd-form-row>
      </oxd-grid>
      <oxd-grid :cols="2" class="orangehrm-full-width-grid">
        <oxd-form-row>
          <oxd-input-field v-model="compensatory.duration.type" type="select" :rules="rules.type"
            :options="durationOptions" :label="$t('Duration')" required />
        </oxd-form-row>
      </oxd-grid>
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
        <oxd-form-row>
          <oxd-input-field v-model="compensatory.comment" type="textarea" :label="$t('general.comments')"
            :rules="rules.comment" />
        </oxd-form-row>
      </oxd-grid>
      <oxd-divider />
          <oxd-form-actions>
            <required-text />
            <submit-button :label="$t('general.apply')" />
          </oxd-form-actions>
        
      </oxd-form>
    </div>
  </div>
</template>
  
<script>
import {
  required,
  validDateFormat,
  shouldNotExceedCharLength,
  startDateShouldBeBeforeEndDate,
} from '@/core/util/validation/rules';
import { yearRange } from '@ohrm/core/util/helper/year-range';
import { diffInDays } from '@ohrm/core/util/helper/datefns';
import { APIService } from '@ohrm/core/util/services/api.service';
import useForm from '@ohrm/core/util/composable/useForm';
import useDateFormat from '@/core/util/composable/useDateFormat';
import useCompOffValidators from '@/orangehrmLeavePlugin/util/composable/useCompoffValidators';
import CompensatoryConflict from '@/orangehrmLeavePlugin/components/CompensatoryConflict';
import useLocale from '@/core/util/composable/useLocale';

const compensatoryModel = {
  applyDate: null,
  comment: '',
  duration: {
    type: null,
  },
  lengthHours: null,
  lengthDays: null,
  expireDate: null,
  durationLabel: null,
};

export default {
  name: 'CompensatoryApply',

  components: {
    'compensatory-conflict': CompensatoryConflict,
  },

  props: {
    workShift: {
      type: Object,
      default: () => ({}),
    },
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/leave/compensatory-off',
    );
    const { validateOverlapCompoff } = useCompOffValidators(http);
    const { formRef, reset } = useForm();
    const { userDateFormat, isDateInFuture } = useDateFormat();

    return {
      http,
      reset,
      formRef,
      userDateFormat,
      validateOverlapCompoff,
    };
  },

  data() {
    return {
      selectedDate:null,
      isLoading: false,
      compensatory: { ...compensatoryModel },
      rules: {
        type: [required],
        applyDate: [required, validDateFormat(this.userDateFormat),
        ],

        comment: [shouldNotExceedCharLength(250)],
      },
      durationOptions: [
        { id: 1, label: 'Full Day', key: 'full_day' },
        { id: 2, label: 'Half Day', key: 'half_day' },
      ],
      showCompensatoryConflict: false,
      isWorkShiftExceeded: false,
      compConflictData: null,
    };
  },
 
  watch: {
    'compensatory.applyDate': function (applyDate) {
      const currentDate = new Date();
      const selectedDate = new Date(applyDate);
      if (!applyDate) {
        this.compensatory.expireDate = null;
        return;
      }
      if (selectedDate > currentDate) {
        this.$toast.warn({
          title: this.$t('general.warning'),
          message: this.$t('Select Valid Date'),
        });
      }

      // Calculate expire date as one month after selected date

      const expireDate = new Date(selectedDate);
      expireDate.setMonth(expireDate.getMonth() + 1);

      this.compensatory.expireDate = expireDate.toISOString().substr(0, 10);
    },
  },

  methods: {
    onSave() {
      this.isLoading = true;
      this.showCompensatoryConflict = false;
      this.compConflictData = null;
      //console.log(this.compensatory.applyDate)
      if (this.compensatory.duration.type.id === 1) {
        this.compensatory.lengthHours = 8.00
        this.compensatory.lengthDays = 1.000
        this.compensatory.durationLabel = this.compensatory.duration.type.key
      }
      else {
        this.compensatory.lengthHours = 4.00
        this.compensatory.lengthDays = 0.5000
        this.compensatory.durationLabel = this.compensatory.duration.type.key
      }
      this.validateOverlapCompoff(this.compensatory)
        .then(({ isConflict, isOverWorkshift, data }) => {
          if (isConflict) {
            this.compConflictData = data;
            this.showCompensatoryConflict = true;
            this.isWorkShiftExceeded = isOverWorkshift;
            return Promise.reject();
          }
          return this.http.create({
            applyDate: this.compensatory.applyDate,
            comment: this.compensatory.comment,
            duration: this.compensatory.duration.type.key,
            lengthHours: this.compensatory.lengthHours,
            lengthDays: this.compensatory.lengthDays,
            expireDate: this.compensatory.expireDate,
          });
        })
        .then(() => {
          this.$toast.saveSuccess();
          this.reset();
        })
        .catch(() => {
          this.showCompensatoryConflict &&
            this.$toast.warn({
              title: this.$t('general.warning'),
              message: this.$t('leave.failed_to_submit'),
            });
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>
  