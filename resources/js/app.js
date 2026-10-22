import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'
import { MotionPlugin } from '@vueuse/motion'
import '@vuepic/vue-datepicker/dist/main.css'

const appName = import.meta.env.VITE_APP_NAME || 'Jepretin'

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue')

        return pages[`./Pages/${name}.vue`]()
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(MotionPlugin, {
                directives: {
                    'reveal-up': {
                        initial: { opacity: 0, y: 28 },
                        visibleOnce: {
                            opacity: 1,
                            y: 0,
                            transition: { duration: 700, ease: 'easeOut' },
                        },
                    },
                },
            })
            .mount(el)
    },
})
