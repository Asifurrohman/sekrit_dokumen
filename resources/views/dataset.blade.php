<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <div>
        <table class="min-w-full text-sm text-left text-gray-700 border border-gray-200">
            <thead class="bg-rose-500 text-white">
                <tr>
                    <th class="px-4 py-2 border border-gray-200">No</th>
                    <th class="px-4 py-2 border border-gray-200">Username</th>
                    <th class="px-4 py-2 border border-gray-200">Date</th>
                    <th class="px-4 py-2 border border-gray-200">Tweet</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datasets as $index => $data)
                <tr class="odd:bg-gray-100 even:bg-white">
                    <td class="px-4 py-2 border border-gray-200">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 border border-gray-200">{{ $data->username }}</td>
                    <td class="px-4 py-2 border border-gray-200">{{ $data->tanggal }}</td>
                    <td class="px-4 py-2 border border-gray-200">{{ $data->isi }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $datasets->links() }}
        </div>
    </div>
</x-layout>