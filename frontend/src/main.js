import './style.css'
import 'core-js/actual/object/group-by';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import ToastPlugin from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-bootstrap.css';
import { plugin, defaultConfig } from '@formkit/vue';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import '@formkit/themes/genesis';
import { initMyLibrary } from '@/utils/library.js';

import App from './App.vue'
import router from './router'
import { ref } from "vue";
const layoutData = ref(JSON.parse(sessionStorage.getItem('user')) || {});
const isLoggedIn = ref(sessionStorage.getItem('isLoggedIn') || false);
const updateLayoutData = (data) => {
  layoutData.value = data;
  sessionStorage.setItem("user", JSON.stringify(data));
  console.log("Updated layoutData:", layoutData.value);
  console.log("SessionStorage.getItem('user'):", sessionStorage.getItem('user'));

};
const syncUnAuthState = () => {
  isLoggedIn.value = false;
  layoutData.value = {};
  sessionStorage.removeItem('isLoggedIn');
  sessionStorage.removeItem('user');
  console.log("Logout Updated isLoggedIn:", isLoggedIn.value);
  console.log("Logout Updated layoutData:", layoutData.value);
  console.log("Logout SessionStorage.getItem('user'):", sessionStorage.getItem('user'));

}

const app = createApp(App)
app.config.performance = false;
app.use(createPinia());
app.use(router);
app.use(ToastPlugin);
app.use(PrimeVue, {
  theme: {
    preset: Aura
  }
});
app.use(plugin, defaultConfig);
app.provide("layoutState", { isLoggedIn, layoutData, updateLayoutData, syncUnAuthState });
initMyLibrary(app, { syncUnAuthState: syncUnAuthState });
app.mount('#app')
