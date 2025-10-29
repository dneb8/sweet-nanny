<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { useNotifications, type Notification } from '@/composables/useNotifications';
import { Bell } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';

const { notifications, unreadCount, loading, fetchNotifications, markAllAsRead, handleNotificationClick } = useNotifications();

const open = ref(false);

let pollInterval: NodeJS.Timeout | null = null;

onMounted(() => {
    fetchNotifications();
    
    // Poll for new notifications every 3 seconds
    pollInterval = setInterval(fetchNotifications, 3000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});

const formatTime = (dateString: string) => {
    try {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now.getTime() - date.getTime();
        
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'Justo ahora';
        if (minutes < 60) return `Hace ${minutes} min`;
        if (hours < 24) return `Hace ${hours} h`;
        if (days < 7) return `Hace ${days} d`;
        
        return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
    } catch {
        return dateString;
    }
};

const getNotificationIcon = (notification: Notification) => {
    if (notification.type === 'avatar_processed') {
        return notification.success ? '✓' : '✗';
    }
    return '•';
};

const getNotificationClass = (notification: Notification) => {
    const base = 'flex items-start gap-3 p-3 hover:bg-accent cursor-pointer transition-colors';
    return notification.read_at ? `${base} opacity-60` : base;
};
</script>

<template>
    <DropdownMenu v-model:open="open">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative">
                <Bell class="h-5 w-5" />
                <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
                <span class="sr-only">Notificaciones</span>
            </Button>
        </DropdownMenuTrigger>
        
        <DropdownMenuContent align="end" class="w-80">
            <DropdownMenuLabel class="flex items-center justify-between">
                <span>Notificaciones</span>
                <Button v-if="unreadCount > 0" variant="ghost" size="sm" class="h-auto p-1 text-xs" @click="markAllAsRead">
                    Marcar todas como leídas
                </Button>
            </DropdownMenuLabel>
            
            <DropdownMenuSeparator />
            
            <ScrollArea v-if="notifications.length > 0" class="h-[300px]">
                <div v-for="notification in notifications" :key="notification.id" @click="handleNotificationClick(notification)">
                    <DropdownMenuItem :class="getNotificationClass(notification)" @select.prevent>
                        <div class="flex-shrink-0 mt-0.5">
                            <span 
                                class="flex h-6 w-6 items-center justify-center rounded-full text-sm"
                                :class="notification.success === false ? 'bg-destructive text-destructive-foreground' : notification.success === true ? 'bg-green-500 text-white' : 'bg-muted'"
                            >
                                {{ getNotificationIcon(notification) }}
                            </span>
                        </div>
                        
                        <div class="flex-1 space-y-1">
                            <p class="text-sm leading-tight">{{ notification.message }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatTime(notification.created_at) }}</p>
                        </div>
                        
                        <div v-if="!notification.read_at" class="flex-shrink-0">
                            <div class="h-2 w-2 rounded-full bg-primary"></div>
                        </div>
                    </DropdownMenuItem>
                </div>
            </ScrollArea>
            
            <div v-else class="p-8 text-center text-sm text-muted-foreground">
                Sin notificaciones
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
