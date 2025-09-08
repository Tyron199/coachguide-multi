<script setup lang="ts">
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
import { submitConfirm } from '@/actions/App/Http/Controllers/Central/RegistrationController';
import { onMounted, ref } from 'vue';

const page = usePage();
const registration = page.props.registration as any;
const token = page.props.token as string;
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

//on mount set suggested subdomain
onMounted(() => {
    companyName.value = registration.company_name;
});

</script>

<template>
    <AuthBase title="Complete your registration"
        :description="`Welcome back ${registration.name}! Complete your ${registration.company_name} coaching platform setup.`">

        <Head title="Complete Registration" />

        <Form v-bind="submitConfirm.form(token)" v-slot="{ errors, processing }" class="flex flex-col gap-6"
            disableWhileProcessing>
            <div class="grid gap-6">
                <!-- Registration Info Fields -->
                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="name">Your Name</Label>
                        <Input id="name" type="text" required :tabindex="1" autocomplete="name" name="name"
                            placeholder="Full name" :default-value="registration.name" />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email"
                            placeholder="email@example.com" :default-value="registration.email" />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="company_name">Company Name</Label>
                        <Input id="company_name" type="text" required :tabindex="3" name="company_name"
                            placeholder="Your Coaching Business" :default-value="registration.company_name" />
                        <InputError :message="errors.company_name" />
                    </div>
                </div>

                <!-- Subdomain Selection -->
                <div class="grid gap-2">
                    <Label for="subdomain">Choose Your Domain</Label>
                    <div class="relative w-full items-center">
                        <Input id="subdomain" type="text" required :tabindex="4" name="subdomain" v-model="subdomain"
                            @input="markAsManuallyEdited" placeholder="your-platform" class="pr-32"
                            :class="subdomainError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : isSubdomainValid ? 'border-green-500 focus:border-green-500 focus:ring-green-500' : ''" />
                        <span
                            class="absolute end-0 inset-y-0 flex items-center justify-center px-3 text-sm text-muted-foreground">
                            .{{ domainSuffix }}
                        </span>
                    </div>
                    <div class="text-xs text-muted-foreground space-y-1">
                        <div>This will be your unique platform URL:</div>
                        <div class="font-mono text-primary break-all">
                            https://{{ subdomain || 'your-platform' }}.{{ domainSuffix }}
                        </div>
                    </div>
                    <div v-if="!subdomain" class="text-xs text-muted-foreground">
                        Use letters, numbers, and hyphens only. 3-30 characters.
                    </div>
                    <InputError :message="subdomainError || errors.subdomain" />
                </div>

                <!-- Password Fields -->
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required :tabindex="5" name="password"
                        placeholder="Create a secure password" />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input id="password_confirmation" type="password" required :tabindex="6"
                        name="password_confirmation" placeholder="Confirm your password" />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" :tabindex="7" :disabled="processing || !!subdomainError">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    Create My Coaching Platform
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4" :tabindex="8">Log in</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
