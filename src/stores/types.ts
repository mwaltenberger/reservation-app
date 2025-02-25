
interface TimeSlot {
  id: number,
  value: string
}
export interface AppState {
  isStammkundenMode: boolean,
  steps: number,
  selectedDate: string,
  selectedAppointmentType: string,
  selectedAppointmentName: string,
  selectedTime: string,
  name: string,
  lastName: string,
  birthday: string,
  telNr: string,
  emailAddress: string,
  patNr: string,
  notice: string,
  stepOneValid: boolean,
  stepTwoValid: boolean,
  stepThreeValid: boolean,
  stepFourValid: boolean,
  stepFiveValid: boolean,
  svnrLogin: boolean,
  svnr: string,
  appointmentTypeNr: string,
  raumNr: string,
  responseDates: unknown[],
  timeSlots: TimeSlot[],
  timeSlotsLoading: boolean,
  appointmentId: number,
}

export function createAppState(): AppState {
  return {
    isStammkundenMode: false,
    steps: 0,
    selectedDate: '',
    selectedAppointmentType: '',
    selectedAppointmentName: '',
    selectedTime: '',
    name: '',
    lastName: '',
    birthday: '',
    telNr: '',
    emailAddress: '',
    notice: '',
    patNr: '',
    svnrLogin: false,
    svnr: '',
    stepOneValid: false,
    stepTwoValid: false,
    stepThreeValid: false,
    stepFourValid: false,
    stepFiveValid: false,
    appointmentTypeNr: '',
    raumNr: '',
    responseDates: [],
    timeSlots: [],
    timeSlotsLoading: true,
    appointmentId: 0
  };
}
