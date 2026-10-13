<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

    <form method="GET" class="flex flex-1 flex-col gap-4 md:flex-row">

        {{-- Mantener todos los filtros excepto q y sort --}}
        @foreach(request()->except(['q', 'sort']) as $key => $value)

            @if(is_array($value))

                @foreach($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach

            @else

                <input type="hidden" name="{{ $key }}" value="{{ $value }}">

            @endif

        @endforeach

        {{-- Buscador --}}
        <div class="flex-1">

            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Buscar productos..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:ring-indigo-500"
            >

        </div>

        {{-- Ordenamiento --}}
        <div class="w-full md:w-64">

            <select
                name="sort"
                data-autosubmit
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:ring-indigo-500"
            >

                @foreach($catalog->sort as $option)

                    <option
                        value="{{ $option->value }}"
                        @selected(request('sort', 'newest') === $option->value)
                    >
                        {{ $option->label }}
                    </option>

                @endforeach

            </select>

        </div>

        {{-- Botón Buscar --}}
        <button
            type="submit"
            class="rounded-lg bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700 transition"
        >
            Buscar
        </button>

    </form>

</div>