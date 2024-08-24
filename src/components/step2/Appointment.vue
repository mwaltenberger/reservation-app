<template>
  <v-col cols="12" class="px-0">
    <v-card
      class="m-5 appointment"
      :subtitle="subTitle"
      :title="title"
      :variant="appointmentTypeNr === currentAppointmentTypeNr ? 'tonal' : 'elevated'"
      link
      @click="selectAppointment"
    >
      <template v-slot:prepend>
        <v-avatar v-if="!isMobile" color="blue-darken-2">
          <v-img
            :width="120"
            aspect-ratio="1/1"
            cover
            :src="imgSrc"
          ></v-img>
        </v-avatar>
      </template>
    </v-card>
  </v-col>
</template>

<script lang="ts" setup>
import {computed, ref, toRefs} from "vue";
import {useAppStore} from "../../stores/app";
import {storeToRefs} from "pinia";

const { appointmentTypeNr } = storeToRefs(useAppStore());

const props = defineProps<{
  title: string;
  subTitle: string;
  imgSrc: string;
  currentAppointmentTypeNr: string;
}>();

const { title, subTitle, imgSrc, currentAppointmentTypeNr } = toRefs(props);
const viewPortWidth = ref(window.innerWidth);
const isMobile = computed(() => {
  return viewPortWidth.value <= 1000;
});

const selectAppointment = () => {
  appointmentTypeNr.value = currentAppointmentTypeNr.value;
}
</script>
<style scoped>
:deep(.v-card-title), :deep(.v-card-subtitle) {
  white-space: normal !important;
}
@media screen and (min-width: 1000px) {
  :deep(.v-card-title) {
    font-size: 1.4em;
  }
  :deep(.v-stepper-window) {
    margin: 0 !important;
  }
}

:deep(.v-card-item__prepend) {
  padding-inline-end: 1rem;
}
</style>
