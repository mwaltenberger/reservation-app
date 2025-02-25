<template>
  <v-form v-model="isValid">
    <v-text-field
      v-model="state.name"
      :error-messages="v$.lastName.$error ? 'Bitte geben Sie Ihren Vornamen an.' : ''"
      label="Vorname *"
      required
      @blur="v$.name.$touch"
      @update:model-value="v$.name.$touch"
      readonly
    ></v-text-field>
    <v-text-field
      v-model="state.lastName"
      :error-messages="v$.lastName.$error ? 'Bitte geben Sie Ihren Nachnamen an.' : ''"
      label="Nachname *"
      required
      @blur="v$.lastName.$touch"
      @update:model-value="v$.lastName.$touch"
      readonly
    ></v-text-field>
    <v-text-field
      v-model="state.birthDay"
      :counter="11"
      :error-messages="v$.birthDay.$error ? 'Bitte geben Sie ein Geburtsdatum an.' : ''"
      label="Geburtsdatum *"
      required
      @blur="v$.birthDay.$touch"
      @update:model-value="v$.birthDay.$touch"
      readonly
    ></v-text-field>
    <v-text-field
      v-model="state.phone"
      :error-messages="v$.phone.$error ? 'Bitte geben Sie eine Telefonnummer an.' : ''"
      label="Telefon Nr. *"
      required
      @blur="v$.phone.$touch"
      @update:model-value="v$.phone.$touch; telNr = $event"
    ></v-text-field>
    <v-text-field
      v-model="state.email"
      :error-messages="v$.email.$error ? 'Bitte geben Sie eine Email Adresse an.' : ''"
      label="Email *"
      required
      @blur="v$.email.$touch"
      @update:model-value="v$.email.$touch; emailAddress = $event"
    ></v-text-field>
    <v-text-field
      v-model="state.note"
      label="Bemerkung "
      @blur="v$.note.$touch"
      @update:model-value="v$.note.$touch; notice = $event"
    ></v-text-field>

    <v-checkbox
      v-if="!isStammkundenMode"
      v-model="state.checkbox"
      :error-messages="v$.checkbox.$error ? 'Bitte aktzeptieren Sie die DSGVO-Bestimmunge.' : ''"
      label="* Hiermit willige ich den auf dieser Website geltenden DSGVO-Bestimmungen ein."
      required
      @blur="v$.checkbox.$touch"
      @change="v$.checkbox.$touch"
    ></v-checkbox>
  </v-form>
</template>

<script lang="ts" setup>
import {reactive, watch, ref, computed, onMounted } from 'vue'
import { useVuelidate } from '@vuelidate/core'
import { required, email, numeric } from '@vuelidate/validators'
import {useAppStore} from "../../stores/app";
import {storeToRefs} from "pinia";
import {VForm} from "vuetify/components";

const { name, lastName, birthday, stepFiveValid, emailAddress, telNr, notice, isStammkundenMode } = storeToRefs(useAppStore());

const initialState = {
  name: name.value,
  lastName: lastName.value,
  birthDay: birthday.value,
  note: '',
  email: emailAddress.value,
  phone: telNr.value,
  checkbox: null,
};
//TODO: use $v.validate for validation
const state = reactive({
  ...initialState,
});

const isValid = ref(false);

const stammkundenRules = computed(() => {
  return isStammkundenMode ? {
    name: { required },
    lastName: { required },
    birthDay: { required },
    note: { },
    email: { required, email },
    phone: { required, numeric },
    checkbox: { },
  } : {
    name: { required },
    lastName: { required },
    birthDay: { required },
    note: { },
    email: { required, email },
    phone: { required, numeric },
    checkbox: { required },
  };
});

const rules = stammkundenRules;

onMounted(() => {
  validateFinalStep(state);
})

const v$ = useVuelidate(rules, state);
const validateFinalStep = (newState: any) => {
  console.log("validate with: ", newState);
  console.log(isStammkundenMode.value);
  if (isStammkundenMode.value) {
    stepFiveValid.value = (
      newState.name !== '' &&
      newState.lastName !== '' &&
      newState.birthDay !== '' &&
      newState.email !== '' &&
      newState.phone !== ''
    );
  } else {
    stepFiveValid.value = (
      newState.name !== '' &&
      newState.lastName !== '' &&
      newState.birthDay !== '' &&
      newState.email !== '' &&
      newState.phone !== '' &&
      newState.checkbox &&
      isValid.value
    );
  }
};

watch(state, (newState) => {
  console.log("hier mit state in watcher: ", newState);
  validateFinalStep(newState);
})
</script>

