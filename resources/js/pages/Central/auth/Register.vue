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
import { useRecaptcha } from '@/composables/useRecaptcha';
import { ref } from 'vue';

const page = usePage();
const recaptchaSiteKey = page.props.recaptcha_site_key as string;

const recaptchaValid = ref(false);
// Initialize reCAPTCHA (only if we have a site key)
const { containerRef, getResponse } = useRecaptcha({
    siteKey: recaptchaSiteKey || '',
    size: 'normal',
    callback: () => {
        recaptchaValid.value = true;
    },
    expiredCallback: () => {
        recaptchaValid.value = false;
    },
    errorCallback: () => {
        recaptchaValid.value = false;
    }
});



// Function to get the current reCAPTCHA token
const getRecaptchaToken = () => {
    return recaptchaSiteKey ? (getResponse() || '') : '';
};
</script>

<template>
    <AuthBase title="Create your coaching platform"
        description="Enter your details below to create your coachguide platform">

        <Head title="Register" />

        <Form v-bind="registerAction.form()" v-slot="{ errors, processing }" class="flex flex-col gap-6"
            disableWhileProcessing>
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
                        placeholder="Your Coaching Business" />
                    <InputError :message="errors.company_name" />
                </div>

                <!-- reCAPTCHA -->
                <div v-if="recaptchaSiteKey" class="flex justify-center">
                    <div ref="containerRef"></div>
                </div>
                <InputError :message="errors['g-recaptcha-response']" />

                <!-- Hidden input for reCAPTCHA token -->

                <input type="hidden" required name="g-recaptcha-response" :value="getRecaptchaToken()" />

                <Button type="submit" class="mt-2 w-full" :tabindex="4" :disabled="processing || !recaptchaValid">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    Continue
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4" :tabindex="5">Log in</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
