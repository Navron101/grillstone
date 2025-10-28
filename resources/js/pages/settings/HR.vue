<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const signatureImage = ref<string | null>(null);
const signatureFile = ref<File | null>(null);
const uploading = ref(false);
const saved = ref(false);
const loading = ref(true);
const signaturePreview = ref<string | null>(null);

const signatureUrl = computed(() => {
    if (signaturePreview.value) {
        return signaturePreview.value;
    }
    if (signatureImage.value) {
        return `/storage/${signatureImage.value}`;
    }
    return null;
});

async function loadSettings() {
    loading.value = true;
    try {
        const resp = await fetch('/api/settings');
        if (!resp.ok) throw new Error('Failed to load settings');
        const data = await resp.json();

        signatureImage.value = data.settings.hr_signature_image?.value ?? null;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        signatureFile.value = target.files[0];

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            signaturePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(signatureFile.value);
    }
}

async function uploadSignature() {
    if (!signatureFile.value) {
        alert('Please select a signature image first');
        return;
    }

    uploading.value = true;
    saved.value = false;

    try {
        const formData = new FormData();
        formData.append('signature', signatureFile.value);

        const resp = await fetch('/api/settings/signature/upload', {
            method: 'POST',
            body: formData
        });

        if (!resp.ok) throw new Error('Failed to upload signature');

        const data = await resp.json();
        signatureImage.value = data.path;
        signatureFile.value = null;
        signaturePreview.value = null;

        saved.value = true;
        setTimeout(() => saved.value = false, 3000);

        // Reload settings
        await loadSettings();
    } catch (e) {
        console.error(e);
        alert('Failed to upload signature');
    } finally {
        uploading.value = false;
    }
}

async function deleteSignature() {
    if (!confirm('Are you sure you want to delete the signature image?')) return;

    try {
        const resp = await fetch('/api/settings/signature', {
            method: 'DELETE'
        });

        if (!resp.ok) throw new Error('Failed to delete signature');

        signatureImage.value = null;
        signaturePreview.value = null;
        signatureFile.value = null;

        alert('Signature deleted successfully');
    } catch (e) {
        console.error(e);
        alert('Failed to delete signature');
    }
}

function cancelUpload() {
    signatureFile.value = null;
    signaturePreview.value = null;
}

onMounted(loadSettings);
</script>

<template>
    <Head title="HR settings" />

    <SettingsLayout>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">HR Settings</h1>
                <p class="text-sm text-gray-600 mt-1">Configure HR document settings and signatures</p>
            </div>

            <div class="flex flex-col space-y-6">
                <HeadingSmall title="HR configuration" description="Configure HR document settings" />

                <div v-if="loading" class="py-8 text-center text-muted-foreground">
                    Loading...
                </div>

                <div v-else class="space-y-6">
                    <!-- Signature Image Upload -->
                    <div class="space-y-4 rounded-lg border p-6">
                        <div class="space-y-2">
                            <Label class="text-base font-semibold">Signature Image</Label>
                            <p class="text-sm text-muted-foreground">
                                Upload a signature image to be included on employment contracts and verification letters.
                                Recommended size: 300x100 pixels (transparent PNG preferred)
                            </p>
                        </div>

                        <!-- Current Signature Display -->
                        <div v-if="signatureUrl && !signatureFile" class="space-y-3">
                            <div class="rounded-lg border bg-muted/30 p-4">
                                <p class="mb-2 text-sm font-medium">Current signature:</p>
                                <img
                                    :src="signatureUrl"
                                    alt="Current signature"
                                    class="max-h-24 border bg-white p-2"
                                />
                            </div>
                            <div class="flex gap-2">
                                <Label
                                    for="signature-upload"
                                    class="cursor-pointer rounded-lg border-2 border-dashed px-4 py-2 text-sm hover:border-primary hover:bg-muted/50"
                                >
                                    Replace signature
                                </Label>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    @click="deleteSignature"
                                >
                                    Delete
                                </Button>
                            </div>
                        </div>

                        <!-- Upload New Signature -->
                        <div v-else class="space-y-3">
                            <!-- Preview -->
                            <div v-if="signaturePreview" class="space-y-3">
                                <div class="rounded-lg border bg-muted/30 p-4">
                                    <p class="mb-2 text-sm font-medium">Preview:</p>
                                    <img
                                        :src="signaturePreview"
                                        alt="Signature preview"
                                        class="max-h-24 border bg-white p-2"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <Button @click="uploadSignature" :disabled="uploading">
                                        <i class="fas fa-upload mr-2"></i>
                                        {{ uploading ? 'Uploading...' : 'Upload Signature' }}
                                    </Button>
                                    <Button variant="outline" @click="cancelUpload">
                                        Cancel
                                    </Button>
                                </div>
                            </div>

                            <!-- File Input -->
                            <div v-else>
                                <Label
                                    for="signature-upload"
                                    class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-8 hover:border-primary hover:bg-muted/50"
                                >
                                    <i class="fas fa-cloud-upload-alt mb-3 text-4xl text-muted-foreground"></i>
                                    <span class="text-sm font-medium">Click to upload signature image</span>
                                    <span class="mt-1 text-xs text-muted-foreground">PNG, JPG, GIF up to 2MB</span>
                                </Label>
                            </div>
                        </div>

                        <input
                            id="signature-upload"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onFileChange"
                        />

                        <!-- Success Message -->
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <div v-show="saved" class="rounded-lg bg-green-50 p-3 text-sm text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>
                                Signature uploaded successfully
                            </div>
                        </Transition>
                    </div>

                    <!-- Info Box -->
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <h4 class="mb-2 flex items-center text-sm font-semibold text-blue-900">
                            <i class="fas fa-info-circle mr-2"></i>
                            How it works
                        </h4>
                        <ul class="space-y-1 text-sm text-blue-800">
                            <li>• The signature will automatically appear on all employment contracts</li>
                            <li>• It will also be included on employment verification letters</li>
                            <li>• The signature replaces the "HR Department" text signature line</li>
                            <li>• For best results, use a transparent PNG with your actual signature</li>
                        </ul>
                    </div>
                </div>
            </div>
    </SettingsLayout>
</template>
