<template>
  <v-card flat class="stammpatient-wrapper">
    <v-card-title>Zugang für Stammpatienten:innen</v-card-title>
    <v-card-subtitle class="mb-3">Um zu überprüfen ob Sie in unserem System als Stammpatient:in vermerkt sind bitten wir
      Sie um, folgende Informationen
    </v-card-subtitle>
    <v-form v-model="isValid">
      <v-text-field
        v-model="state.name"
        :error-messages="v$.lastName.$error ? 'Bitte geben Sie Ihren Vornamen an.' : ''"
        label="Vorname *"
        required
        class="text-body-1"
        @blur="v$.name.$touch"
        @input="v$.name.$touch; name = state.name"
      ></v-text-field>
      <v-text-field
        v-model="state.lastName"
        :error-messages="v$.lastName.$error ? 'Bitte geben Sie Ihren Nachnamen an.' : ''"
        label="Nachname *"
        required
        @blur="v$.lastName.$touch"
        @input="v$.lastName.$touch; lastName = state.lastName"
      ></v-text-field>

      <v-row>
        <v-col cols="9" sm="11">
          <v-text-field
            v-model="state.birthDay"
            :error-messages="v$.birthDay.$error ? 'Bitte geben Sie ein Geburtsdatum an.' : ''"
            label="Geburtsdatum *"
            required
            v-maska="'##.##.####'"
            placeholder="__.__.____"
            data-maska-eager
            @blur="v$.birthDay.$touch"
            @input="v$.birthDay.$touch; birthday = state.birthDay"
          ></v-text-field>
        </v-col>
        <v-col cols="3" sm="1">
          <v-menu :close-on-content-click="false">
            <template v-slot:activator="{ props }">
              <v-btn
                color="primary"
                icon="mdi-calendar"
                v-bind="props"
              >
              </v-btn>
            </template>
            <v-date-picker @update:model-value="updateDatePickerDate"/>
          </v-menu>
        </v-col>
      </v-row>
      <v-checkbox
        v-model="state.checkbox"
        :error-messages="v$.checkbox.$error ? 'Bitte aktzeptieren Sie die DSGVO-Bestimmunge.' : ''"
        label="* Hiermit willige ich den auf dieser Website geltenden DSGVO-Bestimmungen ein."
        required
        @blur="v$.checkbox.$touch"
        @change="v$.checkbox.$touch"
      ></v-checkbox>
    </v-form>
  </v-card>
</template>

<script lang="ts" setup>
import {ref, reactive, watch} from 'vue'
import {useVuelidate} from '@vuelidate/core'
import {required} from '@vuelidate/validators'
import {useAppStore} from "../../stores/app";
import {storeToRefs} from "pinia";
import {vMaska} from "maska/vue"

const {name, lastName, birthday, stepOneValid} = storeToRefs(useAppStore());

const isValid = ref(false);

const initialState = {
  name: '',
  lastName: '',
  birthDay: '',
  checkbox: null,
};
//TODO: use $v.validate for validation
const state = reactive({
  ...initialState,
});


const rules = {
  name: {required},
  lastName: {required},
  birthDay: {required},
  checkbox: {required},
};

const v$ = useVuelidate(rules, state);

const updateDatePickerDate = (date: any) => {
  const tmpDate = date.toLocaleDateString().split('.');
  if (tmpDate[0].length === 1) tmpDate[0] = `0${tmpDate[0]}`;
  if (tmpDate[1].length === 1) tmpDate[1] = `0${tmpDate[1]}`;
  console.log(tmpDate.join('.'));
  state.birthDay = tmpDate.join('.');
}

watch(state, (newState) => {
  stepOneValid.value = !!(newState.name !== '' && newState.lastName !== '' && newState.birthDay !== '' && newState.checkbox && isValid.value);
})


</script>
<style lang="scss" scoped>
:deep(.v-card-title), :deep(.v-card-subtitle) {
  white-space: normal !important;
}
.stammpatient-wrapper {
  background: none;
}
</style>
