import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import { Icon } from "@iconify/vue"

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

import App from './App.vue'
import router from './router'
import VueApexCharts from "vue3-apexcharts"

const app = createApp(App)

app.component("Icon", Icon)

app.use(Toast)

app.use(createPinia())
app.use(router)

// app.use(VueApexCharts)
app.component("apexchart", VueApexCharts)

app.mount('#app')
