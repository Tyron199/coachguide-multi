<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { CreditCard } from 'lucide-vue-next';
import { manage, subscribe } from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';
const page = usePage();
const subscribed = page.props.tenant.subscribed;

const sidebarNavItems: NavItem[] = [

];

//If not subscribed yet, show subscribe button
if (subscribed) {
    sidebarNavItems.push({
        title: 'Subscription',
        href: manage().url,
        icon: CreditCard,
    });
} else {
    sidebarNavItems.push({
        title: 'Subscribe',
        href: subscribe().url,
        icon: CreditCard,
    });
}


const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Subscription" description="Manage your subscription" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button v-for="item in sidebarNavItems"
                        :key="typeof item.href === 'string' ? item.href : item.href?.url" variant="ghost" :class="[
                            'w-full justify-start',
                            { 'bg-muted': currentPath === (typeof item.href === 'string' ? item.href : item.href?.url) },
                        ]" as-child>
                        <Link :href="item.href">
                        <component :is="item.icon" class="mr-2 h-4 w-4" />
                        {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 min-w-0">
                <section class="">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
