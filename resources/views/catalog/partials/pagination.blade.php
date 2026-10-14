@if($paginator->hasPages())
    <nav class="mt-8 rounded-2xl border border-neutral-200 bg-white px-4 py-3 shadow-sm" aria-label="Paginación del catálogo">
        <div class="text-sm">
            {{ $paginator->links() }}
        </div>
    </nav>
@endif