<div class="rounded-3xl border border-outline-variant/30 bg-white p-8">

    <h2 class="font-headline-md text-headline-md text-on-surface mb-6">
        Descripción
    </h2>

    @if($product->description)
        <div class="prose max-w-none prose-p:text-secondary prose-headings:text-on-surface">
            {!! $product->description !!}
        </div>
    @else
        <p class="text-secondary">
            Este producto no tiene descripción.
        </p>
    @endif

</div>