import { type NavItem } from "@/types";
import { BookOpen, Calendar1Icon, Settings2, Folder, LayoutGrid, Users, UserStar } from "lucide-vue-next";
import { dashboard } from "@/routes/tenant";
import clients from "@/routes/tenant/clients";
import coachingSessions from "@/routes/tenant/coaching-sessions";
import ThemeController from '@/actions/App/Http/Controllers/Tenant/Admin/ThemeController';
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
    {
        title: 'Manage Coaches',
        href: "#",
        icon: UserStar,
    },
    {
        title: 'Platform Settings',
        href: ThemeController.index().url,
        icon: Settings2,
    },
];

const footerNavItems: NavItem[] = [

];

export {
    mainNavItems,
    footerNavItems,
    adminNavItems,
}