@props(['active' => false])

<li>
    <a {{ $attributes }} class="{{ $active ? 'bg-rose-500 text-white' : 'text-black hover:text-rose-500' }} rounded block py-2 px-4">
        {{ $slot }}
    </a>
</li>