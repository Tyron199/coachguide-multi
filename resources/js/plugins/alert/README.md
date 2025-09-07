# Universal Alert System

A Vue plugin that provides a promise-based alert dialog system using your existing AlertDialog components.

## Installation

The plugin is already registered in `app.ts` and the `AlertProvider` is mounted in `AppLayout.vue`.

## Usage

### 1. Direct Import (Recommended)

```typescript
import { alertConfirm, alertInfo, alertWarning, alertError } from '@/plugins/alert';

// Simple confirmation
const confirmed = await alertConfirm("Are you sure you want to delete this?", "destructive");
if (confirmed) {
  // User clicked confirm
}

// Advanced confirmation with custom options
const confirmed = await alertConfirm({
  title: "Delete Selected Items",
  description: "This action cannot be undone and will permanently remove all selected data.",
  confirmText: "Delete Items",
  cancelText: "Cancel",
  variant: "destructive"
});

// Info, warning, and error alerts (no cancel button)
await alertInfo("Operation completed successfully!");
await alertWarning("This action may have side effects.");
await alertError("An error occurred while processing your request.");
```

### 2. Using the Composable

```typescript
import { useAlert } from '@/plugins/alert';

const alert = useAlert();

const handleDelete = async () => {
  const confirmed = await alert.confirm("Delete this item?", "destructive");
  if (confirmed) {
    // Delete logic
  }
};
```

### 3. Global Properties (Options API)

```typescript
// In a Vue component using Options API
export default {
  methods: {
    async handleAction() {
      const confirmed = await this.$alert.confirm("Continue?");
      if (confirmed) {
        // Action logic
      }
    }
  }
}
```

## API Reference

### `alertConfirm(input, variant?)`

**Parameters:**
- `input`: `string | AlertOptions` - Either a simple message string or a full options object
- `variant`: `'default' | 'destructive'` - Button variant (only used when input is a string)

**Returns:** `Promise<boolean>` - `true` if confirmed, `false` if cancelled

### `alertInfo(message)`, `alertWarning(message)`, `alertError(message)`

**Parameters:**
- `message`: `string` - The message to display

**Returns:** `Promise<void>` - Resolves when the user clicks OK

### AlertOptions Interface

```typescript
interface AlertOptions {
  title: string;           // Dialog title
  description?: string;    // Dialog description/message
  variant?: 'default' | 'destructive'; // Button styling
  confirmText?: string;    // Confirm button text (default: "Confirm")
  cancelText?: string;     // Cancel button text (default: "Cancel")
  showCancel?: boolean;    // Show cancel button (default: true)
}
```

## Examples from ListClients.vue

Before (78 lines of AlertDialog markup):
```vue
<AlertDialog v-if="selectedClients.length > 0">
  <AlertDialogTrigger as-child>
    <Button variant="destructive">Delete Selected</Button>
  </AlertDialogTrigger>
  <AlertDialogContent>
    <!-- ... lots of markup ... -->
  </AlertDialogContent>
</AlertDialog>
```

After (clean and simple):
```vue
<Button 
  variant="destructive"
  @click="handleDeleteSelected"
>
  Delete Selected
</Button>
```

```typescript
const handleDeleteSelected = async () => {
  const confirmed = await alertConfirm({
    title: 'Delete Selected Clients',
    description: 'This action cannot be undone...',
    variant: 'destructive'
  });
  
  if (confirmed) {
    // Execute delete logic
  }
};
```

## Benefits

- **Reduced Code**: Eliminates repetitive AlertDialog markup
- **Consistent UX**: All alerts look and behave the same
- **Promise-based**: Clean async/await syntax
- **Type Safe**: Full TypeScript support
- **Flexible**: Simple strings or complex configurations
- **Maintainable**: Single place to update alert behavior
