import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useNotify } from './useNotify';
import axios from 'axios';

export interface Notification {
    id: string;
    type: string;
    message: string;
    success?: boolean;
    redirect?: string;
    read_at: string | null;
    created_at: string;
}

const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);
const loading = ref(false);

export function useNotifications() {
    const { notifySuccess, notifyError, notifyInfo } = useNotify();

    const fetchNotifications = async () => {
        try {
            loading.value = true;
            const response = await axios.get(route('notifications.index'));
            notifications.value = response.data.notifications;
            unreadCount.value = response.data.unread_count;
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        } finally {
            loading.value = false;
        }
    };

    const markAsRead = async (notificationId: string) => {
        try {
            await axios.post(route('notifications.read', notificationId));
            
            // Update local state
            const notification = notifications.value.find(n => n.id === notificationId);
            if (notification) {
                notification.read_at = new Date().toISOString();
            }
            
            // Update unread count
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await axios.post(route('notifications.readAll'));
            
            // Update local state
            notifications.value.forEach(n => {
                if (!n.read_at) {
                    n.read_at = new Date().toISOString();
                }
            });
            
            unreadCount.value = 0;
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    };

    const handleNotificationClick = (notification: Notification) => {
        if (!notification.read_at) {
            markAsRead(notification.id);
        }
        
        if (notification.redirect) {
            router.visit(notification.redirect);
        }
    };

    const unreadNotifications = computed(() => 
        notifications.value.filter(n => !n.read_at)
    );

    // Listen for new notifications via events (for future broadcast integration)
    const handleNewNotification = (data: any) => {
        // Show toast with unified system
        if (data.type === 'avatar') {
            if (data.success) {
                notifySuccess(
                    data.message,
                    undefined,
                    'mdi:check-circle',
                    5000
                );
            } else {
                notifyError(
                    data.message,
                    undefined,
                    'mdi:alert-circle',
                    6000
                );
            }
        } else {
            // Generic notification
            notifyInfo(data.message, undefined, 'mdi:bell', 5000);
        }

        // Refresh notifications
        fetchNotifications();
    };

    return {
        notifications,
        unreadCount,
        unreadNotifications,
        loading,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        handleNotificationClick,
        handleNewNotification,
    };
}
