<div>
<div class="flex items-center justify-end">
    <div class="rounded-xl overflow-hidden backdrop-blur-sm bg-white/90 border border-gray-100 shadow-sm p-0.5">
        <div class="relative bg-gradient-to-r from-blue-50 to-indigo-50 rounded-[10px] px-4 py-3">
            <div class="absolute inset-0 overflow-hidden">
                <div class="bubbles-container absolute inset-0">
                    <div class="bubble bubble-1"></div>
                    <div class="bubble bubble-2"></div>
                </div>
            </div>
            
            <div class="relative">
                <div class="flex items-center justify-end gap-2">
                    <div class="text-xl font-mono font-black tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-violet-600" id="digital-clock-time">
                        {{ $currentTime }}
                    </div>
                    <div class="text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-blue-500/10 to-violet-500/10 text-blue-600" id="digital-clock-timezone"></div>
                </div>
                <div class="text-sm text-gray-600 font-medium flex items-center justify-end gap-1">
                    <span>{{ \Carbon\Carbon::parse($currentDate)->format('l, F j, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Bubble animation in clock background */
.bubbles-container {
  overflow: hidden;
}

.bubble {
  position: absolute;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  animation: bubble-float linear infinite;
}

.bubble-1 {
  width: 15px;
  height: 15px;
  bottom: -15px;
  left: 20%;
  animation-duration: 8s;
}

.bubble-2 {
  width: 10px;
  height: 10px;
  bottom: -10px;
  left: 60%;
  animation-duration: 6s;
  animation-delay: 1s;
}

@keyframes bubble-float {
  0% {
    transform: translateY(0);
    opacity: 0;
  }
  20% {
    opacity: 0.5;
  }
  100% {
    transform: translateY(-60px);
    opacity: 0;
  }
}
</style>
</div>