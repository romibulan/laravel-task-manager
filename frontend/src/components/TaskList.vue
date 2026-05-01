<template>
  <!-- Tak Form -->
  <div>
    <TaskForm
      @task-added="handleTaskCreateOrUpdate"
      @cancel-task="hideDialog"
      :initialData="taskToEdit"
      :showDialog="show"
    />
  </div>
  <!-- end task form -->
  <div class="max-w-7xl mx-auto mt-10">
    <h3 class="text-2xl font-semibold mb-4">Your Tasks</h3>

    <div
      v-if="isLoading"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-10 backdrop-blur-sm"
    >
      <vueSpinner size="30" color="blue" />
    </div>
    <div v-else>
      <div class="mb-4 flex justify-between items-center">
        <!-- Search Input -->
        <div class="relative max-w-sm">
          <!-- Search Icon (Optional) -->
          <div
            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
          >
            <svg
              class="w-4 h-4 text-gray-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              ></path>
            </svg>
          </div>

          <!-- Search Input -->
          <input
            v-model="searchQuery"
            type="text"
            class="block w-full p-2.5 pl-10 pr-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Search..."
          />

          <!-- Cross (Clear) Button -->
          <button
            @click="searchQuery = ''"
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>
        <!-- end search input >

        <!-- Due Date Filter -->
        <div class="flex flex-wrap gap-6 mb-5">
          <span class="text-sm font-medium">Due date: </span>
          <div class="flex items-center">
            <RadioButton v-model="filterDate" inputId="all" value="all" />
            <label for="all" class="ml-2">All</label>
          </div>
          <div class="flex items-center">
            <RadioButton v-model="filterDate" inputId="past_due" value="past_due" />
            <label for="past_due" class="ml-2">Past due</label>
          </div>
          <div class="flex items-center">
            <RadioButton v-model="filterDate" inputId="due_today" value="due_today" />
            <label for="due_today" class="ml-2">Due today</label>
          </div>
        </div>
        <!-- end due date filter -->

        <!-- Status Filter -->
        <!-- Filter Checkbox Dropdown -->
        <MultiSelect
          v-model="filterStatus"
          :options="statusFilterOptions"
          optionLabel="name"
          optionValue="code"
          placeholder="Select Status"
          :maxSelectedLabels="3"
        />
        <!-- end status filter -->
      </div>
      <div class="mb-4 flex justify-between items-center">
        <span class="text-sm font-medium"
          >Showing {{ filteredItems.length }} of {{ tasks.length }} tasks</span
        >
        <div v-if="filterStatus.length > 0" class="ml-12 flex items-center space-x-2">
          <span class="text-sm font-medium">Status: </span>
          <div v-for="filter in filterStatus" :key="filter">
            <Chip
              :label="statusFilterOptions.find((option) => option.code === filter)?.name"
              :icon="iconClass(filter)"
              removable
              @remove="filterStatus = filterStatus.filter((f) => f !== filter)"
            />
          </div>
        </div>
        <div class="flex items-center gap-4">
          <Button @click="showDialog()" label="New Task" icon="pi pi-plus" />
          <Button
            @click="getTasks()"
            label="Refresh"
            icon="pi pi-refresh"
            severity="secondary"
          />
        </div>
      </div>
      <div class="overflow-x-auto">
        <!-- Tasks Table -->
        <div v-if="filteredItems.length > 0">
          <table class="m-auto table-auto w-full">
            <thead>
              <tr>
                <th class="px-4 py-2 border">Task</th>
                <th class="px-4 py-2 border">Creator</th>
                <th class="px-4 py-2 border">Due Date</th>
                <th class="px-4 py-2 border">Status</th>
                <th class="px-4 py-2 border">Update Status</th>
                <th class="px-4 py-2 border">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="task in filteredItems"
                :key="task.id"
                :class="{
                  'bg-green-100': highlighted === task.id && statusColor === 'green',
                  'bg-yellow-100': highlighted === task.id && statusColor === 'yellow',
                }"
              >
                <td class="px-4 py-2 border">{{ task.title }}</td>
                <td class="w-40 px-4 py-2 border">{{ task?.owner?.name }}</td>
                <td class="w-40 px-4 py-2 border">{{ task.due_date }}</td>
                <td class="w-40 px-4 py-2 border">
                  <Chip
                    :label="task.extra.data.label"
                    :icon="iconClass(task.status)"
                    :pt="{
                      root: rootClass(task.status),
                      label: labelClass(task.status),
                      icon: iconStyle(task.status),
                    }"
                  />
                </td>
                <td class="px-4 py-2 border">
                  <div
                    class="flex flex-col space-y-2"
                    v-for="transition in task.extra.data.transitions"
                  >
                    <button
                      @click="updateStatus(task.id, transition.value)"
                      class="flex items-center space-x-1 px-2 py-1 text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-shadow"
                      :class="{
                        'bg-green-100 tex t-gray-700 hover:bg-green-200':
                          transition.color === 'green',
                        'bg-yellow-100 text-gray-700 hover:bg-yellow-200':
                          transition.color === 'yellow',
                      }"
                    >
                      <i :class="updateIconClass(transition.value)" aria-hidden="true"></i
                      ><span>{{ transition.label }}</span>
                    </button>
                  </div>
                </td>
                <td class="px-4 py-2 border flex items-center space-x-2">
                  <Button
                    icon="pi pi-pencil"
                    severity="warning"
                    @click="handleEdit(task.id)"
                    label="Edit"
                  />
                  <Button
                    icon="pi pi-trash"
                    severity="danger"
                    @click="deleteTask(task.id)"
                    label="Delete"
                  />
                </td>
              </tr>
            </tbody>
          </table>
          <div class="mt-4">
            <tailwind-pagination
              :data="pages"
              :limit="3"
              @pagination-change-page="getTasks"
            />
          </div>
        </div>
        <div v-else>No Tasks Found!</div>
        <!-- End tasks table -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { VueSpinner } from "vue3-spinners";
import { useToast } from "vue-toast-notification";
import { TailwindPagination } from "laravel-vue-pagination";
import Button from "PrimeVue/Button";
import MultiSelect from "primevue/multiselect";
import RadioButton from "primevue/radiobutton";
import Chip from "primevue/chip";
import { format, parse } from "@formkit/tempo";

import { ref, onMounted, computed, watch } from "vue";
import http from "../utils/http";
import TaskForm from "./TaskForm.vue";
// import Button from "../components/Button.vue";

const $toast = useToast();

const tasks = ref([]);

const taskToEdit = ref({});

const show = ref(false);

const filterStatus = ref([]);

const filterDate = ref("all");

const searchQuery = ref("");

const statusFilterOptions = ref([
  { name: "Pending", code: "pending" },
  { name: "In Progress", code: "in_progress" },
  { name: "Completed", code: "completed" },
]);

const pages = ref({});

const highlighted = ref(null);

const statusColor = ref(null);

const isLoading = ref(false);

const showDialog = function () {
  show.value = true;
};

const hideDialog = function () {
  taskToEdit.value = {};
  show.value = false;
};

const hideLoader = () => {
  isLoading.value = false;
};

const showLoader = () => {
  isLoading.value = true;
};

const highlightRow = (taskId, color) => {
  highlighted.value = taskId;
  statusColor.value = color;
  setTimeout(() => {
    highlighted.value = null;
    statusColor.value = null;
  }, 5000);
};

const filteredItems = computed(() => {
  console.log("filtering tasks based on status:", filterStatus.value.length);
  let filtered = tasks.value
    .map((task) => ({
      ...task,
      due_date: format(parse(task.due_date), "long"),
    }))
    .sort((a, b) => new Date(a.due_date) - new Date(b.due_date));

  if (filterStatus.value.length > 0) {
    filtered = filtered.filter((task) => filterStatus.value.includes(task.status));
  }

  if (searchQuery.value.trim() !== "") {
    filtered = filtered.filter((task) =>
      task.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (filterDate.value === "past_due") {
    const today = new Date();
    filtered = filtered.filter((task) => {
      const dueDate = new Date(task.due_date);
      return dueDate.setHours(0, 0, 0, 0) < today.setHours(0, 0, 0, 0);
    });
  } else if (filterDate.value === "due_today") {
    const today = new Date();
    filtered = filtered.filter((task) => {
      const dueDate = new Date(task.due_date);
      return dueDate.setHours(0, 0, 0, 0) == today.setHours(0, 0, 0, 0);
    });
  }
  return filtered;
});

function handleEdit(taskID) {
  const task = http.get(`/tasks/${taskID}`).then((response) => {
    if (response.data?.data) {
      taskToEdit.value = response.data?.data;
      showDialog();
    }
  });
}

function handleTaskCreateOrUpdate(task = null) {
  if (task) {
    const existingTaskIndex = tasks.value.findIndex((t) => t.id === task.id);
    if (existingTaskIndex !== -1) {
      tasks.value[existingTaskIndex] = task;
      $toast.success("Task updated successfully!");
    } else {
      tasks.value.unshift(task);
      $toast.success("Task added successfully!");
    }
    hideDialog();
    highlightRow(task.id, task.extra.data.color);
  } else {
    getTasks();
  }
}

function updateStatus(taskId, newStatus) {
  console.log(`updating task ${taskId} to...${newStatus}`);
  http
    .patch(`/tasks/${taskId}`, { status: newStatus })
    .then((response) => {
      if (response?.data?.success) {
        $toast.success(response.data?.message);
        const updatedTask = response.data?.data;
        tasks.value = tasks.value.map((member) =>
          member.id === updatedTask.id ? { ...member, ...updatedTask } : member
        );
        highlightRow(taskId, updatedTask.extra.data.color);
      }
    })
    .catch((e) => {
      console.log("error in updating task status", e);
      // if (error instanceof AxiosError) {
      console.error(
        "Tasks updating failed:",
        error.response.message || "Internal Server Error"
      );
      $toast.error("could'nt update task, Something went wrong");
      // }
    });
}

const deleteTask = (taskId) => {
  if (confirm("Are you sure to delete this task?"))
    http
      .delete(`/tasks/${taskId}`)
      .then((response) => {
        if (response?.data?.success) {
          $toast.success(response?.data?.message);
          tasks.value = tasks.value.filter((task) => task.id !== response.data?.id);
        }
      })
      .catch((e) => {
        // if(e instanceof AxiosError){
        console.error("Tasks deletion failed:", e || "Internal Server Error");
        $toast.error("could'nt delete task, Something went wrong");
        // }
      });
};

getTasks();

function getTasks(page = 1) {
  showLoader();
  http
    .get(`/tasks?page=${page}`)
    .then((response) => {
      if (response.data?.data) {
        tasks.value = response.data?.data;
        pages.value = response.data;
      }
    })
    .catch((error) => {
      // if (error instanceof AxiosError) {
      console.error(
        "Tasks fetching failed:",
        error.response.message || "Internal Server Error"
      );
      //}
    })
    .finally(() => {
      hideLoader();
    });
}

function rootClass(status) {
  return {
    class:
      status === "completed"
        ? "border border-green-500"
        : status === "in_progress"
        ? "border border-orange-500"
        : "border bg-gray-500",
  };
}

function labelClass(status) {
  return {
    class:
      status === "completed"
        ? "text-green-800 italic"
        : status === "in_progress"
        ? "text-orange-800 italic"
        : "text-gray-800 italic",
  };
}

function iconStyle(status) {
  return {
    style:
      status === "completed"
        ? "color: green"
        : status === "in_progress"
        ? "color: orange"
        : "color: gray",
  };
}

const iconClass = function (status) {
  return status === "completed"
    ? "pi pi-check-circle"
    : status === "in_progress"
    ? "pi pi-cog"
    : "pi pi-clock";
};

function updateIconClass(status) {
  return status === "pending"
    ? "fa fa-thin fa-clock-o"
    : status === "in_progress"
    ? "fa fa-thin fa-gear"
    : "fa fa-thin fa-check-circle";
}
</script>

<style scoped>
.orange-chip :deep(.p-chip) {
  background-color: #ffd8b0 !important;
}
</style>
