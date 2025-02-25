<template>
  <v-card
    class="mx-auto pa-2"
    max-width="300"
    :loading="timeSlotsLoading"
    flat
  >
    <v-list
      v-model:selected="selectedTimes"
      ref="listRef"
    >
      <v-list-subheader>Freie Termine für {{ new Date(selectedDate).toLocaleDateString() }}</v-list-subheader>
      <v-list-item
        v-for="(item, i) in timeSlots"
        ref="listItemRef"
        :key="i"
        :value="item.id"
        color="primary"
        rounded="xl"
        @click="setTime(item.value, item.id)"
      >
        <template v-slot:prepend>
          <v-icon icon="mdi-clock"></v-icon>
        </template>
        <v-list-item-title>{{ item.value }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-card>
</template>


<script setup lang="ts">
import {useAppStore} from "../../stores/app";
import {storeToRefs} from "pinia";
import {ref, watch, Ref} from "vue";
import {VListItem} from "vuetify/components";

const { selectedDate, selectedTime, stepFourValid, timeSlots, timeSlotsLoading, appointmentId } = storeToRefs(useAppStore());
const selectedTimes = ref();
const listItemRef: Ref<any[]> = ref([]);
const setTime = (time: any, id: number) => {
  selectedTime.value = time;
  appointmentId.value = id;
  stepFourValid.value = true;

}

watch(selectedTime, (newTime) => {
  if(newTime === '') selectedTimes.value = [];
})

</script>

