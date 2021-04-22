Nova.booting((Vue, router, store) => {
  Vue.component('index-chunked-video', require('./components/IndexField'))
  Vue.component('detail-chunked-video', require('./components/DetailField'))
  Vue.component('form-chunked-video', require('./components/FormField'))
})
