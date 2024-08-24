<template>
  <v-skeleton-loader v-if="loading" type="card"></v-skeleton-loader>
  <v-container v-if="!loading && responseDates.length > 0">
    <v-row justify="center">
      <v-date-picker
        ref="datePicker"
        v-model="datePickerDate"
        max-width="400"
        @update:model-value="updateDatePickerDate"
        :min="new Date()"
        :allowed-dates="allowedDates"
        title="Datum auswählen"
        text="Datum eingeben"
      />
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import {useAppStore} from "../../stores/app";
import { ref, computed, onMounted } from "vue";
import {storeToRefs} from "pinia";
import {VDatePicker} from "vuetify/components";
import axios from "axios";

const { selectedDate, stepThreeValid, responseDates, raumNr, timeSlots, timeSlotsLoading, selectedTime } = storeToRefs(useAppStore());

const datePicker = ref<InstanceType<typeof VDatePicker>>();
const loading = ref(false);
const allowedDates = computed(() => {
  const dates = [];
  for(let i = 0; i < 90; i++) {
    const today = new Date();
    today.setDate(today.getDate() + i);
    if(!responseDates.value.includes(today.toISOString().split('T')[0])) dates.push(today.toISOString().split('T')[0]);
  }
  return dates;
});

const datePickerDate = ref(new Date(allowedDates.value[0]));
const updateDatePickerDate = (event: Date) => {
  //add 2 hours since "toISOString" only handles in UTC dates (2 hours diff to GMT +2)
  timeSlotsLoading.value = true;
  selectedTime.value = '';
  const requestDate = new Date(event.setTime(event.getTime() + (2*60*60*1000)));
  selectedDate.value = requestDate.toISOString().split('T')[0];
  axios.post("https://www.zahnarzt-eferding.at/wp-admin/admin-ajax.php",
    {
      'action': 'dayInfo',
      'date':  selectedDate.value,
      'terminType': raumNr.value
    },
    {
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      }
    }).then(function(response) {
    timeSlots.value = response.data;
    stepThreeValid.value = true;
    timeSlotsLoading.value = false;
  }).catch(function (error) {
    console.error(error);
    timeSlotsLoading.value = false;
  });
}

const setNewDates = () => {
  datePickerDate.value = new Date(allowedDates.value[0]);
  updateDatePickerDate(datePickerDate.value);
}

onMounted(() => {
  if (allowedDates.value[0]) updateDatePickerDate(new Date(allowedDates.value[0]));
})
defineExpose({setNewDates});
</script>
<style scoped>
@media screen and (max-width: 1000px) {
  :deep(.v-date-picker-month) {
    justify-content: start;
  }
  :deep(.v-date-picker-controls) {
    display: block;
  }
  :deep(.v-date-picker-controls__month) {
    margin: auto;
    max-width: 100px;
  }
}
</style>
