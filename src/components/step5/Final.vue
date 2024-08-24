<template>
  <v-card flat>
    <v-card-title>Termin am {{ new Date(selectedDate).toLocaleDateString() }} um {{ selectedTime }}</v-card-title>
    <v-card-subtitle class="mb-3">Finalisieren Sie Ihren Termin für eine {{selectedAppointmentName}} in der Ordination Dr. Letsch</v-card-subtitle>
    <final-form ref="finalForm"/>
  </v-card>
</template>

<script lang="ts" setup>
import FinalForm from "./FinalForm.vue";
import {storeToRefs} from "pinia";
import {useAppStore} from "../../stores/app";
const { selectedDate, selectedTime, selectedAppointmentName } = storeToRefs(useAppStore());

</script>
<style lang="scss" scoped>
:deep(.v-card-title), :deep(.v-card-subtitle) {
  white-space: normal !important;
}
</style>

