<template>
    <GuestLayout>
        <!-- Privacy Policy Content -->

        <Head title="Privacy Policy" />

        <template #links>
            <nav class="hidden md:flex items-center space-x-6">
                <a :href="home()" class="text-sm font-medium hover:text-primary">Home</a>
            </nav>
        </template>

        <section class="py-12 md:py-16">
            <div class="container mx-auto max-w-4xl px-4 md:px-6">
                <div class="space-y-8">
                    <div class="text-center mb-10">
                        <h1 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl mb-4">
                            Privacy Policy
                        </h1>
                        <p class="text-gray-500 dark:text-gray-400">

                        </p>
                    </div>

                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        <section>
                            <h2>Introduction</h2>
                            <p>
                                At CoachGuide ("we," "our," or "us"), we respect your privacy and are committed to
                                protecting your personal data. This Privacy Policy explains how we collect, use,
                                disclose, and safeguard your information when you use our coaching management platform.
                            </p>
                            <p>
                                Please read this Privacy Policy carefully. By accessing or using our Service, you
                                acknowledge that you have read, understood, and agree to be bound by all the terms of
                                this Privacy Policy.
                            </p>
                        </section>

                        <section>
                            <h2>Information We Collect</h2>
                            <h3>Personal Information</h3>
                            <p>
                                We may collect personal information that you voluntarily provide to us when you:
                            </p>
                            <ul>
                                <li>Register for an account</li>
                                <li>Fill in forms on our platform</li>
                                <li>Subscribe to our service</li>
                                <li>Request customer support</li>
                                <li>Participate in surveys or promotions</li>
                            </ul>
                            <p>
                                This information may include:
                            </p>
                            <ul>
                                <li>Name, email address, and contact details</li>
                                <li>Billing information and payment details</li>
                                <li>Professional information related to your coaching practice</li>
                                <li>Client information that you input into our system</li>
                            </ul>

                            <h3>Automatically Collected Information</h3>
                            <p>
                                When you access our Service, we may automatically collect certain information,
                                including:
                            </p>
                            <ul>
                                <li>Device information (browser type, operating system, IP address)</li>
                                <li>Usage data (pages visited, time spent on pages, links clicked)</li>
                                <li>Cookies and similar tracking technologies</li>
                            </ul>
                        </section>

                        <section>
                            <h2>How We Use Your Information</h2>
                            <p>
                                We use the information we collect for various purposes, including to:
                            </p>
                            <ul>
                                <li>Provide, maintain, and improve our Service</li>
                                <li>Process transactions and manage your account</li>
                                <li>Send you service-related notifications</li>
                                <li>Respond to your comments, questions, and requests</li>
                                <li>Monitor and analyze usage patterns and trends</li>
                                <li>Protect the security and integrity of our Service</li>
                                <li>Comply with legal obligations</li>
                            </ul>
                        </section>

                        <section>
                            <h2>Data Sharing and Disclosure</h2>
                            <p>
                                We may share your information in the following situations:
                            </p>
                            <ul>
                                <li><strong>Service Providers:</strong> We may share your information with third-party
                                    vendors, service providers, and contractors who perform services on our behalf.</li>
                                <li><strong>Business Transfers:</strong> If we are involved in a merger, acquisition, or
                                    sale of all or a portion of our assets, your information may be transferred as part
                                    of that transaction.</li>
                                <li><strong>Legal Requirements:</strong> We may disclose your information if required to
                                    do so by law or in response to valid requests by public authorities.</li>
                                <li><strong>With Your Consent:</strong> We may share your information with your consent
                                    or at your direction.</li>
                            </ul>
                        </section>

                        <section>
                            <h2>Data Security</h2>
                            <p>
                                We implement appropriate technical and organizational measures to protect the security
                                of your personal information. However, please be aware that no method of transmission
                                over the Internet or method of electronic storage is 100% secure.
                            </p>
                        </section>

                        <section>
                            <h2>Your Data Protection Rights</h2>
                            <p>
                                Depending on your location, you may have certain rights regarding your personal
                                information, including:
                            </p>
                            <ul>
                                <li>The right to access your personal information</li>
                                <li>The right to rectify inaccurate or incomplete information</li>
                                <li>The right to erasure of your personal information</li>
                                <li>The right to restrict processing of your personal information</li>
                                <li>The right to data portability</li>
                                <li>The right to object to processing of your personal information</li>
                            </ul>
                            <p>
                                To exercise these rights, please contact us using the information provided in the
                                "Contact Us" section.
                            </p>
                        </section>

                        <section>
                            <h2>Cookies and Tracking Technologies</h2>
                            <p>
                                We use cookies and similar tracking technologies to track activity on our Service and
                                hold certain information. You can instruct your browser to refuse all cookies or to
                                indicate when a cookie is being sent.
                            </p>
                        </section>

                        <section>
                            <h2>Children's Privacy</h2>
                            <p>
                                Our Service is not intended for individuals under the age of 18. We do not knowingly
                                collect personal information from children under 18. If we become aware that we have
                                collected personal information from a child under 18, we will take steps to delete that
                                information.
                            </p>
                        </section>

                        <section>
                            <h2>Changes to This Privacy Policy</h2>
                            <p>
                                We may update our Privacy Policy from time to time. We will notify you of any changes by
                                posting the new Privacy Policy on this page and updating the "Last Updated" date.
                            </p>
                        </section>

                        <section>
                            <h2>Contact Us</h2>
                            <p>
                                If you have any questions about this Privacy Policy, please contact us at:
                            </p>
                            <p>
                                Email: privacy@coachguide.com<br>
                                Address: 123 Privacy Street, Business District, 12345
                            </p>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';
import { terms, privacy } from '@/routes/central';


</script>
