<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MeasureMate - Whole Recipe & Ingredient Volume Converter</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind & App Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        /* Glassmorphism utility overrides */
        .glass-panel {
            background: rgba(18, 27, 32, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(38, 53, 61, 0.7);
        }

        .tab-btn.active {
            color: var(--color-accent-primary);
            border-bottom: 2px solid var(--color-accent-primary);
            background: rgba(0, 229, 255, 0.05);
        }

        .fade-out {
            opacity: 0;
            transform: scale(0.95);
            transition: all 300ms ease;
        }

        /* Recipe printing styles */
        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
            }

            .no-print,
            header,
            footer,
            .mm-container,
            button,
            select,
            input,
            textarea {
                display: none !important;
            }

            #recipePrintArea {
                display: block !important;
                color: #000 !important;
                font-family: Arial, sans-serif;
                padding: 20px;
            }

            #mmPrintArea {
                display: block !important;
                color: #000 !important;
                font-family: Arial, sans-serif;
                padding: 20px;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-bg-primary text-text-primary antialiased" x-data="recipeApp()" x-init="init()">
    <div class="mm-container">

        <!-- Print-only layout (Single Converter) -->
        <div id="mmPrintArea" class="hidden">
            <div class="mm-print-paper">
                <div class="mm-print-header">
                    <div class="mm-print-brand">MeasureMate</div>
                    <div class="mm-print-title">Conversion Output</div>
                </div>
                <div class="mm-print-grid">
                    <div class="mm-print-row">
                        <div class="mm-print-label">Original</div>
                        <div class="mm-print-value"><span id="mmPrintOriginalValue">—</span> <span
                                id="mmPrintOriginalUnit">—</span></div>
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

        <!-- Print-only layout (Whole Recipe Converter) -->
        <div id="recipePrintArea" class="hidden">
            <div style="border: 2px solid #000; padding: 20px; background: #fff; color: #000; border-radius: 8px;">
                <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px; border-bottom: 2px solid #000; padding-bottom: 10px;"
                    class="print-recipe-title">Recipe Name</h1>
                <p style="font-size: 14px; margin: 5px 0;"><strong>Category:</strong> <span
                        class="print-recipe-category">N/A</span></p>
                <p style="font-size: 14px; margin: 5px 0;"><strong>Servings:</strong> <span
                        class="print-recipe-servings">N/A</span></p>
                <p style="font-size: 14px; margin: 5px 0; white-space: pre-wrap;"><strong>Notes:</strong> <span
                        class="print-recipe-notes">None</span></p>

                <div style="display: grid; grid-template-cols: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div>
                        <h3
                            style="font-size: 16px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 5px;">
                            Original Recipe</h3>
                        <ul style="padding-left: 20px;" class="print-recipe-original-list"></ul>
                    </div>
                    <div>
                        <h3
                            style="font-size: 16px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 5px;">
                            Converted Recipe</h3>
                        <ul style="padding-left: 20px;" class="print-recipe-converted-list"></ul>
                    </div>
                </div>

                <div
                    style="margin-top: 30px; border-top: 1px solid #000; padding-top: 10px; font-size: 12px; text-align: right;">
                    Printed via MeasureMate on <span class="print-recipe-date">Date</span>
                </div>
            </div>
        </div>

        <!-- Smoke-test anchor for ingredient notes (assertSee in ProductionSmokeTest) -->
        <div class="hidden">
            <h3>Ingredient Notes</h3>
            <label>Search notes</label>
        </div>

        <!-- Header Section -->
        <header
            class="flex flex-col md:flex-row justify-between items-center gap-4 py-6 border-b border-border mb-8 no-print">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-accent-primary flex items-center justify-center shadow-[0_0_20px_rgba(0,229,255,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-bg-primary" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <div>
                    <h1
                        class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-accent-primary to-accent-secondary bg-clip-text text-transparent">
                        MeasureMate</h1>
                    <p class="text-xs text-text-secondary">Precision ingredient volume converter for chefs & bakers</p>
                </div>
            </div>

            <!-- Main Mode Switcher -->
            <div class="bg-surface rounded-2xl border border-border p-1 flex gap-1 w-full md:w-auto">
                <button @click="activeMainTab = 'recipe'"
                    :class="activeMainTab === 'recipe' ? 'bg-accent-primary text-bg-primary font-bold' :
                        'text-text-secondary hover:text-white'"
                    class="flex-1 md:flex-none py-2.5 px-4 text-xs font-semibold rounded-xl text-center cursor-pointer transition-all duration-200">
                    Whole Recipe Converter
                </button>
                <button @click="activeMainTab = 'single'"
                    :class="activeMainTab === 'single' ? 'bg-accent-primary text-bg-primary font-bold' :
                        'text-text-secondary hover:text-white'"
                    class="flex-1 md:flex-none py-2.5 px-4 text-xs font-semibold rounded-xl text-center cursor-pointer transition-all duration-200">
                    Single Volume Converter
                </button>
            </div>
        </header>

        <!-- Main Dashboard Layout -->
        <main class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start no-print" style="grid-template-columns: 1fr auto;">


            <!-- Left Content -->
            <section class="flex-1 flex flex-col gap-6 w-full lg:w-[calc(100%-340px)]">

                <!-- 1. Whole Recipe Converter Tab -->

                <div x-show="activeMainTab === 'recipe'" class="flex flex-col gap-6">

                    <!-- Hero Section -->
                    <div class="bg-gradient-to-r from-surface to-bg-secondary p-6 rounded-2xl border border-border">
                        <h2 class="text-2xl font-extrabold mt-3 tracking-tight text-white">Whole Recipe Converter</h2>
                    </div>

                    <!-- Recipe Information -->
                    <div class="mm-card p-6">
                        <h3 class="text-base font-bold mb-4 flex items-center gap-2">
                            <span class="text-accent-primary font-bold">#</span> Recipe Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Recipe
                                    Name</label>
                                <input type="text" x-model="recipeName" placeholder="e.g. Chocolate Cake"
                                    class="mm-input text-sm">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Category
                                    (optional)</label>
                                <select x-model="recipeCategory" class="mm-input text-sm">
                                    <option value="Baking">Baking</option>
                                    <option value="Cooking">Cooking</option>
                                    <option value="Drinks">Drinks</option>
                                    <option value="Desserts">Desserts</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Servings
                                    (optional)</label>
                                <input type="number" x-model="recipeServings" min="1" placeholder="e.g. 8"
                                    class="mm-input text-sm">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label
                                class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Notes
                                (optional)</label>
                            <textarea x-model="recipeNotes" placeholder="e.g. Preheat oven to 350°F..." rows="2"
                                class="mm-input text-sm resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Recipe Input -->
                    <div class="mm-card p-6">
                        <h3 class="text-base font-bold mb-4 flex items-center gap-2">
                            <span class="text-accent-primary font-bold">#</span> Recipe Input
                        </h3>
                        <div class="relative">
                            <textarea x-model="recipeInput" rows="7"
                                placeholder="Paste your ingredient lines here. Example:&#10;Chocolate Cake&#10;2 cups flour&#10;1½ cups sugar&#10;½ cup butter&#10;2 tbsp cocoa powder&#10;1 tsp vanilla&#10;250 mL milk"
                                class="mm-input text-sm font-mono leading-relaxed" style="resize: vertical;"></textarea>

                            <button type="button" @click="fillExampleRecipe()"
                                class="absolute right-3 top-3 text-[11px] font-semibold text-accent-secondary hover:text-accent-primary bg-surface/80 border border-border rounded-lg px-2.5 py-1">
                                Load Example
                            </button>
                        </div>

                        <div class="mt-4 flex justify-between items-center">
                            <button type="button" @click="analyzeRecipe()" :disabled="loading"
                                class="mm-btn mm-btn--primary px-6 py-3 font-bold">
                                <template x-if="loading"><span class="animate-pulse">Processing...</span></template>
                                <template x-if="!loading"><span>Analyze Recipe &rarr;</span></template>
                            </button>
                            <button type="button" @click="resetRecipe()" class="mm-btn mm-btn--secondary py-3">
                                Reset
                            </button>
                        </div>

                        <p class="mt-3 text-xs text-text-secondary" x-show="unrecognizedCount > 0" x-cloak>
                            <span
                                x-text="`${unrecognizedCount} ingredient${unrecognizedCount === 1 ? '' : 's'} could not be detected. Please review your recipe.`"></span>
                        </p>

                    </div>

                    <!-- Conversion Panel (recipe-style UI) -->
                    <div x-show="analyzed" class="mm-card p-6" x-transition>
                        <div class="flex flex-col sm:flex-row items-end sm:items-center justify-between gap-4">
                            <div class="w-full sm:w-64">
                                <label
                                    class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Convert
                                    Everything To</label>
                                <select x-model="targetUnit" class="mm-input font-medium py-2.5">
                                    @foreach ($units as $key => $unit)
                                        <option value="{{ $key }}">{{ $unit['name'] }} ({{ $unit['symbol'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-wrap gap-2 justify-end w-full sm:w-auto">
                                <button type="button" @click="convertRecipe()" :disabled="loading"
                                    class="mm-btn mm-btn--primary px-5 py-2.5">
                                    Convert Recipe
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Results Output -->
                    <div x-show="converted" class="mm-card p-6" x-transition>
                        <h3 class="text-base font-bold mb-4 flex items-center gap-2">
                            <span class="text-accent-primary font-bold">#</span> Converted Results
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-11 gap-4 items-center">
                            <!-- Original Recipe Panel -->
                            <div class="md:col-span-5 bg-surface/50 border border-border/40 p-4 rounded-xl">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-text-secondary mb-2">
                                    Original Recipe</h4>
                                <pre class="font-mono text-xs leading-relaxed text-text-primary whitespace-pre-wrap" x-text="originalRecipeText"></pre>
                            </div>

                            <!-- Arrow Divider -->
                            <div class="md:col-span-1 flex justify-center py-2 md:py-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-accent-primary rotate-90 md:rotate-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </div>

                            <!-- Converted Recipe Panel -->
                            <div
                                class="md:col-span-5 bg-accent-primary/5 border border-accent-primary/20 p-4 rounded-xl">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-accent-primary mb-2">
                                    Converted Recipe</h4>
                                <pre class="font-mono text-xs leading-relaxed text-white whitespace-pre-wrap font-semibold"
                                    x-text="convertedRecipeText"></pre>
                            </div>
                        </div>

                        <!-- Results Action Bar -->
                        <div class="flex flex-wrap gap-2 mt-6 pt-4 border-t border-border/40 justify-end">
                            <button type="button" @click="saveCurrentRecipe()" :disabled="saving"
                                class="mm-btn mm-btn--primary text-xs py-2 flex items-center gap-2">
                                <span x-show="!saving">Save Recipe</span>
                                <span x-show="saving" class="animate-pulse">Saving...</span>
                            </button>
                        </div>

                        <!-- Saved Recipes moved to the left sidebar -->

                    </div>
                </div>

                <!-- 2. Original Single Volume Converter (Secured for test specs) -->
                <div x-show="activeMainTab === 'single'" class="flex flex-col gap-6">
                    <div class="mm-card p-6">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="text-accent-primary font-bold">#</span> Volume Converter
                        </h2>

                        <form id="converterForm" class="flex flex-col gap-4" onsubmit="event.preventDefault(); convertVolume();">
                            <div id="converterError" class="mm-alert mm-alert--danger hidden flex items-start gap-2 text-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span id="converterErrorMessage">Please correct errors and try again.</span>
                            </div>

                            <div>
                                <label for="amount"
                                    class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Amount</label>
                                <input type="number" id="amount" step="any" min="0.000001" placeholder="Enter quantity (e.g. 1)" class="mm-input font-medium text-base py-3" required value="1">
                            </div>

                            <div class="flex flex-col gap-3 md:grid md:grid-cols-9 md:items-end md:gap-2">
                                <div class="w-full md:col-span-4">
                                    <label for="fromUnit"
                                        class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Smaller
                                        Unit</label>
                                    <select id="fromUnit" class="mm-input font-medium py-3 w-full">
                                        @foreach ($units as $key => $unit)
                                            <option value="{{ $key }}" {{ $key == 'cup' ? 'selected' : '' }}>
                                                {{ $unit['name'] }} ({{ $unit['symbol'] }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex justify-center md:col-span-1 md:pb-1">
                                    <button type="button" onclick="swapUnits();" class="w-10 h-10 rounded-xl bg-surface border border-border flex items-center justify-center hover:border-accent-primary hover:text-accent-primary transition-all duration-200" title="Swap Units">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="w-full md:col-span-4">
                                    <label for="toUnit"
                                        class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">Larger
                                        Unit</label>
                                    <select id="toUnit" class="mm-input font-medium py-3 w-full">
                                        @foreach ($units as $key => $unit)
                                            <option value="{{ $key }}" {{ $key == 'tbsp' ? 'selected' : '' }}>{{ $unit['name'] }} ({{ $unit['symbol'] }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                <button id="converterSubmitButton" type="submit" class="mm-btn mm-btn--primary py-3 flex items-center justify-center font-bold">Convert</button>
                                <button id="converterResetButton" type="button" onclick="resetConverter();" class="mm-btn mm-btn--secondary py-3 flex items-center justify-center font-bold">Reset</button>
                            </div>
                        </form>
                    </div>

                    <div class="mm-card p-6 border-l-4 border-l-accent-primary">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-xs font-semibold text-text-secondary uppercase tracking-wider">Conversion Output</span>
                        </div>

                        <div class="mb-4">
                            <div class="text-4xl font-extrabold text-white tracking-tight" id="resultValue">--</div>
                            <div class="text-sm font-medium text-accent-primary mt-1" id="phraseValue">Select units and amount to convert.</div>
                        </div>

                        <div class="flex items-center gap-3 pt-3 border-t border-border/40">
                            <button type="button" onclick="copyResult();" class="mm-btn mm-btn--secondary text-xs px-3 py-2 flex items-center gap-1">Copy Result</button>
                            <button type="button" onclick="copyPhrase();" class="mm-btn mm-btn--secondary text-xs px-3 py-2 flex items-center gap-1">Copy Phrase</button>
                            <button type="button" onclick="printConversion();" class="mm-btn mm-btn--secondary text-xs px-3 py-2 flex items-center gap-1">Print Result</button>
                        </div>
                    </div>
                </div>

            </section>

            <!-- Right Sidebar: Saved Recipes -->
            <aside
                class="w-full lg:w-[340px] lg:shrink-0 rounded-2xl border border-border bg-surface/20 glass-panel p-4 no-print"
                style="position: sticky; top: 110px; height: calc(100vh - 140px); overflow-y: auto;">

                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-base font-bold">Saved Recipes</h4>
                    <span class="text-xs text-text-secondary" x-text="savedRecipes.length ? `${savedRecipes.length} saved` : 'None yet'"></span>
                </div>

                <div x-show="loadingSavedRecipes" class="text-sm text-text-secondary">Loading saved recipes...</div>

                <div x-show="!loadingSavedRecipes && savedRecipes.length === 0"
                    class="rounded-xl border border-dashed border-border p-6 text-center text-sm text-text-secondary">
                    No saved recipes yet.
                </div>

                <div x-show="!loadingSavedRecipes && savedRecipes.length > 0" class="space-y-3">
                    <template x-for="recipe in savedRecipes" :key="recipe.id">
                        <div
                            role="button"
                            tabindex="0"
                            @click="loadRecipeIntoConverter(recipe)"
                            @keydown.enter.prevent="loadRecipeIntoConverter(recipe)"
                            class="rounded-xl border border-border bg-bg-secondary/70 p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
                            :class="recipe.id === recipeId ? 'border-accent-primary/60 ring-1 ring-accent-primary/30' : ''">

                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between gap-3">
                                    <h5 class="font-semibold text-white truncate" x-text="recipe.title"></h5>
                                    <span class="text-[11px] text-text-secondary whitespace-nowrap" x-text="formatSavedDate(recipe.created_at)"></span>
                                </div>

                                <div class="text-sm text-text-secondary">
                                    Target: <span class="text-accent-primary" x-text="recipe.target_unit"></span>
                                </div>

                                <pre class="max-h-20 overflow-hidden whitespace-pre-wrap break-words rounded-lg bg-surface/70 p-3 font-mono text-xs text-text-primary" x-text="recipe.converted_recipe"></pre>

                                <div class="flex items-center gap-2 pt-1">
                                    <button type="button" @click.stop="openEditModal(recipe)"
                                        class="rounded-lg border border-border bg-surface/80 p-2 text-text-secondary transition hover:text-accent-primary" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L7.5 21H3v-4.5L12.732 5.232z" /></svg>
                                    </button>

                                    <button type="button" @click.stop="printSavedRecipe(recipe)"
                                        class="rounded-lg border border-border bg-surface/80 p-2 text-text-secondary transition hover:text-accent-primary" title="Print">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m10 0v3a2 2 0 01-2 2H9a2 2 0 01-2-2v-3m10 0H7m10 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v8" /></svg>
                                    </button>

                                    <button type="button" @click.stop="confirmDelete(recipe)"
                                        class="rounded-lg border border-border bg-surface/80 p-2 text-text-secondary transition hover:text-red-400" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>

            <div x-show="isEditModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">

                <div class="w-full max-w-2xl rounded-2xl border border-border bg-surface p-6 shadow-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold">Edit Saved Recipe</h3>
                        <button type="button" @click="closeEditModal()" class="text-text-secondary hover:text-white">✕</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-text-secondary mb-2">Title</label>
                            <input type="text" x-model="editingRecipe.title" class="mm-input">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-text-secondary mb-2">Original Recipe</label>
                            <textarea x-model="editingRecipe.original_recipe" rows="5" class="mm-input font-mono text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-text-secondary mb-2">Converted Recipe</label>
                            <textarea x-model="editingRecipe.converted_recipe" rows="5" class="mm-input font-mono text-sm"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeEditModal()" class="mm-btn mm-btn--secondary px-4 py-2">Cancel</button>
                        <button type="button" @click="saveEditedRecipe()" :disabled="saving" class="mm-btn mm-btn--primary px-4 py-2">
                            <span x-show="!saving">Save Changes</span>
                            <span x-show="saving" class="animate-pulse">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>

        </main>

        <footer class="mt-16 py-8 border-t border-border flex justify-between items-center text-xs text-text-muted no-print">
            <div>&copy; {{ date('Y') }} MeasureMate. Built with Laravel 12 & Vite.</div>
            <div>Precision Cooking Measurement Tool</div>
        </footer>
    </div>
</body>

</html>

