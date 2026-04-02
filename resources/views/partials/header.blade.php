<nav class="bg-blue-600 p-4 text-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center gap-2">
            <i class="fas fa-stopwatch text-2xl"></i>
            <h1 class="text-xl font-bold tracking-tight">OJT ChronoLink</h1>
        </div>
        
        <div class="flex items-center gap-8">
            @auth
                <div class="hidden md:flex items-center gap-6 border-r border-blue-500 pr-6">
                    <a href="{{ route('dashboard') }}" 
                       class="text-sm font-bold opacity-90 hover:opacity-100 transition flex items-center gap-2">
                        <i class="fas fa-th-large text-xs"></i> Dashboard
                    </a>
                    <a href="{{ route('dtr.manage') }}" 
                       class="text-sm font-bold opacity-90 hover:opacity-100 transition flex items-center gap-2">
                        <i class="fas fa-tasks text-xs"></i> DTR Management
                    </a>
                    <a href="{{ route('dtr.record') }}" 
                       class="text-sm font-bold opacity-90 hover:opacity-100 transition flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-xs"></i> Record
                    </a>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Logout" 
                            class="p-2.5 rounded-xl bg-blue-700/50 hover:bg-red-500 text-white transition-all duration-300 group shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 group-hover:scale-110 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3-3H15m-3-3 3 3-3 3" />
                        </svg>
                    </button>
                </form>
            @endauth

            @guest
                <a href="/login" class="text-sm font-bold border-b-2 border-transparent hover:border-white transition">Login</a>
            @endguest
        </div>
    </div>
</nav>