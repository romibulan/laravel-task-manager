<template>
  <div>
    <Dialog v-model:visible="show" modal header="Task" :style="{ width: '400px' }">
      <span class="text-surface-500 block mb-8">{{
        form.id ? "Update your Task" : "Create new Task"
      }}</span>
      <FormKit
        :disabled="form.status === 'completed'"
        id="task-form"
        type="form"
        :actions="false"
        @submit="handleSubmit"
      >
        <FormKit
          type="text"
          v-model="form.title"
          name="title"
          label="Task Title"
          validation="required"
        />
        <FormKit
          type="textarea"
          v-model="form.description"
          name="description"
          label="Task Description"
        />

        <FormKit
          type="date"
          v-model="form.due_date"
          name="due_date"
          label="Due Date"
          :validation="`required|date_after:${today}`"
          :validation-messages="{
            date_after: 'Please select a date after today.',
          }"
        />
        <FormKit
          v-if="form.id"
          type="select"
          v-model="form.status"
          name="status"
          label="Status"
          :options="statuses"
        />

        <div class="flex items-center justify-end space-x-2 mt-4">
          <Button v-if="form.id" :loading="loading" type="submit" label="Update Task" />
          <Button v-else :loading="loading" type="submit" label="Add Task" />
          <Button label="Cancel" severity="secondary" @click="$emit('cancelTask')" />
        </div>
      </FormKit>
    </Dialog>
  </div>
</template>

<script setup>
import Button from "PrimeVue/Button";
import Dialog from "primevue/dialog";

import { ref, watch, computed } from "vue";
import { reset } from "@formkit/core";
import http from "../utils/http";

const today = new Date().toISOString().split("T")[0];

const show = ref(false);

const props = defineProps({
  initialData: {
    type: Object,
    required: false,
    default: {
      title: "",
      description: "",
      due_date: "",
      status: "pending",
    },
  },
  showDialog: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const emit = defineEmits(["taskAdded", "cancelTask"]);

const form = ref({ ...props.initialData });

const loading = ref(false);

watch(
  () => props.initialData,
  (newData) => {
    if (newData) {
      console.log("show.value: ", show.value);
      console.log("Updating form with new initialData: ", newData);
      form.value = newData;
    }
  },
  {
    immediate: true,
    // deep: true,
  }
);

watch(
  () => props.showDialog,
  (newVal) => {
    show.value = newVal;
    console.log("showDialog changed: ", newVal, "show.value: ", show.value);
  },
  {
    immediate: true,
  }
);

const statuses = computed(() => {
  if (form.value.id) {
    let options = form.value.extra.data.transitions.map((t) => {
      let subset = ["label", "value"].reduce((acc, key) => {
        if (key in t) acc[key] = t[key];
        return acc;
      }, {});
      return subset;
    });
    options.unshift({
      label: form.value.extra.data.label,
      value: form.value.status,
    });
    return options;
  }
});

const handleSubmit = (formData, node) => {
  let endpoint = form.value.id ? `/tasks/${form.value.id}` : "/tasks";
  let method = form.value.id ? "put" : "post";
  loading.value = true;
  http({ method, url: endpoint, data: formData })
    .then((response) => {
      console.log("Task created: ", response);
      if (response.data?.success) {
        response.data?.data
          ? emit("taskAdded", response.data.data)
          : emit("taskAdded", null);
      }
    })
    .catch((error) => {
      console.error("Error saving task:", error);
      if (
        error.response &&
        error.response.status === 422 &&
        error.response?.data?.errors
      ) {
        const errors = error.response.data.errors;
        for (const field in errors) {
          if (errors.hasOwnProperty(field)) {
            errors[field] = errors[field].join("<br>");
          }
        }
        node.setErrors(
          ["The server rejected this request."], // General form errors
          errors
        );
      }
    })
    .finally(() => {
      loading.value = false;
    });
};
</script>

<style lang="scss" scoped></style>
