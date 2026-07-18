import Alpine from 'alpinejs';
import {
    Activity,
    AlertTriangle,
    Box,
    CalendarClock,
    CheckCircle2,
    ChevronRight,
    createIcons,
    Download,
    Eye,
    FolderGit2,
    Globe2,
    History,
    Home,
    Info,
    LogOut,
    Menu,
    Pause,
    Pencil,
    Play,
    Plus,
    RefreshCw,
    Rocket,
    Search,
    Server,
    Settings2,
    Smartphone,
    UserRound,
    Users,
    Trash2,
    X,
} from 'lucide';

window.Alpine = Alpine;

Alpine.store('pwa', {
    deferredPrompt: null,
    canInstall: false,
    helpOpen: false,
    installed: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
    isAppleMobile: /iphone|ipad|ipod/i.test(window.navigator.userAgent),

    init() {
        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            this.deferredPrompt = event;
            this.canInstall = true;
        });

        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.canInstall = false;
            this.installed = true;
            this.helpOpen = false;
        });
    },

    async install() {
        if (!this.deferredPrompt) {
            this.helpOpen = true;
            return;
        }

        await this.deferredPrompt.prompt();
        const choice = await this.deferredPrompt.userChoice;

        if (choice.outcome === 'accepted') {
            this.installed = true;
        }

        this.deferredPrompt = null;
        this.canInstall = false;
    },
});

Alpine.start();

if ('serviceWorker' in navigator && (window.isSecureContext || ['localhost', '127.0.0.1'].includes(window.location.hostname))) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js?v=3', { updateViaCache: 'none' })
            .then((registration) => registration.update())
            .catch(() => {
                // Le tableau de bord reste disponible si le navigateur refuse le service worker.
            });
    });
}

createIcons({
    icons: {
        Activity,
        AlertTriangle,
        Box,
        CalendarClock,
        CheckCircle2,
        ChevronRight,
        Download,
        Eye,
        FolderGit2,
        Globe2,
        History,
        Home,
        Info,
        LogOut,
        Menu,
        Pause,
        Pencil,
        Play,
        Plus,
        RefreshCw,
        Rocket,
        Search,
        Server,
        Settings2,
        Smartphone,
        UserRound,
        Users,
        Trash2,
        X,
    },
});
