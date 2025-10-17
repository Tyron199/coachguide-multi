import { type NavItem } from "@/types";
import { Calendar1Icon, Settings2, LayoutGrid, Users, Hammer, ChartLine, UserStar, BookOpen, CheckSquare } from "lucide-vue-next";
import { dashboard } from "@/routes/tenant";
import clients from "@/routes/tenant/coach/clients";
import coachingSessions from "@/routes/tenant/coach/coaching-sessions";
import { index as themeIndex } from '@/actions/App/Http/Controllers/Tenant/Admin/ThemeController';
import { models as showModels } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingFrameworkController';
import { index as coachingLogIndex } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingLogController';
import { index as coachesIndex } from '@/actions/App/Http/Controllers/Tenant/Admin/CoachController';
import { index as administratorsIndex } from '@/actions/App/Http/Controllers/Tenant/Admin/AdminController';
import { all as resourceLibraryAll } from '@/actions/App/Http/Controllers/Tenant/Coach/ResourceLibraryController';
import { index as clientSessionsIndex } from '@/actions/App/Http/Controllers/Tenant/Client/SessionController';
import { index as clientTasksIndex } from '@/actions/App/Http/Controllers/Tenant/Client/TaskController';
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
        href: coachingLogIndex(),
        icon: ChartLine
    },
    {
        title: 'Resource Library',
        href: resourceLibraryAll(),
        icon: BookOpen
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
    // {
    //     title: 'Subscription',
    //     href: manage().url,
    //     icon: CreditCard,
    // },
];

const clientNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Sessions',
        href: clientSessionsIndex(),
        icon: Calendar1Icon,
    },
    {
        title: 'Tasks',
        href: clientTasksIndex(),
        icon: CheckSquare,
    },
];

const footerNavItems: NavItem[] = [

];

export {
    mainNavItems,
    footerNavItems,
    adminNavItems,
    clientNavItems,
}