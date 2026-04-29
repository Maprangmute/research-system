<section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
    @php
        $isActive = function ($patterns) {
            foreach ((array) $patterns as $pattern) {
                if (request()->routeIs($pattern)) {
                    return true;
                }
            }
            return false;
        };

        $tabClass = function ($active) {
            if ($active) {
                return 'border-cyan-200 bg-cyan-50 text-cyan-800';
            }
            return 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50';
        };
    @endphp

    <nav class="flex flex-wrap gap-2">
        <a href="{{ route('outputs.index') }}"
            class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-medium {{ $tabClass($isActive('outputs.index')) }}">
            รายการทั้งหมด
        </a>
        <a href="{{ route('outputs.people') }}"
            class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-medium {{ $tabClass($isActive(['outputs.people', 'outputs.people.*'])) }}">
            Outputs รายบุคคล
        </a>
        <a href="{{ route('outputs.visualization') }}"
            class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-medium {{ $tabClass($isActive('outputs.visualization')) }}">
            Visualization
        </a>
        <a href="{{ route('outputs.papers') }}"
            class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-medium {{ $tabClass($isActive('outputs.papers')) }}">
            Papers
        </a>
        <a href="{{ route('outputs.presentations') }}"
            class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-medium {{ $tabClass($isActive('outputs.presentations')) }}">
            Presentations
        </a>
        <a href="{{ route('outputs.textbooks') }}"
            class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-medium {{ $tabClass($isActive('outputs.textbooks')) }}">
            Textbooks
        </a>
        <a href="{{ route('outputs.external-academic') }}"
            class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-medium {{ $tabClass($isActive(['outputs.external-academic', 'outputs.external-academic.*'])) }}">
            External Academic
        </a>
        <a href="{{ route('outputs.project-data-quality') }}"
            class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-medium {{ $tabClass($isActive('outputs.project-data-quality')) }}">
            รายงานข้อมูลผิดพลาดของโปรเจค
        </a>
    </nav>
</section>
