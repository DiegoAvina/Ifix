<aside class="w-72 space-y-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-xl">

    @foreach($filters as $filter)

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 shadow-sm">

            <h3 class="font-semibold text-sm uppercase tracking-[0.18em] text-slate-600 mb-4">
                {{ $filter->title }}
            </h3>

            @if($filter->key === 'price')

                <form method="GET" class="space-y-4">

                    {{-- Mantener los demás filtros --}}
                    @foreach(request()->except(['min_price', 'max_price']) as $key => $value)

                        @if(is_array($value))

                            @foreach($value as $item)
                                <input
                                    type="hidden"
                                    name="{{ $key }}[]"
                                    value="{{ $item }}"
                                >
                            @endforeach

                        @else

                            <input
                                type="hidden"
                                name="{{ $key }}"
                                value="{{ $value }}"
                            >

                        @endif

                    @endforeach

                    <div class="space-y-2">

                        <label class="block text-sm font-medium text-slate-700">
                            Precio mínimo
                        </label>

                        <input
                            type="number"
                            name="min_price"
                            min="{{ $filter->extra->min }}"
                            max="{{ $filter->extra->max }}"
                            value="{{ request('min_price', $filter->extra->min) }}"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                        >

                    </div>

                    <div class="space-y-2">

                        <label class="block text-sm font-medium text-slate-700">
                            Precio máximo
                        </label>

                        <input
                            type="number"
                            name="max_price"
                            min="{{ $filter->extra->min }}"
                            max="{{ $filter->extra->max }}"
                            value="{{ request('max_price', $filter->extra->max) }}"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                        >

                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-full bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    >
                        Aplicar filtro
                    </button>

                </form>

            @else

                <div class="space-y-2">

                    @foreach($filter->options as $option)

                        <a
                            href="{{ $option->url }}"
                            class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-100 transition"
                        >

                            <div class="flex items-center gap-3">

                                <input
                                    type="checkbox"
                                    @checked($option->selected)
                                    class="pointer-events-none"
                                    tabindex="-1"
                                >

                                <span>
                                    {{ $option->label }}
                                </span>

                            </div>

                            @if($option->count)

                                <span class="text-sm text-gray-400">
                                    {{ $option->count }}
                                </span>

                            @endif

                        </a>

                    @endforeach

                </div>

            @endif

        </div>

    @endforeach

</aside>