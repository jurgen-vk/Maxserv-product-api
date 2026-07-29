import Alpine from 'alpinejs'

Alpine.data('selectSearch', ({ value = '', options = [] } = {}) => ({
    open: false,
    query: '',
    value,
    options,
    get filteredOptions() {
        return this.options.filter(o => o.label.toLowerCase().includes(this.query.toLowerCase()))
    },
    get selectedLabel() {
        return this.options.find(o => o.value === this.value)?.label ?? null
    },
    select(v) {
        this.value = v
        this.open = false
        this.query = ''
    },
}))
