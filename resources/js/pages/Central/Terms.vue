<template>
    <GuestLayout>
        <!-- Terms and Conditions Content -->

        <Head title="Terms and Conditions" />

        <template #links>
            <nav class="hidden md:flex items-center space-x-6">
                <TextLink :href="home()" class="text-sm font-medium hover:text-primary">Home</TextLink>
            </nav>
        </template>

        <section class="py-12 md:py-16">
            <div class="container mx-auto max-w-4xl px-4 md:px-6">
                <div class="space-y-8">
                    <div class="text-center mb-10">
                        <h1 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl mb-4">
                            Terms and Conditions
                        </h1>
                        <p class="text-gray-500 dark:text-gray-400">
                            Last Updated: {{ new Date().toLocaleDateString() }}
                        </p>
                    </div>

                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        <section>
                            <h2>1. Introduction</h2>
                            <p>
                                Welcome to CoachGuide ("we," "our," or "us"). By accessing or using our coaching
                                management
                                platform
                                (the "Service"), you agree to be bound by these Terms and Conditions ("Terms"). Please
                                read
                                these
                                Terms carefully before using our Service.
                            </p>
                            <p>
                                If you do not agree to these Terms, please do not use our Service. By using the Service,
                                you
                                represent
                                that you are at least 18 years of age and have the legal capacity to enter into these
                                Terms.
                            </p>
                        </section>

                        <section>
                            <h2>2. Definitions</h2>
                            <p>
                                <strong>"Account"</strong> means a unique account created for you to access our Service.
                            </p>
                            <p>
                                <strong>"Coach"</strong> refers to users who utilize the Service to manage their
                                coaching
                                practice.
                            </p>
                            <p>
                                <strong>"Coachee" or "Client"</strong> refers to individuals who receive coaching
                                services from
                                Coaches.
                            </p>
                            <p>
                                <strong>"Content"</strong> refers to any information, data, text, documents, or other
                                materials
                                uploaded,
                                stored, or shared through the Service.
                            </p>
                            <p>
                                <strong>"Subscription"</strong> refers to the recurring payment arrangement for access
                                to the
                                Service.
                            </p>
                        </section>

                        <section>
                            <h2>3. Account Registration</h2>
                            <p>
                                To use certain features of the Service, you must register for an Account. You agree to
                                provide
                                accurate,
                                current, and complete information during the registration process and to update such
                                information
                                to keep
                                it accurate, current, and complete.
                            </p>
                            <p>
                                You are responsible for safeguarding your password and for all activities that occur
                                under your
                                Account.
                                You agree to notify us immediately of any unauthorized use of your Account or any other
                                breach
                                of security.
                            </p>
                        </section>

                        <section>
                            <h2>4. Subscription and Payments</h2>
                            <p>
                                The Service is available on a subscription basis. By subscribing to our Service, you
                                agree to
                                pay the
                                applicable subscription fees as they become due.
                            </p>
                            <p>
                                Subscription fees are billed in advance on a recurring basis according to the billing
                                cycle you
                                select.
                                Unless otherwise stated, subscriptions automatically renew until canceled.
                            </p>
                            <p>
                                You may cancel your subscription at any time through your account settings or by
                                contacting our
                                customer
                                support. Upon cancellation, your subscription will remain active until the end of the
                                current
                                billing period.
                            </p>
                            <p>
                                We reserve the right to change our subscription fees upon reasonable notice. Such notice
                                will be
                                provided
                                via email or through the Service.
                            </p>
                        </section>

                        <section>
                            <h2>5. User Content and Responsibilities</h2>
                            <p>
                                You retain all rights to any Content you submit, post, or display on or through the
                                Service. By
                                submitting,
                                posting, or displaying Content, you grant us a worldwide, non-exclusive, royalty-free
                                license to
                                use,
                                reproduce, adapt, modify, and process the Content solely for the purpose of providing
                                the
                                Service.
                            </p>
                            <p>
                                You are solely responsible for all Content that you upload, post, email, transmit, or
                                otherwise
                                make
                                available via the Service. You represent and warrant that:
                            </p>
                            <ul>
                                <li>You own or have the necessary rights to use and authorize us to use the Content;
                                </li>
                                <li>The Content does not violate any third party's rights, including intellectual
                                    property
                                    rights and privacy rights;</li>
                                <li>The Content complies with these Terms and all applicable laws and regulations.</li>
                            </ul>
                        </section>

                        <section>
                            <h2>6. Acceptable Use</h2>
                            <p>
                                You agree not to use the Service to:
                            </p>
                            <ul>
                                <li>Violate any applicable laws or regulations;</li>
                                <li>Infringe upon the rights of others;</li>
                                <li>Upload or transmit viruses, malware, or other malicious code;</li>
                                <li>Interfere with or disrupt the integrity or performance of the Service;</li>
                                <li>Attempt to gain unauthorized access to the Service or related systems;</li>
                                <li>Collect or harvest data about other users without their consent;</li>
                                <li>Send spam, unsolicited communications, or advertisements.</li>
                            </ul>
                        </section>

                        <section>
                            <h2>7. Intellectual Property Rights</h2>
                            <p>
                                The Service and its original content (excluding Content provided by users), features,
                                and
                                functionality
                                are and will remain the exclusive property of CoachGuide and its licensors. The Service
                                is
                                protected by
                                copyright, trademark, and other laws.
                            </p>
                            <p>
                                Our trademarks and trade dress may not be used in connection with any product or service
                                without
                                our
                                prior written consent.
                            </p>
                        </section>

                        <section>
                            <h2>8. Confidentiality and Data Protection</h2>
                            <p>
                                We understand the sensitive nature of coaching relationships. We are committed to
                                maintaining
                                the
                                confidentiality of information shared through our Service. Please refer to our Privacy
                                Policy
                                for
                                details on how we collect, use, and protect your personal information.
                            </p>
                            <p>
                                As a Coach using our Service, you are responsible for maintaining the confidentiality of
                                your
                                Clients'
                                information in accordance with applicable professional standards and regulations.
                            </p>
                        </section>

                        <section>
                            <h2>9. Limitation of Liability</h2>
                            <p>
                                To the maximum extent permitted by law, in no event shall CoachGuide, its directors,
                                employees,
                                partners,
                                agents, suppliers, or affiliates be liable for any indirect, incidental, special,
                                consequential,
                                or
                                punitive damages, including without limitation, loss of profits, data, use, goodwill, or
                                other
                                intangible
                                losses, resulting from:
                            </p>
                            <ul>
                                <li>Your access to or use of or inability to access or use the Service;</li>
                                <li>Any conduct or content of any third party on the Service;</li>
                                <li>Any content obtained from the Service; and</li>
                                <li>Unauthorized access, use, or alteration of your transmissions or content.</li>
                            </ul>
                        </section>

                        <section>
                            <h2>10. Disclaimer of Warranties</h2>
                            <p>
                                The Service is provided on an "AS IS" and "AS AVAILABLE" basis. CoachGuide expressly
                                disclaims
                                all
                                warranties of any kind, whether express or implied, including but not limited to the
                                implied
                                warranties
                                of merchantability, fitness for a particular purpose, and non-infringement.
                            </p>
                            <p>
                                We do not warrant that the Service will be uninterrupted, timely, secure, or error-free,
                                or that
                                any
                                defects will be corrected. We do not make any warranties or representations regarding
                                the use of
                                the
                                materials in the Service in terms of their correctness, accuracy, reliability, or
                                otherwise.
                            </p>
                        </section>

                        <section>
                            <h2>11. Termination</h2>
                            <p>
                                We may terminate or suspend your Account and access to the Service immediately, without
                                prior
                                notice or
                                liability, for any reason, including without limitation if you breach these Terms.
                            </p>
                            <p>
                                Upon termination, your right to use the Service will immediately cease. If you wish to
                                terminate
                                your
                                Account, you may simply discontinue using the Service or contact us to request Account
                                deletion.
                            </p>
                        </section>

                        <section>
                            <h2>12. Changes to Terms</h2>
                            <p>
                                We reserve the right to modify or replace these Terms at any time. If a revision is
                                material, we
                                will
                                provide at least 30 days' notice prior to any new terms taking effect. What constitutes
                                a
                                material change
                                will be determined at our sole discretion.
                            </p>
                            <p>
                                By continuing to access or use our Service after those revisions become effective, you
                                agree to
                                be bound
                                by the revised terms. If you do not agree to the new terms, please stop using the
                                Service.
                            </p>
                        </section>

                        <section>
                            <h2>13. Governing Law</h2>
                            <p>
                                These Terms shall be governed and construed in accordance with the laws applicable in
                                your
                                jurisdiction,
                                without regard to its conflict of law provisions.
                            </p>
                        </section>

                        <section>
                            <h2>14. Dispute Resolution</h2>
                            <p>
                                Any disputes arising out of or relating to these Terms or the Service shall first be
                                attempted
                                to be
                                resolved through good-faith negotiation. If such dispute cannot be resolved through
                                negotiation,
                                it shall
                                be submitted to mediation in accordance with the rules of a mutually agreed upon
                                mediation
                                service.
                            </p>
                        </section>

                        <section>
                            <h2>15. Contact Us</h2>
                            <p>
                                If you have any questions about these Terms, please contact us at:
                            </p>
                            <p>
                                Email: terms@coachguide.biz<br>
                                Address: 123 Terms Street, Business District, 12345
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
import { home } from '@/routes/central';
import TextLink from '@/components/TextLink.vue';
</script>
