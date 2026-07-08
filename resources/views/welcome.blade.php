<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MeasureMate - Premium Ingredient Volume Converter</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind & App Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        /* Glassmorphism utility overrides if needed */
        .glass-panel {
            background: rgba(18, 27, 32, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(38, 53, 61, 0.7);
        }

        /* Tab highlights */
        .tab-btn.active {
            color: var(--color-accent-primary);
            border-bottom: 2px solid var(--color-accent-primary);
            background: rgba(0, 229, 255, 0.05);
        }

        /* Fade transitions */
        .fade-out {
            opacity: 0;
            transform: scale(0.95);
            transition: all 300ms ease;
        }
    </style>
</head>

<body class="min-h-screen bg-bg-primary text-text-primary antialiased">
    <div class="mm-container">

        <!-- Print-only layout (hidden until user clicks Print) -->
        <div id="mmPrintArea" class="hidden">
            <div class="mm-print-paper">
                <div class="mm-print-header">
                    <div class="mm-print-brand">MeasureMate</div>
                    <div class="mm-print-title">Conversion Output</div>
                </div>

                <div class="mm-print-grid">
                    <div class="mm-print-row">
                        <div class="mm-print-label">Original</div>
                        <div class="mm-print-value"><span id="mmPrintOriginalValue">—</span> <span id="mmPrintOriginalUnit">—</span></div>
                    </div>

                    <div class="mm-print-row">
                        <div class="mm-print-label">Target</div>
                        <div class="mm-print-value"><span id="mmPrintTargetUnit">—</span></div>
                    </div>

                    <div class="mm-print-row">
                        <div class="mm-print-label">Final Calculated Result</div>
                        <div class="mm-print-value"><span id="mmPrintFinalResult">—</span></div>
                    </div>

                    <div class="mm-print-row">
                        <div class="mm-print-label">Ingredient</div>
                        <div class="mm-print-value"><span id="mmPrintIngredient">—</span></div>
                    </div>

                    <div class="mm-print-row">
                        <div class="mm-print-label">Date/Time of Calculation</div>
                        <div class="mm-print-value"><span id="mmPrintCalculatedAt">—</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Section -->
        <header class="flex flex-col md:flex-row justify-between items-center gap-4 py-6 border-b border-border mb-8">
            <div class="flex items-center gap-3">
                <!-- Brand Icon -->
                <div class="w-10 h-10 rounded-xl bg-accent-primary flex items-center justify-center shadow-[0_0_20px_rgba(0,229,255,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-bg-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-accent-primary to-accent-secondary bg-clip-text text-transparent">MeasureMate</h1>
                    <p class="text-xs text-text-secondary">Precision ingredient volume converter for chefs & bakers</p>
                </div>
            </div>
            
        </header>

        <!-- Main Dashboard Layout -->
        <main class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Converter Engine (spans 5) -->
            <section class="lg:col-span-5 flex flex-col gap-6">
                <div class="mm-card p-6">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span class="text-accent-primary font-bold">#</span> Volume Converter
                    </h2>
                    
                    <form id="converterForm" class="flex flex-col gap-4" onsubmit="event.preventDefault(); convertVolume();">
                        
                        <!-- Error Alert Block -->
                        <div id="converterError" class="mm-alert mm-alert--danger hidden flex items-start gap-2 text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span id="converterErrorMessage">Please correct errors and try again.</span>
                        </div>

                        <!-- Input Amount -->
                        <div>
                            <label for="amount" class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Amount</label>
                            <input type="number" id="amount" step="any" min="0.000001" placeholder="Enter quantity (e.g. 1)" class="mm-input font-medium text-base py-3" required value="1">
                        </div>

                        <!-- Units Grid -->
                        <div class="flex flex-col gap-3 md:grid md:grid-cols-9 md:items-end md:gap-2">
                            <!-- From Unit -->
                            <div class="w-full md:col-span-4">
                                <label for="fromUnit" class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Smaller Unit</label>
                                <select id="fromUnit" class="mm-input font-medium py-3 w-full">
                                    @foreach($units as $key => $unit)
                                        <option value="{{ $key }}" {{ $key == 'cup' ? 'selected' : '' }}>{{ $unit['name'] }} ({{ $unit['symbol'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Swap Button -->
                            <div class="flex justify-center md:col-span-1 md:pb-1">
                                <button type="button" onclick="swapUnits();" class="w-10 h-10 rounded-xl bg-surface border border-border flex items-center justify-center hover:border-accent-primary hover:text-accent-primary transition-all duration-200" title="Swap Units">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- To Unit -->
                            <div class="w-full md:col-span-4">
                                <label for="toUnit" class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Larger Unit</label>
                                <select id="toUnit" class="mm-input font-medium py-3 w-full">
                                    @foreach($units as $key => $unit)
                                        <option value="{{ $key }}" {{ $key == 'tbsp' ? 'selected' : '' }}>{{ $unit['name'] }} ({{ $unit['symbol'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Buttons Group -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                            <button type="submit" class="mm-btn mm-btn--primary py-3 flex items-center justify-center font-bold">
                                Convert
                            </button>
                            <button type="button" onclick="resetConverter();" class="mm-btn mm-btn--secondary py-3 flex items-center justify-center font-bold">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Result Card -->
                <div class="mm-card p-6 border-l-4 border-l-accent-primary">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-semibold text-text-secondary uppercase tracking-wider">Conversion Output</span>
                        
                        <!-- Toggle Favorite Button -->
                        <button
                            id="favoriteBtn"
                            onclick="toggleCurrentFavorite();"
                            class="text-text-muted hover:text-yellow-400 transition-all duration-200"
                            title="Add to Favorites"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Calculated Result -->
                    <div class="mb-4">
                        <div class="text-4xl font-extrabold text-white tracking-tight" id="resultValue">--</div>
                        <div class="text-sm font-medium text-accent-primary mt-1" id="phraseValue">Select units and amount to convert.</div>
                    </div>

                    <!-- Action Bar -->
                    <div class="flex items-center gap-3 pt-3 border-t border-border/40">
                        <button type="button" onclick="copyResult();" class="mm-btn mm-btn--secondary text-xs px-3 py-2 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v10a2 2 0 002 2h8a2 2 0 002-2V7" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16" />
                            </svg>
                            Copy Result
                        </button>

                        <button type="button" onclick="copyPhrase();" class="mm-btn mm-btn--secondary text-xs px-3 py-2 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h8" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h4" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                            </svg>
                            Copy Phrase
                        </button>

                        <button type="button" onclick="printConversion();" class="mm-btn mm-btn--secondary text-xs px-3 py-2 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 14h12v8H6v-8z" />
                            </svg>
                            Print Result
                        </button>
                    </div>
                </div>
            </section>
            
            <!-- Right Column: Interactive Dashboard Tabs (spans 7) -->
            <section class="lg:col-span-7 flex flex-col gap-6">
                
                <!-- Tab Controls -->
                <div class="bg-surface rounded-2xl border border-border p-1.5 flex gap-1">
                    <button onclick="switchTab('notesTab');" id="notesTabBtn" class="tab-btn active flex-1 py-3 font-semibold text-sm rounded-xl text-center cursor-pointer transition-all duration-200">
                        Ingredient Notes
                    </button>
                    <button onclick="switchTab('favoritesTab');" id="favoritesTabBtn" class="tab-btn flex-1 py-3 font-semibold text-sm rounded-xl text-center cursor-pointer transition-all duration-200">
                        Favorites
                    </button>
                    <button onclick="switchTab('historyTab');" id="historyTabBtn" class="tab-btn flex-1 py-3 font-semibold text-sm rounded-xl text-center cursor-pointer transition-all duration-200">
                        Recent History
                    </button>
                </div>

                <!-- Tab 1: Ingredient Notes CRUD Panel -->
                <div id="notesPanel" class="tab-panel mm-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-2">
                            <span class="text-accent-secondary font-bold">#</span>
                            <h3 class="text-base font-bold text-white">Ingredient Notes</h3>
                        </div>
                    </div>

                    <!-- Notepad UI (Ingredient Notes) -->
                    <div class="mm-notepad-shell mb-6">
                        <div class="mm-notepad-ornament" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2.5" class="text-accent-primary">
                                <path d="M32 10c10 0 18 8 18 18s-8 18-18 18-18-8-18-18 8-18 18-18Z" opacity="0.45" />
                                <path d="M32 18c6 0 10 4 10 10s-4 10-10 10-10-4-10-10 4-10 10-10Z" />
                                <path d="M14 50c6-8 12-12 18-12s12 4 18 12" opacity="0.35" />
                            </svg>
                        </div>

                        <div class="mm-notepad-header">
                            <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                                <div class="flex-1">
                                    <label class="block text-[11px] font-bold uppercase text-text-secondary mb-1">Title</label>
                                    <input id="noteTitle" type="text" class="mm-input text-sm py-2" placeholder="e.g. Baking tips" value="">
                                </div>
                                <div class="w-full sm:w-44">
                                    <label class="block text-[11px] font-bold uppercase text-text-secondary mb-1">Category</label>
                                    <select id="noteCategory" class="mm-input text-sm py-2">
                                        <option value="Baking">Baking</option>
                                        <option value="Cooking" selected>Cooking</option>
                                        <option value="Drinks">Drinks</option>
                                        <option value="Desserts">Desserts</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mm-notepad-toolbar" role="toolbar" aria-label="Ingredient Notes toolbar">
                            <button type="button" onclick="notesNew()" class="mm-tool-btn" title="New Note (Ctrl+N)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                New Note
                            </button>
                            <button type="button" onclick="notesSave(true)" class="mm-tool-btn" title="Save Note (Ctrl+S)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                                Save Note
                            </button>

                            <button type="button" onclick="notesClearEditor()" class="mm-tool-btn" title="Clear">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                Clear Note
                            </button>

                            <div class="flex-1" style="min-width:220px">
                                <label class="sr-only" for="notesSearch">Search Notes (Ctrl+F)</label>
                                <input id="notesSearch" type="text" placeholder="Search notes..." oninput="filterNotes()" class="mm-input text-xs py-2" style="padding-top:9px;padding-bottom:9px;">
                            </div>
                        </div>

                        <div class="mm-notepad-writing">
                            <input type="hidden" id="noteId" value="">

                            <input type="hidden" id="noteFavorite" value="0">

                            <textarea id="noteBody" class="mm-notepad-textarea" placeholder="Write your ingredient notes, cooking tips, recipe ideas, or kitchen reminders here..." oninput="notesOnBodyInput()"></textarea>

                            <div class="mm-notepad-meta-row">
                                <div class="mm-notepad-badge mm-notepad-counter">
                                    <span id="notesCharCount">0</span>
                                    <span class="text-text-muted">characters</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes List -->
                    <div id="notesListContainer" class="flex flex-col gap-3 max-h-[360px] overflow-y-auto pr-1">
                        @forelse($notes as $note)
                            <div onclick="notesLoadNoteToEditor({{ $note->id }});" style="cursor:pointer;" class="bg-surface/50 border border-border/60 hover:border-border rounded-xl p-4 flex justify-between items-start gap-4 transition-all duration-200" id="note-card-{{ $note->id }}" data-note-id="{{ $note->id }}" data-favorite="{{ (int) $note->is_favorite }}">
                                <div class="mr-3 shrink-0 flex items-start">
                    <button type="button" onclick="event.stopPropagation(); toggleNoteFavorite({{ $note->id }});" aria-label="Toggle favorite" title="Toggle favorite" class="p-1">

                                        @if($note->is_favorite)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </svg>
                                        @endif
                                    </button>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-white text-sm" id="note-name-val-{{ $note->id }}">{{ $note->ingredient_name }}</h4>
                                    <p class="text-xs text-text-secondary mt-1" id="note-text-val-{{ $note->id }}">{{ $note->notes }}</p>
                                </div>
                                @php $noteHasContent = trim((string)($note->notes ?? '')) !== '' @endphp
                                <div class="flex gap-1 shrink-0">
                                    <button
                                        type="button"
                                        onclick="event.stopPropagation(); printIngredientNote({{ $note->id }});"
                                        class="p-1 text-text-muted hover:text-accent-primary transition-colors"
                                        title="Print Note"
                                        aria-label="Print"
                                        @if(!$noteHasContent) disabled @endif
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 14h12v8H6v-8z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div id="noNotesMessage" class="text-xs text-text-muted py-6 text-center">No ingredient notes found. Create your first note above.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Tab 2: Favorites Panel -->
                <div id="favoritesPanel" class="tab-panel mm-card p-6 hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="text-accent-secondary">#</span> Saved Favorite Conversions
                        </h3>
                    </div>

                    <!-- Favorites List -->
                    <div id="favoritesListContainer" class="flex flex-col gap-3 max-h-[420px] overflow-y-auto pr-1">
                        @forelse($favorites as $favorite)
                            <div class="bg-surface/50 border border-border/60 hover:border-border rounded-xl p-4 flex justify-between items-center gap-4 transition-all duration-200" id="favorite-card-{{ $favorite->id }}">
                                <div class="flex-1 cursor-pointer" onclick="loadFavorite({{ $favorite->amount }}, '{{ $favorite->from_unit }}', '{{ $favorite->to_unit }}');">
                                    <h4 class="font-bold text-white text-sm">
                                        {{ $favorite->amount }} {{ $favorite->from_unit }} &rarr; {{ $favorite->to_unit }}
                                    </h4>
                                    <p class="text-[11px] text-accent-secondary mt-0.5">Click to load conversion</p>
                                </div>
                                <div class="shrink-0">
                                    <button onclick="deleteFavorite({{ $favorite->id }});" class="p-1 text-text-muted hover:text-red-400 transition-colors" title="Delete Favorite">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div id="noFavoritesMessage" class="text-xs text-text-muted py-6 text-center">No favorites saved yet. Perform a conversion and click the star to save one.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Tab 3: History Panel -->
                <div id="historyPanel" class="tab-panel mm-card p-6 hidden">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="text-accent-secondary">#</span> Recent Conversions Log
                        </h3>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleHistorySelection();" id="selectHistoryBtn" class="mm-btn mm-btn--secondary text-xs px-3 py-1.5 font-semibold">
                                Select
                            </button>
                            <button type="button" onclick="deleteSelectedHistory();" id="deleteSelectedHistoryBtn" class="mm-btn mm-btn--danger text-xs px-3 py-1.5 font-semibold hidden">
                                Delete Selected
                            </button>
                            <button type="button" onclick="clearHistory();" id="clearHistoryBtn" class="mm-btn mm-btn--danger text-xs px-3 py-1.5 flex items-center gap-1 font-semibold {{ $history->isEmpty() ? 'hidden' : '' }}">
                                Clear All
                            </button>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" class="space-y-3 mb-4" id="historyFilterForm">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <input type="text" name="history_search" value="{{ old('history_search', $search) }}" placeholder="Search conversions..." class="mm-input text-xs py-2">
                            <select name="history_unit" class="mm-input text-xs py-2">
                                <option value="">All units</option>
                                @foreach(array_keys($units) as $unit)
                                    <option value="{{ $unit }}" {{ $unit === $unit ? '' : '' }}>{{ strtoupper($unit) }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="history_ingredient" value="{{ old('history_ingredient', $ingredient) }}" placeholder="Ingredient" class="mm-input text-xs py-2">
                            <select name="history_sort" class="mm-input text-xs py-2">
                                <option value="desc" {{ $sort === 'desc' ? 'selected' : '' }}>Newest first</option>
                                <option value="asc" {{ $sort === 'asc' ? 'selected' : '' }}>Oldest first</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <button type="submit" class="mm-btn mm-btn--primary text-xs px-3 py-1.5 font-semibold">Apply</button>
                            <a href="{{ route('dashboard') }}" class="text-xs text-text-muted hover:text-white">Reset</a>
                        </div>
                    </form>

                    <div id="historyListContainer" class="flex flex-col gap-3 max-h-[420px] overflow-y-auto pr-1">
                        @forelse($history as $item)
                            <div class="history-item bg-surface/50 border border-border/60 hover:border-border rounded-xl p-3.5 flex justify-between items-start gap-4 transition-all duration-200" id="history-card-{{ $item->id }}" data-phrase="{{ e(strtolower($item->result_text)) }}">
                                <label class="mt-1 hidden history-select-toggle">
                                    <input type="checkbox" class="history-checkbox rounded border-border bg-transparent" value="{{ $item->id }}">
                                </label>
                                <div class="flex-1 cursor-pointer" onclick="loadFavorite({{ $item->value_entered }}, '{{ e($item->from_unit) }}', '{{ e($item->to_unit) }}');">
                                    <p class="text-xs text-text-primary font-medium">{{ e($item->result_text) }}</p>
                                    <div class="text-[10px] text-text-muted mt-1 space-y-0.5">
                                        <div>Original: {{ e($item->value_entered) }} {{ e($item->from_unit) }} → {{ e($item->converted_value) }} {{ e($item->to_unit) }}</div>
                                        @if($item->ingredient)
                                            <div>Ingredient: {{ e($item->ingredient) }}</div>
                                        @endif
                                        <div>{{ $item->created_at->format('M j, Y H:i') }}</div>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <button type="button" onclick="deleteHistory({{ $item->id }});" class="p-1 text-text-muted hover:text-red-400 transition-colors" title="Delete Log">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div id="noHistoryMessage" class="text-xs text-text-muted py-6 text-center">No conversion history logged yet.</div>
                        @endforelse
                    </div>

                    @if($history->hasPages())
                        <div class="mt-4 flex justify-center">
                            {{ $history->links('pagination::tailwind') }}
                        </div>
                    @endif
                </div>

            </section>
        </main>
        
        <!-- Footer -->
        <footer class="mt-16 py-8 border-t border-border flex justify-between items-center text-xs text-text-muted">
            <div>
                &copy; {{ date('Y') }} MeasureMate. Built with Laravel 12 & Vite.
            </div>
            <div>
                Precision Cooking Measurement Tool
            </div>
        </footer>
    </div>

    <!-- AJAX & DOM Logic Scripts -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        const IS_AUTHENTICATED = @json(Auth::check());

        function number_format(number, decimals = 0, decPoint = '.', thousandsSep = '') {
            const value = Number(number ?? 0);
            if (!Number.isFinite(value)) {
                return '0';
            }

            const fixed = value.toFixed(decimals);
            return fixed.replace(/(\.\d*?[1-9])0+$/, '$1').replace(/\.0+$/, '');
        }

        // Global states
        let lastResult = '';
        let lastPhrase = '';
        let currentIsFavorite = false;

        // Switch panels tab
        function switchTab(tabId) {
            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            if (tabId === 'notesTab') {
                document.getElementById('notesPanel').classList.remove('hidden');
                document.getElementById('notesTabBtn').classList.add('active');
            } else if (tabId === 'favoritesTab') {
                document.getElementById('favoritesPanel').classList.remove('hidden');
                document.getElementById('favoritesTabBtn').classList.add('active');
            } else if (tabId === 'historyTab') {
                document.getElementById('historyPanel').classList.remove('hidden');
                document.getElementById('historyTabBtn').classList.add('active');
            }
        }

        // Normalize unit order so: fromUnit is always the smaller unit, toUnit is always the larger unit.
        // This must be the single source of truth and is called on load, reset, swap, and whenever unit selections change.
        function normalizeUnitOrder() {
            const fromSelect = document.getElementById('fromUnit');
            const toSelect = document.getElementById('toUnit');
            if (!fromSelect || !toSelect) return;

            const fromUnit = fromSelect.value;
            const toUnit = toSelect.value;
            if (!fromUnit || !toUnit) return;
            if (fromUnit === toUnit) return;

            // Uses the same ml hierarchy as the backend (see config/conversions.php)
            const mlByUnit = {
                ml: 1.0,
                tsp: 4.92892159375,
                tbsp: 14.78676478125,
                floz: 29.5735295625,
                cup: 236.5882365,
                pint: 473.176473015625,
                quart: 946.35294603125,
                liter: 1000.0,
                gallon: 3785.411784125,
            };

            const fromMl = mlByUnit[fromUnit];
            const toMl = mlByUnit[toUnit];
            if (!Number.isFinite(fromMl) || !Number.isFinite(toMl)) return;

            // If currently reversed, swap values.
            if (fromMl > toMl) {
                const temp = fromSelect.value;
                fromSelect.value = toSelect.value;
                toSelect.value = temp;
            }
        }

        // Swap unit selections (still normalizes afterward so smaller remains first)
        function swapUnits() {
            const fromSelect = document.getElementById('fromUnit');
            const toSelect = document.getElementById('toUnit');
            if (!fromSelect || !toSelect) return;
            const temp = fromSelect.value;
            fromSelect.value = toSelect.value;
            toSelect.value = temp;

            normalizeUnitOrder();
            showNotification('Units swapped.');
        }


        // Reset converter inputs and output display
        function resetConverter() {
            const amountInput = document.getElementById('amount');
            const resultValue = document.getElementById('resultValue');
            const phraseValue = document.getElementById('phraseValue');
            const errorBlock = document.getElementById('converterError');
            if (amountInput) amountInput.value = '1';
            if (resultValue) resultValue.innerText = '--';
            if (phraseValue) phraseValue.innerText = 'Select units and amount to convert.';
            if (errorBlock) errorBlock.classList.add('hidden');
            updateFavoriteStar(false);
            lastResult = '';
            lastPhrase = '';

            // Ensure unit order is still correct after reset.
            normalizeUnitOrder();

            showNotification('Converter reset.');
        }


        // Update the visual state of the favorite star
        function updateFavoriteStar(isFav) {
            currentIsFavorite = isFav;
            const star = document.getElementById('favoriteBtn');
            if (isFav) {
                star.classList.remove('text-text-muted');
                star.classList.add('text-yellow-400');
                star.setAttribute('title', 'Remove from Favorites');
                star.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                `;
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-text-muted');
                star.setAttribute('title', 'Add to Favorites');
                star.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                `;
            }
        }

        // Perform real-time conversion via POST AJAX
        async function convertVolume() {
            const amountInput = document.getElementById('amount');
            const fromSelect = document.getElementById('fromUnit');
            const toSelect = document.getElementById('toUnit');
            const errorBlock = document.getElementById('converterError');
            const errorMessage = document.getElementById('converterErrorMessage');

            errorBlock.classList.add('hidden');

            const payload = {
                amount: amountInput.value,
                from_unit: fromSelect.value,
                to_unit: toSelect.value
            };

            try {
                const response = await fetch('/convert', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Validation error');
                }

                // Render result
                document.getElementById('resultValue').innerText = data.result;
                document.getElementById('phraseValue').innerText = data.phrase;
                lastResult = data.result;
                lastPhrase = data.phrase;

                updateFavoriteStar(data.is_favorite);

                // Insert into history list dynamically
                insertHistoryDOM(data.history_item);
            } catch (err) {
                errorMessage.innerText = err.message;
                errorBlock.classList.remove('hidden');
                document.getElementById('resultValue').innerText = '--';
                document.getElementById('phraseValue').innerText = 'Conversion failed.';
            }
        }

        // Copy outputs to clipboard
        async function copyResult() {
            if (!lastResult) {
                showNotification('Run a conversion first.');
                return;
            }

            try {
                await navigator.clipboard.writeText(lastResult);
                showNotification('Result copied to clipboard!');
            } catch {
                showNotification('Unable to copy result.');
            }
        }

        async function copyPhrase() {
            if (!lastPhrase) {
                showNotification('Run a conversion first.');
                return;
            }

            try {
                await navigator.clipboard.writeText(lastPhrase);
                showNotification('Phrase copied to clipboard!');
            } catch {
                showNotification('Unable to copy phrase.');
            }
        }

        function printConversion() {
            const resultValueEl = document.getElementById('resultValue');
            const phraseValueEl = document.getElementById('phraseValue');
            const amountEl = document.getElementById('amount');
            const fromUnitEl = document.getElementById('fromUnit');
            const toUnitEl = document.getElementById('toUnit');

            const currentResult = (resultValueEl?.innerText || '').trim();
            const currentPhrase = (phraseValueEl?.innerText || '').trim();

            // Prevent printing empty/placeholder output
            if (!currentResult || currentResult === '--' || !currentPhrase || currentPhrase.includes('Select units')) {
                showNotification('Please run a conversion before printing.');
                return;
            }


            const now = new Date();
            const printedOn = now.toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });

            const ingredient = (window.__lastIngredientName || '').trim() || '—';

            // Escape for safe HTML injection
            const esc = (str) => String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const originalValue = (amountEl?.value || '').trim();
            const originalUnit = (fromUnitEl?.value || '').trim();
            const targetUnit = (toUnitEl?.value || '').trim();

            // Populate dedicated same-page print layout
            const printArea = document.getElementById('mmPrintArea');
            if (!printArea) {
                alert('Print layout missing.');
                return;
            }

            printArea.querySelector('#mmPrintOriginalValue').innerHTML = esc(originalValue || '—');
            printArea.querySelector('#mmPrintOriginalUnit').innerHTML = esc(originalUnit || '—');
            printArea.querySelector('#mmPrintTargetUnit').innerHTML = esc(targetUnit || '—');
            printArea.querySelector('#mmPrintFinalResult').innerHTML = esc(currentResult || '—');
            printArea.querySelector('#mmPrintIngredient').innerHTML = esc(ingredient);
            printArea.querySelector('#mmPrintCalculatedAt').innerHTML = esc(printedOn);

            // Print from same page so CSS @media print works.
            document.body.classList.add('mm-printing-active');
            printArea.classList.remove('hidden');

            const cleanup = () => {
                printArea.classList.add('hidden');
                document.body.classList.remove('mm-printing-active');
            };

            window.onafterprint = cleanup;
            window.print();

            setTimeout(cleanup, 1000);
        }

        function showNotification(msg) {
            const notification = document.createElement('div');
            notification.className = 'fixed bottom-5 right-5 bg-accent-primary text-bg-primary text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg z-50 transition-all duration-300';
            notification.innerText = msg;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }

        // Load a favorite conversion and re-sync the active star state from the server
        async function loadFavorite(amount, fromUnit, toUnit) {
            document.getElementById('amount').value = amount;
            document.getElementById('fromUnit').value = fromUnit;
            document.getElementById('toUnit').value = toUnit;
            await convertVolume();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Toggle favorite status for the currently entered parameters
        async function toggleCurrentFavorite() {
            const amount = document.getElementById('amount').value;
            const fromUnit = document.getElementById('fromUnit').value;
            const toUnit = document.getElementById('toUnit').value;

            if (!amount) return;

            const amount6 = number_format((parseFloat(amount) || 0), 6, '.', '');
            const payload = { amount: amount6, from_unit: fromUnit, to_unit: toUnit, ingredient: null };

            try {
                const response = await fetch('/favorites', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message);

                if (data.status === 'added') {
                    updateFavoriteStar(true);
                    await refreshFavoritesList();
                    showNotification('Added to Favorites!');
                } else {
                    updateFavoriteStar(false);
                    await refreshFavoritesList();
                    showNotification('Removed from Favorites.');
                }
            } catch (err) {
                showNotification('Error toggling favorite.');
            }
        }

        function buildFavoriteCardMarkup(favorite) {
            return `
                <div class="flex-1 cursor-pointer" onclick="loadFavorite(${favorite.amount}, '${escapeHtml(favorite.from_unit)}', '${escapeHtml(favorite.to_unit)}');">
                    <h4 class="font-bold text-white text-sm">
                        ${escapeHtml(favorite.amount)} ${escapeHtml(favorite.from_unit)} &rarr; ${escapeHtml(favorite.to_unit)}
                    </h4>
                    <p class="text-[11px] text-accent-secondary mt-0.5">Click to load conversion</p>
                </div>
                <div class="shrink-0">
                    <button onclick="deleteFavorite(${favorite.id});" class="p-1 text-text-muted hover:text-red-400 transition-colors" title="Delete Favorite">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `;
        }

        function insertFavoriteDOM(favorite) {
            const container = document.getElementById('favoritesListContainer');
            const noMsg = document.getElementById('noFavoritesMessage');
            if (noMsg) noMsg.remove();

            const card = document.createElement('div');
            card.className = 'bg-surface/50 border border-border/60 hover:border-border rounded-xl p-4 flex justify-between items-center gap-4 transition-all duration-200';
            card.id = `favorite-card-${favorite.id}`;
            card.innerHTML = buildFavoriteCardMarkup(favorite);
            container.prepend(card);
        }

        async function refreshFavoritesList() {
            try {
                const response = await fetch('/favorites', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) throw new Error(data.message || 'Unable to load favorites');

                const container = document.getElementById('favoritesListContainer');
                if (!container) return;

                container.innerHTML = '';
                const favorites = Array.isArray(data.favorites) ? data.favorites : [];

                if (favorites.length === 0) {
                    container.innerHTML = '<div id="noFavoritesMessage" class="text-xs text-text-muted py-6 text-center">No favorites saved yet. Perform a conversion and click the star to save one.</div>';
                    return;
                }

                favorites.forEach((favorite) => {
                    const card = document.createElement('div');
                    card.className = 'bg-surface/50 border border-border/60 hover:border-border rounded-xl p-4 flex justify-between items-center gap-4 transition-all duration-200';
                    card.id = `favorite-card-${favorite.id}`;
                    card.innerHTML = buildFavoriteCardMarkup(favorite);
                    container.appendChild(card);
                });
            } catch (err) {
                console.warn(err);
            }
        }

        async function deleteFavorite(id) {
            try {
                const response = await fetch(`/favorites/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (!response.ok) throw new Error();

                await refreshFavoritesList();

                // If currently loaded values match deleted favorite, clear active star
                const amount = document.getElementById('amount').value;
                const fromUnit = document.getElementById('fromUnit').value;
                const toUnit = document.getElementById('toUnit').value;
                // Run silent validation checks
                const currentFavResponse = await fetch('/convert', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({ amount, from_unit: fromUnit, to_unit: toUnit })
                });
                const currentFavData = await currentFavResponse.json();
                if (currentFavResponse.ok) {
                    updateFavoriteStar(currentFavData.is_favorite);
                }
            } catch (err) {
                showNotification('Error deleting favorite.');
            }
        }

        function removeFavoriteDOMByParams(amount, from, to) {
            document.querySelectorAll('#favoritesListContainer > div').forEach(card => {
                if (card.innerText.includes(`${amount} ${from}`) && card.innerText.includes(to)) {
                    card.classList.add('fade-out');
                    setTimeout(() => {
                        card.remove();
                        checkFavoritesEmpty();
                    }, 300);
                }
            });
        }

        function checkFavoritesEmpty() {
            const container = document.getElementById('favoritesListContainer');
            if (container.children.length === 0) {
                container.innerHTML = `<div id="noFavoritesMessage" class="text-xs text-text-muted py-6 text-center">No favorites saved yet. Perform a conversion and click the star to save one.</div>`;
            }
        }

        // Ingredient Notes (Notepad UI) - uses existing backend /notes endpoints

        function notesGetEditorState() {
            return {
                id: document.getElementById('noteId')?.value || '',
                title: document.getElementById('noteTitle')?.value || '',
                category: document.getElementById('noteCategory')?.value || 'Cooking',
                favorite: (document.getElementById('noteFavorite')?.value || '0') === '1',
                body: document.getElementById('noteBody')?.value || ''
            };
        }

        function updateNoteFavoriteButton() {
            const btn = document.getElementById('favoriteNoteBtn');
            if (!btn) return;
            const active = (document.getElementById('noteFavorite')?.value || '0') === '1';
            btn.classList.toggle('text-accent-primary', active);
            btn.classList.toggle('text-text-muted', !active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            btn.setAttribute('title', active ? 'Remove favorite note' : 'Favorite note');
        }

        function notesUpdateCharCount() {
            const body = document.getElementById('noteBody');
            const counter = document.getElementById('notesCharCount');
            if (!body || !counter) return;
            const count = (body.value || '').length;
            counter.innerText = String(count);
        }

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }


        function printIngredientNote(noteId) {
            const titleEl = document.getElementById(`note-name-val-${noteId}`);
            const bodyEl = document.getElementById(`note-text-val-${noteId}`);

            const rawTitle = titleEl?.innerText ?? '';
            const title = rawTitle.trim() ? rawTitle.trim() : 'Untitled';

            const rawBody = bodyEl?.innerText ?? '';
            const body = rawBody.trim();

            if (!body) {
                alert('This note has no content to print.');
                return;
            }

            const now = new Date();
            const printedOn = now.toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });

            // Best-effort metadata (the current cards only render title + notes).
            const category = 'Cooking';
            const createdAt = now.toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit' });
            const updatedAt = printedOn;

            // Printable HTML only (no embedded <script>), then parent triggers print.
            const html = `<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Print Ingredient Note</title>
<style>
    @media print {
        body { background:#fff !important; color:#000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    body{ font-family: Arial, Helvetica, sans-serif; color:#000; background:#fff; margin:0; }
    .mm-print-page{ padding: 20mm; }
    .mm-brand{ font-size:16px; font-weight:800; margin:0 0 4px; }
    .mm-section{ font-size:14px; font-weight:700; margin:0 0 10px; }
    .mm-meta{ border-top:1px solid #000; border-bottom:1px solid #000; padding: 8px 0; margin: 12px 0; }
    .mm-meta-row{ display:flex; flex-wrap:wrap; gap: 16px; font-size:12px; }
    .mm-label{ font-weight:700; }
    .mm-note-title{ font-size:16px; font-weight:700; margin-top: 6px; }
    .mm-note-body{ white-space: pre-wrap; line-height:1.45; margin-top: 10px; font-size: 12.5px; }
    .mm-footer{ margin-top: 18px; font-size: 11.5px; }
</style>
</head>
<body>
    <div class="mm-print-page">
        <div>
            <div class="mm-brand">MeasureMate</div>
            <div class="mm-section">Ingredient Note</div>
        </div>

        <div class="mm-meta">
            <div class="mm-note-title">${escapeHtml(title)}</div>
            <div class="mm-meta-row">
                <div><span class="mm-label">Category</span>: ${escapeHtml(category)}</div>
                <div><span class="mm-label">Date Created</span>: ${escapeHtml(createdAt)}</div>
                <div><span class="mm-label">Last Updated</span>: ${escapeHtml(updatedAt)}</div>
            </div>
        </div>

        <div class="mm-note-body">${escapeHtml(rawBody)}</div>

        <div class="mm-footer">
            <div>Generated by MeasureMate</div>
            <div>Printed on: ${escapeHtml(printedOn)}</div>
        </div>
    </div>
</body>
</html>`;

            const printUsingPopup = () => {
                const printWin = window.open('', '_blank', 'noopener,noreferrer');
                if (!printWin) return false;

                try {
                    printWin.document.open();
                    printWin.document.write(html);
                    printWin.document.close();

                    const doPrint = () => {
                        try {
                            printWin.focus();
                            printWin.print();
                        } catch {
                            // ignore
                        }
                    };

                    // Delay slightly to allow fonts/resources to render
                    setTimeout(doPrint, 250);
                    return true;
                } catch (e) {
                    try { printWin.close(); } catch {}
                    return false;
                }
            };

            const printUsingIframe = () => {
                try {
                    const iframe = document.createElement('iframe');
                    iframe.style.position = 'fixed';
                    iframe.style.right = '0';
                    iframe.style.bottom = '0';
                    iframe.style.width = '0';
                    iframe.style.height = '0';
                    iframe.style.border = '0';
                    iframe.setAttribute('aria-hidden', 'true');
                    document.body.appendChild(iframe);

                    const idoc = iframe.contentDocument || iframe.contentWindow.document;
                    idoc.open();
                    idoc.write(html);
                    idoc.close();

                    const tryPrint = () => {
                        try {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                        } catch (e) {
                            // ignore
                        } finally {
                            setTimeout(() => { try { iframe.remove(); } catch {} }, 400);
                        }
                    };

                    // Some browsers require a slight delay to render the content.
                    iframe.onload = () => setTimeout(tryPrint, 150);
                    // Fallback: attempt print after a timeout even if onload doesn't fire.
                    setTimeout(tryPrint, 400);
                    return true;
                } catch (e) {
                    return false;
                }
            };

            // Try popup first; if blocked, fallback to iframe printing
            const usedPopup = printUsingPopup();
            if (!usedPopup) {
                const usedIframe = printUsingIframe();
                if (!usedIframe) {
                    alert('Unable to open print window. Please allow popups or try printing from your browser menu.');
                }
            }
        }




        function notesLocalKey() {
            const userKey = @json(Auth::check() ? Auth::id() : 'guest');
            return `mm_ingredient_notes_draft_${userKey}`;
        }

        // Avoid any accidental focus/active state on page load.
        // (No autofocus attributes and no editor-button focus calls.)

        function notesPersistDraft() {
            const st = notesGetEditorState();
            localStorage.setItem(notesLocalKey(), JSON.stringify({
                noteId: st.id,
                title: st.title,
                category: st.category,

                favorite: st.favorite,
                body: st.body,
                updatedAt: Date.now()
            }));

            localStorage.setItem('mm_ingredient_notes_favorite', JSON.stringify(window.__notesFavorite || {}));
        }

        function notesRestoreDraft() {
            const raw = localStorage.getItem(notesLocalKey());
            if (!raw) return;
            try {
                const draft = JSON.parse(raw);
                if (!draft) return;

                // If the saved noteId is no longer present in the DOM, treat it as stale.
                // (After delete, the server record is gone but localStorage can keep old IDs.)
                const staleId = draft.noteId;
                let noteIdIsStillPresent = true;
                if (staleId) {
                    const card = document.getElementById(`note-card-${staleId}`);
                    noteIdIsStillPresent = !!card;
                }

                const noteIdEl = document.getElementById('noteId');
                const titleEl = document.getElementById('noteTitle');
                const catEl = document.getElementById('noteCategory');
                const favEl = document.getElementById('noteFavorite');
                const bodyEl = document.getElementById('noteBody');

                if (noteIdEl) noteIdEl.value = noteIdIsStillPresent ? (draft.noteId || '') : '';
                if (titleEl) titleEl.value = draft.title || '';
                if (catEl) catEl.value = draft.category || 'Cooking';
                if (favEl) favEl.value = noteIdIsStillPresent && draft.favorite ? '1' : '0';
                if (bodyEl) bodyEl.value = draft.body || '';

                notesUpdateCharCount();
            } catch (e) {
                // ignore
            }
        }


        function notesOnBodyInput() {
            notesUpdateCharCount();
            // Debounced local auto-save
            if (window.__notesDraftTimer) clearTimeout(window.__notesDraftTimer);
            window.__notesDraftTimer = setTimeout(() => {
                notesPersistDraft();
            }, 600);
        }

        async function notesSave(syncToServer = true) {
            const st = notesGetEditorState();
            const trimmedTitle = (st.title || '').trim();
            const trimmedBody = (st.body || '').trim();
            if (!trimmedTitle && !trimmedBody) {
                showNotification('Add a title or note content before saving.');
                return;
            }

            // Always persist locally
            notesPersistDraft();

            if (!syncToServer) return;

            const payload = {
                ingredient_name: trimmedTitle || '(Untitled)',
                notes: trimmedBody,
                is_favorite: st.favorite
            };

            const url = st.id ? `/notes/${st.id}` : '/notes';
            const method = st.id ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Save failed');

                // Update DOM for title/body
                if (st.id) {
                    const nameEl = document.getElementById(`note-name-val-${st.id}`);
                    const textEl = document.getElementById(`note-text-val-${st.id}`);
                    if (nameEl) nameEl.innerText = data.note.ingredient_name;
                    if (textEl) textEl.innerText = data.note.notes;

                    const card = document.getElementById(`note-card-${st.id}`);
                    if (card) {
                        card.setAttribute('data-favorite', data.note.is_favorite ? '1' : '0');
                    }
                    // Ensure print button exists and is enabled/disabled based on content
                    try {
                        const card = document.getElementById(`note-card-${st.id}`);
                        if (card) {
                            const printBtn = card.querySelector('button[aria-label="Print"]');
                            const hasContent = String(data.note.notes || '').trim().length > 0;
                            if (printBtn) {
                                if (hasContent) printBtn.removeAttribute('disabled'); else printBtn.setAttribute('disabled', '');
                            } else if (hasContent) {
                                // create print button and insert before delete button
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'p-1 text-text-muted hover:text-accent-primary transition-colors';
                                btn.setAttribute('title', 'Print Note');
                                btn.setAttribute('aria-label', 'Print');
                                btn.setAttribute('onclick', `printIngredientNote(${data.note.id});`);
                                btn.innerHTML = `\n                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">\n                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7" />\n                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />\n                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 14h12v8H6v-8z" />\n                                    </svg>`;
                                // insert before the delete button (last child)
                                const actions = card.querySelector('.flex.gap-1.shrink-0');
                                if (actions) {
                                    // insert as the second element (after edit)
                                    actions.insertBefore(btn, actions.children[1] || null);
                                }
                            }
                            // Update favorite star in the card if present
                            try {
                                const starBtn = card.querySelector('button[aria-label="Toggle favorite"]');
                                if (starBtn) {
                                    const fav = !!data.note.is_favorite;
                                    starBtn.innerHTML = fav ? `\n                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">\n                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />\n                                        </svg>` : `\n                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">\n                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />\n                                        </svg>`;
                                }
                            } catch (e) {}
                        }
                    } catch (e) {
                        // ignore DOM update errors
                    }
                } else {
                    insertNoteDOM(data.note);
                    document.getElementById('noteId').value = data.note.id;

                    // Ensure the newly inserted card has a print button and is visible without requiring refresh
                    try {
                        const card = document.getElementById(`note-card-${data.note.id}`);
                        if (card) {
                            card.classList.add('note-card-visible');
                            const hasContent = String(data.note.notes || '').trim().length > 0;
                            let printBtn = card.querySelector('button[aria-label="Print"]');
                            if (!printBtn && hasContent) {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'p-1 text-text-muted hover:text-accent-primary transition-colors';
                                btn.setAttribute('title', 'Print Note');
                                btn.setAttribute('aria-label', 'Print');
                                btn.setAttribute('onclick', `printIngredientNote(${data.note.id});`);
                                btn.innerHTML = `\n                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">\n                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7" />\n                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />\n                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 14h12v8H6v-8z" />\n                                    </svg>`;
                                const actions = card.querySelector('.flex.gap-1.shrink-0');
                                if (actions) actions.insertBefore(btn, actions.children[1] || null);
                            } else if (printBtn) {
                                if (hasContent) printBtn.removeAttribute('disabled'); else printBtn.setAttribute('disabled', '');
                            }
                        }
                    } catch (e) {
                        // ignore
                    }
                }

                notesPersistDraft();
                showNotification(st.id ? 'Note updated.' : 'Note saved.');
            } catch (e) {
                showNotification(e.message || 'Error saving note');
            }
        }

        function notesNew() {
            const noteIdEl = document.getElementById('noteId');
            const titleEl = document.getElementById('noteTitle');
            const categoryEl = document.getElementById('noteCategory');
            const favoriteEl = document.getElementById('noteFavorite');
            const bodyEl = document.getElementById('noteBody');
            if (noteIdEl) noteIdEl.value = '';
            if (titleEl) titleEl.value = '';
            if (categoryEl) categoryEl.value = 'Cooking';
            if (favoriteEl) favoriteEl.value = '0';
            if (bodyEl) bodyEl.value = '';
            notesUpdateCharCount();
            updateNoteFavoriteButton();
            notesPersistDraft();
            showNotification('New note started.');
        }

        async function notesLoadNoteToEditor(id) {
            const card = document.getElementById(`note-card-${id}`);
            if (!card) return;
            const title = document.getElementById(`note-name-val-${id}`)?.innerText || '';
            const body = document.getElementById(`note-text-val-${id}`)?.innerText || '';

            document.getElementById('noteId').value = id;
            document.getElementById('noteTitle').value = title;
            document.getElementById('noteBody').value = body;
            document.getElementById('noteCategory').value = 'Cooking';
            document.getElementById('noteFavorite').value = (document.getElementById(`note-card-${id}`)?.getAttribute('data-favorite') === '1' || (window.__notesFavorite || {})[id]) ? '1' : '0';

            updateNoteFavoriteButton();
            notesUpdateCharCount();
            notesPersistDraft();
            // Focus editor so user can immediately edit
            try {
                const bodyEl = document.getElementById('noteBody');
                if (bodyEl) {
                    bodyEl.focus();
                    // move caret to end
                    const len = bodyEl.value.length;
                    if (typeof bodyEl.selectionStart === 'number') {
                        bodyEl.selectionStart = bodyEl.selectionEnd = len;
                    }
                }
                // scroll editor into view
                const shell = document.querySelector('.mm-notepad-shell');
                if (shell) shell.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch (e) {
                // ignore
            }
        }

        function notesClearEditor() {
            notesNew();
        }



        // Toggle favorite state for a given note id via AJAX
        async function toggleNoteFavorite(noteId) {
            const card = document.getElementById(`note-card-${noteId}`);
            const current = card ? (card.getAttribute('data-favorite') === '1') : false;
            const newVal = !current;
            const titleEl = document.getElementById(`note-name-val-${noteId}`);
            const bodyEl = document.getElementById(`note-text-val-${noteId}`);
            const payload = {
                ingredient_name: titleEl?.innerText.trim() || '',
                notes: bodyEl?.innerText.trim() || '',
                is_favorite: newVal ? 1 : 0,
            };

            try {
                const response = await fetch(`/notes/${noteId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Unable to toggle favorite');

                if (card) {
                    card.setAttribute('data-favorite', data.note?.is_favorite ? '1' : '0');
                    const starBtn = card.querySelector('button[aria-label="Toggle favorite"]');
                    if (starBtn) {
                        starBtn.innerHTML = data.note?.is_favorite ? `\n                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">\n                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />\n                            </svg>` : `\n                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">\n                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />\n                            </svg>`;
                    }
                }

                const noteIdEl = document.getElementById('noteId');
                if (noteIdEl && String(noteIdEl.value) === String(noteId)) {
                    document.getElementById('noteFavorite').value = data.note?.is_favorite ? '1' : '0';
                    updateNoteFavoriteButton();
                }

                window.__notesFavorite = window.__notesFavorite || {};
                window.__notesFavorite[noteId] = !!data.note?.is_favorite;
                localStorage.setItem('mm_ingredient_notes_favorite', JSON.stringify(window.__notesFavorite || {}));

                showNotification(data.note?.is_favorite ? 'Marked favorite' : 'Unmarked favorite');
            } catch (e) {
                showNotification(e.message || 'Error toggling favorite');
            }
        }

        function insertNoteDOM(note) {
            const container = document.getElementById('notesListContainer');
            const noMsg = document.getElementById('noNotesMessage');
            if (noMsg) noMsg.remove();

            const card = document.createElement('div');
            card.className = 'bg-surface/50 border border-border/60 hover:border-border rounded-xl p-4 flex justify-between items-start gap-4 transition-all duration-200';
            card.id = `note-card-${note.id}`;
            card.setAttribute('data-note-id', String(note.id));
            card.style.cursor = 'pointer';
            card.onclick = function() { notesLoadNoteToEditor(note.id); };
            const printDisabled = (String(note.notes || '').trim().length === 0) ? 'disabled' : '';
            card.innerHTML = `
                <div class="mr-3 shrink-0 flex items-start">
                    <button onclick="event.stopPropagation(); toggleNoteFavorite(${note.id});" aria-label="Toggle favorite" title="Toggle favorite" class="p-1" id="note-star-${note.id}">
                        ${String(note.is_favorite ? `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>` : `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>`)}
                    </button>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-white text-sm" id="note-name-val-${note.id}">${escapeHtml(note.ingredient_name)}</h4>
                    <p class="text-xs text-text-secondary mt-1 whitespace-pre-wrap" id="note-text-val-${note.id}">${escapeHtml(note.notes)}</p>
                </div>
                <div class="flex gap-1 shrink-0">

                    <button
                        type="button"
                        onclick="event.stopPropagation(); printIngredientNote(${note.id});"
                        class="p-1 text-text-muted hover:text-accent-primary transition-colors"
                        title="Print Note"
                        aria-label="Print"
                        ${printDisabled}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 14h12v8H6v-8z" />
                        </svg>
                    </button>

                </div>
            `;

            container.prepend(card);
        }


        function checkNotesEmpty() {
            const container = document.getElementById('notesListContainer');
            if (!container) return;
            if (container.children.length === 0) {
                container.innerHTML = `<div id="noNotesMessage" class="text-xs text-text-muted py-6 text-center">No ingredient notes found. Create your first note above.</div>`;
            }
        }

        function filterNotes() {
            const query = (document.getElementById('notesSearch')?.value || '').toLowerCase().trim();
            const cards = document.querySelectorAll('#notesListContainer > div[id^="note-card-"]');
            cards.forEach(card => {
                const id = card.getAttribute('id').replace('note-card-', '');
                const title = document.getElementById(`note-name-val-${id}`)?.innerText || '';
                const body = document.getElementById(`note-text-val-${id}`)?.innerText || '';
                const hay = `${title}\n${body}`.toLowerCase();
                const visible = hay.includes(query);
                card.style.display = visible ? 'flex' : 'none';
                card.classList.toggle('note-card-visible', visible);
            });
        }

        // Keyboard shortcuts for the notepad
        function notesInstallShortcuts() {
            document.addEventListener('keydown', (e) => {
                const isMac = navigator.platform.toLowerCase().includes('mac');
                const ctrlOrCmd = isMac ? e.metaKey : e.ctrlKey;

                if (!ctrlOrCmd) return;

                if (e.key === 's' || e.key === 'S') {
                    e.preventDefault();
                    notesSave(true);
                }

                if (e.key === 'n' || e.key === 'N') {
                    e.preventDefault();
                    notesNew();
                }

                if (e.key === 'f' || e.key === 'F') {
                    e.preventDefault();
                    const el = document.getElementById('notesSearch');
                    if (el) {
                        el.focus();
                        filterNotes();
                    }
                }
            });
        }

        // Initialize editor state on load
        (function initIngredientNotesNotepad() {
            window.__notesFavorite = {};

            // Initialize unit dropdown order (also fixes any persisted/restored reversed state)
            // Default for new visitors should remain: smaller=ml, larger=gallon.
            const fromSelect = document.getElementById('fromUnit');
            const toSelect = document.getElementById('toUnit');
            if (fromSelect && toSelect) {
                // If the page was loaded with the intended defaults in markup, this is a no-op.
                normalizeUnitOrder();

                // Ensure every user change re-normalizes.
                fromSelect.addEventListener('change', normalizeUnitOrder);
                toSelect.addEventListener('change', normalizeUnitOrder);
            }


            // restore draft to textarea + title
            notesRestoreDraft();
            notesUpdateCharCount();
            updateNoteFavoriteButton();

            const rawFav = localStorage.getItem('mm_ingredient_notes_favorite');
            if (rawFav) { try { window.__notesFavorite = JSON.parse(rawFav) || {}; } catch {} }

            filterNotes();
            notesInstallShortcuts();

            // keep list visibility class updated
            const cards = document.querySelectorAll('#notesListContainer > div[id^="note-card-"]');
            cards.forEach(c => c.classList.add('note-card-visible'));

            // Hook auto-save on load
            const body = document.getElementById('noteBody');
            if (body) {
                body.addEventListener('input', notesOnBodyInput);
            }
        })();

        // History Log management
        function insertHistoryDOM(item) {
            const container = document.getElementById('historyListContainer');
            const noMsg = document.getElementById('noHistoryMessage');
            if (noMsg) noMsg.remove();

            const clearBtn = document.getElementById('clearHistoryBtn');
            if (clearBtn) clearBtn.classList.remove('hidden');

            const card = document.createElement('div');
            card.className = 'history-item bg-surface/50 border border-border/60 hover:border-border rounded-xl p-3.5 flex justify-between items-start gap-4 transition-all duration-200';
            card.id = `history-card-${item.id}`;
            card.setAttribute('data-phrase', (item.phrase || '').toLowerCase());
            card.innerHTML = `
                <label class="mt-1 hidden history-select-toggle"><input type="checkbox" class="history-checkbox rounded border-border bg-transparent" value="${item.id}"></label>
                <div class="flex-1 cursor-pointer" onclick="loadFavorite(${item.amount}, '${escapeHtml(item.from_unit)}', '${escapeHtml(item.to_unit)}');">
                    <p class="text-xs text-text-primary font-medium">${escapeHtml(item.phrase || '')}</p>
                    <div class="text-[10px] text-text-muted mt-1 space-y-0.5">
                        <div>Original: ${escapeHtml(item.amount)} ${escapeHtml(item.from_unit)} → ${escapeHtml(item.result)} ${escapeHtml(item.to_unit)}</div>
                        ${item.ingredient ? `<div>Ingredient: ${escapeHtml(item.ingredient)}</div>` : ''}
                        <div>just now</div>
                    </div>
                </div>
                <div class="shrink-0">
                    <button onclick="deleteHistory(${item.id});" class="p-1 text-text-muted hover:text-red-400 transition-colors" title="Delete Log">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `;
            container.prepend(card);
        }

        async function deleteHistory(id) {
            try {
                const response = await fetch(`/history/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (!response.ok) throw new Error();

                const card = document.getElementById(`history-card-${id}`);
                if (card) {
                    card.classList.add('fade-out');
                    setTimeout(() => {
                        card.remove();
                        checkHistoryEmpty();
                    }, 300);
                }
                showNotification('History item deleted.');
            } catch (err) {
                showNotification('Error deleting history item.');
            }
        }

        async function deleteSelectedHistory() {
            const selected = Array.from(document.querySelectorAll('.history-checkbox:checked')).map(input => Number(input.value));
            if (selected.length === 0) {
                showNotification('Select at least one history item.');
                return;
            }

            if (!confirm('Delete the selected history records?')) return;

            try {
                const response = await fetch('/history/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ ids: selected })
                });
                if (!response.ok) throw new Error();

                selected.forEach(id => {
                    const card = document.getElementById(`history-card-${id}`);
                    if (card) {
                        card.classList.add('fade-out');
                        setTimeout(() => card.remove(), 300);
                    }
                });
                setTimeout(checkHistoryEmpty, 350);
                showNotification('Selected history items deleted.');
            } catch (err) {
                showNotification('Error deleting selected history items.');
            }
        }

        async function clearHistory() {
            if (!confirm('Are you sure you want to clear your entire conversion history?')) return;

            try {
                const response = await fetch('/history', {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (!response.ok) throw new Error();

                const container = document.getElementById('historyListContainer');
                container.querySelectorAll('.history-item').forEach(item => {
                    item.classList.add('fade-out');
                });

                setTimeout(() => {
                    container.innerHTML = `<div id="noHistoryMessage" class="text-xs text-text-muted py-6 text-center">No conversion history logged yet.</div>`;
                    const clearBtn = document.getElementById('clearHistoryBtn');
                    if (clearBtn) clearBtn.classList.add('hidden');
                    const deleteSelectedBtn = document.getElementById('deleteSelectedHistoryBtn');
                    if (deleteSelectedBtn) deleteSelectedBtn.classList.add('hidden');
                }, 300);

                showNotification('History cleared.');
            } catch (err) {
                showNotification('Error clearing history.');
            }
        }

        function checkHistoryEmpty() {
            const container = document.getElementById('historyListContainer');
            const items = container.querySelectorAll('.history-item');
            if (items.length === 0) {
                container.innerHTML = `<div id="noHistoryMessage" class="text-xs text-text-muted py-6 text-center">No conversion history logged yet.</div>`;
                const clearBtn = document.getElementById('clearHistoryBtn');
                if (clearBtn) clearBtn.classList.add('hidden');
                const deleteSelectedBtn = document.getElementById('deleteSelectedHistoryBtn');
                if (deleteSelectedBtn) deleteSelectedBtn.classList.add('hidden');
            }
        }

        function toggleHistorySelection() {
            const toggles = document.querySelectorAll('.history-select-toggle');
            const visible = Array.from(toggles).some(el => el.classList.contains('hidden'));
            toggles.forEach(el => {
                el.classList.toggle('hidden', !visible);
            });
            const deleteSelectedBtn = document.getElementById('deleteSelectedHistoryBtn');
            if (deleteSelectedBtn) {
                deleteSelectedBtn.classList.toggle('hidden', !visible);
            }
        }

        function filterHistory() {
            const query = document.getElementById('historySearch').value.toLowerCase().trim();
            const items = document.querySelectorAll('.history-item');

            items.forEach(item => {
                const phrase = item.getAttribute('data-phrase') || '';
                if (phrase.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>
