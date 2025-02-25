<template>
  <v-container>
    <v-row class="appointment-row">
      <p>Eingeloggt als: <strong style="color: #232323">{{ name }} {{lastName}}</strong></p>
      <appointment
        v-for="item in items" v-bind:key="item.WebTArtNr"
        :current-appointment-type-nr="item.WebTArtNr"
        :sub-title="`Dauer ungefähr: ${item.Std_Dauer_Anzeige} min.`"
        :title="item.WebTArtBez"
        :img-src="imageLink(item.WebTArtBez)"
        :responseDates="responseDates"
        @click="setTArtNr(item.WebTArtNr, item.WebTArtBez)"
      />
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import Appointment from "./Appointment.vue";
import {onMounted, watch, ref, Ref, computed} from "vue";
import {useAppStore} from "../../stores/app";
import {storeToRefs} from "pinia";
import axios from "axios";
import { AppointmentDto } from "@/types";

const { appointmentTypeNr, stepTwoValid, raumNr, responseDates, selectedAppointmentName, name, lastName } = storeToRefs(useAppStore());

const items: Ref<AppointmentDto[]> = ref([]);

const emit = defineEmits<{
  (e: 'set-loading', value: boolean): void;
  (e: 'validate-step'): void;
}>();

const imageLink = (description: string) => {
    if(description.includes('Zahnreinigung')) {
      return "https://www.zahnarzt-eferding.at/wp-content/uploads/2017/01/Zahnarzt-Eferding_Letsch_Mundhygiene.png";
    }
    return "https://www.zahnarzt-eferding.at/wp-content/uploads/2017/01/Zahnarzt-Eferding_Letsch_Arbeit-1.png";
};

onMounted(() => {
  axios.post("https://www.zahnarzt-eferding.at/wp-admin/admin-ajax.php",
{
      'action': 'appointmentTypes',
  },
    {
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      }
    }).then(function(response) {
    items.value = response.data as AppointmentDto[];
    items.value.sort((a, b) => a.Sort_WebTArt - b.Sort_WebTArt);
  }).catch(function (error) {
      console.error(error);
  });
});

const checkDays = computed(() => {
  const days = [];
  const today = new Date();
  for(let i = 0; i < 90; i++) {
    if (i !== 0) today.setDate(today.getDate()+1);
    days.push(today.toISOString().split('T')[0]);
  }
  return days;
})
const setTArtNr = (currentNr: string, currentName: string) => {
  emit('set-loading', true);
  raumNr.value = currentNr;
  selectedAppointmentName.value = currentName;
  axios.post("https://www.zahnarzt-eferding.at/wp-admin/admin-ajax.php",
    {
      'action': 'checkDays',
      'days':  checkDays.value,
      'room': raumNr.value
    },
    {
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      }
    }).then(function(response) {
    responseDates.value = response.data;
    emit('set-loading', false);
    emit('validate-step');
  }).catch(function (error) {
    console.error(error);
    emit('set-loading', false);
  });

};

watch(appointmentTypeNr, (newNr) => {
  stepTwoValid.value = newNr !== "0";
});

</script>

<style lang="scss" scoped>
.appointment-row {
  max-width: 600px;
  margin: auto;
}
</style>
