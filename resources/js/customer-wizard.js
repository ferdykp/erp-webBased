export default function customerWizard(openFlag) {
    return {
        step: 1,
        totalSteps: 2,
        init() { this.$watch(openFlag, open => { if (open) { this.step = 1; this.focusStep(); } }); },
        focusStep() {
            this.$nextTick(() => this.$refs.wizardForm.querySelector(`[data-wizard-step="${this.step}"] input, [data-wizard-step="${this.step}"] textarea`)?.focus());
        },
        validateAndNext() {
            const section = this.$refs.wizardForm.querySelector(`[data-wizard-step="${this.step}"]`);
            const invalid = [...section.querySelectorAll('input, select, textarea')].find(input => !input.checkValidity());
            if (invalid) { invalid.reportValidity(); return; }
            if (this.step < this.totalSteps) { this.step++; this.focusStep(); }
        },
        prevStep() { if (this.step > 1) { this.step--; this.focusStep(); } },
        validateSubmission(event) {
            const invalid = [...this.$refs.wizardForm.elements].find(input => input.willValidate && !input.checkValidity());
            if (!invalid) return;
            event.preventDefault();
            this.step = Number(invalid.closest('[data-wizard-step]')?.dataset.wizardStep || 1);
            this.$nextTick(() => { invalid.focus(); invalid.reportValidity(); });
        },
    };
}
