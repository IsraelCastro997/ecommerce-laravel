document.addEventListener('alpine:init', () => {
 window.Alpine.data('productEditor', () => ({
  errors: {}, touched: {}, saving: false, message: '', failure: '', updateUrl: '',
  validate(field) {
   if (!field.name || field.type === 'hidden') return;
   if (field.name === 'available_until' || field.name === 'available_from') {
    const start=this.$refs.form.elements.namedItem('available_from');
    const end=this.$refs.form.elements.namedItem('available_until');
    end.setCustomValidity(start.value && end.value && end.value < start.value ? 'La vigencia final debe ser posterior al inicio.' : '');
   }
   this.touched[field.name] = true;
   this.errors[field.name] = field.validity.valid ? [] : [field.validationMessage];
  },
  changed(field) {
   this.message = '';
   if (this.touched[field.name] || this.errors[field.name]?.length) this.validate(field);
  },
  async submit() {
   if (this.saving) return;
   const form = this.$refs.form;
   this.message = ''; this.failure = '';
   for (const field of form.elements) this.validate(field);
   if (!form.checkValidity()) { form.querySelector(':invalid')?.focus(); return; }
   this.saving = true;
   try {
    const body = new window.FormData(form);
    if (this.updateUrl) body.set('_method', 'PUT');
    const response = await fetch(this.updateUrl || form.action, {method:'POST', body, credentials:'same-origin', headers:{Accept:'application/json'}});
    if (!response.headers.get('content-type')?.includes('application/json')) throw new Error('No se pudo guardar. Revisa tu sesión e intenta de nuevo.');
    const data = await response.json();
    if (response.status === 422) {
     this.errors = data.errors || {};
     const first = Object.keys(this.errors)[0];
     form.elements.namedItem(first)?.focus();
     return;
    }
    if (!response.ok) throw new Error(data.message || 'No se pudo guardar el producto. Revisa tu sesión e intenta de nuevo.');
    this.errors = {}; this.updateUrl = data.update_url; this.message = data.message;
   } catch (error) { this.failure = error.message; }
   finally { this.saving = false; }
  },
 }));
});
