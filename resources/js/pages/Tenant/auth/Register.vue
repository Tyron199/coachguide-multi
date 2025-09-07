<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Tenant/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes/tenant';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

interface Props {
    prefilledData?: {
        name: string;
        email: string;
    } | null;
    isTokenValid?: boolean;
    token?: string;
}

const props = defineProps<Props>();

const formData = ref({
    name: '',
    email: '',
    token: '',
});

// Pre-fill form data if token is valid
onMounted(() => {
    if (props.isTokenValid && props.prefilledData) {
        formData.value.name = props.prefilledData.name;
        formData.value.email = props.prefilledData.email;
    }
    if (props.token) {
        formData.value.token = props.token;
    }
});
</script>

<template>
    <AuthBase :title="props.isTokenValid ? 'Complete your invitation' : 'Create an account'"
        :description="props.isTokenValid ? 'Set your password to complete your account setup' : 'Enter your details below to create your account'">

        <Head title="Register" />

        <Form v-bind="RegisteredUserController.store.form()" :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name"
                        placeholder="Full name" v-model="formData.name" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email"
                        placeholder="email@example.com" v-model="formData.email" :readonly="props.isTokenValid"
                        :class="props.isTokenValid ? 'bg-muted cursor-not-allowed' : ''" />
                    <InputError :message="errors.email" />
                    <p v-if="props.isTokenValid" class="text-xs text-muted-foreground">
                        Email pre-filled from your invitation
                    </p>
                </div>

                <!-- Hidden token field -->
                <input type="hidden" name="token" v-model="formData.token" />

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required :tabindex="3" autocomplete="new-password"
                        name="password" placeholder="Password" />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input id="password_confirmation" type="password" required :tabindex="4" autocomplete="new-password"
                        name="password_confirmation" placeholder="Confirm password" />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="processing">
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
