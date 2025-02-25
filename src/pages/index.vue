<template>
  <v-stepper
    v-model="steps"
    hide-actions
    flat
    :mobile="isMobile"
    class="stepper"
    bg-color="rgba(0, 0, 0, 0.01)"
  >
    <v-stepper-header>
      <v-stepper-item
        v-if="isStammkundenMode"
        value="1"
        title="Stammpatient:innen Abfrage"
      >

      </v-stepper-item>

      <v-divider />

      <v-stepper-item
        :value="isStammkundenMode ? '2' : '1'"
        title="Leistung wählen"
      >
      </v-stepper-item>

      <v-divider />

      <v-stepper-item
        :value="isStammkundenMode ? '3' : '2'"
        title="Freien Termin finden"
      >
      </v-stepper-item>

      <v-divider />

      <v-stepper-item
        :value="isStammkundenMode ? '4' : '3'"
        title="Abschließen"
      >
      </v-stepper-item>
    </v-stepper-header>
    <v-stepper-window>
      <v-stepper-window-item v-if="isStammkundenMode" value="1">
        <stammpatient ref="stammpatientForm" />
      </v-stepper-window-item>

      <v-stepper-window-item :value="isStammkundenMode ? '2' : '1'">
        <appointment-type @set-loading="setLoading" @validate-step="validateCurrentStep"/>
      </v-stepper-window-item>

      <v-stepper-window-item :value="isStammkundenMode ? '3' : '2'">
        <v-row>
          <v-col cols="12" sm="6">
            <calendar ref="calendar"/>
          </v-col>
          <v-col cols="12" sm="6">
            <time-slots />
          </v-col>
        </v-row>
      </v-stepper-window-item>

      <v-stepper-window-item :value="isStammkundenMode ? '4' : '3'">
        <final :is-stammkunden-mode="isStammkundenMode"/>
      </v-stepper-window-item>
    </v-stepper-window>
    <v-stepper-actions
      @click:prev="stepBack"
      :disabled="disableNextButton"
      prev-text="Zurück"
    >
      <template v-slot:next>
        <v-btn
          :loading="nextBtnLoading"
          @click="validateCurrentStep"
        >
          {{ isLastStep ? 'Abschicken' : 'Weiter' }}
        </v-btn>
      </template>
    </v-stepper-actions>
  </v-stepper>
</template>

<script lang="ts" setup>
import Stammpatient from "../components/step1/Stammpatient.vue";
import AppointmentType from "../components/step2/AppointmentType.vue";
import Calendar from "../components/step3/Calendar.vue";
import TimeSlots from "../components/step4/TimeSlots.vue";
import Final from "../components/step5/Final.vue";
import { ref, computed, onMounted } from "vue";
import {useAppStore} from "../stores/app";
import {storeToRefs} from "pinia";
import axios from "axios";


onMounted(() => {
  axios.post("https://www.zahnarzt-eferding.at/wp-admin/admin-ajax.php",
    {
      'action': 'checkStammabfrage',
    },
{
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      }
    }).then(function(response) {
    if (response.data === "1") {
      isStammkundenMode.value = true;
    }
  }).catch(function (error) {
    console.error(error);
  });
});

const {
  isStammkundenMode,
  steps,
  stepOneValid,
  stepTwoValid,
  stepThreeValid,
  stepFourValid,
  stepFiveValid,
  name,
  lastName,
  birthday,
  telNr,
  emailAddress,
  notice,
  selectedTime,
  selectedDate,
  raumNr,
  appointmentTypeNr,
  svnrLogin,
  svnr,
  patNr,
  appointmentId
} = storeToRefs(useAppStore());
const stammpatientForm = ref<InstanceType<typeof Stammpatient>>();
const nextBtnLoading = ref(false);
const viewPortWidth = ref(window.innerWidth);
const isMobile = computed(() => {
  return viewPortWidth.value <= 1000;
});
const calendar = ref<InstanceType<typeof Calendar>>();

const isLastStep = computed(() => {
  return (isStammkundenMode.value && steps.value === 3) || (!isStammkundenMode.value && steps.value === 2);
});



const disableNextButton = computed(() => {
  if (
    (steps.value === 0 && !stepOneValid.value) ||
    (steps.value === 1 && !stepTwoValid.value) ||
    (steps.value === 2 && (!stepThreeValid.value || !stepFourValid.value)) ||
    (steps.value === 3 && !stepFiveValid.value)
  ) {
    return 'next';
  }
  return false;
})

const setLoading = (loading: boolean) => {
  nextBtnLoading.value = loading;
}
const validateCurrentStep = async () => {
  if (isLastStep.value){
    //window.location.href = `https://www.zahnarzt-eferding.at/bitte-bestaetigen-sie-ihren-termin/?vn=${name.value}&nn=${lastName.value}&tel=${telNr.value}&email=${emailAddress.value}&notice=${notice.value}&time=${selectedTime.value}&date=${selectedDate.value}&room=${raumNr.value}&type=${appointmentTypeNr.value}&geburtsdatum=${birthday.value}&patnr=${patNr.value}`;
    nextBtnLoading.value = true;
    axios.post("https://www.zahnarzt-eferding.at/wp-admin/admin-ajax.php",
      {
        'action': 'scheduler',
        'firstname':  name.value,
        'lastname': lastName.value,
        'telnr': telNr.value,
        'email': emailAddress.value,
        'dsgvo-checkbox':	"dsgvo-einwilligung",
        'notice': notice.value,
        'time': `${selectedTime.value.split(' ')[0]}:00`,
        'date': selectedDate.value,
        'type': raumNr.value,
        'geburtsdatum': birthday.value.replace(/\./g, '-'),
        'patnr': patNr.value,
        'id': appointmentId.value
      },
      {
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        }
      }).then(function(response) {
      nextBtnLoading.value = false;
      if(response.data === 1) window.location.href = "https://www.zahnarzt-eferding.at/bitte-bestaetigen-sie-ihren-termin/";
    }).catch(function (error) {
      console.error(error);
      nextBtnLoading.value = false;
      window.location.href = "https://www.zahnarzt-eferding.at/bitte-bestaetigen-sie-ihren-termin/"
    });
  }
  if (steps.value === 0 && isStammkundenMode.value) {
    nextBtnLoading.value = true;
    let data = {};
    if (svnrLogin.value) {
      data = {
        'action': 'checkStammkunde',
        'svnr': svnr.value
      };
    } else {
      data = {
        'action': 'checkStammkunde',
        'erst-firstname':  name.value,
        'erst-lastname': lastName.value,
        'erst-geburtsdatum': birthday.value
      };
    }
    axios.post("https://www.zahnarzt-eferding.at/wp-admin/admin-ajax.php",
      data,
      {
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        }
      }).then(function(response) {
      const { patnr, email, strtelnr } = response.data;
      emailAddress.value = email;
      patNr.value = patnr;
      telNr.value = strtelnr;
      if (svnrLogin.value) {
        const { nn, vn, geb } = response.data;
        name.value = vn;
        lastName.value = nn;
        birthday.value = geb;
      }
      axios.post("https://www.zahnarzt-eferding.at/wp-admin/admin-ajax.php",
        {
          'action': 'checkEmailPossible',
        },
        {
          headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
          }
        }).then(function(response) {
        if(patnr !== null && patnr !== undefined && patnr !== "undefined"){
          nextBtnLoading.value = false;
          steps.value++;
        } else if (response.data === "1") {
          window.location.href = "https://www.zahnarzt-eferding.at/kein-stammkunde-2";
          return;
        } else {
          window.location.href = "https://www.zahnarzt-eferding.at/kein-stammkunde/?vorname=" + name.value !== 'null' ? name.value : '' + "&nachname=" + lastName.value !== 'null' ? lastName.value : '' + "&geburtsdatum=" + birthday.value !== 'null' ? birthday.value : '';
        }
      }).catch(function (error) {
        console.error(error);
      });

    }).catch(function (error) {
      console.error(error);
    });
  } else if (!isLastStep.value) {
    steps.value++;
  }
  if ((isStammkundenMode.value && steps.value === 2) || (!isStammkundenMode.value && steps.value === 1)) {
    calendar.value?.setNewDates();
  }
}
const stepBack = () => {
  steps.value--;
  if ((isStammkundenMode.value && (steps.value === 1 || steps.value === 2)) || (!isStammkundenMode.value && (steps.value === 0|| steps.value === 1))) {
    appointmentTypeNr.value = '0';
    stepTwoValid.value = false;
    selectedTime.value = '';
    stepThreeValid.value = false;
    selectedDate.value = '';
    stepFourValid.value = false;

  }
  // if ((isStammkundenMode.value && steps.value === 2) || (!isStammkundenMode.value && steps.value === 1)) {
  //   console.log("reset steps: ", steps.value);
  //   selectedTime.value = '';
  //   stepThreeValid.value = false;
  //   selectedDate.value = '';
  //   stepFourValid.value = false;
  // }
}
</script>
<style lang="scss" scoped>
  .v-stepper-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    box-shadow: none;
  }
  .stepper {
    border: 1px solid rgba(0,0,0,0.1);
  }
</style>

