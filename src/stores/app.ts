// Utilities
import { defineStore } from 'pinia'
import {createAppState} from "./types";

export const useAppStore = defineStore('app', {
  state: () => {
    return createAppState();
  },
  getters: {
  },
  actions: {
  }
})
