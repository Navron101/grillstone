// resources/js/app.ts (or app.js if you’re using TS via allowJs)
import { createApp, h, type DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'

// Vite will import async modules that have a { default: DefineComponent }
const pages = import.meta.glob('./Pages/**/*.vue') as Record<
  string,
  () => Promise<{ default: DefineComponent }>
>

createInertiaApp({
  resolve: (name: string) => {
    const loader = pages[`./Pages/${name}.vue`]
    if (!loader) throw new Error(`Inertia page not found: ${name}`)
    // Return a Promise<DefineComponent> to satisfy the typing
    return loader().then(mod => mod.default)
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
    app.use(plugin)
    app.use(ZiggyVue, (window as any).Ziggy)
    app.mount(el)
  },
})
