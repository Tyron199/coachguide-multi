# Polling

## Poll helper

Polling your server for new information on the current page is common, so Inertia provides a poll helper designed to help reduce the amount of boilerplate code. In addition, the poll helper will automatically stop polling when the page is unmounted.

The only required argument is the polling interval in milliseconds.

Vue:

```js
import { usePoll } from '@inertiajs/vue3'
usePoll(2000)
```

React:

```jsx
import { usePoll } from '@inertiajs/react'
usePoll(2000)
```

Svelte:

```js
import { usePoll } from '@inertiajs/svelte'
usePoll(2000)
```

If you need to pass additional request options to the poll helper, you can pass any of the `router.reload` options as the second parameter.

Vue:

```js
import { usePoll } from '@inertiajs/vue3'
usePoll(2000, {
onStart() {
console.log('Polling request started')
},
onFinish() {
console.log('Polling request finished')
}
})
```

React:

```jsx
import { usePoll } from '@inertiajs/react'
usePoll(2000, {
onStart() {
console.log('Polling request started')
},
onFinish() {
console.log('Polling request finished')
}
})
```

Svelte:

```js
import { usePoll } from '@inertiajs/svelte'
usePoll(2000, {
onStart() {
console.log('Polling request started')
},
onFinish() {
console.log('Polling request finished')
}
})
```

If you'd like more control over the polling behavior, the poll helper provides `stop` and `start`methods that allow you to manually start and stop polling. You can pass the `autoStart: false` option to the poll helper to prevent it from automatically starting polling when the component is mounted.

Vue:

```markup
<script setup>
import { usePoll } from '@inertiajs/vue3'
const { start, stop } = usePoll(2000, {}, {
autoStart: false,
})
</script>
<template>
<button @click="start">Start polling</button>
<button @click="stop">Stop polling</button>
</template>
```

React:

```jsx
import { usePoll } from '@inertiajs/react'
export default () => {
const { start, stop } = usePoll(2000, {}, {
autoStart: false,
})
return (
<div>
<button onClick={start}>Start polling</button>
<button onClick={stop}>Stop polling</button>
</div>
)
}
```

Svelte:

```js
import { usePoll } from '@inertiajs/svelte'
const { start, stop } = usePoll(2000, {}, {
autoStart: false,
})
```

## Throttling

By default, the poll helper will throttle requests by 90% when the browser tab is in the background. If you'd like to disable this behavior, you can pass the `keepAlive` option to the poll helper.

Vue:

```js
import { usePoll } from '@inertiajs/vue3'
usePoll(2000, {}, {
keepAlive: true,
})
```

React:

```jsx
import { usePoll } from '@inertiajs/react'
usePoll(2000, {}, {
keepAlive: true,
})
```

Svelte:

```js
import { usePoll } from '@inertiajs/svelte'
usePoll(2000, {}, {
keepAlive: true,
})
```