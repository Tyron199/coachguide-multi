import { type NavItem } from "@/types";
import { BookOpen, Calendar1Icon, Settings2, Folder, LayoutGrid, Users, UserStar, CreditCard } from "lucide-vue-next";
import { dashboard } from "@/routes/tenant";
import clients from "@/routes/tenant/coach/clients";
import coachingSessions from "@/routes/tenant/coach/coaching-sessions";
import { index as themeIndex } from '@/actions/App/Http/Controllers/Tenant/Admin/ThemeController';
import { manage } from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Clients',
        href: clients.index(),
        icon: Users,
    },
    {
        title: 'Sessions',
        href: coachingSessions.index(),
        icon: Calendar1Icon,

    },
];


const adminNavItems: NavItem[] = [
    // {
    //     title: 'Manage Coaches',
    //     href: "#",
    //     icon: UserStar,
    // },
    {
        title: 'Platform Branding',
        href: themeIndex().url,
        icon: Settings2,
    },
    {
        title: 'Subscription',
        href: manage().url,
        icon: CreditCard,
    },
];

const footerNavItems: NavItem[] = [

];

export {
    mainNavItems,
    footerNavItems,
    adminNavItems,
}