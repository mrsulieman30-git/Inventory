<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none bg-slate-900/40 dark:bg-black/60 backdrop-blur-sm transition-opacity" @click.self="close">
      <div class="relative w-auto my-6 mx-auto max-w-3xl">
        <!-- Modal content -->
        <div class="border-0 rounded-2xl shadow-2xl relative flex flex-col w-full bg-white dark:bg-dark-surface outline-none focus:outline-none overflow-hidden transform transition-transform duration-300 scale-100">

          <!-- Header -->
          <div class="flex items-start justify-between p-5 border-b border-slate-200 dark:border-dark-border/50 rounded-t-2xl">
            <h3 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">
              {{ title }}
            </h3>
            <button class="p-1 ml-auto bg-transparent border-0 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 float-right text-3xl leading-none font-semibold outline-none focus:outline-none transition-colors" @click="close">
              <span class="bg-transparent h-6 w-6 text-2xl block outline-none focus:outline-none">
                &times;
              </span>
            </button>
          </div>

          <!-- Body -->
          <div class="relative p-6 flex-auto text-slate-600 dark:text-slate-300 text-lg leading-relaxed">
            <slot></slot>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end p-6 border-t border-slate-200 dark:border-dark-border/50 rounded-b-2xl bg-slate-50 dark:bg-slate-800/50">
            <button class="btn text-slate-500 bg-transparent border border-solid border-slate-300 hover:bg-slate-100 hover:text-slate-700 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700 active:bg-slate-200 font-bold uppercase px-6 py-3 rounded-xl outline-none focus:outline-none mr-4 mb-1 ease-linear transition-all duration-150" type="button" @click="close">
              Cancel
            </button>
            <button :class="['btn font-bold uppercase text-white px-6 py-3 rounded-xl shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150', actionClass]" type="button" @click="$emit('confirm')">
              {{ actionText }}
            </button>
          </div>

        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: Boolean,
  title: {
    type: String,
    default: 'Notification'
  },
  actionText: {
    type: String,
    default: 'Confirm'
  },
  actionClass: {
    type: String,
    default: 'btn-primary' // Allows 'btn-success', 'btn-danger' based on action type
  }
});

const emit = defineEmits(['update:show', 'confirm']);

const close = () => {
  emit('update:show', false);
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative {
  transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
}
</style>
