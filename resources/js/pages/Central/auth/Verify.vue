<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthBase from '@/layouts/AuthLayout.vue';
import TextLink from '@/components/TextLink.vue';
import { register } from '@/routes/central/';

defineProps({
    email: String,
});
</script>

<template>
    <AuthBase title="Verification email sent"
        :description="`We've sent an email to ${email} with a link to complete your registration`">

        <Head title="Registration Initiated" />

        <div class="flex flex-col items-center">
            <div class="text-center text-sm text-muted-foreground">
                <p class="mb-4">
                    The link will expire in 24 hours. If you don't see the email in your inbox, please check your spam
                    folder.
                </p>
                <p>
                    Didn't receive the email?
                    <TextLink :href="register()" class="underline underline-offset-4">Try
                        again
                        with a different email</TextLink>
                </p>
            </div>


        </div>
    </AuthBase>
</template>
