<aside class="bg-white px-4 hidden md:block md:w-1/4 lg:w-1/5 min-h-screen shadow-sm relative">
    <div class="sticky top-10">
        <ul class="text-lg space-y-4 my-10">
            <x-nav-link href="/statistics" :active="request()->is('statistics')">Statistik</x-nav-link>
            <x-nav-link href="/dataset" :active="request()->is('dataset')">Dataset</x-nav-link>
        </ul>
    </div>
</aside>