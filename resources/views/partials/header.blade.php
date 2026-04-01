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
                </div>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Logout" 
                            class="p-2.5 rounded-xl bg-blue-700/50 hover:bg-red-500 text-white transition-all duration-300 group shadow-sm">
                        <i class="fas fa-power-off text-lg group-hover:scale-110 transition-transform"></i>
                    </button>
                </form>
            @endauth

            @guest
                <a href="/login" class="text-sm font-bold border-b-2 border-transparent hover:border-white transition">Login</a>
            @endguest
        </div>
    </div>
</nav>