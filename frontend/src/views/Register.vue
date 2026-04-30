<template>
  <AppLayout>
    <div>
      <div class="header">
        <h2 class="text-2xl font-semibold">Register To Site</h2>
      </div>
      <p class="text-gray-600 mt-4">
        Please fill up the form below to register to our site.
      </p>
      <div
        class="max-w-md mx-auto mt-10 p-6 bg-gray-200 border border-gray-300 rounded-xl shadow-md overflow-hidden flex flex-col items-center"
      >
        <form @submit.prevent="register" class="w-full space-y-6">
          <div class="flex items-center space-x-2 mb-4">
            <h3 class="text-xl font-semibold">Create Account</h3>
            <UserIcon class="w-6 h-6 text-gray-500" />
          </div>

          <div>
            <label for="name" class="block text-sm text-gray-700 font-semibold mb-1"
              >Name</label
            >
            <input
              v-model="user.name"
              type="text"
              id="name"
              class="w-full px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 hover:border-blue-300 focus:bg-white focus:outline-none focus:ring focus:ring-blue-400"
              :class="{ 'border-red-500 bg-red-50': formErrors?.name }"
              @input="formErrors.name = null"
              placeholder="Enter your Name"
            />
            <Error :error="formErrors?.name" />
          </div>

          <div>
            <label for="email" class="block text-sm text-gray-700 font-semibold mb-1"
              >Email</label
            >
            <input
              v-model="user.email"
              type="text"
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

          <div>
            <label
              for="password_confirmation"
              class="block text-sm text-gray-700 font-semibold mb-1"
              >Confirmation Password</label
            >
            <input
              v-model="user.password_confirmation"
              type="password"
              id="password_confirmation"
              class="w-full px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 hover:border-blue-300 focus:bg-white focus:outline-none focus:ring focus:ring-blue-400"
              :class="{ 'border-red-500 bg-red-50': formErrors?.password_confirmation }"
              @input="formErrors.password_confirmation = null"
              placeholder="Confirm your password"
            />
          </div>
          <Button type="submit" :loading="isLoading"> Register </Button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from "../layouts/AppLayout.vue";
import { ref, reactive, computed, watch, inject, onMounted } from "vue";
import http from "../utils/http";
import router from "../router";
import axios from "axios";
import { useToast } from "vue-toast-notification";
import { UserIcon } from "@heroicons/vue/24/solid";
import Error from "../components/Error.vue";
import Button from "../components/Button.vue";

const { isLoggedIn } = inject("layoutState");

onMounted(() => {
  if (isLoggedIn.value) {
    router.push("/dashboard");
  }
});

const $toast = useToast();

const user = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const formErrors = ref({});

const isLoading = ref(false);

const isFormValid = computed(() => {
  return (
    user.name.trim() !== "" &&
    user.email.trim() !== "" &&
    user.password.trim() !== "" &&
    user.password_confirmation.trim() !== ""
  );
});

watch(formErrors, (newValue, oldValue) => {
  const errorKeys = Object.keys(formErrors.value);
  const resets = {};
  if (errorKeys.length > 0) {
    errorKeys.forEach((field) => {
      console.log(`Resetting field: ${field}`);
      resets[field] = "";
    });
    resetForm(user, resets);
  }
});

const clearErrors = () => {
  formErrors.value = {};
};

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

async function register() {
  clearErrors();
  showLoader();
  console.log("Registering user:", user);
  await http.get("/sanctum/csrf-cookie");
  http
    .post("/register", user)
    .then((response) => {
      console.log(response);
      if (response?.data.success) {
        console.log("Registration successful:", response?.data);
        $toast.success(response?.data?.message || "Registration successful!");
        router.push("/login");
      }
    })
    .catch((error) => {
      console.error("Registration failed:", error);
      console.log(
        "Error response data:",
        error.response ? error.response?.data : "No response data"
      );
      // Handle registration errors (e.g., display error messages)
      showErrors(error, formErrors, $toast);
    })
    .finally(() => {
      hideLoader();
    });
}
</script>

<style lang="scss" scoped></style>
