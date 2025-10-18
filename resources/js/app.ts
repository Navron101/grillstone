import { createApp, h, defineComponent, type DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'

// Load BOTH trees eagerly so new files show up immediately
const upper = import.meta.glob('./Pages/**/*.vue', { eager: true }) as Record<string, any>
const lower = import.meta.glob('./pages/**/*.vue', { eager: true }) as Record<string, any>
const pages = { ...upper, ...lower }

function resolvePage(name: string): DefineComponent {
  // exact matches first
  const keyUpper = `./Pages/${name}.vue`
  const keyLower = `./pages/${name}.vue`
  if (pages[keyUpper]?.default) return pages[keyUpper].default
  if (pages[keyLower]?.default) return pages[keyLower].default

  // case-insensitive fallback
  const wanted = keyUpper.toLowerCase()
  const hit = Object.keys(pages).find(k => k.toLowerCase() === wanted)
  if (hit && pages[hit]?.default) return pages[hit].default

  // very safe fallback component (typed)
  console.error('[Inertia] Missing page:', name, 'Available:', Object.keys(pages))
  return defineComponent({
    name: 'MissingPage',
    props: { name: { type: String, required: true } },
    setup(props) {
      return () =>
        h('div',
          { style: 'padding:12px;margin:12px;border:1px solid #fecaca;color:#991b1b;background:#fff7ed' },
          `Missing page: ${props.name}`
        )
    }
  }) as DefineComponent
}

createInertiaApp({
  resolve: (name) => resolvePage(name),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
    app.use(plugin)
    app.use(ZiggyVue, (window as any).Ziggy)
    app.mount(el)
  },
})
