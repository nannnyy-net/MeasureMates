// Extracted from welcome.blade.php to restore Alpine initialization.
// This file intentionally contains the Alpine component factory that was previously inline.

/* global CSRF_TOKEN, showNotification */

export function recipeApp() {
    return {
        activeMainTab: 'recipe',

        recipeName: '',
        recipeCategory: 'Baking',
        recipeServings: '',
        recipeNotes: '',
        recipeInput: '',
        ingredients: [],
        targetUnit: 'ml',
        analyzed: false,
        converted: false,
        loading: false,
        saving: false,
        loadingSavedRecipes: false,
        unrecognizedCount: 0,
        originalRecipeText: '',
        convertedRecipeText: '',
        savedRecipes: [],
        isEditModalOpen: false,
        editingRecipe: null,

        recipeId: null,

        csrfToken() {
            // Prefer meta tag; fall back to window.CSRF_TOKEN.
            const meta = document.head?.querySelector('meta[name="csrf-token"]');
            return meta?.content || CSRF_TOKEN || '';
        },

        async requestJSON(url, options = {}) {
            const headers = {
                Accept: 'application/json',
                ...(options.headers || {}),
            };

            // Ensure CSRF token for non-GET requests.
            const method = (options.method || 'GET').toUpperCase();
            if (method !== 'GET') {
                headers['X-CSRF-TOKEN'] = options.headers?.['X-CSRF-TOKEN'] || this.csrfToken();
            }

            try {
                const response = await fetch(url, {
                    ...options,
                    headers,
                });

                const text = await response.text();
                let data = null;
                if (text) {
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        // Non-JSON responses should not be silently ignored.
                        console.error('Non-JSON response received', {
                            url,
                            method,
                            status: response.status,
                            text: text.slice(0, 500),
                        });
                    }
                }

                if (!response.ok) {
                    const message = data?.message || data?.error || `Request failed (${response.status})`;
                    console.error('Request failed', { url, method, status: response.status, data, text });
                    throw new Error(message);
                }

                return data;
            } catch (e) {
                console.error('Fetch error', { url, method, error: e });
                showNotification(e?.message || 'Request failed.');
                throw e;
            }
        },

        init() {
            // Always initialize for the specific Alpine instance.
            // The previous global "init guard" could cause multiple component instances
            // to desync (save updates were applied to one instance, while the sidebar
            // was bound to another).
            if (!window.recipeAppInstanceCounter) window.recipeAppInstanceCounter = 0;
            window.recipeAppInstanceCounter += 1;
            const instanceId = window.recipeAppInstanceCounter;

            this.__instanceId = instanceId;
            console.log('[recipeApp:init] instanceId=', instanceId);

            window.recipeAppInstance = this;
            this.fetchSavedRecipes();
        },


        loadRecipeIntoConverter(recipe) {
            if (!recipe) return;

            // Mark which saved recipe is selected (optional highlight)
            this.recipeId = recipe.id;

            // Populate Whole Recipe Converter inputs
            this.recipeName = recipe.title ?? '';
            this.recipeInput = recipe.original_recipe ?? '';

            // These fields are not persisted in saved_recipes (only target_unit is),
            // but we keep the UI consistent.
            this.recipeCategory = this.recipeCategory || 'Baking';
            this.recipeServings = this.recipeServings || '';
            this.recipeNotes = this.recipeNotes || '';

            this.targetUnit = recipe.target_unit ?? this.targetUnit ?? 'ml';

            // Show converted output panel
            this.originalRecipeText = recipe.original_recipe ?? '';
            this.convertedRecipeText = recipe.converted_recipe ?? '';
            this.analyzed = true;
            this.converted = true;

            // Reset error/unrecognized indicators
            this.loading = false;
            this.unrecognizedCount = 0;

            // If user was on Single tab, switch to Whole Recipe tab for the loaded content
            this.activeMainTab = 'recipe';
        },


        fillExampleRecipe() {

            this.recipeName = 'Chocolate Cake';
            this.recipeCategory = 'Baking';
            this.recipeServings = '8';
            this.recipeNotes = 'Preheat oven to 350°F. Bake for 30-35 minutes.';
            this.recipeInput =
                'Chocolate Cake\n\n2 cups flour\n1½ cups sugar\n½ cup butter\n2 tbsp cocoa powder\n1 tsp vanilla\n250 mL milk';
        },

        async analyzeRecipe() {
            if (!this.recipeInput.trim()) {
                showNotification('Please enter a recipe first.');
                return;
            }

            this.loading = true;
            this.unrecognizedCount = 0;

            try {
                const data = await this.requestJSON('/recipe/analyze', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ recipe_text: this.recipeInput }),
                });

                if (data?.success) {

                    this.ingredients = Array.isArray(data.ingredients) ? data.ingredients : [];
                    this.unrecognizedCount = Array.isArray(data.ingredients)
                        ? data.ingredients.filter((i) => !i.original_unit).length
                        : 0;

                    this.analyzed = true;
                    this.converted = false;
                    this.originalRecipeText = '';
                    this.convertedRecipeText = '';
                    showNotification('Recipe analyzed successfully!');
                } else {
                    showNotification(data?.message || 'Analysis failed.');
                }
            } catch {
                showNotification('Error analyzing recipe.');
            } finally {
                this.loading = false;
            }
        },

        async convertRecipe() {
            if (!this.recipeInput.trim()) {
                showNotification('Please enter a recipe first.');
                return;
            }

            this.loading = true;
            try {
                const data = await this.requestJSON('/recipe/convert', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        recipe_text: this.recipeInput,
                        target_unit: this.targetUnit,
                        recipe_name: this.recipeName,
                        recipe_category: this.recipeCategory,
                        recipe_servings: this.recipeServings,
                        recipe_notes: this.recipeNotes,
                    }),
                });

                if (data?.success) {

                    this.recipeId = data.recipe_id;
                    this.ingredients = Array.isArray(data.ingredients) ? data.ingredients : [];
                    this.originalRecipeText = data.original_text || '';
                    this.convertedRecipeText = data.converted_text || '';
                    this.converted = true;
                    this.analyzed = true;
                    this.unrecognizedCount = data.unrecognized_count ?? 0;
                    showNotification('Recipe converted successfully!');
                } else {
                    showNotification(data?.message || 'Conversion failed.');
                }
            } catch {
                showNotification('Error converting recipe.');
            } finally {
                this.loading = false;
            }
        },

        resetRecipe() {
            this.recipeId = null;
            this.recipeName = '';
            this.recipeCategory = 'Baking';
            this.recipeServings = '';
            this.recipeNotes = '';
            this.recipeInput = '';
            this.ingredients = [];
            this.analyzed = false;
            this.converted = false;
            this.originalRecipeText = '';
            this.convertedRecipeText = '';
            showNotification('Recipe converter reset.');
        },

        getRecipePayload() {
            const originalRecipe = (this.originalRecipeText || '').trim() || (this.recipeInput || '').trim();
            const convertedRecipe = (this.convertedRecipeText || '').trim() || originalRecipe;
            const title = (this.recipeName || '').trim() || this.extractTitleFromRecipe(this.recipeInput) || 'Untitled Recipe';

            return {
                title,
                original_recipe: originalRecipe,
                converted_recipe: convertedRecipe,
                target_unit: this.targetUnit || 'ml',
            };
        },

        extractTitleFromRecipe(value) {
            return (value || '')
                .split(/\r?\n/)
                .map((line) => line.trim())
                .find(Boolean) || '';
        },

        async fetchSavedRecipes() {
            console.log('[recipeApp:fetchSavedRecipes] start', {
                instanceId: this.__instanceId,
                beforeLen: Array.isArray(this.savedRecipes) ? this.savedRecipes.length : 'non-array',
            });
            this.loadingSavedRecipes = true;
            try {
                // Prevent caching by appending a cache buster and setting cache: 'no-store'
                const data = await this.requestJSON(`/saved-recipes?_t=${Date.now()}`, {
                    method: 'GET',
                    cache: 'no-store',
                    headers: {
                        Accept: 'application/json',
                    },
                });

                console.log('[recipeApp:fetchSavedRecipes] response received', {
                    instanceId: this.__instanceId,
                    hasData: !!data,
                    dataKeys: data ? Object.keys(data) : [],
                    parsedDataIsArray: Array.isArray(data?.data),
                    receivedLen: Array.isArray(data?.data) ? data.data.length : null,
                });

                // Ensure Alpine reactivity by replacing with a brand new array.
                const next = Array.isArray(data?.data) ? data.data : [];
                this.savedRecipes = [...next];

                console.log('[recipeApp:fetchSavedRecipes] updated savedRecipes', {
                    instanceId: this.__instanceId,
                    afterLen: this.savedRecipes.length,
                });
            } catch (error) {
                console.error('[recipeApp:fetchSavedRecipes] failed', {
                    instanceId: this.__instanceId,
                    error: error?.message,
                });
                showNotification(error.message || 'Unable to load saved recipes.');
            } finally {
                this.loadingSavedRecipes = false;
                console.log('[recipeApp:fetchSavedRecipes] end', {
                    instanceId: this.__instanceId,
                    finalLen: Array.isArray(this.savedRecipes) ? this.savedRecipes.length : 'non-array',
                });
            }
        },


        async saveCurrentRecipe() {
            console.log('[recipeApp:saveCurrentRecipe] clicked', {
                instanceId: this.__instanceId,
                beforeSavedLen: Array.isArray(this.savedRecipes) ? this.savedRecipes.length : 'non-array',
            });
            if (!this.recipeInput.trim()) {
                showNotification('Please enter a recipe first.');
                return;
            }

            const payload = this.getRecipePayload();
            const hasRecipeContent = payload.original_recipe && payload.converted_recipe;

            if (!hasRecipeContent) {
                showNotification('Convert a recipe before saving it.');
                return;
            }

            this.saving = true;
            try {
                console.log('[recipeApp:saveCurrentRecipe] ajax request sent', {
                    instanceId: this.__instanceId,
                    url: '/saved-recipes',
                    payloadTitle: payload.title,
                });

                const data = await this.requestJSON('/saved-recipes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                console.log('[recipeApp:saveCurrentRecipe] response received', {
                    instanceId: this.__instanceId,
                    success: data?.success,
                    hasData: !!data?.data,
                    responseDataId: data?.data?.id,
                });

                if (data?.success) {
                    console.log('[recipeApp:saveCurrentRecipe] API success payload', {
                        instanceId: this.__instanceId,
                        returnedId: data?.data?.id,
                        returnedTitle: data?.data?.title,
                        returnedLenBeforeFetch: Array.isArray(this.savedRecipes) ? this.savedRecipes.length : null,
                    });
                    showNotification(data.message || 'Recipe saved successfully.');

                    // Optimistic UI update to ensure sidebar updates immediately.
                    if (data?.data) {
                        const newId = String(data.data.id);
                        const exists = this.savedRecipes.some((r) => String(r.id) === newId);
                        console.log('[recipeApp:saveCurrentRecipe] optimistic check', {
                            instanceId: this.__instanceId,
                            newId,
                            exists,
                            beforeLen: this.savedRecipes.length,
                        });
                        if (!exists) {
                            this.savedRecipes = [data.data, ...this.savedRecipes];
                        }
                        console.log('[recipeApp:saveCurrentRecipe] optimistic updated', {
                            instanceId: this.__instanceId,
                            afterLen: this.savedRecipes.length,
                        });
                    }

                    // Re-sync with backend to guarantee consistency.
                    console.log('[recipeApp:saveCurrentRecipe] awaiting fetchSavedRecipes', {
                        instanceId: this.__instanceId,
                    });
                    await this.fetchSavedRecipes();
                    console.log('[recipeApp:saveCurrentRecipe] fetchSavedRecipes completed', {
                        instanceId: this.__instanceId,
                        finalLen: this.savedRecipes.length,
                    });
                } else {
                    showNotification(data?.message || 'Unable to save recipe.');
                }
            } catch (error) {
                console.error('[recipeApp:saveCurrentRecipe] failed', {
                    instanceId: this.__instanceId,
                    error: error?.message,
                });
                showNotification(error.message || 'Unable to save recipe.');
            } finally {
                this.saving = false;
                console.log('[recipeApp:saveCurrentRecipe] done', {
                    instanceId: this.__instanceId,
                    saving: this.saving,
                    savedLen: Array.isArray(this.savedRecipes) ? this.savedRecipes.length : 'non-array',
                });
            }
        },

        openEditModal(recipe) {
            this.editingRecipe = { ...recipe };
            this.isEditModalOpen = true;
        },

        closeEditModal() {
            this.isEditModalOpen = false;
            this.editingRecipe = null;
        },

        async saveEditedRecipe() {
            if (!this.editingRecipe) return;

            this.saving = true;
            try {
                const data = await this.requestJSON(`/saved-recipes/${this.editingRecipe.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: this.editingRecipe.title,
                        original_recipe: this.editingRecipe.original_recipe,
                        converted_recipe: this.editingRecipe.converted_recipe,
                        target_unit: this.editingRecipe.target_unit || this.targetUnit,
                    }),
                });

                if (data?.success) {

                    showNotification(data.message || 'Recipe updated successfully.');
                    await this.fetchSavedRecipes();
                    this.closeEditModal();
                } else {
                    showNotification(data?.message || 'Unable to update recipe.');
                }
            } catch (error) {
                showNotification(error.message || 'Unable to update recipe.');
            } finally {
                this.saving = false;
            }
        },

        async confirmDelete(recipe) {
            console.log('[recipeApp:confirmDelete] clicked', {
                instanceId: this.__instanceId,
                beforeSavedLen: Array.isArray(this.savedRecipes) ? this.savedRecipes.length : 'non-array',
                recipeId: recipe?.id,
            });
            if (!window.confirm('Delete this saved recipe?')) {
                return;
            }

            this.saving = true;
            const recipeId = recipe?.id;

            try {
                if (!recipeId) {
                    showNotification('Unable to delete: missing recipe id.');
                    return;
                }

                console.log('[recipeApp:confirmDelete] ajax request sent', {
                    instanceId: this.__instanceId,
                    url: `/saved-recipes/${recipeId}`,
                });

                const data = await this.requestJSON(`/saved-recipes/${recipeId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                });

                console.log('[recipeApp:confirmDelete] response received', {
                    instanceId: this.__instanceId,
                    success: data?.success,
                    message: data?.message,
                    deletedId: recipeId,
                });

                if (data?.success) {
                    const beforeIds = this.savedRecipes.map((i) => String(i.id));
                    this.savedRecipes = this.savedRecipes.filter((item) => String(item.id) !== String(recipeId));
                    console.log('[recipeApp:confirmDelete] optimistic updated', {
                        instanceId: this.__instanceId,
                        beforeLen: beforeIds.length,
                        afterLen: this.savedRecipes.length,
                        removed: beforeIds.includes(String(recipeId)),
                    });

                    showNotification(data.message || 'Recipe deleted successfully.');

                    console.log('[recipeApp:confirmDelete] awaiting fetchSavedRecipes', {
                        instanceId: this.__instanceId,
                    });
                    await this.fetchSavedRecipes();
                    console.log('[recipeApp:confirmDelete] fetchSavedRecipes completed', {
                        instanceId: this.__instanceId,
                        finalLen: this.savedRecipes.length,
                    });
                } else {
                    showNotification(data?.message || 'Unable to delete recipe.');
                    await this.fetchSavedRecipes();
                }
            } catch (error) {
                console.error('[recipeApp:confirmDelete] failed', {
                    instanceId: this.__instanceId,
                    error: error?.message,
                });
                showNotification(error.message || 'Unable to delete recipe.');
                await this.fetchSavedRecipes();
            } finally {
                this.saving = false;
                console.log('[recipeApp:confirmDelete] done', {
                    instanceId: this.__instanceId,
                    saving: this.saving,
                    savedLen: Array.isArray(this.savedRecipes) ? this.savedRecipes.length : 'non-array',
                });
            }
        },

        printSavedRecipe(recipe) {
            if (!recipe) return;
            window.open(`/saved-recipes/${recipe.id}/print`, '_blank');
        },

        formatSavedDate(value) {
            if (!value) return 'Saved recently';
            return `Saved: ${new Date(value).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            })}`;
        },
    };
}

