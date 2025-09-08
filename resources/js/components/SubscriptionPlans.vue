<template>
    <div class="flex flex-col items-center justify-center space-y-4 text-center mb-8" v-if="title">
        <div class="space-y-2">
            <h2 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl">
                {{ title }}
            </h2>
            <p class="mx-auto max-w-[700px] text-gray-500 md:text-xl dark:text-gray-400">
                {{ description }}
            </p>
        </div>
    </div>
    <div class="w-full py-8">

        <!-- Switch -->
        <div class="flex justify-center items-center mb-8">
            <span class="mr-3 text-sm font-medium">Monthly</span>
            <Switch v-model="isAnnual" />
            <span class="ml-3 text-sm font-medium relative">
                Annual
                <span class="absolute -top-10 start-auto -end-28">
                    <span class="flex items-center">
                        <svg class="w-14 h-8 -me-6" width="45" height="25" viewBox="0 0 45 25" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M43.2951 3.47877C43.8357 3.59191 44.3656 3.24541 44.4788 2.70484C44.5919 2.16427 44.2454 1.63433 43.7049 1.52119L43.2951 3.47877ZM4.63031 24.4936C4.90293 24.9739 5.51329 25.1423 5.99361 24.8697L13.8208 20.4272C14.3011 20.1546 14.4695 19.5443 14.1969 19.0639C13.9242 18.5836 13.3139 18.4152 12.8336 18.6879L5.87608 22.6367L1.92723 15.6792C1.65462 15.1989 1.04426 15.0305 0.563943 15.3031C0.0836291 15.5757 -0.0847477 16.1861 0.187863 16.6664L4.63031 24.4936ZM43.7049 1.52119C32.7389 -0.77401 23.9595 0.99522 17.3905 5.28788C10.8356 9.57127 6.58742 16.2977 4.53601 23.7341L6.46399 24.2659C8.41258 17.2023 12.4144 10.9287 18.4845 6.96211C24.5405 3.00476 32.7611 1.27399 43.2951 3.47877L43.7049 1.52119Z"
                                fill="currentColor" class="text-muted-foreground" />
                        </svg>
                        <Badge variant="outline" class="bg-accent text-accent-foreground">Save up to 20%</Badge>
                    </span>
                </span>
            </span>
        </div>

        <!-- Grid -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:items-stretch">
            <!-- Card for each plan -->
            <div v-for="(plan, key) in plans" :key="key" class="flex flex-col rounded-lg shadow-sm overflow-hidden"
                :class="[
                    key === 'tier2'
                        ? 'border-2 border-primary'
                        : 'border border-border'
                ]">
                <div class="text-center p-6">
                    <Badge v-if="key === 'tier2'" variant="outline" class="mb-3 bg-accent text-accent-foreground">
                        Most popular
                    </Badge>
                    <h3 class="text-xl font-semibold mb-2">{{ plan.name }}</h3>
                    <div class="mb-4">
                        <span class="font-bold text-5xl">{{ isAnnual ? plan.yearly_price : plan.monthly_price }}</span>
                        <span class="text-sm text-muted-foreground block mt-1">
                            {{ isAnnual ? 'per year' : 'per month' }}
                        </span>
                        <span v-if="isAnnual" class="text-sm text-primary font-medium">
                            {{ plan.yearly_discount }} discount
                        </span>
                    </div>
                    <p class="text-muted-foreground text-sm">
                        {{ plan.description }}
                    </p>

                </div>

                <Separator />

                <div class="flex-1 p-6">
                    <ul class="space-y-4">
                        <li v-for="(featureGroup, index) in plan.features" :key="index" class="space-y-2">
                            <!-- Feature Group Header -->
                            <div class="flex items-start">
                                <Icon v-if="featureGroup.details && featureGroup.details.length > 0" name="check"
                                    class="h-5 w-5 text-primary shrink-0 mr-2 mt-0.5" />
                                <Icon v-else name="x" class="h-5 w-5 text-muted-foreground shrink-0 mr-2 mt-0.5" />
                                <span class="font-semibold">{{ featureGroup.name }}</span>
                            </div>

                            <!-- Feature Details -->
                            <ul v-if="featureGroup.details && featureGroup.details.length > 0" class="ml-7 space-y-1">
                                <li v-for="(detail, detailIndex) in featureGroup.details" :key="detailIndex"
                                    class="text-sm text-muted-foreground">
                                    {{ detail }}
                                </li>
                            </ul>
                            <div v-else class="ml-7">
                                <span class="text-sm text-muted-foreground italic">Not included</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="p-6 pt-0 mt-auto">
                    <div v-if="isCurrentPlan(plan)" class="flex items-center justify-center p-2">
                        <Icon name="CheckCircle" class="h-4 w-4 text-primary mr-2 text-green-500" />
                        <span class="text-sm font-medium">Current Plan</span>
                    </div>
                    <Button v-else :variant="key === 'tier2' ? 'default' : 'secondary'" class="w-full"
                        @click="selectPlan(key)" :disabled="props.loading"
                        :class="{ 'opacity-50 cursor-not-allowed': props.loading }">
                        <span v-if="props.loading && selectedPlanKey === key" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Processing...
                        </span>
                        <span v-else>{{ buttonLabel }}</span>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import Icon from '@/components/Icon.vue';

const props = defineProps({
    plans: {
        type: Object,
        required: true
    },
    title: {
        type: String,
        default: 'Pricing'
    },
    description: {
        type: String,
        default: 'Whatever your status, our offers evolve according to your needs.'
    },
    loading: {
        type: Boolean,
        default: false
    },
    canHaveTrial: {
        type: Boolean,
        default: false
    },
    subscription: {
        type: Object,
        default: null
    }
});

/*
Plan:
{ "name": "Basic", "monthly_price": "R295", "yearly_price": "R3200", "yearly_discount": "10%", "paddle_id_monthly": "pri_01jrtx7hjvx6nkrqgpx1cf15gz", "paddle_id_monthly_trial": "pri_01jntfd8ena6gdn5mdy22x3fed", "paddle_id_yearly": "pri_01jrtxanj3b8vvrs030q5b7gwc", "paddle_id_yearly_trial": "pri_01jp35kvhe4dazy164vk7tkyeq", "description": "Essential coaching tools", "features": [ "Coach Access:", "Access to CoachGuide (standard look)", "Coachees/Client Contracting", "Coaching Records", "Coaching Actions", "Coaching Log & CPD", "Reflection & Supervision" ] }


Subscription:
{ "id": 2, "billable_type": "App\\Models\\Central\\Tenant", "billable_id": 1, "type": "default", "paddle_id": "sub_01jrvvg6zwy2qwpnd3x3vcmqm6", "status": "active", "trial_ends_at": null, "paused_at": null, "ends_at": null, "created_at": "2025-04-15T04:32:17.000000Z", "updated_at": "2025-04-15T04:32:17.000000Z", "items": [ { "id": 2, "subscription_id": 2, "product_id": "pro_01jqsdxp3pfjp85vc9th6sv5gb", "price_id": "pri_01jrvvad6jq7x9eszfg3gkgych", "status": "active", "quantity": 1, "created_at": "2025-04-15T04:32:17.000000Z", "updated_at": "2025-04-15T04:32:17.000000Z" } ] }
*/

const emit = defineEmits(['plan-selected']);

const isAnnual = ref(false);
const selectedPlanKey = ref(null);

// New function to get the price ID
function getPriceId(planKey, isAnnual) {
    const plan = props.plans[planKey];
    if (isAnnual) {
        return props.canHaveTrial ? plan.paddle_id_yearly_trial : plan.paddle_id_yearly;
    } else {
        return props.canHaveTrial ? plan.paddle_id_monthly_trial : plan.paddle_id_monthly;
    }
}

function selectPlan(planKey) {
    selectedPlanKey.value = planKey;
    const priceId = getPriceId(planKey, isAnnual.value);
    emit('plan-selected', priceId);
}

const isCurrentPlan = (plan) => {
    //Check if the current subscription matches the given plan (check all paddle_ids)
    return props.subscription && props.subscription.items.some(item =>
        item.price_id === plan.paddle_id_monthly ||
        item.price_id === plan.paddle_id_yearly ||
        item.price_id === plan.paddle_id_monthly_trial ||
        item.price_id === plan.paddle_id_yearly_trial
    );
}

const hasSubscription = computed(() => {
    return props.subscription && props.subscription.items.length > 0;
})

const buttonLabel = computed(() => {
    if (hasSubscription.value) {
        return 'Change plan';
    }

    if (props.canHaveTrial) {
        return 'Start free trial';
    }

    return 'Get started';
});

// These utility functions can be used if you need to categorize features
// function getFeatureCategories() {
//     const categories = [];

//     for (const planKey in props.plans) {
//         const features = props.plans[planKey].features;
//         for (const feature of features) {
//             if (feature.endsWith(':')) {
//                 const category = feature.slice(0, -1);
//                 if (!categories.includes(category)) {
//                     categories.push(category);
//                 }
//             }
//         }
//     }

//     return categories;
// }

// function getCategoryFeatures(category) {
//     const features = [];
//     const tier3Features = props.plans.tier3?.features || [];

//     const categoryIndex = tier3Features.findIndex(f => f === `${category}:`);

//     if (categoryIndex !== -1) {
//         for (let i = categoryIndex + 1; i < tier3Features.length; i++) {
//             if (tier3Features[i].endsWith(':')) {
//                 break;
//             }
//             if (!features.includes(tier3Features[i]) && !tier3Features[i].includes('None')) {
//                 features.push(tier3Features[i]);
//             }
//         }
//     }

//     return features;
// }

// function hasFeature(planKey, feature) {
//     const planFeatures = props.plans[planKey].features;
//     return planFeatures.includes(feature) && !feature.includes('None');
// }
</script>

<style scoped>
.toggle {
    position: relative;
    display: inline-block;
    width: 3.5rem;
    height: 2rem;
    background-color: rgba(0, 0, 0, 0.25);
    border-radius: 9999px;
    transition: all 0.3s;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
}

.toggle:checked {
    background-color: var(--primary);
}

.toggle:before {
    content: '';
    position: absolute;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    top: 0.25rem;
    left: 0.25rem;
    background-color: white;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    transition: all 0.3s;
}

.toggle:checked:before {
    transform: translateX(1.5rem);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    color: white;
    background-color: var(--primary);
    border-radius: 9999px;
}
</style>
