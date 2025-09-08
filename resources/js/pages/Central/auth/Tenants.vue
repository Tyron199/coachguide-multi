<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Building } from 'lucide-vue-next';
import { select as selectTenant } from '@/routes/central/login/tenants';
import { login } from '@/routes/central';

// Update the Tenant type to match the structure from your controller
interface Tenant {
    id: number;
    name: string;
    url: string;
}

const props = defineProps<{
    tenants: Tenant[];
    email: string;
}>();

const form = useForm({
    tenant: '',
});

// Helper function to extract hostname from URL
const getHostname = (url: string): string => {
    try {
        return new URL(url).hostname;
    } catch (e) {
        console.error('Error extracting hostname from URL:', e);
        return url;
    }
};

const submit = () => {
    // Find the selected tenant
    const selectedTenant = props.tenants.find(t => t.id.toString() === form.tenant);

    if (selectedTenant) {
        // Redirect directly to the tenant URL
        window.location.href = selectedTenant.url;
    } else {
        form.post(selectTenant().url, {
            onFinish: () => form.reset(),
        });
    }
};

</script>

<template>
    <AuthBase title="Select your organization" description="Choose which platform you want to access">

        <Head title="Select Organization" />

        <div class="mb-6 p-4 bg-muted rounded-md">
            <p class="text-sm">
                Your account has access to multiple coaching platforms. Please select which one you want to log in to.
            </p>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label>Select an organization</Label>
                    <RadioGroup v-model="form.tenant" class="grid gap-3">
                        <div v-for="tenant in tenants" :key="tenant.id"
                            class="flex items-center space-x-3 border p-4 rounded-md">
                            <RadioGroupItem :value="tenant.id.toString()" :id="`tenant-${tenant.id}`" />
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                <Building class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <Label :for="`tenant-${tenant.id}`" class="font-medium">{{ tenant.name }}</Label>
                                <p class="text-xs text-muted-foreground">{{ getHostname(tenant.url) }}</p>
                            </div>
                        </div>
                    </RadioGroup>
                    <InputError :message="form.errors.tenant" />
                </div>

                <Button type="submit" class="mt-2 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    Continue to selected platform
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                <TextLink :href="login()" class="underline underline-offset-4">
                    Try a different email
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
