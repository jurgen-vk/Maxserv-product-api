import Alpine from 'alpinejs'

window.Alpine = Alpine

Alpine.store('toast', {
    visible: false,
    message: '',
    type: 'success',
    timer: null,
    show(message, type = 'success') {
        this.message = message
        this.type = type
        this.visible = true
        clearTimeout(this.timer)
        this.timer = setTimeout(() => this.hide(), 4000)
    },
    hide() {
        this.visible = false
    },
})

Alpine.store('import', {
    running: false,
    processed: 0,
    total: 0,
    get percent() {
        return this.total > 0 ? Math.round((this.processed / this.total) * 100) : 0
    },
    async start() {
        this.running = true

        try {
            const res = await fetch('/imports', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ importers: ['products'] }),
            })

            if (!res.ok) {
                const { error } = await res.json()
                throw new Error(error ?? 'Unknown error')
            }

            const { imports } = await res.json()
            const [{ id: importId }] = imports

            const source = new EventSource(
                document.body.dataset.mercureUrl + '?topic=' + encodeURIComponent('imports/' + importId)
            )

            source.onmessage = async (event) => {
                const update = JSON.parse(event.data)

                if (update.status === 'running') {
                    this.processed = update.processed
                    this.total = update.total
                    return
                }

                if (update.status === 'completed') {
                    source.close()
                    Alpine.store('toast').show(update.count + ' products imported successfully')
                    await this.refreshTable()
                    this.reset()
                }
            }

            source.onerror = () => {
                source.close()
                Alpine.store('toast').show('Import connection lost', 'error')
                this.reset()
            }
        } catch (err) {
            Alpine.store('toast').show('Import failed: ' + err.message, 'error')
            this.reset()
        }
    },
    reset() {
        this.running = false
        this.processed = 0
        this.total = 0
    },
    async refreshTable() {
        const tableUrl = new URL('/products', window.location.origin)
        tableUrl.searchParams.set('partial', 'table')
        new URLSearchParams(window.location.search).forEach((v, k) => {
            if (k !== 'partial') tableUrl.searchParams.set(k, v)
        })

        const tableRes = await fetch(tableUrl)
        document.getElementById('products-table-container').outerHTML = await tableRes.text()
    },
})

Alpine.data('importsList', (initial) => ({
    imports: initial,
    init() {
        const source = new EventSource(
            document.body.dataset.mercureUrl + '?topic=' + encodeURIComponent('imports/{id}')
        )

        source.onmessage = (event) => {
            const update = JSON.parse(event.data)

            if (this.imports[update.id]) {
                Object.assign(this.imports[update.id], update)
            }
        }
    },
}))

import.meta.glob('./**/*.js', { eager: true })
import.meta.glob('../../templates/**/*.js', { eager: true })

Alpine.start()
