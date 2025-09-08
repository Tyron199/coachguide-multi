<script setup lang="ts">
import { store as registerAction } from '@/actions/App/Http/Controllers/Central/RegistrationController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes/central';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { useSubdomain } from '@/composables/useSubdomain';

const page = usePage();
const domainSuffix = page.props.domain_suffix as string;
const reservedSubdomains = page.props.reserved_subdomains as string[];

// Use the shared subdomain composable
const {
    companyName,
    subdomain,
    subdomainError,
    isSubdomainValid,
    markAsManuallyEdited
} = useSubdomain(reservedSubdomains);
</script>

<template>
    <AuthBase title="Create your coaching platform"
        description="Enter your details below to create your coachguide platform">

        <Head title="Register" />

        <Form v-bind="registerAction.form()" v-slot="{ errors, processing }" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Your Name</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name"
                        placeholder="Full name" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email"
                        placeholder="email@example.com" />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="company_name">Company Name</Label>
                    <Input id="company_name" type="text" required :tabindex="3" name="company_name"
                        placeholder="Your Coaching Business" v-model="companyName" />
                    <InputError :message="errors.company_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="subdomain">Domain</Label>
                    <div class="relative w-full items-center">
                        <Input id="subdomain" type="text" required :tabindex="4" name="subdomain"
                            placeholder="your-platform" v-model="subdomain" @input="markAsManuallyEdited" class="pr-32"
                            :class="subdomainError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : isSubdomainValid ? 'border-green-500 focus:border-green-500 focus:ring-green-500' : ''" />
                        <span
                            class="absolute end-0 inset-y-0 flex items-center justify-center px-3 text-sm text-muted-foreground">
                            .{{ domainSuffix }}
                        </span>
                    </div>
                    <div class="text-xs text-muted-foreground space-y-1">
                        <div>This will be your unique link:</div>
                        <div class="font-mono text-blue-600 break-all">https://{{ subdomain || 'your-platform' }}.{{
                            domainSuffix }}</div>
                    </div>
                    <div v-if="!subdomain" class="text-xs text-muted-foreground">
                        Use letters, numbers, and hyphens only. 3-30 characters.
                    </div>
                    <InputError :message="subdomainError || errors.subdomain" />
                </div>

                <Button type="submit" class="mt-2 w-full" :tabindex="5" :disabled="processing || !!subdomainError">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
