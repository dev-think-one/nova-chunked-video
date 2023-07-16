import IndexField from './components/IndexField.vue'
import DetailField from './components/DetailField.vue'
import FormField from './components/FormField.vue'

Nova.booting((app, store) => {
    app.component('IndexChunkedVideo', IndexField)
    app.component('DetailChunkedVideo', DetailField)
    app.component('FormChunkedVideo', FormField)
})
