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
                        <div class="grid grid-cols-9 items-end gap-2">
                            <!-- From Unit -->
                            <div class="col-span-4">
                                <label for="fromUnit" class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">From</label>
                                <select id="fromUnit" class="mm-input font-medium py-3">
                                    @foreach($units as $key => $unit)
                                        <option value="{{ $key }}" {{ $key == 'cup' ? 'selected' : '' }}>{{ $unit['name'] }} ({{ $unit['symbol'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Swap Button -->
                            <div class="col-span-1 flex justify-center pb-1">
                                <button type="button" onclick="swapUnits();" class="w-10 h-10 rounded-xl bg-surface border border-border flex items-center justify-center hover:border-accent-primary hover:text-accent-primary transition-all duration-200" title="Swap Units">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- To Unit -->
                            <div class="col-span-4">
                                <label for="toUnit" class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">To</label>
                                <select id="toUnit" class="mm-input font-medium py-3">
                                    @foreach($units as $key => $unit)
                                        <option value="{{ $key }}" {{ $key == 'tbsp' ? 'selected' : '' }}>{{ $unit['name'] }} ({{ $unit['symbol'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Buttons Group -->
                        <div class="grid grid-cols-2 gap-3 mt-2">
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
                        <button id="favoriteBtn" onclick="toggleCurrentFavorite();" class="text-text-muted hover:text-yellow-400 transition-all duration-200" title="Add to Favorites">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            Copy Result
                        </button>
                        <button type="button" onclick="copyPhrase();" class="mm-btn mm-btn--secondary text-xs px-3 py-2 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
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
                            <button type="button" onclick="notesEdit()" class="mm-tool-btn" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                                Edit
                            </button>
                            <button type="button" onclick="notesDelete()" class="mm-tool-btn" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                Delete
                            </button>

                            <div class="flex-1" style="min-width:220px">
                                <label class="sr-only" for="notesSearch">Search Notes (Ctrl+F)</label>
                                <input id="notesSearch" type="text" placeholder="Search notes..." oninput="filterNotes()" class="mm-input text-xs py-2" style="padding-top:9px;padding-bottom:9px;">
                            </div>

                            <button type="button" onclick="notesClearEditor()" class="mm-tool-btn" title="Clear">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                Clear Note
                            </button>

                            <button type="button" onclick="notesTogglePin()" class="mm-tool-btn" id="pinNoteBtn" title="Pin Note">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17v5"/><path d="M9 9l-2 2 6 6 2-2-6-6Z"/><path d="M16 3l5 5-6 6-5-5 6-6Z"/></svg>
                                Pin Note
                            </button>

                            <button type="button" onclick="notesToggleFavorite()" class="mm-tool-btn" id="favoriteNoteBtn" title="Favorite Note">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17.3 5.6 21l1.8-7.1L2 9.2l7.2-.6L12 2l2.8 6.6 7.2.6-5.4 4.7L18.4 21 12 17.3Z"/></svg>
                                Favorite Note
                            </button>
                        </div>

                        <div class="mm-notepad-writing">
                            <input type="hidden" id="noteId" value="">
                            <input type="hidden" id="notePinned" value="0">
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
                            <div class="bg-surface/50 border border-border/60 hover:border-border rounded-xl p-4 flex justify-between items-start gap-4 transition-all duration-200" id="note-card-{{ $note->id }}">
                                <div class="flex-1">
                                    <h4 class="font-bold text-white text-sm" id="note-name-val-{{ $note->id }}">{{ $note->ingredient_name }}</h4>
                                    <p class="text-xs text-text-secondary mt-1" id="note-text-val-{{ $note->id }}">{{ $note->notes }}</p>
                                </div>
                                @php $noteHasContent = trim((string)($note->notes ?? '')) !== '' @endphp
                                <div class="flex gap-1 shrink-0">
                                    <button onclick="notesLoadNoteToEditor({{ $note->id }});" class="p-1 text-text-muted hover:text-accent-primary transition-colors" title="Edit" aria-label="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        onclick="printIngredientNote({{ $note->id }});"
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

                                    <button onclick="notesDelete({{ $note->id }});" class="p-1 text-text-muted hover:text-red-400 transition-colors" title="Delete" aria-label="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

        // Swap unit selections
        function swapUnits() {
            const fromSelect = document.getElementById('fromUnit');
            const toSelect = document.getElementById('toUnit');
            if (!fromSelect || !toSelect) return;
            const temp = fromSelect.value;
            fromSelect.value = toSelect.value;
            toSelect.value = temp;
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
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
            if (!lastResult || !lastPhrase) {
                showNotification('Please run a conversion before printing.');
                return;
            }

            const now = new Date();
            const printedOn = now.toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });

            const title = 'MeasureMate - Conversion';

            // Escape for safe HTML injection
            const esc = (str) => String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const html = `<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>${esc(title)}</title>
<style>
    @media print {
        body { background:#fff !important; color:#000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    body{ font-family: Arial, Helvetica, sans-serif; color:#000; background:#fff; margin:0; }
    .mm-print-page{ padding: 20mm; }
    .mm-brand{ font-size:16px; font-weight:800; margin:0 0 4px; }
    .mm-section{ font-size:14px; font-weight:700; margin:0 0 10px; }
    .mm-box{ border:1px solid #000; padding: 12px 14px; margin-top: 10px; }
    .mm-result{ font-size: 22px; font-weight: 800; margin-top: 8px; }
    .mm-phrase{ font-size: 14px; font-weight: 600; margin-top: 6px; white-space: pre-wrap; }
    .mm-meta{ border-top:1px solid #000; padding-top: 10px; margin-top: 18px; font-size: 11.5px; }
</style>
</head>
<body>
    <div class="mm-print-page">
        <div>
            <div class="mm-brand">MeasureMate</div>
            <div class="mm-section">Conversion Output</div>
        </div>

        <div class="mm-box">
            <div class="mm-label" style="font-weight:700; font-size:12px;">Result</div>
            <div class="mm-result">${esc(lastResult)}</div>

            <div class="mm-phrase">${esc(lastPhrase)}</div>
        </div>

        <div class="mm-meta">
            <div>Generated by MeasureMate</div>
            <div>Printed on: ${esc(printedOn)}</div>
        </div>
    </div>
</body>
</html>`;

            const printWin = window.open('', '_blank', 'noopener,noreferrer');
            if (!printWin) {
                alert('Popup blocked. Please allow popups to print.');
                return;
            }

            printWin.document.open();
            printWin.document.write(html);
            printWin.document.close();

            const doPrint = () => {
                try {
                    printWin.focus();
                    printWin.print();
                } catch {}
            };

            // Retry briefly for kiosks / printer dialog reliability
            const start = Date.now();
            const maxWaitMs = 1000;

            const tryPrint = () => {
                try {
                    const rs = printWin.document && printWin.document.readyState;
                    if (rs && rs !== 'complete') {
                        if (Date.now() - start < maxWaitMs) requestAnimationFrame(tryPrint);
                        return;
                    }
                    doPrint();
                } catch {
                    // ignore
                }
            };

            tryPrint();
            setTimeout(() => tryPrint(), 200);
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

        // Load a favorite conversion
        function loadFavorite(amount, fromUnit, toUnit) {
            document.getElementById('amount').value = amount;
            document.getElementById('fromUnit').value = fromUnit;
            document.getElementById('toUnit').value = toUnit;
            convertVolume();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Toggle favorite status for the currently entered parameters
        async function toggleCurrentFavorite() {
            const amount = document.getElementById('amount').value;
            const fromUnit = document.getElementById('fromUnit').value;
            const toUnit = document.getElementById('toUnit').value;

            if (!amount) return;

            const payload = { amount, from_unit: fromUnit, to_unit: toUnit };

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
                    insertFavoriteDOM(data.favorite);
                    showNotification('Added to Favorites!');
                } else {
                    updateFavoriteStar(false);
                    removeFavoriteDOMByParams(amount, fromUnit, toUnit);
                    showNotification('Removed from Favorites.');
                }
            } catch (err) {
                showNotification('Error toggling favorite.');
            }
        }

        // Favorite DOM manipulation
        function insertFavoriteDOM(favorite) {
            const container = document.getElementById('favoritesListContainer');
            const noMsg = document.getElementById('noFavoritesMessage');
            if (noMsg) noMsg.remove();

            const card = document.createElement('div');
            card.className = 'bg-surface/50 border border-border/60 hover:border-border rounded-xl p-4 flex justify-between items-center gap-4 transition-all duration-200';
            card.id = `favorite-card-${favorite.id}`;
            card.innerHTML = `
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
            container.prepend(card);
        }

        async function deleteFavorite(id) {
            try {
                const response = await fetch(`/favorites/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (!response.ok) throw new Error();

                const card = document.getElementById(`favorite-card-${id}`);
                if (card) {
                    card.classList.add('fade-out');
                    setTimeout(() => {
                        card.remove();
                        checkFavoritesEmpty();
                    }, 300);
                }

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
                pinned: (document.getElementById('notePinned')?.value || '0') === '1',
                favorite: (document.getElementById('noteFavorite')?.value || '0') === '1',
                body: document.getElementById('noteBody')?.value || ''
            };
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

            const printWin = window.open('', '_blank', 'noopener,noreferrer');
            if (!printWin) {
                alert('Popup blocked. Please allow popups to print.');
                return;
            }

            printWin.document.open();
            printWin.document.write(html);
            printWin.document.close();

            // Trigger print after the new document is fully parsed.
            const doPrint = () => {
                try {
                    printWin.focus();
                    printWin.print();
                } catch {
                    // ignore
                }
            };

            // Some browsers need a tiny delay even after document.close().
            setTimeout(doPrint, 150);
        }




        function notesLocalKey() {
            const userKey = '{{ auth()->check() ? auth()->id() : "guest" }}';
            return `mm_ingredient_notes_draft_${userKey}`;
        }

        function notesPersistDraft() {
            const st = notesGetEditorState();
            localStorage.setItem(notesLocalKey(), JSON.stringify({
                noteId: st.id,
                title: st.title,
                category: st.category,
                pinned: st.pinned,
                favorite: st.favorite,
                body: st.body,
                updatedAt: Date.now()
            }));
            localStorage.setItem('mm_ingredient_notes_pinned', JSON.stringify(window.__notesPinned || {}));
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
                const pinnedEl = document.getElementById('notePinned');
                const favEl = document.getElementById('noteFavorite');
                const bodyEl = document.getElementById('noteBody');

                if (noteIdEl) noteIdEl.value = noteIdIsStillPresent ? (draft.noteId || '') : '';
                if (titleEl) titleEl.value = draft.title || '';
                if (catEl) catEl.value = draft.category || 'Cooking';
                if (pinnedEl) pinnedEl.value = noteIdIsStillPresent && draft.pinned ? '1' : '0';
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
                notes: trimmedBody
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
                        // keep pinned ordering purely client-side
                    }
                } else {
                    insertNoteDOM(data.note);
                    document.getElementById('noteId').value = data.note.id;
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
            const pinnedEl = document.getElementById('notePinned');
            const favoriteEl = document.getElementById('noteFavorite');
            const bodyEl = document.getElementById('noteBody');
            if (noteIdEl) noteIdEl.value = '';
            if (titleEl) titleEl.value = '';
            if (categoryEl) categoryEl.value = 'Cooking';
            if (pinnedEl) pinnedEl.value = '0';
            if (favoriteEl) favoriteEl.value = '0';
            if (bodyEl) bodyEl.value = '';
            notesUpdateCharCount();
            notesPersistDraft();
            showNotification('New note started.');
        }

        function notesEdit() {
            // Load the first visible note card into the editor
            const first = document.querySelector('#notesListContainer .note-card-visible');
            if (!first) return;
            const id = first.getAttribute('data-note-id');
            if (id) notesLoadNoteToEditor(id);
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
            document.getElementById('notePinned').value = (window.__notesPinned || {})[id] ? '1' : '0';
            document.getElementById('noteFavorite').value = (window.__notesFavorite || {})[id] ? '1' : '0';

            notesUpdateCharCount();
            notesPersistDraft();
        }

        async function notesDelete(noteId = null) {
            const st = notesGetEditorState();
            const targetId = noteId ?? st.id;
            if (!targetId) return;
            if (!confirm('Delete this ingredient note? This action cannot be undone.')) return;

            try {
                const response = await fetch(`/notes/${targetId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    // Missing note can happen if the ID is stale (already deleted elsewhere)
                    if (response.status === 404) {
                        const card = document.getElementById(`note-card-${targetId}`);
                        if (card) {
                            card.classList.add('fade-out');
                            setTimeout(() => {
                                card.remove();
                                checkNotesEmpty();
                            }, 300);
                        }

                        showNotification(data.message || 'This note no longer exists.');
                        // Reset editor state and local hidden inputs
                        notesNew();
                        return;
                    }

                    throw new Error(data.message || 'Error deleting note.');
                }

                const card = document.getElementById(`note-card-${targetId}`);
                if (card) {
                    card.classList.add('fade-out');
                    setTimeout(() => {
                        card.remove();
                        checkNotesEmpty();
                    }, 300);
                }

                showNotification('Note deleted.');
                notesNew();
            } catch (e) {
                showNotification(e.message || 'Error deleting note.');
            }
        }


        function notesClearEditor() {
            notesNew();
        }

        function notesTogglePin() {
            const st = notesGetEditorState();
            if (!st.id) {
                showNotification('Save the note first to pin it.');
                return;
            }
            window.__notesPinned = window.__notesPinned || {};
            const cur = !!window.__notesPinned[st.id];
            window.__notesPinned[st.id] = !cur;
            document.getElementById('notePinned').value = !cur ? '1' : '0';
            notesReorderPinned();
            notesPersistDraft();
        }

        function notesToggleFavorite() {
            const st = notesGetEditorState();
            if (!st.id) {
                showNotification('Save the note first to favorite it.');
                return;
            }
            window.__notesFavorite = window.__notesFavorite || {};
            const cur = !!window.__notesFavorite[st.id];
            window.__notesFavorite[st.id] = !cur;
            document.getElementById('noteFavorite').value = !cur ? '1' : '0';
            notesPersistDraft();
        }

        function notesReorderPinned() {
            const container = document.getElementById('notesListContainer');
            if (!container) return;
            const cards = Array.from(container.querySelectorAll('div[id^="note-card-"]'));
            const pinned = [];
            const rest = [];

            cards.forEach(c => {
                const id = c.getAttribute('id').replace('note-card-', '');
                if (window.__notesPinned && window.__notesPinned[id]) pinned.push(c); else rest.push(c);
            });

            container.innerHTML = '';
            [...pinned, ...rest].forEach(c => container.appendChild(c));
        }

        function insertNoteDOM(note) {
            const container = document.getElementById('notesListContainer');
            const noMsg = document.getElementById('noNotesMessage');
            if (noMsg) noMsg.remove();

            const card = document.createElement('div');
            card.className = 'bg-surface/50 border border-border/60 hover:border-border rounded-xl p-4 flex justify-between items-start gap-4 transition-all duration-200';
            card.id = `note-card-${note.id}`;
            card.setAttribute('data-note-id', String(note.id));
            card.innerHTML = `
                <div class="flex-1">
                    <h4 class="font-bold text-white text-sm" id="note-name-val-${note.id}">${escapeHtml(note.ingredient_name)}</h4>
                    <p class="text-xs text-text-secondary mt-1 whitespace-pre-wrap" id="note-text-val-${note.id}">${escapeHtml(note.notes)}</p>
                </div>
                <div class="flex gap-1 shrink-0">
                    <button onclick="notesLoadNoteToEditor(${note.id})" class="p-1 text-text-muted hover:text-accent-primary transition-colors" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <button onclick="notesDelete(${note.id});" class="p-1 text-text-muted hover:text-red-400 transition-colors" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `;

            container.prepend(card);
            notesReorderPinned();
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
            window.__notesPinned = {};
            window.__notesFavorite = {};

            // restore draft to textarea + title
            notesRestoreDraft();
            notesUpdateCharCount();

            // Ensure pinned ordering based on local maps stored in draft
            const rawPinned = localStorage.getItem('mm_ingredient_notes_pinned');
            const rawFav = localStorage.getItem('mm_ingredient_notes_favorite');
            if (rawPinned) { try { window.__notesPinned = JSON.parse(rawPinned) || {}; } catch {} }
            if (rawFav) { try { window.__notesFavorite = JSON.parse(rawFav) || {}; } catch {} }

            notesReorderPinned();
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
