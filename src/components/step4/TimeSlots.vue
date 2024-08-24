<template>
  <v-card
    class="mx-auto pa-2"
    max-width="300"
    :loading="timeSlotsLoading"
    flat
  >
    <v-list v-model="times">
      <v-list-subheader>Freie Termine für {{ new Date(selectedDate).toLocaleDateString() }}</v-list-subheader>
      <v-list-item
        v-for="(item, i) in timeSlots"
        ref="item"
        :key="i"
        :value="item"
        color="primary"
        rounded="xl"
        @click="setTime(item)"
      >
        <template v-slot:prepend>
          <v-icon icon="mdi-clock"></v-icon>
        </template>
        <v-list-item-title>{{ item }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script setup lang="ts">
import {useAppStore} from "../../stores/app";
import {storeToRefs} from "pinia";
import {ref} from "vue";
import {VList} from "vuetify/components";

const { selectedDate, selectedTime, stepFourValid, timeSlots, timeSlotsLoading } = storeToRefs(useAppStore());
const times = ref();
const item = ref();

const setTime = (time: any) => {
  selectedTime.value = time;
  console.log("times: ", times.value);
  console.log("item: ", item.value);
  stepFourValid.value = true;
}
</script>

