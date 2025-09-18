<script setup lang="ts">
import TwoFactorController from '@/actions/App/Http/Controllers/Tenant/Settings/TwoFactorController';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { show } from '@/routes/tenant/two-factor';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Shield, ShieldCheck, ShieldX } from 'lucide-vue-next';

interface Props {
    twoFactorEnabled: boolean;
    qrCodeUrl?: string;
    secret?: string;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: '2FA settings',
        href: show().url,
    },
];

const codeInput = ref<HTMLInputElement | null>(null);
const passwordInput = ref<HTMLInputElement | null>(null);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="2FA settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Two-Factor Authentication"
                    description="Add an extra layer of security to your account with two-factor authentication" />

                <!-- Current Status -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ShieldCheck v-if="props.twoFactorEnabled" class="h-5 w-5 text-green-600" />
                            <ShieldX v-else class="h-5 w-5 text-gray-400" />
                            Status
                        </CardTitle>
                        <CardDescription>
                            <Badge v-if="props.twoFactorEnabled" variant="default" class="bg-green-100 text-green-800">
                                Enabled
                            </Badge>
                            <Badge v-else variant="secondary">
                                Disabled
                            </Badge>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p v-if="props.twoFactorEnabled" class="text-sm text-muted-foreground">
                            Two-factor authentication is currently enabled for your account. You will be prompted for a
                            verification code when logging in.
                        </p>
                        <p v-else class="text-sm text-muted-foreground">
                            Two-factor authentication is not enabled. Enable it below to add an extra layer of security
                            to your account.
                        </p>
                    </CardContent>
                </Card>

                <!-- Enable 2FA Section -->
                <Card v-if="!props.twoFactorEnabled">
                    <CardHeader>
                        <CardTitle>Enable Two-Factor Authentication</CardTitle>
                        <CardDescription>
                            Scan the QR code below with your authenticator app, then enter the verification code to
                            enable 2FA.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- QR Code Section -->
                        <div v-if="props.qrCodeUrl" class="space-y-4">
                            <div class="text-center">
                                <div class="inline-block p-4 bg-white border rounded-lg">
                                    <img :src="props.qrCodeUrl" alt="2FA QR Code" class="w-48 h-48" />
                                </div>
                            </div>

                            <div class="text-center">
                                <p class="text-sm text-muted-foreground mb-2">
                                    Can't scan the QR code? Enter this secret manually:
                                </p>
                                <code class="px-2 py-1 bg-muted rounded text-sm font-mono">{{ props.secret }}</code>
                            </div>

                            <!-- Verification Form -->
                            <Form v-bind="TwoFactorController.confirm.form()" :options="{
                                preserveScroll: true,
                            }" reset-on-success class="space-y-4" v-slot="{ errors, processing, recentlySuccessful }">
                                <div class="grid gap-2">
                                    <Label for="code">Verification Code</Label>
                                    <Input id="code" ref="codeInput" name="code" type="text" maxlength="6"
                                        class="text-center text-lg tracking-widest font-mono" placeholder="000000"
                                        autocomplete="one-time-code" />
                                    <InputError :message="errors.code" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <Button :disabled="processing" type="submit">
                                        Enable Two-Factor Authentication
                                    </Button>

                                    <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                        <p v-show="recentlySuccessful" class="text-sm text-green-600">Two-factor
                                            authentication enabled successfully!</p>
                                    </Transition>
                                </div>
                            </Form>
                        </div>

                        <!-- Enable Button -->
                        <div v-else>
                            <Form v-bind="TwoFactorController.enable.form()" :options="{
                                preserveScroll: true,
                            }" v-slot="{ processing }">
                                <Button :disabled="processing" type="submit" class="w-full">
                                    <Shield class="h-4 w-4 mr-2" />
                                    Generate QR Code
                                </Button>
                            </Form>
                        </div>
                    </CardContent>
                </Card>

                <!-- Disable 2FA Section -->
                <Card v-if="props.twoFactorEnabled">
                    <CardHeader>
                        <CardTitle class="text-destructive">Disable Two-Factor Authentication</CardTitle>
                        <CardDescription>
                            Remove two-factor authentication from your account. This will make your account less secure.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form v-bind="TwoFactorController.disable.form()" :options="{
                            preserveScroll: true,
                        }" reset-on-success class="space-y-4" v-slot="{ errors, processing, recentlySuccessful }">
                            <div class="grid gap-2">
                                <Label for="password">Confirm Password</Label>
                                <Input id="password" ref="passwordInput" name="password" type="password"
                                    class="mt-1 block w-full" autocomplete="current-password"
                                    placeholder="Enter your password to confirm" />
                                <InputError :message="errors.password" />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button :disabled="processing" type="submit" variant="destructive">
                                    Disable Two-Factor Authentication
                                </Button>

                                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                    <p v-show="recentlySuccessful" class="text-sm text-orange-600">Two-factor
                                        authentication has been disabled.</p>
                                </Transition>
                            </div>
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
