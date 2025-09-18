<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Form } from '@inertiajs/vue3';
import { LoaderCircle, Shield } from 'lucide-vue-next';
import { logout } from '@/routes/tenant';
import TwoFactorController from '@/actions/App/Http/Controllers/Tenant/Settings/TwoFactorController';
import { ref } from 'vue';

const codeInput = ref<HTMLInputElement | null>(null);
</script>

<template>
    <AuthBase title="Two-Factor Authentication" description="Please enter the 6-digit code from your authenticator app">

        <Head title="2FA Verification" />

        <Form v-bind="TwoFactorController.verify.form()" :reset-on-success="['code']" v-slot="{ errors, processing }"
            class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="code">Authentication Code</Label>
                    <Input id="code" ref="codeInput" type="text" name="code" required autofocus :tabindex="1"
                        maxlength="6" autocomplete="one-time-code" placeholder="000000"
                        class="text-center text-lg tracking-widest font-mono" />
                    <InputError :message="errors.code" />
                    <p class="text-xs text-muted-foreground text-center">
                        Enter the 6-digit code from your authenticator app
                    </p>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="2" :disabled="processing">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin mr-2" />
                    <Shield v-else class="h-4 w-4 mr-2" />
                    Verify
                </Button>
            </div>

            <TextLink :href="logout()" as="button" class="mx-auto block text-sm text-muted-foreground" :tabindex="3">
                Log out
            </TextLink>
        </Form>

    </AuthBase>
</template>