<section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm sm:p-7">

    <h2 class="font-heading text-xl font-extrabold text-neutral-950 sm:text-2xl">
        Descripción
    </h2>

    @if($product->description)
        <div class="prose mt-4 max-w-none prose-p:text-neutral-600 prose-headings:font-heading prose-headings:text-neutral-950 prose-strong:text-neutral-900 prose-a:text-[#a76500]">
            {!! $product->description !!}
        </div>
    @else
        <p class="mt-4 text-sm leading-6 text-neutral-600">
            Este producto no tiene descripción.
        </p>
    @endif

</section>