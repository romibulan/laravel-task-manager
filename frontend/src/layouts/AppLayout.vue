<template>
  <div class="min-h-screen bg-gray-300 flex flex-col">
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md">
      <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <h1 class="text-xl font-bold text-blue-600">Task Manager</h1>
        <ul class="flex space-x-4">
          <li>
            <RouterLink to="/" class="text-gray-600 font-bold hover:text-blue-500"
              >Home</RouterLink
            >
          </li>
          <li>
            <RouterLink
              to="/dashboard"
              class="text-gray-600 font-bold hover:text-blue-500"
              >Dashboard</RouterLink
            >
          </li>
        </ul>
        <ul class="flex space-x-4" v-if="!isLoggedIn">
          <li>
            <RouterLink to="/register" class="text-gray-600 font-bold hover:text-blue-500"
              >Register</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/login" class="text-gray-600 font-bold hover:text-blue-500"
              >Login</RouterLink
            >
          </li>
        </ul>
        <ul class="flex items-center space-x-4" v-else>
          <li class="text-gray-900 font-bold leading-9 tracking-tight">
            <span v-if="layoutData && layoutData?.name" class="text-gray-500">
              Welcome back!
            </span>
            {{ layoutData.name }}
          </li>
          <li>|</li>
          <li>
            <a
              href="#"
              @click.prevent="logout"
              class="text-sm font-semibold text-gray-700 hover:text-red-600 transition"
            >
              Logout
            </a>
          </li>
        </ul>
      </div>
    </nav>
    <!-- Main Content Area -->
    <div class="h-16"></div>
    <!-- Spacer for fixed navbar -->
    <main class="flex-grow container mx-auto p-6">
      <div class="bg-white p-8 rounded-lg shadow">
        <slot></slot>
      </div>
    </main>
    <div class="h-16"></div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white p-4 text-center">&copy; 2024 My Vue App</footer>
  </div>
</template>

<script setup>
import { RouterLink } from "vue-router";
import { onMounted, inject, toRefs, computed } from "vue";
import http from "../utils/http";
import router from "../router";

const { isLoggedIn, layoutData, updateLayoutData, syncUnAuthState } = inject(
  "layoutState"
);
//const userName = computed(() => layoutData.value?.name);

console.log("AppLayout - isLoggedIn:", isLoggedIn.value);
console.log("AppLayout - layoutData:", layoutData.value);

const logout = () => {
  http
    .post("/logout")
    .then((response) => {
      if (response?.data?.success) {
        //localStorage.removeItem("token");
        syncUnAuthState();

        console.log("Logout successful, redirecting to login page...");
        console.log("isLoggedIn: " + isLoggedIn.value);
        console.log("layoutData: " + layoutData.value);

        router.push("/login");
      }
    })
    .catch((error) => {
      console.error("Error during logout:", error);
    });
};
</script>

<style lang="scss" scoped></style>
