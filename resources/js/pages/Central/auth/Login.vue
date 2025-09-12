<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Form } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { register } from '@/routes/central';
import { store as loginAction } from '@/actions/App/Http/Controllers/Central/LoginController';



</script>

<template>
    <AuthBase title="Log in to your coaching platform" description="Enter your email to get started">

        <Head title="Login" />



        <Form v-bind="loginAction.form()" v-slot="{ errors, processing }" class="flex flex-col gap-6"
            disableWhileProcessing>
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required autofocus :tabindex="1" autocomplete="email" name="email"
                        placeholder="email@example.com" />
                    <InputError :message="errors.email" />
                </div>

                <Button type="submit" class="mt-2 w-full" :disabled="processing" :tabindex="2">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin mr-2" />
                    Continue
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="register()" class="underline underline-offset-4" :tabindex="3">Register
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
