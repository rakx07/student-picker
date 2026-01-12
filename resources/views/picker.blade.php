<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Random Picker</title>

    <!-- Tailwind CDN (ok for small app; can be compiled later) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .glow { box-shadow: 0 0 0 3px rgba(34,197,94,.35); border-color: rgba(34,197,94,.65); }
        .shake { animation: shake .35s linear infinite; }
        @keyframes shake {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(0.8deg); }
            50% { transform: rotate(0deg); }
            75% { transform: rotate(-0.8deg); }
            100% { transform: rotate(0deg); }
        }
        .fade-in { animation: fadeIn .18s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>

<body class="bg-slate-50">
<div class="max-w-6xl mx-auto p-6 space-y-6">

    <!-- Header -->
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Student Random Picker</h1>
            <p class="text-slate-600">Paste list → spin animation → pick winner → winner removed → export selected/remaining</p>
        </div>

        <div class="flex flex-wrap gap-2 justify-end">
            <a class="px-3 py-2 rounded bg-slate-900 text-white text-sm" href="{{ route('picker.export.csv') }}">Export Selected CSV</a>
            <a class="px-3 py-2 rounded bg-slate-900 text-white text-sm" href="{{ route('picker.export.xlsx') }}">Export Selected Excel</a>
            <a class="px-3 py-2 rounded bg-slate-900 text-white text-sm" href="{{ route('picker.export.pdf') }}">Export Selected PDF</a>

            <a class="px-3 py-2 rounded bg-indigo-700 text-white text-sm" href="{{ route('picker.export.remaining.xlsx') }}">Export Remaining Excel</a>
            <a class="px-3 py-2 rounded bg-indigo-700 text-white text-sm" href="{{ route('picker.export.remaining.csv') }}">Export Remaining CSV</a>

            <form method="POST" action="{{ route('picker.reset') }}">
                @csrf
                <button class="px-3 py-2 rounded bg-red-600 text-white text-sm">Reset</button>
            </form>
        </div>
    </div>

    @if(session('ok'))
        <div class="p-3 rounded bg-emerald-100 text-emerald-900">{{ session('ok') }}</div>
    @endif

    <!-- ONE PAGE GRID -->
    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Column 1: Paste / Import -->
        <div class="bg-white rounded-xl shadow p-5 space-y-3">
            <h2 class="font-semibold text-lg">1) Paste Students</h2>

            <form method="POST" action="{{ route('picker.import') }}" class="space-y-3">
                @csrf
                <textarea name="students" rows="12"
                          class="w-full rounded border p-3 focus:outline-none focus:ring"
                          placeholder="Paste from Notepad / Excel / Sheets.
One student per line.

If pasted from Excel with multiple columns (e.g. Firstname, Lastname),
it will combine them into a FULL NAME."></textarea>

                @error('students')
                <div class="text-red-600 text-sm">{{ $message }}</div>
                @enderror

                <button class="w-full px-4 py-2 rounded bg-blue-600 text-white">
                    Import List
                </button>
            </form>

            <div class="text-sm text-slate-600">
                Tip: For Excel, copy rows/columns then paste here.
            </div>
        </div>

        <!-- Column 2: Roulette / Draw -->
        <div class="bg-white rounded-xl shadow p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-lg">2) Roulette Draw</h2>
                <div class="text-sm text-slate-600">
                    Remaining: <b id="remainingCount">{{ count($remaining ?? []) }}</b>
                </div>
            </div>

            <div id="wheel" class="rounded-xl border bg-slate-50 p-4">
                <div class="flex items-center justify-between mb-3">
                    <button id="btnDraw"
                            class="px-4 py-2 rounded bg-emerald-600 text-white disabled:opacity-50"
                            {{ (count($remaining ?? []) ? '' : 'disabled') }}>
                        Draw
                    </button>

                    <div class="text-sm text-slate-600">
                        Winner: <span id="winnerInline" class="font-semibold text-slate-900">—</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 max-h-80 overflow-auto" id="rouletteList">
                    @foreach(($remaining ?? []) as $i => $name)
                        <div class="p-2 rounded bg-white border" data-index="{{ $i }}">{{ $name }}</div>
                    @endforeach
                </div>

                <div class="mt-4 text-sm text-slate-600">
                    Animation highlights names quickly like a “wheel”, then stops at the winner.
                </div>
            </div>
        </div>

        <!-- Column 3: Lists -->
        <div class="space-y-6">
            <!-- Remaining list -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-lg">Remaining List</h2>
                    <span class="text-sm text-slate-600">Auto-updates</span>
                </div>

                <ul id="remainingList" class="space-y-2 max-h-48 overflow-auto">
                    @foreach(($remaining ?? []) as $name)
                        <li class="p-2 rounded border bg-slate-50">{{ $name }}</li>
                    @endforeach
                </ul>
            </div>

            <!-- Selected list -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-lg">Selected List</h2>
                    <span class="text-sm text-slate-600">Exportable</span>
                </div>

                <ul id="selectedList" class="space-y-2 max-h-64 overflow-auto">
                    @foreach(($selected ?? []) as $row)
                        <li class="p-2 rounded border bg-emerald-50">
                            <div class="font-semibold">#{{ $row['draw_no'] }} — {{ $row['name'] }}</div>
                            <div class="text-xs text-slate-600">{{ $row['drawn_at'] }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- CONGRATS MODAL -->
<div id="winnerModal" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" id="modalOverlay"></div>

    <div class="relative h-full w-full flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 fade-in">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-slate-600">🎉 Congratulations!</div>
                    <div class="text-2xl font-bold mt-1" id="modalWinnerName">Winner Name</div>
                </div>
                <button id="modalCloseBtn" class="px-3 py-1 rounded bg-slate-100 hover:bg-slate-200">✕</button>
            </div>

            <div class="mt-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4">
                <div class="text-sm text-slate-700">You have been selected!</div>
                <div class="text-xs text-slate-500 mt-2" id="modalMeta">—</div>
            </div>

            <div class="mt-5 flex gap-2 justify-end">
                <button id="modalOkBtn" class="px-4 py-2 rounded bg-emerald-600 text-white">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const btnDraw = document.getElementById('btnDraw');
const rouletteList = document.getElementById('rouletteList');
const winnerInline = document.getElementById('winnerInline');
const remainingCount = document.getElementById('remainingCount');

const remainingList = document.getElementById('remainingList');
const selectedList = document.getElementById('selectedList');
const wheel = document.getElementById('wheel');

// Modal elements
const winnerModal = document.getElementById('winnerModal');
const modalOverlay = document.getElementById('modalOverlay');
const modalCloseBtn = document.getElementById('modalCloseBtn');
const modalOkBtn = document.getElementById('modalOkBtn');
const modalWinnerName = document.getElementById('modalWinnerName');
const modalMeta = document.getElementById('modalMeta');

function escapeHtml(s) {
    return String(s)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function openModal(winnerName, drawnAt, drawNo) {
    modalWinnerName.textContent = winnerName;
    modalMeta.textContent = `Draw #${drawNo} • ${drawnAt}`;
    winnerModal.classList.remove('hidden');
}

function closeModal() {
    winnerModal.classList.add('hidden');
}

modalOverlay.addEventListener('click', closeModal);
modalCloseBtn.addEventListener('click', closeModal);
modalOkBtn.addEventListener('click', closeModal);
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

function renderRemaining(arr) {
    remainingCount.textContent = arr.length;
    btnDraw.disabled = arr.length === 0;

    remainingList.innerHTML = arr.map(n =>
        `<li class="p-2 rounded border bg-slate-50">${escapeHtml(n)}</li>`
    ).join('');

    rouletteList.innerHTML = arr.map((n, i) =>
        `<div class="p-2 rounded bg-white border" data-index="${i}">${escapeHtml(n)}</div>`
    ).join('');
}

function renderSelected(arr) {
    selectedList.innerHTML = arr.map(r => `
        <li class="p-2 rounded border bg-emerald-50">
            <div class="font-semibold">#${r.draw_no} — ${escapeHtml(r.name)}</div>
            <div class="text-xs text-slate-600">${escapeHtml(r.drawn_at)}</div>
        </li>
    `).join('');
}

async function animateRouletteStopAt(stopIndex, durationMs = 2200) {
    const items = [...rouletteList.querySelectorAll('[data-index]')];
    if (!items.length) return;

    wheel.classList.add('shake');

    let idx = 0;
    const start = performance.now();

    return new Promise(resolve => {
        const tick = (now) => {
            const elapsed = now - start;
            const t = Math.min(1, elapsed / durationMs);

            const speed = 18 + Math.floor(160 * (1 - t));

            if (Math.floor(elapsed) % speed === 0) {
                items.forEach(el => el.classList.remove('glow'));
                items[idx].classList.add('glow');
                items[idx].scrollIntoView({ block: 'nearest' });
                idx = (idx + 1) % items.length;
            }

            if (t < 1) {
                requestAnimationFrame(tick);
            } else {
                items.forEach(el => el.classList.remove('glow'));
                const stopEl = items[stopIndex] || items[0];
                stopEl.classList.add('glow');
                stopEl.scrollIntoView({ block: 'nearest' });

                wheel.classList.remove('shake');
                resolve();
            }
        };
        requestAnimationFrame(tick);
    });
}

btnDraw?.addEventListener('click', async () => {
    btnDraw.disabled = true;
    winnerInline.textContent = '...';

    const domItems = [...rouletteList.querySelectorAll('[data-index]')].map(el => el.textContent.trim());

    const res = await fetch("{{ route('picker.draw') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    });

    if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        alert(err.message || 'Draw failed.');
        btnDraw.disabled = false;
        winnerInline.textContent = '—';
        return;
    }

    const data = await res.json();

    let stopIndex = 0;
    const w = (data.winner || '').trim();
    const foundIndex = domItems.findIndex(x => x === w);
    if (foundIndex >= 0) stopIndex = foundIndex;

    await animateRouletteStopAt(stopIndex, 2200);

    winnerInline.textContent = data.winner;

    renderRemaining(data.remaining);
    renderSelected(data.selected);

    const last = data.selected[data.selected.length - 1];
    if (last) openModal(last.name, last.drawn_at, last.draw_no);

    btnDraw.disabled = (data.remaining.length === 0);
});
</script>

</body>
</html>
