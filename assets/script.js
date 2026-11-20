// Moving On Up Project 2 — client-side validation and confirmation prompts.
function isNumber(value, min, max) { const n = Number(value); return value.trim() !== '' && Number.isFinite(n) && n >= min && n <= max; }
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('#togglePassword');
  if (toggle) toggle.addEventListener('click', () => {
    const input = document.querySelector('#password');
    const hidden = input.type === 'password'; input.type = hidden ? 'text' : 'password';
    toggle.textContent = hidden ? 'Hide' : 'Show';
  });
  document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', e => {
    if (!validateForm(form)) { e.preventDefault(); return; }
    if (!confirm(form.dataset.confirm)) e.preventDefault();
  }));
  document.querySelectorAll('form:not([data-confirm])').forEach(form => form.addEventListener('submit', e => {
    if (!validateForm(form)) e.preventDefault();
  }));
});
function validateForm(form) {
  for (const field of form.querySelectorAll('[data-rule]')) {
    const rule = field.dataset.rule, value = field.value.trim();
    if (!value && !field.required) continue;
    let valid = true;
    if (rule === 'name') valid = /^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/.test(value);
    if (rule === 'id') valid = /^\d{4}$/.test(value);
    if (rule === 'phone') valid = /^\d{10}$/.test(value.replace(/[\s()-]/g,''));
    if (rule === 'zip') valid = /^\d{5}$/.test(value);
    if (rule === 'state') valid = /^[A-Za-z]{2}$/.test(value);
    if (rule === 'positive') valid = isNumber(value, 0.01, 999999999);
    if (rule === 'email') valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    if (rule === 'date') valid = /^\d{4}-\d{2}-\d{2}$/.test(value) && !Number.isNaN(Date.parse(value));
    if (!valid) { alert('Please enter a valid value for ' + (field.dataset.label || field.name) + '.'); field.focus(); return false; }
  }
  const emailConfirm = form.querySelector('[name=email_confirmation]');
  const email = form.querySelector('[name=email]');
  if (emailConfirm?.checked && !email?.value.trim()) { alert('Enter an email address because email confirmation is requested.'); email.focus(); return false; }
  return true;
}
