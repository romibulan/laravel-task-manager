<template>
  <AppLayout>
    <div>
      <div class="header">
        <h2 class="text-3xl font-semibold">Login</h2>
      </div>
      <p class="text-gray-600 mt-4">Please fill up the form below to login.</p>
      <div
        class="max-w-md mx-auto mt-10 p-6 bg-gray-200 border border-gray-300 rounded-xl shadow-md overflow-hidden flex flex-col items-center"
      >
        <form @submit.prevent="login" class="w-full space-y-6">
          <h3 class="text-xl font-semibold">Login</h3>
          <div>
            <label for="email" class="block text-sm text-gray-700 font-semibold mb-1"
              >Email</label
            >
            <input
              v-model="user.email"
              type="email"
              id="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 hover:border-blue-300 focus:bg-white focus:outline-none focus:ring focus:ring-blue-400"
              :class="{ 'border-red-500 bg-red-50': formErrors?.email }"
              @input="formErrors.email = null"
              placeholder="Enter your email"
            />
            <Error :error="formErrors?.email" />
          </div>

          <div>
            <label for="password" class="block text-sm text-gray-700 font-semibold mb-1"
              >Password</label
            >
            <input
              v-model="user.password"
              type="password"
              id="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 hover:border-blue-300 focus:bg-white focus:outline-none focus:ring focus:ring-blue-400"
              :class="{ 'border-red-500 bg-red-50': formErrors?.password }"
              @input="formErrors.password = null"
              placeholder="Enter your password"
            />
            <Error :error="formErrors?.password" />
          </div>
          <Button type="submit" :loading="isLoading"> Login </Button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from "../layouts/AppLayout.vue";
import { ref, reactive, watch, inject, onMounted } from "vue";
import http from "../utils/http";
import router from "../router";
import { useToast } from "vue-toast-notification";
import Button from "../components/Button.vue";
import Error from "../components/Error.vue";

const { isLoggedIn } = inject("layoutState");

onMounted(() => {
  //return;
  if (isLoggedIn.value) {
    router.push("/dashboard");
  }
});

const user = reactive({
  email: "",
  password: "",
});

const $toast = useToast();

const formErrors = ref({});

const isLoading = ref(false);

const clearErrors = () => {
  formErrors.value = {};
};

watch(formErrors, (newValue, oldValue) => {
  const errorKeys = Object.keys(formErrors.value);
  const resets = {};
  if (errorKeys.length > 0) {
    errorKeys.forEach((field) => {
      console.log(`Resetting field: ${field}`);
      resets[field] = "";
    });
    resetForm(user, resets);
    console.log(`Current user state after reset:`, user.value);
  }
});

const resetForm = (user, resets = null) => {
  if (resets) {
    // user[field] = "";
    Object.assign(user, resets);
    return;
  }
  Object.assign(user, {});
};

const hideLoader = () => {
  isLoading.value = false;
};

const showLoader = () => {
  isLoading.value = true;
};

const showErrors = (error, formErrors, $toast) => {
  if (error.response && error.response.status === 422) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      for (const field in errors) {
        if (errors.hasOwnProperty(field)) {
          errors[field] = errors[field].join("<br>");
        }
      }
      formErrors.value = errors;
    }
  } else {
    $toast.error("An unexpected error occurred. Please try again.");
  }
};

async function login() {
  console.log("Logging user:", user);
  clearErrors();
  showLoader();
  await http.get("/sanctum/csrf-cookie");
  console.log("reading xsrf-token with axios", document.cookie);
  console.log("CSRF cookie set, proceeding with login...");
  http
    .post("/login", user)
    .then((response) => {
      console.log("Login successful:", response?.data);
      if (response?.data?.success || response?.data?.status === "authenticated") {
        $toast.success(response?.data?.message || "Login successful!");
        isLoggedIn.value = true;
        sessionStorage.setItem("isLoggedIn", true);
        router.push("/dashboard");
      }
    })
    .catch((error) => {
      //  if(error instanceof AxiosError){
      console.error("Login failed:", error.response.message || "Internal Server Error");
      if (error.response.status === 422) {
        showErrors(error, formErrors, $toast);
      } else {
        $toast.error("An unexpected error occurred. Please try again.");
      }
    })
    .finally(() => {
      hideLoader();
    });
}
</script>

<style lang="scss" scoped></style>
