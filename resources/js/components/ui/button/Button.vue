<script setup lang="ts">
import type { PrimitiveProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import type { ButtonVariants } from "."
import { computed } from "vue"
import { Primitive } from "reka-ui"
import { cn } from "@/lib/utils"
import { buttonVariants } from "."

interface Props extends PrimitiveProps {
  variant?: ButtonVariants["variant"]
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  as: "button",
})

// Create responsive classes for mobile-first sizing
const responsiveClasses = computed(() => {
  if (!props.size || props.size === 'default') {
    // Mobile: sm size, Desktop: default size
    return 'h-8 px-3 has-[>svg]:px-2.5 md:h-9 md:px-4 md:has-[>svg]:px-3'
  }
  if (props.size === 'lg') {
    // Mobile: default size, Desktop: lg size  
    return 'h-9 px-4 has-[>svg]:px-3 md:h-10 md:px-6 md:has-[>svg]:px-4'
  }
  // For 'sm' and 'icon', return empty string to use original size
  return ''
})
</script>

<template>
  <Primitive data-slot="button" :as="as" :as-child="asChild" :class="cn(
    buttonVariants({ variant, size }),
    responsiveClasses,
    props.class
  )">
    <slot />
  </Primitive>
</template>
