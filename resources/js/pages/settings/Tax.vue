<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const taxEnabled = ref(true);
const taxRate = ref(15.00);
const saving = ref(false);
const saved = ref(false);
const loading = ref(true);

async function loadSettings() {
    loading.value = true;
    try {
        const resp = await fetch('/api/settings');
        if (!resp.ok) throw new Error('Failed to load settings');
        const data = await resp.json();

        taxEnabled.value = data.settings.tax_enabled?.value ?? true;
        taxRate.value = data.settings.tax_rate?.value ?? 15.00;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function saveSettings() {
    saving.value = true;
    saved.value = false;
    try {
        const resp = await fetch('/api/settings/bulk', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                settings: {
                    tax_enabled: taxEnabled.value ? '1' : '0',
                    tax_rate: taxRate.value.toString()
                }
            })
        });

        if (!resp.ok) throw new Error('Failed to save settings');

        saved.value = true;
        setTimeout(() => saved.value = false, 3000);
    } catch (e) {
        console.error(e);
        alert('Failed to save settings');
    } finally {
        saving.value = false;
    }
}

onMounted(loadSettings);
</script>

<template>
    <Head title="Tax settings" />

    <SettingsLayout>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Tax Settings</h1>
                <p class="text-sm text-gray-600 mt-1">Configure tax rates and settings for your POS system</p>
            </div>

            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Tax configuration" description="Configure tax settings for your POS" />

                <div v-if="loading" class="py-8 text-center text-muted-foreground">
                    Loading...
                </div>

                <div v-else class="space-y-6">
                    <!-- Enable Tax Toggle -->
                    <div class="flex items-center justify-between space-x-2 rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label for="tax-enabled" class="text-base">Enable tax</Label>
                            <div class="text-sm text-muted-foreground">
                                Apply tax to all orders in the POS
                            </div>
                        </div>
                        <Checkbox id="tax-enabled" v-model:checked="taxEnabled" />
                    </div>

                    <!-- Tax Rate Input -->
                    <div class="grid gap-2">
                        <Label for="tax-rate">Tax rate (%)</Label>
                        <Input
                            id="tax-rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            v-model.number="taxRate"
                            :disabled="!taxEnabled"
                            placeholder="15.00"
                        />
                        <p class="text-sm text-muted-foreground">
                            Enter the tax percentage (e.g., 15.00 for 15%)
                        </p>
                    </div>

                    <!-- Preview -->
                    <div v-if="taxEnabled" class="rounded-lg border bg-muted/50 p-4">
                        <h4 class="mb-3 text-sm font-medium">Preview</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Subtotal:</span>
                                <span>JMD 1,000.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Tax ({{ taxRate }}%):</span>
                                <span>JMD {{ ((1000 * taxRate) / 100).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2 font-semibold">
                                <span>Total:</span>
                                <span>JMD {{ (1000 + (1000 * taxRate) / 100).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex items-center gap-4">
                        <Button @click="saveSettings" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="saved" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </div>
            </div>
    </SettingsLayout>
</template>
