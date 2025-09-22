import { type NavItem } from "@/types";
import { Calendar1Icon, Settings2, LayoutGrid, Users, CreditCard, Hammer, ChartLine, UserStar } from "lucide-vue-next";
import { dashboard } from "@/routes/tenant";
import clients from "@/routes/tenant/coach/clients";
import coachingSessions from "@/routes/tenant/coach/coaching-sessions";
import { index as themeIndex } from '@/actions/App/Http/Controllers/Tenant/Admin/ThemeController';
import { manage } from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';
import { models as showModels } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingFrameworkController';
import { index as coachingLogIndex } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingLogController';
import { index as coachesIndex } from '@/actions/App/Http/Controllers/Tenant/Admin/CoachController';
import { index as administratorsIndex } from '@/actions/App/Http/Controllers/Tenant/Admin/AdminController';
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
    {
        title: 'Tools & Models',
        href: showModels().url,
        icon: Hammer,
    },
    {
        title: 'Growth Tracker',
        href: coachingLogIndex().url,
        icon: ChartLine
    }
];


const adminNavItems: NavItem[] = [
    {
        title: 'Manage Coaches',
        href: coachesIndex(),
        icon: UserStar,
    },
    {
        title: 'Manage Administrators',
        href: administratorsIndex(),
        icon: UserStar,
    },
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