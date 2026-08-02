import {
    ArrowLeft,
    ArrowRight,
    BadgeCheck,
    CalendarDays,
    Check,
    ChevronRight,
    CirclePlay,
    Heart,
    House,
    MapPin,
    Mars,
    Menu,
    PawPrint,
    Pencil,
    Plus,
    Quote,
    Search,
    ShieldCheck,
    Sparkles,
    Star,
    Trash2,
    UserRound,
    Venus,
    createIcons,
} from 'lucide';

const renderIcons = () => createIcons({
    icons: {
        ArrowLeft,
        ArrowRight,
        BadgeCheck,
        CalendarDays,
        Check,
        ChevronRight,
        CirclePlay,
        Heart,
        House,
        MapPin,
        Mars,
        Menu,
        PawPrint,
        Pencil,
        Plus,
        Quote,
        Search,
        ShieldCheck,
        Sparkles,
        Star,
        Trash2,
        UserRound,
        Venus,
    },
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderIcons);
} else {
    renderIcons();
}
