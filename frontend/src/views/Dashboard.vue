<template>
  <div>
    <AppLayout>
      <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
        <p>
          Welcome to your dashboard! Here you can manage your account and view your
          activity.
        </p>
      </div>
      <TaskList />
    </AppLayout>
  </div>
</template>

<script setup>
import AppLayout from "../layouts/AppLayout.vue";
import TaskList from "../components/TaskList.vue";
import http from "../utils/http";
import router from "../router";
import { ref, onMounted, inject } from "vue";

const { isLoggedIn, layoutData, updateLayoutData } = inject("layoutState");

onMounted(() => {
  if (!isLoggedIn.value) {
    router.push("/login");
  }
  console.log("Dashboard - isLoggedIn:", isLoggedIn.value);
  console.log("Dashboard - layoutData:", layoutData.value);
  if (
    isLoggedIn.value &&
    (Object.keys(layoutData.value).length === 0 || layoutData === null)
  ) {
    console.log("Fetching dashboard data...");
    http.get("/user").then((response) => {
      console.log("Dashboard data:", response?.data?.data);
      updateLayoutData(response?.data?.data);
    });
    // .catch((error) => {
    //    console.error("Error fetching dashboard data:", error.response.status);
    //   if (error.response && error.response.status === 401) {
    //	syncUnAuthState();
    //	router.push("/login");
    //  }
    // });
  }
});
</script>

<style lang="scss" scoped></style>
