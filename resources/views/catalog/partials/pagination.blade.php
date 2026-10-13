@if($catalog->products->paginator->hasPages())

    <div class="mt-10">

        {{ $catalog->products->paginator->links() }}

    </div>

@endif