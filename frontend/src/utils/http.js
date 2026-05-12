import axios from 'axios';
import router from "../router";
import { useHttpConfig } from '@/utils/library.js';
//const baseUrl = import.meta.env.VITE_API_BASE_URL;
const baseUrl = '/api'; // Use relative URL to leverage proxy in development and correct path in production

axios.defaults.baseURL = baseUrl// Set your backend URL here
axios.defaults.withCredentials = true; // Required for sending cookies
axios.defaults.withXSRFToken = true;    // Newer Axios versions (1.6+) require this to send the X-XSRF-TOKEN header
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.interceptors.request.use(config => {
  console.log('Request URL:', config.url);

  if (config.url === '/sanctum/csrf-cookie') {
    console.log('config.url:', config.url);
    console.log('Setting baseURL for Sanctum CSRF request');
    //const newBase = import.meta.env.VITE_API_BASE_URL;
    // const newBase = import.meta.env.VITE_SANCTUM_URL;
    const newBase = '';

    // Ensure the URL is not already absolute before prepending
    //if (!config.url.startsWith('http') || !config.url.startsWith('https')) {
    axios.defaults.baseURL = newBase; // Reset baseURL to default for subsequent requests
    config.url = `${newBase}${config.url}`;

    //}
  }
  return config;
}, error => {
  return Promise.reject(error);
});

axios.interceptors.response.use(
  response => response,
  error => {
    if (!error.response) {
      // We have a network error
      console.error('Network error:', error);
      console.error('Network error cause:', error.message);
      console.error('Network error status code:', error.code);
    }
    else if (error.response && error.response.status === 401) {
      const { syncUnAuthState } = useHttpConfig();
      syncUnAuthState();
      router.push("/login");
    }
    return Promise.reject(error);
  }
);

export default axios;
