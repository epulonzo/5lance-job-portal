<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'text'
  },
  id: {
    type: String,
    default: () => `input-${Math.random().toString(36).substr(2, 9)}`
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const inputClasses = computed(() => {
  const base = 'w-full px-4 py-3 bg-white dark:bg-slate-900 border text-slate-900 dark:text-white rounded-xl transition-all duration-200 outline-none focus:ring-2';
  const normal = 'border-slate-200 dark:border-slate-800 focus:border-brand-500 focus:ring-brand-500/20';
  const errorState = 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20';
  return `${base} ${props.error ? errorState : normal}`;
});
</script>

<template>
  <div class="w-full flex flex-col gap-1.5 text-left">
    <label
      :for="id"
      class="text-xs font-semibold text-slate-500 dark:text-slate-400 tracking-wider uppercase"
    >
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>
    <div class="relative">
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :class="inputClasses"
        :required="required"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
    </div>
    <!-- Error Text -->
    <transition
      enter-active-class="transition-all duration-200 ease-out"
      leave-active-class="transition-all duration-150 ease-in"
      enter-from-class="opacity-0 -translate-y-1"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <span v-if="error" class="text-xs text-rose-500 font-medium mt-0.5">{{ error }}</span>
    </transition>
  </div>
</template>
