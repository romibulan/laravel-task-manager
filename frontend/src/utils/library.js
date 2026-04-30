// your-library.js
// import { inject, runWithContext } from 'vue';
let configStore = {};
//let appInstance = null;
console.log('Library instance loaded');
// 1. Function to register the app with your library


export const initMyLibrary = (app, config = {}) => {
  configStore = config;
  //appInstance = app;

};


export const useHttpConfig = () => {
  const { syncUnAuthState } = getMyLibraryConfig('syncUnAuthState');
  return { syncUnAuthState };
};

// 2. Specialized function using inject outside of setup()
const getMyLibraryConfig = (...keys) => {

  console.log('configStore:', configStore);
  // if (!appInstance) {
  //   throw new Error('Library not initialized. Call initMyLibrary(app) first.');
  // }

  // Use runWithContext to provide the context for injection
  // return appInstance.runWithContext(() => {
  //   let injects = configs.reduce((acc, config) => {
  //     acc[config] = inject(config, null);
  //     return acc;
  //   }, {});
  //   return injects;
  // });

  const result = {};

  keys.forEach((key) => {
    if (!(key in configStore)) {
      throw new Error(`Config "${key}" not found.`);
    }
    result[key] = configStore[key];
  });

  return result;

}


