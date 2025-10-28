<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';

import SettingsLayout from '@/layouts/settings/Layout.vue';
</script>

<template>
    <Head title="Appearance settings" />

    <SettingsLayout>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Appearance Settings</h1>
                <p class="text-sm text-gray-600 mt-1">Customize the look and feel of your application</p>
            </div>

            <div class="space-y-6">
                <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
                <AppearanceTabs />
            </div>
    </SettingsLayout>
</template>
