<section class="bg-white py-20 border-y border-outline-variant/20">
    <div class="max-w-screen-2xl mx-auto px-8">

        <div class="text-center mb-12">
            <span class="font-label-md text-label-md text-primary uppercase tracking-widest mb-3 block">
                Confianza
            </span>

            <h2 class="font-headline-md text-headline-md text-on-surface">
                Marcas que distribuimos
            </h2>

            <p class="font-body-md text-body-md text-secondary mt-3">
                Trabajamos con fabricantes líderes en tecnología.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-x-16 gap-y-8">

            @foreach($brands as $brand)

                <h3 class="font-semibold text-on-surface/50 transition-colors duration-300 hover:text-on-surface">
                    {{ $brand['name'] }}
                </h3>

            @endforeach

        </div>

    </div>
</section>