import IndexField from './components/IndexField.vue'
import DetailField from './components/DetailField.vue'
import FormField from './components/FormField.vue'

Nova.booting((app, store) => {
    app.component('index-chunked-video', IndexField)
    app.component('detail-chunked-video', DetailField)
    app.component('form-chunked-video', FormField)
})
