<template>
    <GuestLayout>

        <Head title="CoachGuide - Your Coaching Companion" />


        <template #links>
            <nav class="hidden md:flex items-center space-x-6">
                <a href="/#features" @click.prevent="scrollToSection"
                    class="text-sm font-medium hover:text-primary">Features</a>
                <a href="/#solutions" @click.prevent="scrollToSection"
                    class="text-sm font-medium hover:text-primary">Solutions</a>
                <a href="/#pricing" @click.prevent="scrollToSection"
                    class="text-sm font-medium hover:text-primary">Pricing</a>

            </nav>
        </template>

        <!-- Hero Section -->
        <section class="py-20 md:py-28">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <div class="flex flex-col items-center space-y-10 text-center">
                    <div class="space-y-4">
                        <h1 class="text-4xl font-bold tracking-tighter sm:text-5xl md:text-6xl">
                            CoachGuide™
                        </h1>
                        <p class="mx-auto max-w-[700px] text-gray-500 md:text-xl dark:text-gray-400">
                            Your Coaching, Streamlined. Your Impact, Amplified.
                        </p>

                    </div>
                    <div class="w-full max-w-3xl overflow-hidden rounded-lg border bg-background shadow-xl">
                        <img src="/images/hero-3.jpg" alt="CoachGuide dashboard" class="w-full h-auto" />
                    </div>
                    <div class="space-y-4">
                        <p class="mx-auto max-w-[900px] text-gray-500 md:text-lg dark:text-gray-400">
                            Welcome to CoachGuide™ — your all-in-one digital platform built specifically for
                            professional
                            coaches. Designed to simplify your coaching practice, CoachGuide helps you effortlessly
                            manage and
                            track coaching sessions, monitor client progress and accountability, generate AI-powered
                            session
                            notes, store contracts and confidentiality agreements, log CPD points, and create detailed
                            feedback
                            reports for both individual clients and organisations. Whether you're coaching individuals
                            or teams,
                            CoachGuide keeps everything organized, secure, and accessible—so you can focus on what
                            matters most:
                            creating impact.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <Link :href="register()">
                        <Button size="lg">Start Free Trial</Button>
                        </Link>
                        <Button size="lg" variant="outline" @click="showDemoModal = true">See Demo</Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-muted/50" id="features">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <div class="flex flex-col items-center justify-center space-y-4 text-center">
                    <div class="space-y-2">
                        <h2 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl">
                            Streamline Your Coaching Practice
                        </h2>
                        <p class="mx-auto max-w-[700px] text-gray-500 md:text-xl dark:text-gray-400">
                            <b>Streamline your coaching practice</b> with tools that take care of the admin, so you can
                            focus
                            on what
                            truly matters—building meaningful, results-driven coaching relationships. CoachGuide brings
                            structure, clarity, and ease to your workflow, helping you stay organized, professional, and
                            fully
                            present for every client.
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16">
                    <!-- Feature 1 -->
                    <div class="flex flex-col items-center space-y-4 p-6 rounded-lg border bg-background shadow-sm">
                        <div class="p-3 rounded-full bg-primary/10">
                            <Icon name="users" class="h-6 w-6 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold">Client Management</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-center">
                            Store detailed client information, emergency contacts, and confidentiality agreements. Keep
                            track of
                            client progress and maintain comprehensive session histories. Organize your practice
                            efficiently.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="flex flex-col items-center space-y-4 p-6 rounded-lg border bg-background shadow-sm">
                        <div class="p-3 rounded-full bg-primary/10">
                            <Icon name="ReceiptText" class="h-6 w-6 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold">Contract Creation</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-center">
                            Utilize built-in contract templates to define coaching timeframes, roles & responsibilities.
                            Customize agreements to match your practice needs. Ensure professional documentation
                            standards.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="flex flex-col items-center space-y-4 p-6 rounded-lg border bg-background shadow-sm">
                        <div class="p-3 rounded-full bg-primary/10">
                            <Icon name="ClipboardList" class="h-6 w-6 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold">Coaching Tools</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-center">
                            Leverage coaching models, techniques, and reflection prompts to design effective sessions.
                            International models and techniques within package options. Expand on your coaching toolkit.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Additional Features Section -->
        <section class="py-20" id="features">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <div class="flex flex-col items-center justify-center space-y-4 text-center mb-16">
                    <div class="space-y-2">
                        <h2 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl">
                            More Powerful Features
                        </h2>
                        <p class="mx-auto max-w-[700px] text-gray-500 md:text-xl dark:text-gray-400">
                            Everything you need to elevate your coaching practice
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Feature 1 -->
                    <div class="flex items-start space-x-4">
                        <div class="p-2 rounded-full bg-primary/10 mt-1">
                            <Icon name="users" class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-2">Build Stronger Connections</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Strengthen every coaching relationship with tools that bring clarity and
                                personalization.
                                Manage detailed company profiles, coachee data, and client engagement all in one
                                place—helping
                                you stay organized and present. Adaptation Support: Easily identify and document
                                coaching
                                adaptations tailored to each client's unique needs for more effective outcomes.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="flex items-start space-x-4">
                        <div class="p-2 rounded-full bg-primary/10 mt-1">
                            <Icon name="FileText" class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-2">Professional Contracting Made Easy</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Save time and stay protected with secure, organized document management.
                                Upload and store coaching contracts, confidentiality agreements, and onboarding
                                materials with
                                confidence.
                                Bonus: Access ready-to-use contract and agreement templates to boost your
                                professionalism and
                                streamline your client onboarding process.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="flex items-start space-x-4">
                        <div class="p-2 rounded-full bg-primary/10 mt-1">
                            <Icon name="ClipboardList" class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-2">Smarter Session Management</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Make every session count with integrated support that enhances consistency and
                                accountability.
                                Use built-in coaching tools and AI-generated notes to capture key insights.
                                Comprehensive Coaching Log: Track all your sessions with detailed notes, follow-up
                                reminders,
                                and clear action items.
                                Automated Communication: Keep clients engaged and on track with scheduled email
                                follow-ups that
                                reinforce progress.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="flex items-start space-x-4">
                        <div class="p-2 rounded-full bg-primary/10 mt-1">
                            <Icon name="award" class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-2">Support Your Growth as a Coach</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Advance your skills and maintain your credentials with ease.
                                Log hours, document supervision sessions, and align your growth with recognized
                                standards.
                                CPD Tracking: Easily track and report your Continuing Professional Development
                                activities in
                                line with ICF and COMENSA guidelines.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 bg-muted/50" id="testimonials">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <div class="flex flex-col items-center justify-center space-y-4 text-center">
                    <div class="space-y-2">
                        <h2 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl">
                            What Coaches Say
                        </h2>
                        <p class="mx-auto max-w-[700px] text-gray-500 md:text-xl dark:text-gray-400">
                            Hear from coaches who have transformed their practice with CoachGuide.
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-16">

                    <div class="flex flex-col p-6 rounded-lg border bg-background shadow-sm"
                        v-for="testimonial in testimonials" :key="testimonial.name">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="flex text-amber-400">
                                <Icon name="star" class="h-5 w-5 fill-current" />
                                <Icon name="star" class="h-5 w-5 fill-current" />
                                <Icon name="star" class="h-5 w-5 fill-current" />
                                <Icon name="star" class="h-5 w-5 fill-current" />
                                <Icon name="star" class="h-5 w-5 fill-current" />
                            </div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">
                            "{{ testimonial.message }}"
                        </p>
                        <div class="flex items-center mt-auto">
                            <Avatar>
                                <AvatarImage :src="testimonial.image" />
                                <AvatarFallback>{{ (testimonial.name.charAt(0) +
                                    testimonial.name.split(" ")[1].charAt(0)).toUpperCase() }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold">{{ testimonial.name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ testimonial.role }}</p>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="py-20 bg-muted/50" id="pricing">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <SubscriptionPlans :plans="plans" title="Choose Your Plan"
                    description="Select the perfect plan to elevate your coaching practice" :can-have-trial="true"
                    @plan-selected="handlePlanSelected" />
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <div class="flex flex-col items-center justify-center space-y-4 text-center">
                    <div class="space-y-2">
                        <h2 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl">
                            Ready to Elevate Your Coaching Practice?
                        </h2>
                        <p class="mx-auto max-w-[700px] text-gray-500 md:text-xl dark:text-gray-400">
                            Experience the power of a streamlined and organized coaching journey today.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 mt-8">
                        <Link :href="register()">
                        <Button size="lg">Start Free Trial</Button>
                        </Link>
                        <Button size="lg" variant="outline">Schedule a Demo</Button>
                    </div>
                </div>
            </div>
        </section>

        <Dialog :open="showDemoModal" @update:open="showDemoModal = false">
            <DialogContent class="sm:max-w-[800px] p-3">
                <DialogHeader>
                    <DialogTitle>Product Demo</DialogTitle>
                    <DialogDescription>Watch how CoachGuide can transform your coaching practice</DialogDescription>
                </DialogHeader>
                <div class="aspect-video">
                    <iframe class="w-full h-full" :src="activeVideoUrl" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
                <DialogFooter class="flex justify-center">
                    <Link :href="register()">
                    <Button>Get Started</Button>
                    </Link>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </GuestLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import Icon from '@/components/Icon.vue';
import SubscriptionPlans from '@/components/SubscriptionPlans.vue';

import GuestLayout from '@/layouts/GuestLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { register } from '@/routes/central';


defineProps({
    plans: {
        type: Object,
        required: true
    },
    testimonials: {
        type: Array,
        default: () => [{
            name: 'John Doe',
            role: 'CEO, Company Inc.',
            message: 'This product has completely transformed how we operate. The efficiency gains alone have paid for the subscription many times over.',
            image: '/images/testimonial-1.jpg'
        },
        {
            name: 'John Smith',
            role: 'CTO, Tech Solutions',
            message: 'The customer support is exceptional. Any time we\'ve had an issue, the team has been quick to respond and resolve it.',
            image: '/images/testimonial-2.jpg'
        },
        {
            name: 'Alex Johnson',
            role: 'Product Manager, Startup XYZ',
            message: 'We\'ve tried several similar products, but this one stands head and shoulders above the rest in terms of features and usability.',
            image: '/images/testimonial-3.jpg'
        }]
    }
});



const scrollToSection = (event) => {
    const href = event.target.href;
    const sectionId = href.split('#')[1];
    const element = document.getElementById(sectionId);
    window.scrollTo({
        top: element.offsetTop - 73, // Offset for the fixed header
        behavior: 'smooth'
    });
};

const handlePlanSelected = (priceId) => {
    console.log('Selected price ID:', priceId);
    // Store selected price ID in session or redirect to registration with it
    router.get(register(), { price_id: priceId });
};

const showDemoModal = ref(false);
const baseVideoUrl = 'https://www.youtube.com/embed/ysvuEIT8f90';
const activeVideoUrl = computed(() =>
    showDemoModal.value ? `${baseVideoUrl}?autoplay=1&mute=1` : baseVideoUrl
);
</script>
