<template>
    <oxd-input-field
    type="select"
    :label="$t('Work Week')"
    :options="options"
  />
  </template>
  
  <script>
  export default {
    data() {
      return {
        options: [
          { id: 1,label:'Mon-Fri', },
          { id:2,label: 'Mon-Sat' },
          // ... Add more workweek objects as needed
        ],
      };
    },
    // Other component options and methods
  };
  </script>
  