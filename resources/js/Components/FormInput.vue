<script setup>
defineProps({
    label: String,
    modelValue: [String, Number],
    type: { type: String, default: 'text' },
    error: String,
    required: Boolean,
    placeholder: String,
});
defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>
        <textarea
            v-if="type === 'textarea'"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="input-field"
            :placeholder="placeholder"
            rows="4"
        />
        <input
            v-else
            :type="type"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="input-field"
            :placeholder="placeholder"
        />
        <p v-if="error" class="text-xs text-red-600 mt-1">{{ error }}</p>
    </div>
</template>
