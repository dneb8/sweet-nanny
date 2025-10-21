<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Icon } from '@iconify/vue'
import { cn } from '@/lib/utils'

interface TagsInputProps {
  modelValue?: string[]
  placeholder?: string
  class?: string
  options?: { value: string; label: string }[]
  disabled?: boolean
}

const props = withDefaults(defineProps<TagsInputProps>(), {
  modelValue: () => [],
  placeholder: 'Agregar...',
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string[]]
}>()

const inputValue = ref('')
const showSuggestions = ref(false)

const tags = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value),
})

const filteredOptions = computed(() => {
  if (!props.options || !inputValue.value) return props.options || []
  const search = inputValue.value.toLowerCase()
  return props.options.filter(
    (opt) =>
      opt.label.toLowerCase().includes(search) &&
      !tags.value.includes(opt.value)
  )
})

function addTag(value?: string) {
  const tagValue = value || inputValue.value.trim()
  if (!tagValue) return
  
  if (!tags.value.includes(tagValue)) {
    tags.value = [...tags.value, tagValue]
  }
  inputValue.value = ''
  showSuggestions.value = false
}

function removeTag(index: number) {
  tags.value = tags.value.filter((_, i) => i !== index)
}

function handleKeyDown(e: KeyboardEvent) {
  if (e.key === 'Enter') {
    e.preventDefault()
    if (filteredOptions.value.length > 0) {
      addTag(filteredOptions.value[0].value)
    } else {
      addTag()
    }
  } else if (e.key === 'Backspace' && !inputValue.value && tags.value.length > 0) {
    removeTag(tags.value.length - 1)
  }
}

function selectOption(value: string) {
  addTag(value)
}

watch(inputValue, (val) => {
  showSuggestions.value = !!val && !!props.options
})
</script>

<template>
  <div class="relative w-full">
    <div
      :class="
        cn(
          'flex min-h-10 w-full flex-wrap gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2',
          props.class
        )
      "
    >
      <Badge
        v-for="(tag, index) in tags"
        :key="index"
        variant="secondary"
        class="gap-1 pr-1"
      >
        <span>{{ props.options?.find(o => o.value === tag)?.label || tag }}</span>
        <button
          v-if="!props.disabled"
          type="button"
          @click="removeTag(index)"
          class="ml-1 rounded-sm hover:bg-muted"
        >
          <Icon icon="lucide:x" class="h-3 w-3" />
        </button>
      </Badge>

      <Input
        v-if="!props.disabled"
        v-model="inputValue"
        type="text"
        :placeholder="tags.length === 0 ? props.placeholder : ''"
        class="h-6 flex-1 border-0 bg-transparent px-0 focus-visible:ring-0 focus-visible:ring-offset-0"
        @keydown="handleKeyDown"
      />
    </div>

    <!-- Suggestions dropdown -->
    <div
      v-if="showSuggestions && filteredOptions.length > 0"
      class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-popover p-1 shadow-md"
    >
      <button
        v-for="option in filteredOptions"
        :key="option.value"
        type="button"
        class="relative flex w-full cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground"
        @click="selectOption(option.value)"
      >
        {{ option.label }}
      </button>
    </div>
  </div>
</template>
