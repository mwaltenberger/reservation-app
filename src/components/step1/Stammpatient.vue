<template>
  <v-card flat class="stammpatient-wrapper">
    <v-card-title class="d-flex">Zugang für Stammpatienten:innen</v-card-title>
    <v-card-subtitle>Um zu überprüfen ob Sie in unserem System als Stammpatient:in vermerkt sind bitten wir
      Sie um, folgende Informationen
    </v-card-subtitle>
    <v-switch color="primary" v-model="svnrLogin" label="Mit SVNR anmelden" class="svnr-switch"></v-switch>
    <v-form v-if="!svnrLogin" v-model="isValid" ref="loginForm">
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
    <v-form v-else v-model="isValid" ref="svnrLoginForm">
      <v-text-field
        v-model="stateSvnr.svnr"
        :error-messages="vSvnr$.svnr.$error ? 'Bitte geben Sie eine gültige Sozialversicherungsnummer an.' : ''"
        label="Sozialversicherungsnummer *"
        required
        v-maska="'##########'"
        placeholder="__________"
        data-maska-eager
        @blur="vSvnr$.svnr.$touch"
        @input="vSvnr$.svnr.$touch; svnr = stateSvnr.svnr"
      ></v-text-field>
      <v-checkbox
        v-model="stateSvnr.checkbox"
        :error-messages="vSvnr$.checkbox.$error ? 'Bitte aktzeptieren Sie die DSGVO-Bestimmunge.' : ''"
        label="* Hiermit willige ich den auf dieser Website geltenden DSGVO-Bestimmungen ein."
        required
        @blur="vSvnr$.checkbox.$touch"
        @change="vSvnr$.checkbox.$touch"
      ></v-checkbox>
    </v-form>
  </v-card>
</template>

<script lang="ts" setup>
import {ref, reactive, watch} from 'vue'
import {VForm} from "vuetify/components";
import {useVuelidate} from '@vuelidate/core'
import {required, minLength} from '@vuelidate/validators'
import {useAppStore} from "../../stores/app";
import {storeToRefs} from "pinia";
import {vMaska} from "maska/vue"

const {name, lastName, birthday, stepOneValid, svnrLogin, svnr} = storeToRefs(useAppStore());

const isValid = ref(false);
const loginForm = ref<InstanceType<typeof VForm>>();
const svnrLoginForm = ref<InstanceType<typeof VForm>>();

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

const initialStateSvnr = {
  svnr: '',
  checkbox: null,
};

const stateSvnr = reactive({
  ...initialStateSvnr,
});

const rules = {
  name: {required},
  lastName: {required},
  birthDay: {required},
  checkbox: {required},
};

const rulesSVNR = {
  svnr: {required, minLength: minLength(10)},
  checkbox: {required},
}

const v$ = useVuelidate(rules, state);

const vSvnr$ = useVuelidate(rulesSVNR, stateSvnr);

const updateDatePickerDate = (date: any) => {
  const tmpDate = date.toLocaleDateString().split('.');
  if (tmpDate[0].length === 1) tmpDate[0] = `0${tmpDate[0]}`;
  if (tmpDate[1].length === 1) tmpDate[1] = `0${tmpDate[1]}`;
  state.birthDay = tmpDate.join('.');
}

watch(state, (newState) => {
  if(!svnrLogin.value) stepOneValid.value = !!(newState.name !== '' && newState.lastName !== '' && newState.birthDay !== '' && newState.checkbox && isValid.value);
});

watch(stateSvnr, (newState) =>{
  if(svnrLogin.value) stepOneValid.value = !!(newState.svnr !== '' && newState.svnr.length === 10 && newState.checkbox);
})

watch(svnrLogin, () => {
  stepOneValid.value = false;
  loginForm.value?.reset();
  svnrLoginForm.value?.reset();
})


</script>
<style lang="scss" scoped>
:deep(.v-card-title), :deep(.v-card-subtitle) {
  white-space: normal !important;
}
.stammpatient-wrapper {
  background: none;
}

.svnr-switch {
  padding: .5rem 1rem;
  max-height: 56px;
}

</style>
