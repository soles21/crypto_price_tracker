<div
    x-data="{ 
        time: '',
        date: '',
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        updateDateTime() {
            const now = new Date();
            this.time = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            this.date = now.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric'
            });
        }
    }"
    x-init="
        updateDateTime();
        setInterval(() => updateDateTime(), 1000);
    "
    class="flex flex-col sm:flex-row items-end sm:items-center bg-white/80 backdrop-blur-sm rounded-lg text-right sm:text-left"
>
    <!-- Date -->
    <div class="px-3 py-1 sm:py-2 sm:border-r border-gray-200 order-2 sm:order-1">
        <div class="text-xs sm:text-sm font-medium text-gray-600" x-text="date"></div>
    </div>
    
    <!-- Time -->
    <div class="px-3 py-1 sm:py-2 order-1 sm:order-2">
        <div class="flex items-baseline space-x-2">
            <div class="text-base sm:text-lg font-mono font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-pink-600" x-text="time"></div>
            <div class="text-[10px] sm:text-xs text-gray-500" x-text="timezone.replace('_', ' ')"></div>
        </div>
    </div>
</div>